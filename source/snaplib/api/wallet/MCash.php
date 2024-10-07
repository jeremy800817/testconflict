<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Azam <azam@silverstream.my>
* @version 1.0
* @created 20-Oct-2021
*/

namespace Snap\api\wallet;

use Snap\api\exception\MyGtpWalletGatewayPaymentNotFound;
use Snap\api\payout\BasePayout;
use Snap\api\payout\MCashPayout;
use Snap\IObservation;
use Snap\object\MyAccountHolder;
use Snap\object\MyPaymentDetail;


class MCash extends BaseWallet
{
    // Production endpoints
    protected const ENDPOINT_PAYMENT = "https://www.mcash.my/cross/hfive/paymentv01";
    protected const ENDPOINT_CHECK_PAYMENT = "https://www.mcash.my/cross/hfive/transaction/status";
    protected const ACTION_BUY = "buy";

    protected const STATUS_SUCCESS = 1;
    protected const STATUS_FAILED = 0;
    
    protected $key = null;
    protected $merchantId = null;
    protected $callbackUrl = null;
    protected $redirectUrl = null;

    protected function __construct($app)
    {
        parent::__construct($app);
        
        $this->key         = $app->getConfig()->{'mygtp.mcash.key'};
        $this->merchantId  = $app->getConfig()->{'mygtp.mcash.merchant'};
        $this->callbackUrl = $app->getConfig()->{'mygtp.mcash.callbackurl'};
        $this->redirectUrl = $app->getConfig()->{'mygtp.mcash.redirecturl'};

        if (! $this->merchantId) {
            throw new \Exception("MCash merchant ID not provided");
        }

        if (! $this->callbackUrl) {
            throw new \Exception("MCash callback URL not provided");
        }

        if (! $this->redirectUrl) {
            throw new \Exception("MCash redirect URL not provided");
        }

        if (! $this->key) {
            throw new \Exception("MCash key not provided");
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
        $params = array_merge($params, json_decode($postBody, true) ?? []);

        // For sell we use payout class
        if (self::ACTION_BUY !== $params['type']) {                        
            $walletClassString = 'MCashPayout';
            if (filter_var($this->app->getConfig()->{'development'}, FILTER_VALIDATE_BOOLEAN)) {
                $walletClassString .= "UAT";
            }

            $payout = BasePayout::getInstance($walletClassString);
            return $payout->handleResponse($params, $postBody);
        }

        $gatewayRef = $params['orderId'];
        $errMsgTag = ['sourcerefno' => ''];
        if (0 < strlen($gatewayRef)) {
            $payment = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $gatewayRef);
            $errMsgTag['sourcerefno'] = $gatewayRef;
        }

        if (! $payment) {
            $this->logDebug(__METHOD__."(): No payment found for response {". json_encode($params)."}");
            throw MyGtpWalletGatewayPaymentNotFound::fromTransaction([], $errMsgTag);
        }

        $this->logApiResponse(json_encode($params), $payment);

        // Verify signed data from callback
        if (! $this->verifyPaymentResponse($params)) {
            $this->log(__METHOD__. "(): Cannot verify signed data.", SNAP_LOG_ERROR);
            $this->log($postBody, SNAP_LOG_ERROR);
            throw new \Exception("Signed data verification failed.");
        }
        $this->logDebug(__METHOD__."() Payment response verified.");

        // Update paymentdetail status
        $this->processCallback($params);
        return ['success' => true];
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
        $this->log("------ MCash prepare transaction payment location -----", SNAP_LOG_DEBUG);
        $data = [            
            'merchant'      => $this->merchantId,
            'action'        => self::ACTION_BUY,
            'refId'         => $payment->token,
            'orderId'       => $payment->sourcerefno,
            'amount'        => number_format(floatval($payment->amount) + floatval($payment->customerfee) + floatval($payment->gatewayfee), 2, '.', ''),
            'description'   => $payment->productdesc,
            'callbackURL'   => $this->callbackUrl,
            'redirectURL'   => $this->redirectUrl,
        ];
        
        $formatted = $this->formatData($data);        
        $data['hash'] = $this->signData($formatted);
        $payment->signeddata = $data['hash'];
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['signeddata']);        
        
        $data['location'] = static::ENDPOINT_PAYMENT;        
        $paywallLocation =  http_build_query($data);

        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());
        $payment->gatewaystatus = 'PENDING';
        $payment->location = $paywallLocation;
        $payment->status = MyPaymentDetail::STATUS_PENDING_PAYMENT;
        $payment->requestedon = $now;
        $payment = $this->app->mypaymentdetailStore()->save($payment);

        $this->log("------ MCash prepare transaction payment location END -----", SNAP_LOG_DEBUG);
        $jsonEncodedRequestData = json_encode($data);
        $this->logApiRequest($jsonEncodedRequestData, $payment);

        return $paywallLocation;
    }
    
    /**
     * Get status of transaction from MCash
     * 
     * @param MyPaymentDetail $paymentDetail
     * 
     */
    function getPaymentStatus($paymentDetail)
    {   
        try {     
            $response = $this->doSendGetTransactionStatus($paymentDetail);
            $returnArray = json_decode($response->getBody()->getContents(), true);

            // Check for response status first. The transaction status is inside data array
            if (self::STATUS_SUCCESS === intval($returnArray['status']) && array_key_exists('data', $returnArray)) {
                $this->log("------ MCash transaction status found for {$paymentDetail->sourcerefno} -----", SNAP_LOG_DEBUG);
                $this->processCallback($returnArray['data']);

                return $returnArray;
            }

            $this->log("------ MCash transaction status not found for {$paymentDetail->sourcerefno} -----", SNAP_LOG_ERROR);
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

    protected function processCallback($response, $skipAppendResponse = false)
    {
        $mcashStatus = intval($response['status']);
        $payment = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $response['orderId']);
        if (! $payment) {
            $this->log(__METHOD__."(): Unable to find corresponding payment. Gateway ref: ({$response['orderId']})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding payment. Gateway ref: ({$response['orderId']})");
        }

        $this->logDebug(__METHOD__."(): Received response from MCash");
        $this->logDebug(json_encode($response));
        if (!$skipAppendResponse) {
            $payment->remarks = $this->appendResponses($payment->remarks, $response);
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['remarks']);
        }
        $payment->verifiedamount = $response['amount'] ?? $payment->verifiedamount;
        $payment->gatewaystatus = $response['status'];
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['verifiedamount', 'gatewaystatus']);
        $initialStatus = $payment->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        // Set status of the payment
        $skip = false;
        $state = $this->app->mygtptransactionManager()->getPaymentDetailStateMachine($payment);
        if (self::STATUS_FAILED == $mcashStatus && $state->can(MyPaymentDetail::STATUS_FAILED)) {
            $action = IObservation::ACTION_REJECT;
            $payment->status = MyPaymentDetail::STATUS_FAILED;
            $payment->failedon = $now;
        } else if (self::STATUS_SUCCESS == $mcashStatus) {
            $action = IObservation::ACTION_CONFIRM;
            $payment->status = MyPaymentDetail::STATUS_SUCCESS;
            $payment->successon = $now;
        } else {
            $this->log("Payment {$payment->sourcerefno} callback received but no action was taken.", SNAP_LOG_ERROR);
            $skip = true;
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
        return base64_encode(hash('sha256', $unsignedData));
    }

    /**
     * Verifies the signed data
     * 
     * @return boolean
     */
    protected function verifySignedData($signedData, $plainData)
    {        
        $signedData = base64_decode($signedData);
        $rawSigned = hash('sha256', $plainData);

        return $rawSigned === $signedData;
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
        $tmpArr = [];
        $tmpArr[] = $this->key;
        $tmpArr[] = $arr['orderId'];
        $tmpArr[] = number_format($arr['amount'], 2, '.', '');
        $tmpArr[] = $arr['action'];        
        $tmpArr[] = $this->merchantId;
        $formatted = implode("", $tmpArr);
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
        $data = [            
            'merchant' => $this->merchantId,
            'orderId'  => $paymentDetail->sourcerefno,
            'action'   => self::ACTION_BUY,
        ];
        
        $formatted    = $this->formatCheckPaymentData($data);
        $data['hash'] = $this->signData($formatted);
        $client       = $this->httpClientFactory();        
        $url          = static::ENDPOINT_CHECK_PAYMENT;
        $response     = $client->post($url, ['form_params' => $data]);

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