<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 12-Nov-2020
*/

namespace Snap\api\wallet;

use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Keys\Version1\AsymmetricPublicKey;
use ParagonIE\Paseto\Keys\Version1\AsymmetricSecretKey;
use ParagonIE\Paseto\Protocol\Version1;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Snap\api\exception\MyGtpWalletGatewayPaymentNotFound;
use Snap\IObservation;
use Snap\object\MyAccountHolder;
use Snap\object\MyPaymentDetail;


class GoPayz extends BaseWallet
{
    // Production endpoints
    protected const ENDPOINT_CHECK_PAYMENT = "https://standalone.gopayz.com.my/standalone/partnerservice/checkPayment";
    protected const ENDPOINT_PAYMENT_PREAUTH = "https://standalone.gopayz.com.my/standalone/doPreAuth";
    protected const ENDPOINT_TRANSACTION_STATUS = "https://standalone.gopayz.com.my/standalone/partnerservice/transactionStatusEnquiry";

    protected const PAYMENT_MODE_PREAUTH = 13;
    protected const PAYMENT_MODE_COMPLETION = 16;

    protected const CURRENCY_MYR = 'MYR';

    protected const LANG_EN = 'en';
    protected const LANG_BM = 'ms';

    protected $merchantId = null;
    protected $returnUrl = null;

    protected function __construct($app)
    {
        parent::__construct($app);

        $this->merchantId  = $app->getConfig()->{'mygtp.gopayz.clientid'};
        $this->returnUrl = $app->getConfig()->{'mygtp.gopayz.returnUrl'};

        if (! $this->merchantId) {
            throw new \Exception("GoPayz client ID not provided");
        }

        if (! $this->returnUrl) {
            throw new \Exception("GoPayz return URL not provided");
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
        $decodedData = $this->verifySignedData($params['payload']);
        
        $paymentRefNo = $decodedData['reqRefno'];
        $errMsgTag = ['paymentrefno' => ''];

        if (0 < strlen($paymentRefNo)) {
            $payment = $this->app->mypaymentdetailStore()->getByField('paymentrefno', $paymentRefNo);
            $errMsgTag['paymentrefno'] = $paymentRefNo;
        }

        if (! $payment) {
            $this->logDebug(__METHOD__."(): No payment found for response {". json_encode($params)."}");
            throw MyGtpWalletGatewayPaymentNotFound::fromTransaction([], $errMsgTag);
        }

        $this->logApiResponse(json_encode($decodedData), $payment);

        $signedData = $params['payload'];
        $payment->signeddata = $signedData;
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['signeddata']);

        if ('SUCCESSFUL' === $decodedData['status'] || 'S' === $decodedData['status']) {            
            $this->getSalesCompletion($decodedData, $payment);
        }

        $payment = $this->app->mypaymentdetailStore()->getById($payment->id);
        $data = [
            'refno' => $payment->paymentrefno,
            'status' => $payment->getStatusString(),
        ];
        
        return ['success' => true, 'data' => $data];
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
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());
        $decodedData = $this->verifySignedData($payment->token);        

        $data = [
            'reqRefno'      => $payment->paymentrefno,
            'requestDate'   => $now->format('YmdHis'),
            'amount'        => number_format(floatval($payment->amount) + floatval($payment->customerfee) + floatval($payment->gatewayfee), 2, '.', ''),
            'currency'      => self::CURRENCY_MYR,
            'channelUserId' => $decodedData['channelUserId'],
            'telHp'         => $decodedData['telHp'],
            'paymentModeID' => self::PAYMENT_MODE_PREAUTH,
            'returnUrl'     => $this->returnUrl,
            'description'   => $payment->productdesc,
            'paymentDate'   => $now->format('YmdHis'),
            'merchantRefNo' => $payment->paymentrefno, //ref number for the transaction
            'partnerId'     => $this->merchantId,
            'partnerToken'  => '',
            'language'      => $this->selectLanguageForAccountHolder($accHolder),
            'channelLoginToken' => $decodedData['channelLoginToken']
        ];
        
        $this->log("----- GoPayz initialize transaction -----", SNAP_LOG_DEBUG);
        $data['signedData'] = $this->signData(json_encode($data));
        $this->log("----- GoPayz initialize transaction END -----", SNAP_LOG_DEBUG);

        $paywallLocation = $this->getPayWallLocation($data['signedData'], $this->merchantId);

        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());
        $payment->gatewaystatus = 'PENDING';
        $payment->location = $paywallLocation;
        $payment->status = MyPaymentDetail::STATUS_PENDING_PAYMENT;
        $payment->requestedon = $now;
        $payment = $this->app->mypaymentdetailStore()->save($payment);

        return $paywallLocation;
    }

    protected function getPaywallLocation($payload, $channel)
    {
        
        $query = http_build_query([
            'channel' => $channel,
            'payload' => $payload,
        ]);

        return static::ENDPOINT_PAYMENT_PREAUTH . '?' . $query;
    }

    /**
     * Return the language constant for GoPayz using the user preferred lang
     *
     * @param  MyAccountHolder $accHolder
     * @return string
     */
    protected function selectLanguageForAccountHolder($accHolder)
    {
        switch ($accHolder->preferredlang) {
            case MyAccountHolder::LANG_BM:
                return self::LANG_BM;
                break;
            default:
                return self::LANG_EN;
                break;
        }
    }

    function getSalesCompletion($decodedData, $payment)
    {
        try {
            $response = $this->doSendSalesCompletion($decodedData, $payment);

            $returnArray = json_decode($response->getBody()->getContents(), true);
            $this->logApiResponse(json_encode($returnArray), $payment);

             // retrivalRefNo is same as PaymentID return by checkPayment
            $returnArray['paymentID'] = $decodedData['retrivalRefNo'];            
            $returnArray['reqRefNo']  = $payment->paymentrefno;
            $returnArray['status']    = $returnArray['status'];            

            // Process callback
            if ($returnArray['status']) {
                $this->processCallback($returnArray, false);
            }

            return $returnArray;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- GoPayz Get Sales Completion Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- GoPayz Get Sales Completion Error End ---", SNAP_LOG_ERROR);
        }
        return $response;
    }

    /**
     * Get status of transaction from GoPayz
     * 
     * @param MyPaymentDetail $paymentDetail
     * 
     */
    function getPaymentStatus($paymentDetail)
    {
        try {            

            $this->log("--- GoPayz Get Transaction Status START ---", SNAP_LOG_DEBUG);
            $response = $this->doSendGetTransactionStatusEnquiry($paymentDetail);
            $jsonDecodedResponse = json_decode($response->getBody()->getContents(), true);
            $returnArray = $this->verifySignedData($jsonDecodedResponse['payload']);
            $this->log(json_encode($jsonDecodedResponse), SNAP_LOG_DEBUG);
            $this->log(json_encode($returnArray), SNAP_LOG_DEBUG);
            $this->log("--- GoPayz Get Transaction Status END ---", SNAP_LOG_DEBUG);
            
            if (0 < count($returnArray['result'])) {
                $decodedData = $returnArray['result'][0];
                
                if (self::PAYMENT_MODE_PREAUTH == $decodedData['paymentModeID']) {
                    
                    if ('SUCCESSFUL' === $decodedData['status'] || 'S' === $decodedData['status']) {

                        // Fill data from data saved during order created earlier
                        $tokenData = $this->verifySignedData($paymentDetail->token);
                        $decodedData['channelUserId'] = $tokenData['channelUserId'];
                        $decodedData['telHp'] = $tokenData['telHp'];
                        $decodedData['partnerId'] = $decodedData['partnerID'] ?? $this->merchantId;
                        $decodedData['channelLoginToken'] = $tokenData['channelLoginToken'];

                        // Do sales completion as it was not done previously
                        $this->getSalesCompletion($decodedData, $paymentDetail);
                    } elseif ('FAILED' === $decodedData['status'] || 'F' === $decodedData['status']) {                                            
                        $decodedData['reqRefNo'] = $paymentDetail->paymentrefno;
                        
                        // Process callback if the transaction failed
                        $this->processCallback($decodedData, true);
                    }

                } elseif (self::PAYMENT_MODE_COMPLETION == $decodedData['paymentModeID']) {
                    // If it reach here means completion was performed previously and 
                    // we need to mark transaction as success
                    $decodedData['reqRefNo'] = $paymentDetail->paymentrefno;
                        
                    $this->processCallback($decodedData);
                }
            }            

            return $returnArray;                
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- GoPayz Get Transaction Status Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- GoPayz Get Transaction Status Error End ---", SNAP_LOG_ERROR);
        }
    }

    public function shouldExpire($paymentDetail)
    {
        $this->log(__METHOD__."(): Check if should expire GoPayz Transaction", SNAP_LOG_DEBUG);
        $response = $this->doSendGetTransactionStatus($paymentDetail);    
        $returnArray = json_decode($response->getBody()->getContents(), true);

        // No results from GoPayz then we need to expire the order
        if (0 === count($returnArray['result'])) {
            $this->log(__METHOD__."(): GoPayz checkPayment API return empty result for {$paymentDetail->paymentrefno}", SNAP_LOG_DEBUG);
            return true;
        }

        return false;
    }

    // public function resetPaymentId($paymentDetail)
    // {
    //     $response = $this->doSendGetTransactionStatus($paymentDetail);    
    //     $returnArray = json_decode($response->getBody()->getContents(), true);

    //     $result = $this->filterCompletedResult($returnArray['result']);
        

    //     $this->logDebug(__METHOD__."(): Received response from GoPayz checkPayment");
    //     $this->logDebug(json_encode($returnArray));

    //     // If already same or null
    //     if ($paymentDetail->gatewayrefno == $result['paymentID'] || null == $result) {
    //         return $paymentDetail;
    //     }

    //     $paymentDetail->gatewayrefno = $result['paymentID'];
    //     $paymentDetail = $this->app->mypaymentdetailStore()->save($paymentDetail, ['gatewayrefno']);        

    //     return $paymentDetail;
    // }

    /**
     * Return the array of completed results
     *
     * @param  array $results
     * @return array
     */
    protected function filterCompletedResult($results)
    {
        // Sales completion was performed either manually / auto
        $completed = [];

        // Got rejected / cancelled during manual sales completion ?
        $cancelled = [];
        
        for ($i = 0; $i < count($results) - 1; $i++) {
            for ($j = $i + 1; $j < count($results); $j++) { 

                if (array_key_exists($results[$i]['paymentID'], $completed) || array_key_exists($results[$j]['paymentID'], $completed)) {
                    continue;
                }

                // Pair matches
                if ($results[$i]['paymentID'] == $results[$j]['paymentID']) {
                    if ($results[$i]['status'] == 'S' && $results[$j]['status'] == 'S') {
                        $completed[$results[$i]['paymentID']] = $results[$i];
                        continue;
                    }
                }

                // Status failed
                if ($results[$i]['status'] == 'F') {
                    $cancelled[$results[$i]['paymentID']] = $results[$i];
                }

                if ($results[$j]['status'] == 'F') {
                    $cancelled[$results[$j]['paymentID']] = $results[$j];
                }
            }            
        }

        return [            
            'cancelled' => $cancelled,
            'completed' => $completed
        ];
    }


    /**
     * Does the actual request to GoPayz server to check payment status
     * 
     * @param MyPaymentDetail $paymentDetail        The payment detail
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendGetTransactionStatus($paymentDetail)
    {
        $client     = $this->httpClientFactory();
        $cert       = $this->app->getConfig()->{'mygtp.gopayz.certificatepath'};
        $privateKey = $this->app->getConfig()->{'mygtp.gopayz.privatekeypath'};

        $url        = static::ENDPOINT_CHECK_PAYMENT . '?channel='. $this->merchantId .'&reqRefNo='. $paymentDetail->paymentrefno;
        $options    = ['cert' => [$cert], 'ssl_key' => [$privateKey]];
        $response   = $client->get($url, $options);

        return $response;
    }

    /**
     * Does the actual request to GoPayz server to check transaction
     * 
     * @param MyPaymentDetail $paymentDetail        The payment detail
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendGetTransactionStatusEnquiry($paymentDetail)
    {
        $client     = $this->httpClientFactory();
        $cert       = $this->app->getConfig()->{'mygtp.gopayz.certificatepath'};
        $privateKey = $this->app->getConfig()->{'mygtp.gopayz.privatekeypath'};
        $reqRefNo   = $paymentDetail->paymentrefno . time();

        $data = [
           'partnerID'    => $this->merchantId,
           'reqRefno'     => $reqRefNo,
           'merchantRefNo' => $paymentDetail->paymentrefno
        ];

        $payload = $this->signData(json_encode($data));

        $url        = static::ENDPOINT_TRANSACTION_STATUS . '?channel='. $this->merchantId .'&reqRefNo='. $reqRefNo .'&payload='.$payload;
        $options    = ['cert' => [$cert], 'ssl_key' => [$privateKey]];
        $response   = $client->get($url, $options);

        return $response;
    }

    /**
     * Does the actual request to GoPayz server to complete transaction
     * 
     * @param array           $decodedData The decoded params
     * @param MyPaymentDetail $payment     The payment detail
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendSalesCompletion($decodedData, $payment)
    {
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());        

        $data = [
            'reqRefno'      => $payment->paymentrefno,
            'requestDate'   => $now->format('YmdHis'),
            'amount'        => number_format(floatval($payment->amount) + floatval($payment->customerfee) + floatval($payment->gatewayfee), 2, '.', ''),
            'currency'      => self::CURRENCY_MYR,
            'channelUserId' => $decodedData['channelUserId'],
            'telHp'         => $decodedData['telHp'],
            'paymentModeID' => self::PAYMENT_MODE_COMPLETION,
            'returnUrl'     => $this->returnUrl,
            // 'description'   => $payment->productdesc,
            'paymentDate'   => $now->format('YmdHis'),
            'merchantRefNo' => $payment->paymentrefno, //ref number for the transaction
            'partnerId'     => $decodedData['partnerId'],
            'partnerToken'  => '',
            'referenceNo'   => $decodedData['referenceNo'],
            'retrivalRefNo' => $decodedData['retrivalRefNo'],
            'approvalCode'  => $decodedData['approvalCode'],
            'virtualNo'     => $decodedData['virtualNo'],
            'channelLoginToken' => $decodedData['channelLoginToken']
        ];
        
        $this->log("----- GoPayz completion transaction START -----", SNAP_LOG_DEBUG);

        $jsonEncodedData = json_encode($data);
        $this->logApiRequest($jsonEncodedData, $payment);
        $data['signedData'] = $this->signData($jsonEncodedData);        
        $url        = $this->getPaywallLocation($data['signedData'], $decodedData['partnerId']);
        $client     = $this->httpClientFactory();
        $response   = $client->get($url);

        $this->log("----- GoPayz completion transaction END -----", SNAP_LOG_DEBUG);
        
        return $response;
    }

    /**
     * Retry sales completion
     * 
     * @param MyPaymentDetail $payment The payment detail
     * 
     * @return void
     */
    protected function retrySalesCompletion($payment)
    {
        $this->log("----- GoPayz retry completion transaction START -----", SNAP_LOG_DEBUG);

        $decodedData = $this->verifySignedData($payment->signeddata);
        $this->getSalesCompletion($decodedData, $payment);
    
        $this->log("----- GoPayz retry completion transaction END -----", SNAP_LOG_DEBUG);

    }
    
    /**
     * Verifies the callback from GoPayz
     * 
     * @return boolean
     */
    function verifyPaymentResponse($response)
    {
        /** @todo */     
        return true;  
    }

    /**
     * 
     * According to GSHRS docs the status we get during redirection / callback is:
     * SUCCESSFUL
     * FAILED
     * DUPLICATED
     * INSUFFICIENT FUND
     * PENDING
     * 
     * But for check payment we only get the:
     * P – (PENDING)
     * S - (SUCCESSFUL)
     * F – (FAILED)
     * 
     * @param [type] $response
     * @param boolean $skipAppendResponse
     * @return void
     */
    protected function processCallback($response, $skipAppendResponse = false)
    {
        $gopayzStatus = $response['status'];
        $payment = $this->app->mypaymentdetailStore()->getByField('paymentrefno', $response['reqRefNo']);
        if (! $payment) {
            $this->log(__METHOD__."(): Unable to find corresponding payment. Gateway ref: ({$response['reqRefNo']})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding payment. Gateway ref: ({$response['reqRefNo']})");
        }

        $this->logDebug(__METHOD__."(): Received response from GoPayz");
        $this->logDebug(json_encode($response));
        if (!$skipAppendResponse) {
            $payment->remarks = $this->appendResponses($payment->remarks, $response);
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['remarks']);
        }
        $payment->verifiedamount = $response['trxAmt'] ?? $payment->verifiedamount;
        $payment->gatewaystatus = $response['status'];
        if (! $payment->gatewayrefno) {
            $payment->gatewayrefno = $response['paymentID'] ?? $payment->gatewayrefno;
        }
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['verifiedamount', 'gatewaystatus', 'gatewayrefno']);
        $initialStatus = $payment->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        // Set status of the payment
        $skip = false;
        $state = $this->app->mygtptransactionManager()->getPaymentDetailStateMachine($payment);
        if (in_array($gopayzStatus, ['INSUFFICIENT FUND', 'FAILED', 'F']) && $state->can(MyPaymentDetail::STATUS_FAILED)) {
            $action = IObservation::ACTION_REJECT;
            $payment->status = MyPaymentDetail::STATUS_FAILED;
            $payment->failedon = $now;

        } else if (in_array($gopayzStatus, ['SUCCESSFUL', 'S'])) {
            $action = IObservation::ACTION_CONFIRM;
            $payment->status = MyPaymentDetail::STATUS_SUCCESS;
            $payment->successon = $now;
        } else {
            $this->log("Payment {$payment->paymentrefno} callback received but no action was taken.", SNAP_LOG_ERROR);
            $skip = true;
        }

        if (!$skip) {
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['gatewaystatus', 'status', 'successon', 'failedon', 'refundedon', 'verifiedamount']);
            $this->notify(new IObservation($payment, $action, $initialStatus, ['response' => $response]));
        }
    }

    /**
     * Sign the data to be sent to GoPayz
     *
     * @param  string $unsignedData
     * @return string
     */
    protected function signData($unsignedData)
    {
        $this->logDebug(__METHOD__."() Signing data: $unsignedData");

        $pvtKeyLoc = $this->app->getConfig()->{'mygtp.gopayz.privatekeypath'};
        $key = file_get_contents($pvtKeyLoc);

        if (0 >= strlen($key)) {
            throw new \Exception("Unable to retrieve private key");
        }
        
        try {
            $privateKey = new AsymmetricSecretKey($key, new Version1);           
            $signedData = Version1::sign($unsignedData, $privateKey);
            return $signedData;

        } catch (PasetoException $e) {
            $this->logDebug(__METHOD__."(): " . $e->getMessage());
            throw new \Exception("Unable to sign data");
        }
    }

    /**
     * Verify the signed data in Paseto format
     *
     * @param  string $signedData
     * @param  string $plainData
     * @return void
     */
    protected function verifySignedData($signedData, $plainData = null)
    {
        $this->logDebug(__METHOD__."(): Verifying data signed: $signedData");

        $pubKeyLoc = $this->app->getConfig()->{'mygtp.gopayz.publickeypath'};
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
     * Appends the responses
     * 
     * @param string    $existing   Existing remarks
     * @param array     $response   Response from GoPayz 
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