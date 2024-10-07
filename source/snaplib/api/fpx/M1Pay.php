<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 12-Nov-2020
*/

namespace Snap\api\fpx;

use Snap\api\exception\MyGtpFpxGatewayPaymentNotFound;
use Snap\api\mygtp\MyGtpApiSender;
use Snap\App;
use Snap\IObservation;
use Snap\object\MyPaymentDetail;


class M1Pay extends BaseFpx
{
    // Production endpoints
    protected const ENDPOINT_TOKEN = "https://keycloak.m1pay.com.my/auth/realms/m1pay-users/protocol/openid-connect/";
    protected const ENDPOINT_CREATE_TRANSACTION = "https://gateway.m1pay.com.my/wall/api/transaction";
    protected const ENDPOINT_GET_TRANSACTION_INFO = "https://gateway.m1pay.com.my/wall/api/m-1-pay-transactions";

    protected const CHANNEL_ONLINE_BANKING  = 'ONLINE_BANKING';
    protected const CHANNEL_CARD_PAYMENT    = 'CARD_PAYMENT';
    protected const CHANNEL_MAYBANK_QR      = 'MAYBANK_QR';
    protected const CHANNEL_UMOBILE         = 'UMOBILE';

    protected $merchantId = null;

    protected function __construct($app)
    {
        parent::__construct($app);

        $this->merchantId = $app->getConfig()->{'mygtp.m1pay.clientid'};
        if (! $this->merchantId) {
            throw new \Exception("M1Pay client ID not provided");
        }
    }

    /**
     * Method to handle direct/indirect returns & callbacks from FPX
     * 
     * @param array $params         Params decoded by handler
     * @param string $postBody      Body of POST request if any
     */
    public function handleRequest($params, $postBody)
    {
        // https://www.m1payall.com/fpx/transactionresult?transactionId=16124093890872955291&sellerOrderNo=24093890218722330
        // https://easigolduat.ace2u.com/fpx/m1front.php?transactionId=24093890218722330
        $gatewayRef = $params['sellerOrderNo'] ?? $params['transactionId'];
        $errMsgTag = ['paymentrefno' => ''];
        if (0 < strlen($gatewayRef)) {
            $payment = $this->app->mypaymentdetailStore()->getByField('gatewayrefno', $gatewayRef);
            $errMsgTag['paymentrefno'] = $gatewayRef;
        }
        if (! $payment) {
            $this->logDebug(__METHOD__."(): No payment found for response {". json_encode($params)."}");
            throw MyGtpFpxGatewayPaymentNotFound::fromTransaction([], $errMsgTag);
        }

        if (isset($params['isfront']) && $params['isfront']) {
            // frontend redirect
            // No data passed during redirect, so we only check trx status if payment is pending
            if (MyPaymentDetail::STATUS_PENDING_PAYMENT == $payment->status) {
                $this->getPaymentStatus($payment);
            }

            // Print receipt page
            $receiptPage = $this->app->mygtptransactionManager()->createReceiptPage($payment);
            $sender = MyGtpApiSender::getInstance("Html", null);
            return $sender->response($this->app, $receiptPage);
        } else {
            // m1 callback
            $response = $params;
            $this->logApiResponse(json_encode($response), $payment);

            // Verify signed data from callback
            if (! $this->verifyPaymentResponse($response)) {
                $this->log(__METHOD__. "(): Cannot verify signed data.", SNAP_LOG_ERROR);
                $this->log($postBody, SNAP_LOG_ERROR);
                throw new \Exception("Signed data verification failed.");
            }
            $this->logDebug(__METHOD__."() Payment response verified.");

            // Update paymentdetail status
            $this->processCallback($response);
            return ['success' => true];
        }
    }


    /**
     * Initializes a transaction for M1Pay gateway
     * merchantOrderNo => $payment->sourcerefno
     * exchangeOrderNo => $payment->paymentrefno
     * sellerOrderNo   => $payment->gatewayrefno
     * 
     * 
     * @param MyAccountHolder $accHolder            The account holder
     * @param MyPaymentDetail $payment              The payment detail
     * 
     * @return string      URL to merchant wall/bank login page
     */
    function initializeTransaction($accHolder, $payment)
    {
        $fpxList = $this->app->getConfig()->{'mygtp.fpx.banklist'};
        $data = [
            'productDescription' => $payment->productdesc,
            'transactionAmount' => number_format(floatval($payment->amount) + floatval($payment->customerfee) + floatval($payment->gatewayfee), 2, '.', ''),
            'merchantId' => $this->merchantId,
            'transactionCurrency' => "MYR", // constant
            'merchantOrderNo' => $payment->sourcerefno,
            'exchangeOrderNo' => $payment->paymentrefno,        // optional
            'fpxBank'         => (isset($fpxList) ? $this->app->getConfig()->{'mygtp.fpx.banklist'} : 1),
            'emailAddress'    => $accHolder->email,
            'channel'         => $this::CHANNEL_ONLINE_BANKING
        ];
        $formatted = $this->formatData($data);
        $data['signedData'] = $this->signData($formatted);
        $payment->signeddata = $data['signedData'];
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['signeddata']);

        try {
            $client = $this->httpClientFactory();
            $response = $this->doSendCreateTransaction($data, $client);
            $jsonEncodedData = json_encode($data);
            $this->logApiRequest($jsonEncodedData, $payment);
            $this->log("------ M1Pay Create transaction request -----", SNAP_LOG_DEBUG);
            $this->log($jsonEncodedData, SNAP_LOG_DEBUG);
            $this->log("------ M1Pay Create transaction request END -----", SNAP_LOG_DEBUG);
            // Paywall location is returned
            if (200 == $response->getStatusCode()) {
                $paywallLocation = $response->getBody()->getContents();
                $this->log("------ M1Pay Create transaction response -----", SNAP_LOG_DEBUG);
                $this->log($paywallLocation, SNAP_LOG_DEBUG);
                $this->log("------ M1Pay Create transaction response END -----", SNAP_LOG_DEBUG);
                $query = parse_url($paywallLocation, PHP_URL_QUERY);
                $queryArr = [];
                parse_str($query, $queryArr);

                $now = new \DateTime();
                $now->setTimezone($this->app->getUserTimezone());
                $token = explode(' ', $client->getConfig('headers')['Authorization'])[1];

                $payment->token = $token;
                $payment->gatewaystatus = 'REQUEST';
                $payment->status = MyPaymentDetail::STATUS_PENDING_PAYMENT;
                $payment->location = $paywallLocation;
                $payment->gatewayrefno = $queryArr['transactionId'];
                $payment->requestedon = $now;
                $payment = $this->app->mypaymentdetailStore()->save($payment);
            }

            return $paywallLocation;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $err = $e->getMessage();
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- M1Pay Create Transaction Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- M1Pay Create Transaction Error End ---", SNAP_LOG_ERROR);
            return false;
        }
    }

    /**
     * Does the actual request to M1 server to create transaction
     * 
     * @param array              $data      The data to send
     * @param \GuzzleHttp\Client $client    Guzzle client
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendCreateTransaction($data, $client)
    {
        $url = $this::ENDPOINT_CREATE_TRANSACTION;
        $body = json_encode($data);
        $response = $client->post($url, [
            'body'  => $body
        ]);

        return $response;
    }


    /**
     * Get status of transaction from M1Pay
     * 
     * @param MyPaymentDetail $paymentDetail
     * 
     * @return MyPaymentDetail Updated paymentdetail
     */
    function getPaymentStatus($paymentDetail)
    {
        try {
            $response = $this->doSendGetTransactionStatus($paymentDetail);

            $returnArray = json_decode($response->getBody()->getContents(), true);
            $returnArray['status'] = $returnArray['transactionStatus'];
            $returnArray['sellerOrderNo'] = $returnArray['transactionId'];

            // Process callback
            $this->processCallback($returnArray, true);
            return $returnArray;

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- M1Pay Get Transaction Status Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- M1Pay Get Transaction Status Error End ---", SNAP_LOG_ERROR);
        }
        return $response;
    }


    /**
     * Does the actual request to M1 server to create transaction
     * 
     * @param MyPaymentDetail $paymentDetail        The payment detail
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendGetTransactionStatus($paymentDetail)
    {
        $client = $this->httpClientFactory();
        $url = $this::ENDPOINT_GET_TRANSACTION_INFO . '/' . $paymentDetail->gatewayrefno;
        $response = $client->get($url);

        return $response;
    }

    /**
     * Verifies the callback from M1
     * 
     * @return boolean
     */
    function verifyPaymentResponse($response)
    {
        $formatArr = [];
        $formatArr[] = $response['transactionAmount'];
        $formatArr[] = $response['fpxTxnId'];
        $formatArr[] = $response['sellerOrderNo'];
        $formatArr[] = $response['status'];
        $formatArr[] = $response['merchantOrderNo'];

        $plain = implode("|", $formatArr);
        return $this->verifySignedData($response['signedData'], $plain);
    }


    /**
     * Available status from M1 docs:
     * 
     * Status of transaction. (Status values are:
     * REQUEST, APPROVED, ROLLBACK, UNSUCCESSFUL, PENDING,
     * CANCELLED, FAILED, CAPTURED, SUCCESSFUL, COMPLETED,
     * CANCEL, COMPLETED_ACK
     * )
     * The followings are the success values of different channels:
     * APPROVED: FPX
     * CAPTURED: CARD_PAYMENT
     * SUCCESSFUL: EMONEI, ALIPAY
     * COMPLETED: BOOST
     * SUCCESSFUL: UMOBILE
     * 
     * 
     * According to M1:
     * REQUEST, is unsuccessful on the cardpayment
     * APPROVED, is successful on FPX
     * ROLLBACK, 
     * UNSUCCESSFUL, 
     * PENDING,
     * CANCELLED, 
     * FAILED, 
     * CAPTURED, is successful on cardpayment
     * SUCCESSFUL, is successful on Emonei wallet
     * COMPLETED,  successful 
     * CANCEL, 
     * 
     * REQUEST the status is not completed on the FPX
     */
    protected function processCallback($response, $skipAppendResponse = false)
    {
        // extract($response, EXTR_PREFIX_ALL, "m1");
        $m1_status = $response['status'];
        $payment = $this->app->mypaymentdetailStore()->getByField('gatewayrefno', $response['sellerOrderNo']);
        if (! $payment) {
            $this->log(__METHOD__."(): Unable to find corresponding payment. Gateway ref: ({$response['sellerOrderNo']})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding payment. Gateway ref: ({$response['sellerOrderNo']})");
        }

        $this->logDebug(__METHOD__."(): Received response from M1");
        $this->logDebug(json_encode($response));
        if (!$skipAppendResponse) {
            $payment->remarks = $this->appendResponses($payment->remarks, $response);
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['remarks']);
        }
        $payment->verifiedamount = $response['transactionAmount'] ?? $payment->verifiedamount;
        $payment->gatewaystatus = $response['status'];
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['verifiedamount', 'gatewaystatus']);
        $initialStatus = $payment->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        // Set status of the payment
        $skip = false;
        $state = $this->app->mygtptransactionManager()->getPaymentDetailStateMachine($payment);
        if (in_array($m1_status, ['UNSUCCESSFUL', 'FAILED']) && $state->can(MyPaymentDetail::STATUS_FAILED)) {
            $action = IObservation::ACTION_REJECT;
            $payment->status = MyPaymentDetail::STATUS_FAILED;
            $payment->failedon = $now;
            
        } else if ("CANCELLED" == $m1_status && $state->can(MyPaymentDetail::STATUS_CANCELLED)) {
            $action = IObservation::ACTION_CANCEL;
            $payment->status = MyPaymentDetail::STATUS_CANCELLED;
            $payment->failedon = $now;

        } else if (in_array($m1_status, ['CAPTURED', 'APPROVED'])) {
            $action = IObservation::ACTION_CONFIRM;
            $payment->status = MyPaymentDetail::STATUS_SUCCESS;
            $payment->successon = $now;

        } else if ("ROLLBACK" == $m1_status && $state->can(MyPaymentDetail::STATUS_REFUNDED)) {
            $action = IObservation::ACTION_REVERSE;
            $payment->status = MyPaymentDetail::STATUS_REFUNDED;
            $payment->refundedon = $now;
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
     * transactionAmount    The price of product or service that merchant wants to sell
     * merchantId           Get it from M1Pay support team
     * transactionCurrency  Currently it needs constant value “MYR”, but it will change in next releases
     * merchantOrderNo      A unique value to keep track of transaction
     * exchangeOrderNo      Extra optional unique value to keep track of transaction
     * productDescription   Description about product or service that merchant wants to sell
     * fpxBank              constant value 1, but it will change in next releases
     * emailAddress         Optional email address of customer
     * signedData           Signing data by private key provided by M1Pay.
     * channel              Optional(If left blank, will use M1 payment wall) Selected channel by customer. (channel values are: ONLINE_BANKING, CARD_PAYMENT, MAYBANK_QR, UMOBILE)
     * phoneNumber          Phone number of the customer (Mandatory only for UMOBILE channel)
     */
    protected function formatData($arr)
    {
        $formatted = "";
        $tmpArr = [];
        $tmpArr[] = $arr['productDescription'] ?? '';
        $tmpArr[] = number_format($arr['transactionAmount'], 2, '.', '');
        $tmpArr[] = $arr['exchangeOrderNo'] ?? '';
        $tmpArr[] = $arr['merchantOrderNo'] ?? '';
        $tmpArr[] = "MYR";
        $tmpArr[] = $arr['emailAddress'];
        $tmpArr[] = $this->merchantId;

        $formatted = implode("|", $tmpArr);
        return $formatted;
    }


    /**
     * Signs data and return hex representation of the signed data
     * 
     * @param string $unsignedData     The data to be signed
     * 
     * @return string
     */
    protected function signData($unsignedData)
    {
        $pvtKeyLoc = $this->app->getConfig()->{'mygtp.m1pay.privatekeypath'};
        $signedData = "";

        $this->logDebug(__METHOD__."() Signing data: $unsignedData");
        $pkeyid = openssl_get_privatekey("file://".$pvtKeyLoc, "");
        if (! $pkeyid) {
            while ($err = openssl_error_string()) {
                $this->logDebug(__FUNCTION__."(): $err");
            }
            throw new \Exception("Unable to retrieve private key");
        }
        openssl_sign($unsignedData, $signedData, $pkeyid, "sha1WithRSAEncryption");
        openssl_pkey_free($pkeyid);

        $hexData = bin2hex($signedData);
        $hexData = strtoupper($hexData);
        return $hexData;
    }

    /**
     * Verifies the signed data
     * 
     * @return boolean
     */
    protected function verifySignedData($signedData, $plainData)
    {
        $pubKeyLoc = $this->app->getConfig()->{'mygtp.m1pay.publickeypath'};
        $signedData = strtolower($signedData);
        $rawSigned = hex2bin($signedData);

        $this->logDebug(__METHOD__."(): Verifying data plain : $plainData");
        $this->logDebug(__METHOD__."(): Verifying data signed: $signedData");

        $pkeyid = openssl_get_publickey("file://".$pubKeyLoc);
        if (! $pkeyid) {
            while ($err = openssl_error_string()) {
                $this->logDebug(__FUNCTION__."(): $err");
            }
            throw new \Exception("Unable to retrieve public key");
        }

        $verified = openssl_verify($plainData, $rawSigned, $pkeyid, "sha1WithRSAEncryption");
        openssl_pkey_free($pkeyid);

        return 1 == $verified;
    }

    /**
     * Appends the responses
     * 
     * @param string    $existing   Existing remarks
     * @param array     $response   Response from M1 
     * 
     * @return string   Appended remarks
     */
    protected function appendResponses($existing, $response)
    {
        $currentRemarks = json_decode($existing, true);
        $now = new \DateTime();

        // Remove signedData from remarks because already verified
        if (isset($response['signedData'])) {
            unset($response['signedData']);
        }

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

        if ($addToken) {
            $options['headers']['Authorization'] = "Bearer " . $this->getAuthToken();
        }

        return new \GuzzleHttp\Client($options);
    }


    /**
     * Get authentication token from M1.
     * 
     * @return string Token
     * 
     */
    protected function getAuthToken()
    {
        $app = App::getInstance();

        // Request token again if missing from cache
        $token = $this->requestAccessToken($app);

        if (null == $token) {
            $this->log(__METHOD__ . "() Failed to get authentication token.", SNAP_LOG_ERROR);
        }

        return $token;
    }

    /**
     * Does the actual request to get new access token for each transaction
     */
    private function requestAccessToken($app)
    {
        try {
            $url = $this::ENDPOINT_TOKEN;
            $username = $this->merchantId;
            $password = $app->getConfig()->{'mygtp.m1pay.clientsecret'};

            $client = $this->httpClientFactory(false);
            $response = $client->request('POST', $url, [
                'form_params' => [
                    'grant_type'     => 'client_credentials',
                    'client_id'      => $username,
                    'client_secret'  => $password
                ]
            ]);

            if (200 == $response->getStatusCode()) {
                $response = json_decode($response->getBody()->getContents(), true);
                $token = $response['access_token'];
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to {$url} with error " . $e->getMessage() . "\nResponse:".$responseBody, SNAP_LOG_ERROR);
            throw $e;
            // $responseBody = "Error caught: " . $e->getMessage();
        }

        return $token;
    }
}


?>