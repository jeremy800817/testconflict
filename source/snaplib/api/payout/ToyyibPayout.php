<?php
/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Dianah <dianah@silverstream.my>
* @version 1.0
* @created 08-04-2022
*/

namespace Snap\api\payout;

use Snap\api\exception\GeneralException;
use Snap\App;
use Snap\IObservation;
use Snap\object\MyAccountHolder;
use Snap\object\MyDisbursement;

class ToyyibPayout extends BasePayout 
{
    protected const ENDPOINT_PAYMENT = "https://api.tybgold.com/purchase-settlement-ace";
    protected const ENDPOINT_CHECKING = "https://api.tybgold.com/purchase-settlement-checking-ace";

    protected const STATUS_SUCCESS = 'success';
    protected const STATUS_FAILED = 'failed';

    protected $merchantId = null;
    protected $secretkey = null;
    protected $usertoken = null;

    protected function __construct($app)
    {
        parent::__construct($app);

        $this->merchantId  = $app->getConfig()->{'mygtp.toyyib.merchant'};
        $this->secretkey   = $app->getConfig()->{'mygtp.toyyib.secretkey'};

        if (! $this->merchantId) {
            throw new \Exception("Toyyib merchant ID not provided");
        }
    }

    public function handleResponse($params, $body)
    {
        // One Call Payout should not reach here
        throw GeneralException::fromTransaction([], [
            'message'   => "Not implemented"
        ]);
    }

    /**
     * Send a payout request to provider
     * 
     * @param MyAccountHolder $accountHolder 
     * @param MyDisbursement $disbursement 
     * @return bool 
     */
    public function createPayout($accountHolder, $disbursement)
    {
        try {
            //echo "Come to toyyib payout \n";
            $this->log("------ ToyyibPayout credit customer request -----", SNAP_LOG_DEBUG);
            $response = $this->creditCustomer($disbursement);
            $this->log("------ ToyyibPayout credit customer request END -----", SNAP_LOG_DEBUG);
            
            if (200 == $response->getStatusCode()) {
                $response = $response->getBody()->getContents();
                $this->log("------ ToyyibPayout credit customer response -----", SNAP_LOG_DEBUG);
                $this->log($response, SNAP_LOG_DEBUG);
                $responseArr = json_decode($response, true);                
                $this->processDisbursementResponse($disbursement, $responseArr);
                $this->log("------ ToyyibPayout credit customer response END -----", SNAP_LOG_DEBUG);
            }

            return true;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $err = $e->getMessage();
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- ToyyibPayout Credit Customer Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- ToyyibPayout Credit Customer Error End ---", SNAP_LOG_ERROR);
            return false;
        }
    }

    /**
     * Process response from toyyib
     *
     * @param  MyDisbursement $disbursement
     * @param  array           $responseArr
     * @return void
     */
    protected function processDisbursementResponse($disbursement, $responseArr)
    {
        $this->log("------ Processing disbursement response -----", SNAP_LOG_DEBUG);  
        $plainData = json_encode($responseArr);
        $this->log($plainData, SNAP_LOG_DEBUG);

        
        //TEST RESPONSE Success
        //$responseArr = array(
        //    "status"    => "success",
        //    "message"   =>"Wallet transaction success",
        //    "data"      => array("amount_debited" => "4.39","transaction_reference_no" => "PAY4681752022411122648")
        //);

        //TEST RESPONSE Failed
        //$responseArr = array(
        //    "status"    => "failed",
        //    "message"   =>"Wallet transaction unsucess",
        //    "data"      => null
        //);

        $disbursement->remarks = $plainData;
        $disbursement->gatewayrefno = $responseArr['data']['transaction_reference_no'];

        $initialStatus = $disbursement->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());
        
        if (self::STATUS_SUCCESS === strtolower($responseArr['status'])) {
            $this->log(__METHOD__ . ": Success payment response received for {$disbursement->refno}" , SNAP_LOG_DEBUG);
            $action = IObservation::ACTION_CONFIRM;            
            $disbursement->disbursedon = $now;
            $disbursement->verifiedamount = $responseArr['data']['amount_credited'];
            $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['disbursedon', 'verifiedamount', 'gatewayrefno', 'remarks']);
        } else if(self::STATUS_FAILED === strtolower($responseArr['status'])){
            // For sell we throw errors and dont retry
            $this->log(__METHOD__ . ": Failed payment response received for ToyyibPayout {$disbursement->refno} - {$disbursement->accountnumber}" , SNAP_LOG_ERROR);
            $this->handleError($responseArr['message'],$disbursement);
        } else {
            // For sell we throw errors and dont retry
            $this->log(__METHOD__ . ": Failed payment response received for {$disbursement->refno}" , SNAP_LOG_ERROR);
            $this->handleError($responseArr['message'],$disbursement);
        }
        $this->notify(new IObservation($disbursement, $action, $initialStatus, ['response' => $responseArr]));
    }

    protected function handleError($error,$disbursement)
    {
        $this->log(__CLASS__ . ': ' . $error . ' for disbursement reference no '. $disbursement->refno , SNAP_LOG_ERROR);
        throw \Snap\api\exception\MyGtpWalletException::fromTransaction(null, ['message' => gettext($error)]);
    }
    /**
     * Manually reads payout status from provider's server
     * 
     * @param MyDisbursement $disbursement 
     * @return exit 
     * @throws GeneralException 
     */
    public function getPayoutStatus($disbursement)
    {
        try {     
            //echo "Come to getPayoutStatus\n";
            $this->log("---ToyyibPayout wallet - Start send request to wallet to checking settlement status for  ".$plainData."---", SNAP_LOG_DEBUG);
            $response = $this->doGetTransactionStatus($disbursement);
            $returnArray = json_decode($response->getBody()->getContents(), true);
            $plainData = json_encode($returnArray);
            $this->log("---ToyyibPayout checking payout response json ".$plainData."---", SNAP_LOG_DEBUG);
            //Array success
            //(
            //    [status] => success
            //    [message] => Transaction is valid
            //    [data] => Array
            //        (
            //            [transaction_ref_no] => PAY567469202232595550
            //            [transaction_amount] => 5.27
            //            [transaction_date] => 2022-03-25 09:55:49
            //            [transaction_status] => SUCCESS
            //            [ace_wallet] => 10022457
            //            [ace_current_wallet_balance] => 15.81
            //        )
            //)

            //Array failed
            //(
            //    [status] => failed
            //    [message] => Parameter transaction_ref_no cannot be empty
            //    [data] => 
            //)
            // Check for response status first. The transaction status is inside data array
            if (self::STATUS_SUCCESS === strtolower($returnArray['status']) && array_key_exists('data', $returnArray)) {
                $this->log("------ ToyyibPayout transaction payout status found for {$disbursement->transactionrefno} -----", SNAP_LOG_DEBUG);
                $this->processCallback($returnArray['data']);
                return $returnArray;
            }
            $this->log("------ ToyyibPayout transaction payout status not found for {$disbursement->transactionrefno} -----", SNAP_LOG_ERROR);
            return $returnArray;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- ToyyibPayout Get payout Transaction Status Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- ToyyibPayout Get payout Transaction Status Error End ---", SNAP_LOG_ERROR);
        }
    }

    /**
     * Credit the customer
     *
     * @param  MyDisbursement $disbursement
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function creditCustomer($disbursement)
    {
        $sm = $this->app->mygtpdisbursementManager()->getStateMachine($disbursement);

        if (! $sm->can(MyDisbursement::STATUS_COMPLETED)) {
            $this->log("confirmBookGoldTransaction({$disbursement->refno}):  Unable to proceed to credit due to status", SNAP_LOG_ERROR);
            throw \Snap\api\exception\MyGtpTransactionExists::fromTransaction(null, ['message' => $disbursement->transactionrefno]);
        }

        $data = [];
        $customerData   = json_decode(base64_decode($disbursement->token), true);        
        $data['amount'] = number_format(floatval($disbursement->amount) + floatval($disbursement->fee), 2, '.', '');
        $data['refno']  = $disbursement->refno . ' ' . $disbursement->transactionrefno;
        $data['note']   = $disbursement->accountnumber;
        $data['requestdata'] = $data['amount'].";".$data['refno'].";".$data['note']; 
        $jsonEncodedRequestData = json_encode($data);
        $this->logApiRequest($jsonEncodedRequestData, $disbursement);
        $this->log($jsonEncodedRequestData, SNAP_LOG_DEBUG);
        
        $now = new \DateTime('now', $this->app->getUserTimezone());
        $disbursementStore = $this->app->mydisbursementStore();
        $disbursement->requestedon = $now;
        $disbursement->signeddata = $data['requestdata'];
        $disbursement = $disbursementStore->save($disbursement, ['signeddata', 'requestedon']);
        $client = $this->httpClientFactory();
        
        return $this->doSendCreditCustomer($disbursement->signeddata, $client);
    }

    /**
     * Does the actual request to OneCall server to credit customer
     * 
     * @param  string             $encryptedData  The encrypted data used as input params
     * @param  \GuzzleHttp\Client $client         Guzzle client
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendCreditCustomer($encryptedData, $client)
    {
        $url = static::ENDPOINT_PAYMENT;
        $response = $this->requestPost($url, $encryptedData, $client);

        return $response;
    }

    /**
     * Does the actual request to MCash server to get transaction status
     * 
     * @param MyPaymentDetail $paymentDetail        The payment detail     
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doGetTransactionStatus($paymentDetail)
    {
        $data = [            
            'secret_key'          => $this->secretkey,
            'transaction_ref_no'  => $paymentDetail->gatewayrefno,
        ];

        $client       = $this->httpClientFactory(false);        
        $url          = static::ENDPOINT_CHECKING;
        $response     = $client->post($url, ['json' => $data]);

        return $response;
    }

    protected function httpClientFactory($addToken = true)
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ];

        if ($addToken) {
            $options['headers']['Authorization'] = "Bearer " . $this->getAuthToken();
        }

        return new \GuzzleHttp\Client($options);
    }

    protected function requestPost($url, $encryptedData, $client)
    {
        $dataArray  = explode(";",$encryptedData);
        //Array
        //(
        //    [0] => 12.71 // amount
        //    [1] => D202204111100005 GT202204111100005 //refno & transaction refno
        //    [2] => BK24842412722022411111646 // note
        //)
        $data     = [
            'amount'    => $dataArray[0],
            'note'      => $dataArray[2]
        ];    
        $response = $client->post($url, ['json' => $data]);

        return $response;
    }

    /**
     * Get authentication token from OneCall.
     * 
     * @return string Token
     * 
     */
    protected function getAuthToken()
    {
        $token = $this->app->mygtpauthManager()->extractBearerTokenFromHeader();

        if (null == $token) {
            $this->log(__METHOD__ . "() Failed to get authentication token.", SNAP_LOG_ERROR);
        }

        //$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJUT1lZSUJAVUFUIiwianRpIjoiNWJkM2Y4ZDFiOWZkMTNmODkwMThlYTk0YTEwZGVkNDAwNGU2Y2E5OWU2MzFjMTBjNmM4ZTVlYjg4NTA5NTE0YjdjODhiMjhmYjA1ZDcwMGMiLCJpYXQiOjE2NDkzODQ1MzQuMzI1OTg5LCJuYmYiOjE2NDkzODQ1MzQuMzI1OTkyLCJleHAiOjE2NDk5ODkzMzQuMjI2ODksInN1YiI6ImVuZG9obWl5YWtvMTFAZ21haWwuY29tIiwic2NvcGVzIjpbXX0.ZxGzTobVZdcUWStc6vCJNYSinHiabrA4_4SEqgi_J8sbrrA3sxGm4dNJDhbhBhYBjt5D-7ZiF6n65YDfrae5oWn1BPPvvKcjvE7n1YE_8Z3Q-L0dP54ATCXWuP0lshQUBgmkTAj-FKwJ5muTEE6SxbXNra45g29EKLxM0yulMpWqNyEb66cmZR7TpSEE6wVjssMcJnMmbF7osJrrnthJRBB1nuvVloQ6vmHbJIs5uaHWByjMTEWQ-zN1coQqCQGS6mB3Q7th9EMG4rkHGsG5LDoPED5-deiZwH63YXyBofLHD2UHvE2YWaI2koUWDn_bZr7PvqDLu_ENXj3Uv4icQA'; //testing purpose

        return $token;
    }
}
?>