<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Dianah <dianah@silverstream.my>
* @version 1.0
* @created 16-Aug-2022
*/

namespace Snap\api\wallet;

use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Keys\Version1\AsymmetricPublicKey;
use ParagonIE\Paseto\Keys\Version1\AsymmetricSecretKey;
use ParagonIE\Paseto\Protocol\Version1;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Snap\api\exception\MyGtpWalletGatewayPaymentNotFound;
use Snap\api\exception\GeneralException;
use Snap\api\payout\BasePayout;
use Snap\api\payout\RedOnePayout;
use Snap\IObservation;
use Snap\object\MyAccountHolder;
use Snap\object\MyPaymentDetail;
use Snap\object\MyGoldTransaction;


class RedOne extends BaseWallet
{
    // Production endpoints
    protected const ENDPOINT_PAYMENT = "";
    protected const ENDPOINT_CHECK_PAYMENT = "";
    protected const ACTION_BUY = "buy";

    protected const CURRENCY_MYR = 'MYR';

    protected const STATUS_SUCCESS = 'SUCCESS';
    protected const STATUS_FAILED = 'FAILED';
    protected const STATUS_PENDING = 'PENDING';

    protected const PAYMENT_METHOD = 'WALLET';

    protected function __construct($app)
    {
        parent::__construct($app);

        $this->directurl = $app->getConfig()->{'mygtp.redone.buy.directurl'};
        $this->indirecturl = $app->getConfig()->{'mygtp.redone.buy.indirecturl'};
        $this->secretkey = $app->getConfig()->{'mygtp.redone.secretkey'};
        $this->querytransaction = $app->getConfig()->{'mygtp.redone.query.transaction'};
        $this->querynotoken = $app->getConfig()->{'mygtp.redone.query.notoken'};
        $this->paymentreversal = $app->getConfig()->{'mygtp.redone.payment.reversal'};
    }

    /**
     * Method to handle direct/indirect returns & callbacks from client
     * 
     * @param array $params         Params decoded by handler
     * @param string $postBody      Body of POST request if any
     */
    public function handleRequest($params, $postBody)
    {
        $this->log("------ Receive post body as ".$postBody." -----", SNAP_LOG_DEBUG);
        $decodedData = json_decode($postBody,true);

        $txnid = isset($decodedData['trxID']) ? $decodedData['trxID']:"";
        $amount = isset($decodedData['amount']) ? $decodedData['amount']:"";
        $description = isset($decodedData['description']) ? $decodedData['description']:"";
        $paymentid = isset($decodedData['paymentID']) ? $decodedData['paymentID']:"";
        $agentid = isset($decodedData['AgentID']) ? $decodedData['AgentID']:"";
        $responsemessage = isset($decodedData['ResponseMessage']) ? $decodedData['ResponseMessage']:"";
        $hashkey = isset($decodedData['HashKey']) ? $decodedData['HashKey']: "";
        $status = isset($decodedData['status']) ? $decodedData['status']: "";

        if($paymentid != ""){
            $check = $hashkey != "" ? $this->checkHashKey($decodedData): false;
            //$check = $this->checkHashKey($decodedData);
            //echo $check;exit;

            if($check){
                $mygoldtransaction = $this->app->mygoldtransactionStore()->getByField('id', $paymentid);
                if(!$mygoldtransaction){
                    throw MyGtpWalletGatewayPaymentNotFound::fromTransaction([], $paymentid);
                }

                $payment = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $mygoldtransaction->refno);
                if($payment) {
                    $this->processCallback($decodedData);
                }
                else{
                    throw MyGtpWalletGatewayPaymentNotFound::fromTransaction([], $paymentid);
                }
                
                return ['success' => true];
            }
            else {
                throw new \Exception("Something wrong. Please contact our service to inform the issue.");
            }
        }
        else{
            return ['success' => false];
        }
    }

    function checkHashKey($data){
        $bool = false;
        $checkstring = $data['paymentID'].$this->secretkey.$data['agentID'].$data['amount'];
        $checkcode = hash('sha256',$checkstring);

        if($checkcode == $data['HashKey']){
            $bool = true;
        }

        return $bool;
    }


    /**
     * Update the gateway request date and status
     * 
     * @param MyAccountHolder $accHolder            The account holder
     * @param MyPaymentDetail $payment              The payment detail
     * 
     * @return bool
     */
    function initializeTransaction($accHolder, $payment)
    {        
        $this->log("------ RedOne prepare transaction payment location -----", SNAP_LOG_DEBUG);
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        try {
            //get mygoldtransaction id to supply as payment ID
            $mygoldtransaction = $this->app->mygoldtransactionStore()->getByField('refno', $payment->sourcerefno);
            if(!$mygoldtransaction){
                $this->log(__METHOD__."(): Unable to find corresponding mygoldtransaction record. refno : ({$payment->sourcerefno})", SNAP_LOG_ERROR);
                throw new \Exception("Unable to find corresponding mygoldtransaction record. refno: ({$payment->sourcerefno})");
            }

            $paymentAmount = $payment->amount + $payment->customerfee;
            $data = [            
                'Amount' => number_format($paymentAmount,2,'.',''),
                'paymentID' => $mygoldtransaction->id,
                'directResponseUrl' => $this->directurl,
                'IndirectResponseUrl' => $this->indirecturl,
                'Description' => $payment->paymentrefno,
                'token' => $payment->token
            ];
    
            $formatted = $this->formatData($data);    
            $data['secureHash'] = $this->signData($formatted);
            $testData = json_encode($data);
            $this->log("------ Data details ".$testData." -----", SNAP_LOG_DEBUG);
            $payment->signeddata = $data['secureHash'];
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['signeddata']);  

            $jsonEncodedData = json_encode($data);

            $this->log("------ RedOne Create transaction request -----", SNAP_LOG_DEBUG);
            $this->log($jsonEncodedData, SNAP_LOG_DEBUG);
            $this->log("------ RedOne Create transaction request END -----", SNAP_LOG_DEBUG);
            
            $dataResponse['data']['refno'] = $payment->paymentrefno;
            $dataResponse['data']['details'] = $data;

            $payment->gatewaystatus = 'PENDING';
            $payment->status = MyPaymentDetail::STATUS_PENDING_PAYMENT;
            $payment->location = json_encode($dataResponse['data']);
 
            $payment->requestedon = $now;
            $payment = $this->app->mypaymentdetailStore()->save($payment);

            $this->log("------ RedOne prepare transaction payment location END -----", SNAP_LOG_DEBUG);
            $jsonEncodedRequestData = json_encode($data);
            $this->logApiRequest($jsonEncodedRequestData, $payment);

            return $payment;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $err = $e->getMessage();
            $this->log("--- RedOne Create Transaction Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error: %d", $e->getMessage()), SNAP_LOG_ERROR);
            $this->log("--- RedOne Create Transaction Error End ---", SNAP_LOG_ERROR);
            return false;
        }
    }
    
    /**
     * Get status of transaction from WavPay
     * 
     * @param MyPaymentDetail $paymentDetail
     * 
     */
    function getPaymentStatus($paymentDetail)
    {   
        try {     
            // $response = $this->doSendGetTransactionStatus($paymentDetail);
            $response = $this->fetchTransactionStatus($paymentDetail);
            $returnArray = json_decode($response->getBody()->getContents(), true);
            

            // Check for response status first. The transaction status is inside data array
            if(isset($returnArray['data'])){
                $data = $this->formatArrayResponse($returnArray);
                if (self::STATUS_SUCCESS === strtoupper($data['status'])) {
                    $this->log("------ RedOne transaction status found for {$paymentDetail->sourcerefno} -----", SNAP_LOG_DEBUG);
                    $this->processCallback($data);
    
                    return $data;
                }
    
                $this->log("------ RedOne transaction status not found for {$paymentDetail->sourcerefno} -----", SNAP_LOG_ERROR);
                return $returnArray;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- RedOne Get Transaction Status Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- RedOne Get Transaction Status Error End ---", SNAP_LOG_ERROR);
        }
    }

    public function formatArrayResponse($arr){
        
        $data['AgentID'] = $arr['data']['agentID'];
        $data['trxID'] = $arr['data']['txnID'];
        $data['paymentID'] = $arr['data']['paymentID'];
        $data['status'] = $arr['data']['retMsg'];
        $data['amount'] = abs($arr['data']['amount']);
        $data['description'] = "";
        $data['ResponseMessage'] = "";
        $data['HashKey'] = "";
        $data['data'] = $arr['data'];

        return $data;
    }
    
    /**
     * Verifies the callback from wavpay
     * 
     * @return boolean
     */
    function verifyPaymentResponse($response,$callback = false)
    {
        $formattedData = $this->formatResponseData($response,$callback);
        return $this->verifySignedDataResponse($response['postSecureHash'], $formattedData);
    }

    protected function processCallback($response, $skipAppendResponse = false)
    {
        //redone status : SUCCESS/PENDING/FAILED
        $mygoldtransaction = $this->app->mygoldtransactionStore()->getByField('id', $response['paymentID']);
        if (! $mygoldtransaction) {
            $this->log(__METHOD__."(): Unable to find corresponding mygoldtransaction record. gtr_id : ({$response['paymentID']})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding mygoldtransaction record. gtr_id: ({$response['paymentID']})");
        }

        $payment = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $mygoldtransaction->refno);
        if (! $payment) {
            $this->log(__METHOD__."(): Unable to find corresponding payment. pdt_sourcerefno : ({$mygoldtransaction->refno})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding payment. pdt_sourcerefno: ({$mygoldtransaction->refno})");
        }

        $this->logDebug(__METHOD__."(): Received response from RedOne");
        $this->logDebug(json_encode($response));

        if (!$skipAppendResponse) {
            $payment->gatewayrefno = $response['trxID'];
            $payment->remarks = $this->appendResponses($payment->remarks, $response);
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['remarks','gatewayrefno']);
        }

        $payment->verifiedamount = number_format(floatval($payment->amount) + floatval($payment->customerfee), 2, '.', '');//add wallet fee
        $payment->gatewaystatus = $response['status'];
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['verifiedamount', 'gatewaystatus']);

        $initialStatus = $payment->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        // Set status of the payment
        $skip = false;
        $state = $this->app->mygtptransactionManager()->getPaymentDetailStateMachine($payment);
        if (self::STATUS_SUCCESS == strtoupper($response['status'])) {
            $action = IObservation::ACTION_CONFIRM;
            $payment->status = MyPaymentDetail::STATUS_SUCCESS;
            $payment->successon = $now;
        }else{
            $action = IObservation::ACTION_REJECT;
            $payment->status = MyPaymentDetail::STATUS_FAILED;
            $payment->failedon = $now;
        }

        if (!$skip) {
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['gatewaystatus', 'status', 'successon', 'failedon', 'refundedon', 'verifiedamount']);
            $this->notify(new IObservation($payment, $action, $initialStatus, ['response' => $response]));
        }
    }

    /**
     * Sign and base64 encode the data to be sent to MCash
     *
     * @param  string $unsignedData
     * @return string
     */
    protected function signData($unsignedData)
    {
        $this->logDebug(__METHOD__."() Signing data: $unsignedData");
        //return base64_encode(hash('sha256', $unsignedData));
        return hash('sha256', $unsignedData);
    }

    /**
     * Verifies the signed data
     * 
     * @return boolean
     */
    protected function verifySignedDataResponse($signedData, $plainData)
    {        
        //$signedData = base64_encode($signedData);
        $rawSigned = strtoupper(hash('sha256', $plainData));

        $this->log("WavPay - comparing hashing from post hash => ".$signedData." & signeddata => ".$rawSigned , SNAP_LOG_ERROR);
        return $rawSigned === $signedData; //because wavpay passing securehash in uppercase
    }

    protected function verifySignedData($signedData, $plainData = null)
    {
        $this->logDebug(__METHOD__."(): Verifying data signed: $signedData");

        $pubKeyLoc = $this->app->getConfig()->{'mygtp.wavpay.publickeypath'};
        $key = file_get_contents($pubKeyLoc);

        if (0 >= strlen($key)) {
            throw new \Exception("Unable to retrieve public key");
        }

        try {
            $publicKey   = new AsymmetricPublicKey($key, new Version1()); 
            $verifiedData = Version1::verify($signedData, $publicKey);
            return json_decode($verifiedData, true);
        } catch (PasetoException $e) {
            $this->logDebug(__METHOD__."(): " . $e->getMessage());
            throw new \Exception("Unable to verify signed data");
        }
        
    }

    /**
     *  key         Key provided by mcash
     *  orderId     User token send by MCash when open merchant H5 page
     *  amount      Amount of transaction
     *  action      Transaction action: buy / sell
     *  merchant    The merchant id provided by MCash
     */
    protected function formatData($arr)
    {              
        //sort the array first
        ksort($arr);  
        $arr[] = $this->secretkey;
        $formatted = implode("|", $arr);
        $this->log("------ Formatted data before hashing ".$formatted." -----", SNAP_LOG_DEBUG);
        return $formatted;
    }

    protected function formatResponseData($arr,$callback)
    {
        $tmpArr = [];
        $tmpArr[] = $arr['postMerchantId'];
        $tmpArr[] = $arr['postOrderId'];
        //if(!$callback) $tmpArr[] = $arr['postPaymentMethod'];
        $tmpArr[] = $arr['postPaymentMethod'];
        $tmpArr[] = $arr['postStatus'];
        $tmpArr[] = $arr['postUserId'];         
        $tmpArr[] = $this->secretkey;        

        $formatted = implode("|", $tmpArr);
        $this->log("------ Formatted data before hashing: After payment ".$formatted." -----", SNAP_LOG_DEBUG);
        return $formatted;
    }

    protected function formatCheckPaymentData($arr)
    {
        $tmpArr = [];
        $tmpArr[] = $this->key;
        $tmpArr[] = $arr['orderId'];
        $tmpArr[] = $arr['action'];        
        $tmpArr[] = $this->merchantId;
        $formatted = implode("", $tmpArr);
        return $formatted;
    }

    /**
     * Appends the responses
     * 
     * @param string    $existing   Existing remarks
     * @param array     $response   Response from MCash 
     * 
     * @return string   Appended remarks
     */
    protected function appendResponses($existing, $response)
    {
        $currentRemarks = json_decode($existing, true);
        $now = new \DateTime();

        $currentRemarks[$now->format('Y-m-d H:i:s')] = $response;
        return json_encode($currentRemarks);
    }

    /**
     * Does the actual request to MCash server to get transaction status
     * 
     * @param MyPaymentDetail $paymentDetail        The payment detail     
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendGetTransactionStatus($paymentDetail)
    {
        $mygoldtransaction = $this->app->mygoldtransactionStore()->getByField('refno', $paymentDetail->sourcerefno);

        $client                 = $this->httpClientFactory($paymentDetail);        
        $url                    = $this->querytransaction."/".$mygoldtransaction->id;
        $response               = $client->get($url);
        
        return $response;
    }

    protected function httpClientFactory($paymentDetail, $addToken = true, $token = "")
    {
        $auth_token = "Bearer ".$paymentDetail->token;
        if($token != ""){
            $auth_token = "Bearer ".$token;
        }
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $auth_token
            ],
        ];

        return new \GuzzleHttp\Client($options);
    }

    function sendPendingRefundStatus($paymentDetail, $token = ""){
        $this->log("WALLET CLASS::SendPendingRefundStatus", SNAP_LOG_DEBUG);
        try{
            $mygoldtransaction = $this->app->mygoldtransactionStore()->getByField('refno', $paymentDetail->sourcerefno);

            $client                 = $this->httpClientFactory($paymentDetail, true, $token);        
            $url                    = $this->paymentreversal."/".$paymentDetail->gatewayrefno; //.$mygoldtransaction->id;
            $this->log("url: {$url}", SNAP_LOG_DEBUG);
            //echo "url: ".$url."\n";

            $response               = $client->get($url);

            // echo "response: ".$response."\n";
            $returnArray = json_decode($response->getBody()->getContents(), true);

            $now = new \DateTime();
            $now->setTimezone($this->app->getUserTimezone());
            
            if(isset($returnArray['data'])){
                $msg = strtoupper($returnArray['data']);
                if(str_contains($msg,'SUCCESSFUL')){
                    $paymentDetail->remarks = $this->appendResponses($paymentDetail->remarks, $returnArray);
                    $paymentDetail->refundedon = $now;

                    $paymentDetail = $this->app->mypaymentdetailStore()->save($paymentDetail);

                    $mygoldtransaction->status = MyGoldTransaction::STATUS_REFUNDED;
                    $mygoldtransaction->modifiedon = $now;

                    $mygoldtransaction = $this->app->mygoldtransactionStore()->save($mygoldtransaction);

                    
                }
            }
        }
        catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();

            //echo "sendPendingRefundStatus-> ".$e->getCode().": ".$body."\n";
            $this->log("--- RedOne Get Transaction Status Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- RedOne Get Transaction Status Error End ---", SNAP_LOG_ERROR);
        }
        catch (Exception $e){
            //echo $e->getMessage()."\n";
            $this->log("sendPendingRefundStatus failed-> ".$e->getMessage(), SNAP_LOG_ERROR);
        }
        return 1;
    }

    function processFailedPaymentStatus($paymentDetail, $token = "")
    {   
        $this->log("WALLET CLASS::processFailedPaymentStatus", SNAP_LOG_DEBUG);
        try {     
            $mygoldtransaction = $this->app->mygoldtransactionStore()->getByField('refno', $paymentDetail->sourcerefno);

            // first api
            $client                 = $this->httpClientFactory($paymentDetail, true, $token);        
            $url                    = $this->querytransaction."/".$mygoldtransaction->id;
            $response               = $client->get($url);

            $returnArray = json_decode($response->getBody()->getContents(), true);
            

            // Check for response status first. The transaction status is inside data array
            if(isset($returnArray['data'])){
                $data = $this->formatArrayResponse($returnArray);
                if (self::STATUS_SUCCESS === strtoupper($data['status'])) {
                    $this->log("------ RedOne transaction status found for {$paymentDetail->sourcerefno} -----", SNAP_LOG_DEBUG);
                    // save the response first
                    $paymentDetail->gatewayrefno = $data['trxID'];
                    $paymentDetail->remarks = $this->appendResponses($paymentDetail->remarks, $data);
                    $paymentDetail = $this->app->mypaymentdetailStore()->save($paymentDetail, ['remarks','gatewayrefno']);
            
                    $paymentDetail->verifiedamount = $paymentDetail->amount;
                    $paymentDetail->gatewaystatus = $data['status'];
                    $paymentDetail = $this->app->mypaymentdetailStore()->save($paymentDetail, ['verifiedamount', 'gatewaystatus']);


                    // then call another api to refund
                    $client                 = $this->httpClientFactory($paymentDetail, true, $token);        
                    $url                    = $this->paymentreversal."/".$paymentDetail->gatewayrefno; //.$mygoldtransaction->id;
                    $this->log("url: {$url}", SNAP_LOG_DEBUG);

                    $response               = $client->get($url);

                    $returnArray = json_decode($response->getBody()->getContents(), true);

                    $now = new \DateTime();
                    $now->setTimezone($this->app->getUserTimezone());
                    
                    if(isset($returnArray['data'])){
                        $msg = strtoupper($returnArray['data']);
                        if(str_contains($msg,'SUCCESSFUL')){
                            $paymentDetail->remarks = $this->customAppendResponses($paymentDetail->remarks, $returnArray);
                            $paymentDetail->refundedon = $now;

                            $paymentDetail = $this->app->mypaymentdetailStore()->save($paymentDetail);

                            $mygoldtransaction->status = MyGoldTransaction::STATUS_REFUNDED;
                            $mygoldtransaction->modifiedon = $now;

                            $mygoldtransaction = $this->app->mygoldtransactionStore()->save($mygoldtransaction);
                        }
                    }
                    
                }
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- processFailedPaymentStatus catch exception ---", SNAP_LOG_DEBUG);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_DEBUG);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_DEBUG);
            $this->log($body, SNAP_LOG_DEBUG);
            $this->log("--- processFailedPaymentStatus catch exception End ---", SNAP_LOG_DEBUG);
        }

        return 1;
    }

    protected function customAppendResponses($existing, $response){
        $currentRemarks = json_decode($existing, true);
        $now = new \DateTime('+2 seconds');

        $currentRemarks[$now->format('Y-m-d H:i:s')] = $response;
        return json_encode($currentRemarks);
    }

    function fetchTransactionStatus($paymentDetail){
        $mygoldtransaction = $this->app->mygoldtransactionStore()->getByField('refno', $paymentDetail->sourcerefno);
        $accountholder = $this->app->myaccountholderStore()->getByField('id',$paymentDetail->accountholderid);
        $agentID = $accountholder->partnercusid;
        if($this->app->getConfig()->{'mygtp.redone.test.id'} != ''){
            $agentID = $this->app->getConfig()->{'mygtp.redone.test.id'};
        }

        $nohash = $mygoldtransaction->id.$this->secretkey.$agentID;
        $hash = hash('sha256',$nohash);

        $postdata = [
            "paymentID" => $mygoldtransaction->id,
            "agentID" => $agentID,
            "hashKey" => $hash
        ];
        $options = [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ];
        $client                 = new \GuzzleHttp\Client($options);        
        $url                    = $this->querynotoken;
        $response               = $client->post($url,['json' => $postdata]);
        
        return $response;
    }
}


?>