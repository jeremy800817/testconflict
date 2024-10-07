<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Dianah <Dianah@silverstream.my>
* @version 1.0
* @created 30-Mar-2022
*/

namespace Snap\api\wallet;

use Exception;
use Snap\api\exception\GeneralException;
use Snap\App;
use Snap\api\exception\MyGtpWalletGatewayPaymentNotFound;
use Snap\api\exception\MyGtpWalletException;
use Snap\api\payout\BasePayout;
use Snap\api\payout\MCashPayout;
use Snap\IObservation;
use Snap\object\MyAccountHolder;
use Snap\object\MyPaymentDetail;
use Snap\object\MyGoldTransaction;
use Snap\object\Order;
use Snap\object\MyLedger;


class HopeGold extends BaseWallet
{
    // Production endpoints
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
            throw new \Exception("HopeGold merchant ID not provided");
        }
    }

    /**
     * Method to handle direct/indirect returns & callbacks from client
     * 
     * @param array $params         Params decoded by handler
     * @param string $postBody      Body of POST request if any
     */
    public function handleRequest($params, $postBody)
    {            
        throw GeneralException::fromTransaction([], [
            'message'   => "Not implemented"
        ]);
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
        $this->log("------ HopeGold prepare transaction payment location -----", SNAP_LOG_DEBUG);
        $this->usertoken = $accHolder->accesstoken;

        $data = [            
            'merchant'      => $this->merchantId,
            'orderId'       => $payment->sourcerefno,
            'note'          => $accHolder->note,
            'amount'        => number_format(floatval($payment->amount) + floatval($payment->customerfee) + floatval($payment->gatewayfee), 2, '.', ''),
            'description'   => $payment->productdesc
        ];
              
        $data['location'] = static::ENDPOINT_PAYMENT;     
        $paywallLocation =  http_build_query($data);

        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());
        $payment->gatewaystatus = 'PENDING';
        $payment->location      = $paywallLocation;
        $payment->remarks       = $accHolder->note;
        $payment->status        = MyPaymentDetail::STATUS_PENDING_PAYMENT;
        $payment->requestedon   = $now;
        $payment = $this->app->mypaymentdetailStore()->save($payment);

        $this->log("------ HopeGold prepare transaction payment location END -----", SNAP_LOG_DEBUG);
        $jsonEncodedRequestData = json_encode($data);
        $this->logApiRequest($jsonEncodedRequestData, $payment);

        /*send payment to HopeGold wallet*/
        $sendToWall = $this->sendTransactionToWall($payment,$data);

        return $sendToWall;
    }

    /**
     * Get status of transaction from MCash
     * 
     * @param MyPaymentDetail $paymentDetail
     * 
     */
    function sendTransactionToWall($payment,$data)
    {   
        $this->log("------ Start sending transaction to HopeGold wallet -----", SNAP_LOG_DEBUG);
        try {
            $response = $this->doSendGetTransactionStatus($data);
            if (200 == $response->getStatusCode()) {
                $returnArray = json_decode($response->getBody()->getContents(), true);

                //Test purpose
                //$returnArray = array(
                //                   "status" => "success",
                //                   "message" => "Wallet transaction success",
                //                    "data" => array(
                //                            "amount_debited" => 5.27,
                //                            "transaction_reference_no" => "PAY567469202232595550"
                //                        )
                //               );

                //Test failed purpose
                //$returnArray = array(
                //                   "status" => "failed",
                //                    "message" => "Wallet transaction unsuccess.",
                //                    "data" => null
                //               );

                if(self::STATUS_SUCCESS !== strtolower($returnArray['status'])){
                    $plainData = json_encode($returnArray);
                    $errors = $returnArray['message'];
                    $this->log(__METHOD__ . "() API return errors: Ref Num ".$data['orderId']." - " . $plainData, SNAP_LOG_ERROR);
                    //throw new \Exception("API for ref no. ".$data['orderId']." return errors: ". $errors);
                    //throw \Snap\api\exception\MyGtpWalletException::fromTransaction(null, ['message' => gettext("API for ref no. ".$data['orderId']." return errors: ". $errors)]);
                }

                /*update status payment*/
                $this->processPaymentResponse($payment, $returnArray);
            } 
            $this->log("------ End sending transaction to HopeGold wallet -----", SNAP_LOG_DEBUG);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to {$url} with error " . $e->getMessage() . "\nResponse:".$responseBody, SNAP_LOG_ERROR);
            throw $e;
        }
        return $returnArray;
    }

    /**
     * Process response from HopeGold
     *
     * @param  MyPaymentDetail $payment
     * @param  array           $responseArr
     * @return void
     */
    protected function processPaymentResponse($payment, $responseArr)
    {
        $this->log("------ Processing HopeGold payment response -----", SNAP_LOG_DEBUG);
        $plainData = json_encode($responseArr);

        $this->log("---HopeGold response json ".$plainData."---", SNAP_LOG_DEBUG);
        //Array
        //(
        //    [status] => success
        //    [message] => Wallet transaction success
        //    [data] => Array
        //        (
        //            [amount_debited] => 5.27
        //            [transaction_reference_no] => PAY567469202232595550
        //        )
        //)
        $noteToSave = $payment->remarks;
        $payment->remarks      .= ';'.$plainData;
        $payment->gatewayrefno  = ($responseArr['data']['transaction_reference_no'] != null) ? $responseArr['data']['transaction_reference_no']:$noteToSave;

        $initialStatus = $payment->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        // Set status of the payment
        $skip = false;
        $state = $this->app->mygtptransactionManager()->getPaymentDetailStateMachine($payment);

        if (self::STATUS_SUCCESS === strtolower($responseArr['status'])) {
            $action = IObservation::ACTION_CONFIRM;
            $payment->status = MyPaymentDetail::STATUS_SUCCESS;
            $payment->successon = $now;
            $payment->verifiedamount = $responseArr['data']['amount_debited'];
            $payment->gatewaystatus = 'SUCCESS';            
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['gatewaystatus', 'status', 'successon', 'failedon', 'verifiedamount', 'gatewayrefno', 'remarks']);
            $this->notify(new IObservation($payment, $action, $initialStatus, ['response' => $responseArr]));
        } else if(self::STATUS_FAILED === strtolower($responseArr['status'])){
            $action = IObservation::ACTION_REJECT;
            $payment->status = MyPaymentDetail::STATUS_FAILED;
            $payment->failedon = $now;
            $payment->gatewaystatus = 'FAILED';
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['gatewaystatus', 'status', 'successon', 'failedon', 'verifiedamount', 'gatewayrefno', 'remarks']);
            $this->notify(new IObservation($payment, $action, $initialStatus, ['response' => $responseArr]));
            $this->handleError($responseArr['message'],$payment);
        }
        else {
            $this->log("---HopeGold wallet - Payment {$payment->paymentrefno} response received but no action was taken.", SNAP_LOG_ERROR);
            $this->handleError($responseArr['message'],$payment);
        }

        $this->log("------ Processing HopeGold payment response END -----", SNAP_LOG_DEBUG);
    }

    protected function handleError($error,$payment)
    {
        $this->log(__CLASS__ . ': ' . $error . ' for payment reference no '. $payment->sourcerefno , SNAP_LOG_ERROR);
        throw \Snap\api\exception\MyGtpWalletException::fromTransaction(null, ['message' => gettext($error)]);
    }
    
    /**
     * Get status of transaction from HopeGold
     * 
     * @param MyPaymentDetail $paymentDetail
     * 
     */
    function getPaymentStatus($paymentDetail)
    {   
        try {     
            //echo "Come to getPaymentStatus\n";
            $response = $this->doGetTransactionStatus($paymentDetail);
            $returnArray = json_decode($response->getBody()->getContents(), true);
            $plainData = json_encode($returnArray);
            $this->log("---HopeGold wallet - Start send request to wallet to checking settlement status for  ".$plainData."---", SNAP_LOG_DEBUG);
            $this->log("---HopeGold wallet - checking response json ".$plainData."---", SNAP_LOG_DEBUG);

            //Test purpose
            //$returnArray = array(
            //                   "status" => "success",
            //                    "message" => "Wallet transaction success",
            //                    "data" => array(
            //                            "amount_debited" => 5.27,
            //                            "transaction_reference_no" => "PAY567469202232595550"
            //                        )
            //               );
            //$returnArray = array(
            //                   "status" => "success",
            //                    "message" => "Transaction is valid",
            //                    "data" => array(
            //                            "transaction_ref_no" => "PAY567469202232595550",
            //                            "transaction_amount" => 5.27,
            //                            "transaction_date" => "2022-03-25 09:55:49",
            //                            "transaction_status" => "SUCCESS",
            //                            "ace_wallet" => "10022457",
            //                            "ace_current_wallet_balance" => 15.81,
            //                        )
            //               );

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
                $this->log("------ HopeGold transaction status found for {$paymentDetail->sourcerefno} -----", SNAP_LOG_DEBUG);
                $this->processCallback($returnArray,$paymentDetail,$customertype);
                return $returnArray;
            }
            $this->log("------ HopeGold transaction status not found for {$paymentDetail->sourcerefno} -----", SNAP_LOG_ERROR);
            return $returnArray;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- HopeGold Get Transaction Status Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- HopeGold Get Transaction Status Error End ---", SNAP_LOG_ERROR);
        }
    }
    
    /**
     * Verifies the callback from HopeGold
     * 
     * @return boolean
     */
    function verifyPaymentResponse($response)
    {
    }

    protected function processCallback($response,$payment,$customertype, $skipAppendResponse = false)
    {
        $this->log("---HopeGold wallet - Process success settlement to update GTP pending transaction ".$payment->sourcerefno."---", SNAP_LOG_DEBUG);
            //[data] => Array
            //        (
            //            [transaction_ref_no] => PAY567469202232595550
            //            [transaction_amount] => 5.27
            //            [transaction_date] => 2022-03-25 09:55:49
            //            [transaction_status] => SUCCESS
            //            [ace_wallet] => 10022457
            //            [ace_current_wallet_balance] => 15.81
            //        )

            //[data] => 
        //$response['data']['transaction_status'] = 'success'; //for testing
        $hopegoldStatus = strtolower($response['data']['transaction_status']);
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());
        /*$payment = $this->app->mypaymentdetailStore()->getByField('gatewayrefno', $response['transaction_ref_no']);
        if (! $payment) {
            $this->log(__METHOD__."(): Unable to find corresponding payment. Gateway ref: ({$response['transaction_ref_no']})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding payment. Gateway ref: ({$response['transaction_ref_no']})");
        }*/

        $this->logDebug(__METHOD__."(): Received checking response from HopeGold");
        $this->logDebug(json_encode($response));
        if (!$skipAppendResponse) {
            $payment->remarks = $this->appendResponses($payment->remarks, $response);
            $payment->transactiondate = $response['data']['transaction_date'];
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['remarks','transactiondate']);
        }

        $payment->verifiedamount = $response['data']['transaction_amount'] ?? $payment->verifiedamount;
        $payment->gatewaystatus = strtoupper($response['status']);
        $payment->gatewayrefno = $response['data']['transaction_ref_no'];
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['verifiedamount', 'gatewaystatus','gatewayrefno']);
        $initialStatus = $payment->status;
        $action = IObservation::ACTION_NONE;

        // Set status of the payment
        $skip = false;
        $state = $this->app->mygtptransactionManager()->getPaymentDetailStateMachine($payment);
        $goldTx = $this->app->mygoldtransactionStore()->getByField('refno', $payment->sourcerefno);

        $createLedger = false;
        if (self::STATUS_FAILED == $hopegoldStatus && $state->can(MyPaymentDetail::STATUS_FAILED)) {
            $this->log("---HopeGold wallet - Settlement status return failed ".$payment->sourcerefno."---", SNAP_LOG_DEBUG);
            $action = IObservation::ACTION_REJECT;
            $payment->status = MyPaymentDetail::STATUS_FAILED;
            $payment->failedon = $now;
            $goldTx->status = MyGoldTransaction::STATUS_FAILED;
        } else if (self::STATUS_SUCCESS == $hopegoldStatus) {
            $this->log("---HopeGold wallet - Settlement status return success ".$payment->sourcerefno."---", SNAP_LOG_DEBUG);
            $action = IObservation::ACTION_CONFIRM;
            $payment->status = MyPaymentDetail::STATUS_SUCCESS;
            $payment->successon = $now;
            $goldTx->status = MyGoldTransaction::STATUS_PAID;
            $createLedger = true;
        } else {
            $this->log("---HopeGold wallet - Payment {$payment->sourcerefno} callback checking received but no action was taken.", SNAP_LOG_ERROR);
            $skip = true;
        }

        if (!$skip) {
            /*Get order*/
            $order = $goldTx->getOrder();
            if(Order::TYPE_COMPANYSELL ==  $order->type) $ledgerType = MyLedger::TYPE_BUY_FPX;
            else $ledgerType = MyLedger::TYPE_SELL;
            $goldTx = $this->app->mygoldtransactionStore()->save($goldTx, ['status']);
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['gatewaystatus', 'status', 'successon', 'failedon', 'refundedon', 'verifiedamount']);

            if($createLedger) {
                $this->log("---HopeGold wallet - Create ledger for {$payment->sourcerefno} if success / createledger is true.", SNAP_LOG_ERROR);
                $this->app->mygtptransactionManager()->sendTransactionToLedger($goldTx,$ledgerType);
            }
            $this->notify(new IObservation($payment, $action, $initialStatus, ['response' => $response]));
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
     * Sign and base64 encode the data to be sent to MCash
     *
     * @param  string $unsignedData
     * @return string
     */
    protected function signData($unsignedData)
    {
    }

    /**
     * Verifies the signed data
     * 
     * @return boolean
     */
    protected function verifySignedData($signedData, $plainData)
    {        
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
        $data = [            
            'amount'    => $paymentDetail['amount'],
            'note'      => $paymentDetail['note']
        ];
        
        $client       = $this->httpClientFactory();        
        $url          = static::ENDPOINT_PAYMENT;
        $response     = $client->post($url, ['json' => $data]);

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

        //$tokenuser = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJUT1lZSUJAVUFUIiwianRpIjoiMDg3YmQ5MmY2NGU2ZGE1NWUwYTBlNTc3M2JjM2NhYzQ5NTE5OTEwNzI4MzFiYjI1Yjk5ODgyZmNiNGE0NjllYzE2NDBkOTFmNTE1YWVlZTQiLCJpYXQiOjE2NDkxMjM2NDgsIm5iZiI6MTY0OTEyMzY0OCwiZXhwIjoxNjQ5NzI4NDQ4LCJzdWIiOiJhaG1hZGp1bGFwOEBnbWFpbC5jb20iLCJzY29wZXMiOltdfQ.NgWMwdrsoYtdrN9Hv7MgYlBXBQfNE3rHqPBn_kEnhfnNO_fLdhcuPd5sMEamnXuxGl3xv23kKYgf8tiAl__g5_U-1Xbr7i7Cb1CuLi-NwOnnMuGIaNRKHQbj4-1HZw5Ga2WfyFrX1qQ2nwZdksy8Y_BrgcaI0kLJY78JLHtcTT8QnRNreAJZCld9tBz8yUdQdufmAxSRy5di_YkzE5LxNv9ddiTdWu5rB-hMGpYx762MPBX2X4yNPmMr-S-lPtmelpZ8fIIaaXCdcs1Bc_fHrCv99rAa8At7BE-CjTngEseyd1Vuzywxf9KzdTIwJUo3a8fwblKH4c4TSAtF2E7wjA'; //testing purpose

        if ($addToken) {
            $options['headers']['Authorization'] = "Bearer " . $this->getAuthToken();
            //$options['headers']['Authorization'] = "Bearer " . $tokenuser; //testing purpose
        }

        $plainData = json_encode($options);
        $this->log("---HopeGold wallet - Header request send to HopeGold wallet => ".$plainData, SNAP_LOG_DEBUG);

        return new \GuzzleHttp\Client($options);
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

        return $token;
    }
}


?>