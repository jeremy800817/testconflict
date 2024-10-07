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

class RedOnePayout extends BasePayout 
{
    protected const ENDPOINT_PAYMENT = "https://login.redone.com.my/ewalletpaymentgateway/api/v1/Ewallet/Sell";
    protected const ACTION_SELL = "SELL";

    protected const WALLETTYPE_MYKAD = "MYKAD";
    protected const WALLETTYPE_OTHER = "PASSPORT";

    protected const STATUS_SUCCESS = 'SUCCESS';
    protected const STATUS_FAILED = 0;
    
    protected $key = null;
    protected $merchantId = null;
    protected $callbackUrl = null;
    protected $redirectUrl = null;

    protected function __construct($app)
    {
        parent::__construct($app);
        
        $this->secretkey = $app->getConfig()->{'mygtp.redone.secretkey'};
        $this->directurl = $app->getConfig()->{'mygtp.redone.sell.directurl'};
        $this->querytransaction = $app->getConfig()->{'mygtp.redone.query.transaction'};
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
        $this->log("------ RedOne prepare transaction payment/disbursement location -----", SNAP_LOG_DEBUG);

        $mygoldtransaction = $this->app->mygoldtransactionStore()->getByField('refno', $disbursement->transactionrefno);
        $data = [        
            'amount'                 => number_format($disbursement->amount,2,'.',''),
            'description'            => $disbursement->refno,
            'paymentID'              => $mygoldtransaction->id,
            'hashKey'                => $this->generateHashKey($accountHolder, $disbursement, $mygoldtransaction),
            'directResponseUrl'      => $this->directurl
        ];

        $data['decrypt'] = $mygoldtransaction->id.$this->secretkey.$accountHolder->partnercusid.(number_format($disbursement->amount,2,'.',''));
        
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone()); 
        $signedData = $this->signData($data);

        $locationPayout = static::ENDPOINT_PAYMENT;
        // $updateLocationPayout = str_replace("merchantuserid",$this->merchantAccId,$locationPayout);

        $data['location'] = $locationPayout;

        //$paywallLocation =  http_build_query($data);
        $paywallLocation =  json_encode($data);

        $disbursement->signeddata = $signedData;
        $disbursement->requestedon = $now;
        $disbursement->location = $paywallLocation;
        $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['signeddata', 'requestedon', 'location']);
        
        $this->log("------ RedOne prepare transaction payment/disbursement location END -----", SNAP_LOG_DEBUG);
        $jsonEncodedRequestData = json_encode($data);
        $this->logApiRequest($jsonEncodedRequestData, $disbursement);

        return $paywallLocation;
    }

    public function generateHashKey($accountHolder, $disbursement, $mygoldtransaction){
        $amount = number_format($disbursement->amount,2,'.','');
        $customerid = $accountHolder->partnercusid;
        if($this->app->getConfig()->{'mygtp.redone.test.id'} != ''){
            $customerid = $this->app->getConfig()->{'mygtp.redone.test.id'};
        }
        $str = $mygoldtransaction->id.$this->secretkey.$customerid.$amount;
        $hashkey = hash('sha256',$str);

        return $hashkey;
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
        $this->log(__METHOD__ . ": print_r: ".print_r($returnArray) , SNAP_LOG_DEBUG);
        if(isset($returnArray['status'])){
            $responseStatus = strtoupper($returnArray['status']);
            if (self::STATUS_SUCCESS !== $responseStatus) {
                $this->notify(new IObservation($disbursement, IObservation::ACTION_CANCEL, $disbursement->status));
            }
        }
        elseif(isset($returnArray['data'])){
            $responseStatus = strtoupper($returnArray['data']['retMsg']);

            if (self::STATUS_SUCCESS !== $responseStatus) {
                $this->notify(new IObservation($disbursement, IObservation::ACTION_CANCEL, $disbursement->status));
            }
        }
        else{
            $this->notify(new IObservation($disbursement, IObservation::ACTION_CANCEL, $disbursement->status));
        }
    }

    /**
     * Verifies the callback from MCash
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

            if(isset($returnArray['data'])){
                $data = $this->formatArrayResponse($returnArray);

                $responseStatus = strtoupper($data['status']);
                $this->log(__METHOD__ . ": Response from RedOne wallet {$url} - ".json_encode($returnArray) , SNAP_LOG_DEBUG);

                // Check for response status first. The transaction status is inside data array
                if (self::STATUS_SUCCESS === $responseStatus) {
                    $this->log("------ RedOne transaction status found for Disbursement {$disbursement->transactionrefno} -----", SNAP_LOG_DEBUG);
                    // $this->processCallback($returnArray['data']);
                    $this->processCallback($data);

                    return $data;
                }
            }

            $this->log("------ RedOne transaction status not found for Disbursement {$disbursement->transactionrefno} -----", SNAP_LOG_ERROR);
            return $returnArray;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- RedOne Get Transaction Status Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- RedOne Get Transaction Status Error End ---", SNAP_LOG_ERROR);

            $returnArray = json_decode($e->getResponse()->getBody()->getContents(), true);
        }

        $this->log(__METHOD__ . ": Out of the catch and returnArray" , SNAP_LOG_DEBUG);
        return $returnArray;
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
     * Sign and base64 encode the data to be sent to MCash
     *
     * @param  string $unsignedData
     * @return string
     */
    protected function signData($unsignedData)
    {
        // $this->logDebug(__METHOD__."() Signing data before: $unsignedData");
        // $pubKeyLoc = $this->app->getConfig()->{'mygtp.wavpay.publickeypath'};
        // $key = file_get_contents($pubKeyLoc);

        // $privateKey = new AsymmetricSecretKey($key, new Version1());           
        // $signedData = Version1::sign($unsignedData, $privateKey);

        // $this->logDebug(__METHOD__."() Signing data after : $signedData");

        // return $signedData;
        return json_encode($unsignedData);
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
        $id = $response['paymentID'];
        $status = strtoupper($response['status']);
        $mygoldtransaction = $this->app->mygoldtransactionStore()->getByField('id', $id);
        if (! $mygoldtransaction) {
            $this->log(__METHOD__."(): Unable to find corresponding mygoldtransaction. mygoldtransaction id: ({$id})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding mygoldtransaction. mygoldtransaction id: ({$id})");
        }

        $disbursement = $this->app->mydisbursementStore()->getByField('transactionrefno', $mygoldtransaction->refno);
        if (! $disbursement) {
            $this->log(__METHOD__."(): Unable to find corresponding payment. disbursement transactionrefno: ({$mygoldtransaction->refno})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding payment. disbursement transactionrefno: ({$mygoldtransaction->refno})");
        }

        $this->logDebug(__METHOD__."(): Received response from RedOne");
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
        if (self::STATUS_SUCCESS == $status) {
            $this->log(__METHOD__ . ": Success payment response received for {$disbursement->transactionrefno}}" , SNAP_LOG_DEBUG);

            $action = IObservation::ACTION_CONFIRM;            
            $disbursement->disbursedon = $now;
            $disbursement->verifiedamount = number_format(floatval($disbursement->amount) + floatval($disbursement->fee), 2, '.', ''); //no amount receive from response
            $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['disbursedon', 'verifiedamount', 'gatewayrefno', 'remarks']);
            
            $this->log(__METHOD__ . ": Success payment response received for {$disbursement->transactionrefno}, code {$status}" , SNAP_LOG_DEBUG);
        } elseif (self::STATUS_SUCCESS != $status) {            
            $this->log(__METHOD__ . ": Failed payment response received for {$disbursement->transactionrefno}, code {$status}" , SNAP_LOG_ERROR);
            $action = IObservation::ACTION_CANCEL;
        } else {
            $this->log("Disbursement {$disbursement->id} callback received but no action was taken.", SNAP_LOG_ERROR);
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
     * Does the actual request to wavpay server to get transaction status
     * 
     * @param MyDisbursement $disbursement        The disbursement object
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendGetTransactionStatus($disbursement)
    {
        $mygoldtransaction = $this->app->mygoldtransactionStore()->getByField('refno', $disbursement->transactionrefno);
        
        $client          = $this->httpClientFactory($disbursement);         
        $locationPayout  = $this->querytransaction."/".$mygoldtransaction->id;
        $response        = $client->get($locationPayout);

        $this->log(__METHOD__ . ": Trigger RedOne wallet {$locationPayout} to get sell response" , SNAP_LOG_DEBUG);

        return $response;
    }

    protected function httpClientFactory($disbursement, $addToken = true)
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$disbursement->token
            ],
        ];

        return new \GuzzleHttp\Client($options);
    }
}
?>