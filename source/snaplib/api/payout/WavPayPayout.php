<?php
/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Azam <azam@silverstream.my>
* @version 1.0
* @created 20-10-2021
*/

namespace Snap\api\payout;

use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Keys\Version1\AsymmetricPublicKey;
use ParagonIE\Paseto\Keys\Version1\AsymmetricSecretKey;
use ParagonIE\Paseto\Protocol\Version1;
use Snap\api\exception\GeneralException;
use Snap\api\exception\MyGtpWalletGatewayPaymentNotFound;
use Snap\App;
use Snap\IObservation;
use Snap\object\MyAccountHolder;
use Snap\object\MyDisbursement;

class WavPayPayout extends BasePayout 
{
    protected const ENDPOINT_PAYMENT = "https://mapi.wavpay.net/api/user/merchantuserid/user-credit-transaction";
    //protected const ENDPOINT_CHECK_PAYMENT = "https://www.mcash.my/cross/hfive/transaction/status";
    protected const ACTION_SELL = "SELL";

    protected const WALLETTYPE_MYKAD = "MYKAD";
    protected const WALLETTYPE_OTHER = "PASSPORT";

    protected const STATUS_SUCCESS = 'RECEIVED';
    protected const STATUS_FAILED = 0;
    
    protected $key = null;
    protected $merchantId = null;
    protected $callbackUrl = null;
    protected $redirectUrl = null;

    protected function __construct($app)
    {
        parent::__construct($app);
        
        $this->merchantAccId    = $app->getConfig()->{'mygtp.wavpay.merchantidsetbywavpay'};
        $this->secretkey        = $app->getConfig()->{'mygtp.wavpay.secretkey'};

        if (! $this->merchantAccId) {
            throw new \Exception("WavPay merchant ID not provided");
        }
    }

    public function handleResponse($params, $body)
    {
        // wavpay Callback        
        $this->logInfo(__METHOD__."() Disbursement callback received: " . $body);
        $params = array_merge($params, json_decode($body, true) ?? []);

        $gatewayRef = $params['orderId'];
        $errMsgTag = ['transactionrefno' => ''];
        if (0 < strlen($gatewayRef)) {
            $disbursement = $this->app->mydisbursementStore()->getByField('transactionrefno', $gatewayRef);
            $errMsgTag['transactionrefno'] = $gatewayRef;
        }
        if (! $disbursement) {
            $this->logDebug(__METHOD__."(): No disbursement found for response {". json_encode($params)."}");
            throw MyGtpWalletGatewayPaymentNotFound::fromTransaction([], $errMsgTag);
        }

        $this->logApiResponse(json_encode($params), $disbursement);

        // Verify signed data from callback
        if (! $this->verifyPaymentResponse($params)) {
            $this->log(__METHOD__. "(): Cannot verify signed data.", SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            throw new \Exception("Signed data verification failed.");
        }
        $this->logDebug(__METHOD__."() Payment response verified.");

        
        $this->processCallback($params);

        return ['success' => true];
    }

    /**
     * Send a payout request to provider
     * 
     * @param MyAccountHolder $accountHolder 
     * @param MyDisbursement $disbursement 
     * @return mixed
     */
    public function createPayout($accountHolder, $disbursement)
    {
        $this->log("------ WavPay prepare transaction payment/disbursement location -----", SNAP_LOG_DEBUG);

        //fortest purpose
        //$token = 'v1.public.eyJyZXF1ZXN0RGF0ZSI6IjIwMjIxMTA4MTE1NzUxIiwiY2hhbm5lbFVzZXJJZCI6IjgwOGNmODQ0LTA4MGUtNDgyMi05ODM0LTRmZmI1YTBjMzkwZiIsImlkVHlwZSI6Ik4iLCJpZE5vIjoiODgwMzEzLTA1LTUwMzYiLCJlbWFpbCI6ImRpeWF6ODhAZ21haWwuY29tIiwidGVsSHAiOiI2MDExNzI3MDgzOTQiLCJjaGFubmVsIjoiV0FWUEFZIiwibmFtZSI6Ik51cmRpYW5haCBiaW50aSBLYW1hcnVkaW4iLCJjaGFubmVsTG9naW5Ub2tlbiI6eyJ0eXBlIjoiU3RyaW5nIiwiZGF0YSI6IjMwNTNjNDhjLWIzODAtNGNkOC1iMTFjLWM4YTQzN2M2YTdmZCJ9fTWORvRSwoCEF2jtJrR7sNTsgVI1198WED22iwdlMDzPthXOyGXA-sCclRA-QxgbDYusmkLLZj4RjBwj09TD5VqfzRTBQ4VQ0hpEowobsn5p13_s_PhGyxWws5WR7BpCEEB0OTVMR7LDw6AU45YOcwI8OzT21jQ9YLAx51CqMNTtWelJvBMnJcSER_VO3EngBfs8JniIxjd1o4FafS5Iatri_fb3tasvX4jJbONIynpymF2P1JykO_9hVgqSWk4PwEMRrHljT4NzUA95NOuZ8uBVVN3sQsWB-Z7IlKBB0NBiN8Tf-_iFLQ0ck2XEVP6kL1ag7sBjl8QZiCN6P4KqK4k';
        //$decodedData    = $this->verifySignedData($token);    
        $decodedData    = $this->verifySignedData($disbursement->token);   
        $decodedLog = json_encode($decodedData);

        $this->log("------ Decoded data details ".$decodedLog." -----", SNAP_LOG_DEBUG);
    
        if($decodedData['idType'] == 'N') $idType = self::WALLETTYPE_MYKAD;
        else $idType = self::WALLETTYPE_OTHER;

        $dataToEncrypt = json_encode([        
            'partnerId'                 => $this->merchantAccId,
            'userId'                    => $decodedData['channelUserId'],
            'userNameAsID'              => $decodedData['name'],
            'userIDType'                => $idType,
            'userIdNumber'              => $decodedData['idNo'],
            'externalReferenceNumber'   => $disbursement->transactionrefno,
            'externalId'                => $disbursement->refno,
            'purpose'                   => self::ACTION_SELL.' - WAVGOLD',
            'amount'                    => number_format(floatval($disbursement->amount) + floatval($disbursement->fee), 2, '.', '')
            //'userEmail'                 => $decodedData['email'],
        ]);

        $data = [        
            'partnerId'                 => $this->merchantAccId,
            'userId'                    => $decodedData['channelUserId'],
            'userNameAsID'              => $decodedData['name'],
            'userIDType'                => $idType,
            'userIdNumber'              => $decodedData['idNo'],
            'externalReferenceNumber'   => $disbursement->transactionrefno,
            'externalId'                => $disbursement->refno,
            'purpose'                   => self::ACTION_SELL.' - WAVGOLD',
            'amount'                    => number_format(floatval($disbursement->amount) + floatval($disbursement->fee), 2, '.', ''),
            'userEmail'                 => $decodedData['email'],
        ];
        
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());                
        //$formatted = $this->formatData($data);
        $data['signeddata'] = $this->signData($dataToEncrypt);

        $locationPayout = static::ENDPOINT_PAYMENT;
        $updateLocationPayout = str_replace("merchantuserid",$this->merchantAccId,$locationPayout);

        $data['location'] = $updateLocationPayout;

        //$paywallLocation =  http_build_query($data);
        $paywallLocation =  json_encode($data);

        $disbursement->signeddata = $data['signeddata'];
        $disbursement->requestedon = $now;
        $disbursement->location = $paywallLocation;
        $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['signeddata', 'requestedon', 'location']);
        
        $this->log("------ WavPay prepare transaction payment/disbursement location END -----", SNAP_LOG_DEBUG);
        $jsonEncodedRequestData = json_encode($data);
        $this->logApiRequest($jsonEncodedRequestData, $disbursement);

        return $paywallLocation;
    }

    /**
     * Handle expired transaction called from the job file after a certain timeout
     *
     * @param  MyDisbursement $disbursement
     * @return void
     */
    public function handleExpiredTransaction($disbursement)
    {        
        $this->log(__METHOD__ . ": No payment response received for {$disbursement->transactionrefno}, notify cancelling order" , SNAP_LOG_ERROR);

        // Notify the listener that the order should be expired / cancelled
        $returnArray = $this->getPayoutStatus($disbursement);

        $responseStatus = strtoupper($returnArray['data']['status']);

        if (array_key_exists('data', $returnArray) && self::STATUS_SUCCESS !== $responseStatus) {
            $this->notify(new IObservation($disbursement, IObservation::ACTION_CANCEL, $disbursement->status));
        }
    }

    /**
     * Verifies the callback from WavPay
     * 
     * @return boolean
     */
    function verifyPaymentResponse($response)
    {
        $formattedData = $this->formatResponseData($response);
        return $this->verifySignedData($response['hash'], $formattedData);
    }

    /**
     * Manually reads payout status from provider's server
     * 
     * @param MyDisbursement $disbursement 
     * 
     * @return array
     */
    public function getPayoutStatus($disbursement)
    {
        try {     
            $response = $this->doSendGetTransactionStatus($disbursement);
            $returnArray = json_decode($response->getBody()->getContents(), true);

            //print_r($returnArray);

            /*$returnArray = array(
                'code'  => 200,
                'msg'   => null,
                'data'  => array(
                    "externalReferenceNumber"=> "GT202211071500003",
                    "externalReferenceId"=> "GT202211071500003",
                    "transactionDateTime"=> "2022-11-04T13:55:09.693",
                    "status"=> "UNRECEIVED"
                )
            );*/

            $responseStatus = strtoupper($returnArray['data']['status']);
            $this->log(__METHOD__ . ": Response from WavPay wallet {$url} - ".json_encode($returnArray) , SNAP_LOG_DEBUG);

            // Check for response status first. The transaction status is inside data array
            if (self::STATUS_SUCCESS === $responseStatus && array_key_exists('data', $returnArray)) {
                $this->log("------ WavPay transaction status found for Disbursement {$disbursement->transactionrefno} -----", SNAP_LOG_DEBUG);
                $this->processCallback($returnArray['data']);

                return $returnArray;
            }

            $this->log("------ WavPay transaction status not found for Disbursement {$disbursement->transactionrefno} -----", SNAP_LOG_ERROR);
            return $returnArray;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- WavPay Get Transaction Status Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- WavPay Get Transaction Status Error End ---", SNAP_LOG_ERROR);
        }
    }

    /**
     * Sign and base64 encode the data to be sent to wavpay
     *
     * @param  string $unsignedData
     * @return string
     */
    protected function signData($unsignedData)
    {
        $this->logDebug(__METHOD__."() Signing data before: $unsignedData");
        $pubKeyLoc = $this->app->getConfig()->{'mygtp.wavpay.publickeypath'};
        $key = file_get_contents($pubKeyLoc);

        $privateKey = new AsymmetricSecretKey($key, new Version1());           
        $signedData = Version1::sign($unsignedData, $privateKey);

        $this->logDebug(__METHOD__."() Signing data after : $signedData");

        return $signedData;
    }

    /**
     * Verifies the signed data
     * 
     * @return boolean
     */
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
     *  key         Key provided by wavpay
     *  orderId     User token send by wavpay when open merchant H5 page
     *  amount      Amount of transaction
     *  action      Transaction action: buy / sell
     *  merchant    The merchant id provided by wavpay
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

    protected function formatResponseData($arr)
    {
        $tmpArr = [];
        $tmpArr[] = $this->key;
        $tmpArr[] = number_format($arr['amount'], 2, '.', '');
        $tmpArr[] = $arr['status'];        
        $tmpArr[] = $arr['orderId'];
        $tmpArr[] = $arr['type'];        
        $tmpArr[] = $this->merchantId;
        $formatted = implode("", $tmpArr);
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

    protected function processCallback($response, $skipAppendResponse = false)
    {
        $transactionRefNo = $response['externalReferenceNumber'];
        $wavpayStatus = intval($response['status']);
        $disbursement = $this->app->mydisbursementStore()->getByField('transactionrefno', $transactionRefNo);
        if (! $disbursement) {
            $this->log(__METHOD__."(): Unable to find corresponding payment. Gateway ref: ({$transactionRefNo})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding payment. Gateway ref: ({$transactionRefNo})");
        }

        $this->logDebug(__METHOD__."(): Received response from WavPay");
        $this->logDebug(json_encode($response));

        if (!$skipAppendResponse) {
            $disbursement->remarks = $this->appendResponses($disbursement->remarks, $response);
            $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['remarks']);
        }

        $initialStatus = $disbursement->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        $skip = false;
        if (self::STATUS_SUCCESS == $wavpayStatus) {
            $this->log(__METHOD__ . ": Success payment response received for {$disbursement->transactionrefno}}" , SNAP_LOG_DEBUG);

            $action = IObservation::ACTION_CONFIRM;            
            $disbursement->disbursedon = $now;
            $disbursement->verifiedamount = number_format(floatval($disbursement->amount) + floatval($disbursement->fee), 2, '.', ''); //no amount receive from response
            $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['disbursedon', 'verifiedamount', 'gatewayrefno', 'remarks']);
            
            $this->log(__METHOD__ . ": Success payment response received for {$transactionRefNo}, code {$wavpayStatus}" , SNAP_LOG_DEBUG);
        } elseif (self::STATUS_SUCCESS != $wavpayStatus) {            
            $this->log(__METHOD__ . ": Failed payment response received for {$transactionRefNo}, code {$wavpayStatus}" , SNAP_LOG_ERROR);
            $action = IObservation::ACTION_CANCEL;
        } else {
            $this->log("Disbursement {$disbursement->transactionrefno} callback received but no action was taken.", SNAP_LOG_ERROR);
            $skip = true;
        }                

        if (!$skip) {
            $this->notify(new IObservation($disbursement, $action, $initialStatus, ['response' => $response]));
        }
    }

    /**
     * Appends the responses
     * 
     * @param string    $existing   Existing remarks
     * @param array     $response   Response from WavPay 
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
     * Does the actual request to wavpay server to get transaction status
     * 
     * @param MyDisbursement $disbursement        The disbursement object
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendGetTransactionStatus($disbursement)
    {
        $signeddata = $disbursement->signeddata;

        $data = [            
            'payload' => $signeddata
        ];
        
        $client          = $this->httpClientFactory(false);         
        $locationPayout  = static::ENDPOINT_PAYMENT;
        $url             = str_replace("merchantuserid",$this->merchantAccId,$locationPayout);
        $response        = $client->post($url, ['json' => $data]);

        $this->log(__METHOD__ . ": Trigger WavPay wallet {$url} to get sell response with payload {$signeddata}" , SNAP_LOG_DEBUG);

        return $response;
    }

    protected function httpClientFactory($addToken = true)
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ];

        return new \GuzzleHttp\Client($options);
    }
}
?>