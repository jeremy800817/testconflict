<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
/* 
    this sms implementation only for `M1 sms gateway`
    hashing checksum, method, is solely for `M1` ONLY
 */

Namespace Snap\manager;

Use \Snap\InputException;
Use \Snap\TLogging;
Use \Snap\IObserver;
Use \Snap\IObservable;
Use \Snap\IObservation;
Use \GuzzleHttp\Client;
Use \GuzzleHttp\Request;
Use \Snap\object\SMSOutBox;

class SmsManager implements IObservable
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;
    
    private $app = null;

    public $phoneNumber;
    public $messagePayLoad;
    public $msgType;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function send(){
        $phoneNumber = $this->phoneNumber;
        $messagePayLoad = $this->messagePayLoad;
        $msgType = $this->msgType;

        if (!$phoneNumber){
            $this->log("SMS ERROR: Invalid Phone Number".$phoneNumber, SNAP_LOG_ERROR);
            throw new \Exception("Invalid Phone Number");
        }
        if (!$messagePayLoad){
            $this->log("SMS ERROR: Invalid Message".$messagePayLoad, SNAP_LOG_ERROR);
            throw new \Exception("Invalid Message");
        }
        if (!$msgType){
            $msgType = SMSOutBox::TYPE_SYSTEM;
            // throw new \Exception("Invalid Type");
        }
        // if number with 0110000000 will add in 6 for sms services standard of country code (60), malaysia only
        if (preg_match('/^[0]/', $phoneNumber)){
            $phoneNumber = '6'.$phoneNumber;
        }
        // validate - 60+{9/10}
        if (!$this->validatePhoneNumber($phoneNumber)){
            $this->log("SMS ERROR: Invalid phone number format : ".$phoneNumber, SNAP_LOG_ERROR);
            throw new \Exception("Invalid phone number format");
        }

        $baseUrl = $this->app->getConfig()->{'gtp.sms.requesturl'};
        $_smsGWUID = $this->app->getConfig()->{'gtp.sms.gwuid'};
        $_smsGWPWD = $this->app->getConfig()->{'gtp.sms.gwpwd'};
        $_smsTYPE = $this->app->getConfig()->{'gtp.sms.type'};
        $_smsChannel = $this->app->getConfig()->{'gtp.sms.channel'};
        // $baseUrl = 'https://203.223.144.231:8728/SMSGWDA/M1DA_SendBulkSMS_9042SKWP3.asp?';
        // $_smsGWUID = '88324-9sad9iufaDLa0023l';
        // $_smsGWPWD = 'c8zlca0i90aOoo3k2oal3l1la0s0';
        // $_smsTYPE = 'BULKSMS';
        // $_smsChannel = 'ACE2U';
        $fullUrl = $baseUrl;

        $rawComposite = $_smsGWUID . $_smsGWPWD . $_smsTYPE . $_smsChannel . $phoneNumber . $messagePayLoad;
        $checkDigitResult = $this->computeCheckDigit($rawComposite, 32, 661, 179, 241, 211, 17, 509);	

        $data = [
            'SMSGWUID' => $_smsGWUID,
            'SMSGWPWD' => $_smsGWPWD,
            'smsTYPE' => $_smsTYPE,
            'channel' => $_smsChannel,
            'CD' => $checkDigitResult,
            'mobileID' => $phoneNumber,
            'smsMessage' => urlencode($messagePayLoad) 
        ];
        foreach($data as $digestKey => $digestValue) {
            $paramArray[] = strtoupper($digestKey)."=".$digestValue;
        }
        $data = join('&', $paramArray);
        $fullUrl = $fullUrl.$data;

        $client = new \GuzzleHttp\Client(['verify' => false]);
        // Send an asynchronous request.
        try {

            $sentTime = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));

            $response = $client->request('POST', $fullUrl, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
            ]); 
            $responseBody = $response->getBody();
            // $data = simplexml_load_string($responseBody);
            // print_r($data);exit;
            // <SMSGWResponse>
            //     <HostName>203.223.144.231</HostName>
            //     <RetVal>0</RetVal>
            //     <ErrMsg>Message Receive correctly</ErrMsg>
            //     <MOBILEID>60120000000</MOBILEID>
            //     <MesgID>66232010D98045FD9D9</MesgID>
            // </SMSGWResponse>
            // RetVal 4 = error

            if('200' == $response->getStatusCode()) {
                $this->log("SapApiSender sending to $destination with data $body -- received response (".$responseBody.")", SNAP_LOG_DEBUG);
                $data = simplexml_load_string($responseBody);
                $errorMsg = $data->ErrMsg;
                $retryCount = $data->RetVal;
                $rawresponse = json_encode($data);
                $reference = $data->MesgID;
                $status = SMSOutBox::STATUS_SUCCESS;
            } else {
                $this->log(__METHOD__ . "() Unable to connect to SMS Service (".$phoneNumber.") ".$response->getStatusCode(), SNAP_LOG_ERROR);
                $responseBody = "Unexpected response from server with status code: ".$response->getStatusCode();
                $errorMsg = $e->getMessage();
                $retryCount = 0;
                $rawresponse = $responseBody;
                $reference = '';
                $status = SMSOutBox::STATUS_FAILED;
            }

            if ($retryCount && intval($retryCount) > 3){
                $this->log(__METHOD__ . "Retry more than 3 times, error occurred" . $e->getMessage() . "\nData: $body\nResponse:".$responseBody, SNAP_LOG_ERROR);
                throw new \Exception("Retry more than 3 times, ".$errorMsg); 
            }

            $phoneNo = $phoneNumber;
            $msg = $messagePayLoad;
            $operator = 'M1';
            $this->log("SMS OUTBOX SAVING: ".$phoneNo, SNAP_LOG_DEBUG);
            $this->smsOutboxInsert($phoneNo, $msg, $sentTime, $reference, $msgType, $operator, $errorMsg, $retryCount, $rawresponse, $status);
            // outbox, status = success

            return true;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = 'SMS ERROR: '.$phoneNo.' Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to SMS Service {$fullUrl} with error " . $e->getMessage() . "\nData: $body\nResponse:".$responseBody, SNAP_LOG_ERROR);
        }
        
    }

    public function computeCheckDigit($szString, $lLen, $lF1, $lF2, $lF3, $lF4, $lF5, $lF6){

        $szChkDigit = null;
        $total = null;
        $i = null;
        $digit = null;
        $szDbg = null;
        
        $total = 0;
        $szChkDigit = "";
        $szDbg = "";

        if (strlen($szString) >= 2) {
            while (strlen($szChkDigit) < $lLen) {
                for ($i = strlen($szString); $i >= 1; $i-=2) {
                    if ($i > 1 && strlen($szChkDigit) < $lLen) {
                        $digit = intval($this->utf8_char_code_at($this->char_at($szString, $i-1), 0));
                        $digit = $digit - $lF1;
                        $total = $total + ($digit % $lF2) * $lF3;
                        $total = $total + $digit;
                        $total = abs($total) ^ intval(strval($this->utf8_char_code_at($this->char_at($szString, $i-1),0)) + strlen($szString));
                        $szChkDigit = $szChkDigit . strtoupper(base_convert(($lF4 - ($total % $lF5) % $lF6), 10, 16));
                    }
                }
            }
            return substr($szChkDigit, 0, $lLen);
        } else {
            return "";
        }
    }

    private function utf8_char_code_at($str, $index)
    {
        $char = mb_substr($str, $index, 1, 'UTF-8');
    
        if (mb_check_encoding($char, 'UTF-8')) {
            $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            return hexdec(bin2hex($ret));
        }else {
            return null;
        }
    }

    private function char_at($str, $pos)
    {
        return $str{$pos};
    }

    private function smsOutboxInsert($phoneNo, $msg, $sentTime, $reference, $msgType, $operator, $errorMsg, $retryCount, $rawresponse, $status){

        // if ($status ..){
        // }

        $entity = $this->app->smsoutboxStore()->create([
            'phoneno' => $phoneNo,
            'msg' => $msg,
            'senttime' => $sentTime,
            'reference' => $reference,
            'msgtype' => $msgType,
            'operator' => $operator,
            'errormsg' => $errorMsg,
            'retrycount' => $retryCount,
            'rawresponse' => $rawresponse,
            'status' => $status,
        ]);

        $this->app->smsoutboxStore()->save($entity);

    }

    // malaysia number only -- 60
    private function validatePhoneNumber($phoneNumber){
        // [60][0-9]\d{9}$|^[60][0-9]\d{10}
        if (preg_match('/[\W]/', $phoneNumber)){
            return false;
        }
        return preg_match('/^(\+?6?01)[0-46-9]-*[0-9]{7,8}$/', $phoneNumber);
    }

}