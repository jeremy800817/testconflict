<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Azam <azam@silverstream.my>
* @version 1.0
* @created 03-Sep-2021
*/

namespace Snap\api\wallet;

use Exception;
use Snap\App;
use Snap\IObservation;
use Snap\object\MyAccountHolder;
use Snap\object\MyPaymentDetail;


class OneCall extends BaseWallet
{
    // Production endpoints   

    /** @todo change to production environment */ 
    protected const ENDPOINT_SYSTEM_LOGIN      = 'https://portal.onecall.my/api/Home/LoginApi/LoginExternal';
    protected const ENDPOINT_CUSTOMER_BALANCE  = 'https://portal.onecall.my/api/Home/ExternalApi/CustomerBalance';
    protected const ENDPOINT_CUSTOMER_DEBIT    = 'https://portal.onecall.my/api/Home/ExternalApi/CustomerDebit';

    // List of known error
    protected const ERR_INSUFFICIENTBALANCE = 'Insufficient Balance. ';
    protected const ERR_INVALIDSESSION      = '-1. Invalid Session';
    
    protected $apiUsername = null;    
    protected $apiPassword = null;    
    protected $apiKey = null;    

    protected function __construct($app)
    {
        parent::__construct($app);

        $this->apiUsername  = $app->getConfig()->{'mygtp.onecall.apiusername'};
        $this->apiPassword  = $app->getConfig()->{'mygtp.onecall.apipassword'};
        $this->apiKey       = $app->getConfig()->{'mygtp.onecall.apikey'};

        if (! $this->apiUsername) {
            throw new \Exception(__CLASS__ . "API username not provided");
        }


        if (! $this->apiPassword) {
            throw new \Exception(__CLASS__ . "API password not provided");
        }

        if (! $this->apiKey) {
            throw new \Exception(__CLASS__ . "API key not provided");
        }

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
        $data = [];
        $customerData = json_decode(base64_decode($payment->token), true);        
        $data['Amount'] = number_format(floatval($payment->amount) + floatval($payment->customerfee) + floatval($payment->gatewayfee), 2, '.', '');
        $data['Note'] = $payment->paymentrefno . ' ' . $payment->sourcerefno . ' ' . $payment->productdesc;
        $data['signedData'] = $this->encryptDebitCustomerData($data['token'] = $customerData['token'], $customerData['pin'], $data['Amount'], $data['Note']);

        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        $payment->requestedon = $now;
        $payment->signeddata  = $data['signedData'];
        $payment->location    = static::ENDPOINT_CUSTOMER_DEBIT;
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['signeddata', 'requestedon', 'location']);
        try {
            $this->log("------ OneCall debit customer request -----", SNAP_LOG_DEBUG);
            $client = $this->httpClientFactory();
            $response = $this->doSendDebitCustomer($payment->signeddata, $client);                        
            $jsonEncodedRequestData = json_encode($data);
            $this->logApiRequest($jsonEncodedRequestData, $payment);
            $this->log($jsonEncodedRequestData, SNAP_LOG_DEBUG);
            $this->log("------ OneCall debit customer request END -----", SNAP_LOG_DEBUG);
            
            if (200 == $response->getStatusCode()) {
                $response = $response->getBody()->getContents();
                $this->log("------ OneCall debit customer response -----", SNAP_LOG_DEBUG);
                $this->log($response, SNAP_LOG_DEBUG);
                $responseArr = json_decode($response, true);                
                
                $this->processPaymentResponse($payment, $responseArr);
                $this->log("------ OneCall debit customer response END -----", SNAP_LOG_DEBUG);
                return true;
            }

            return false;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $err = $e->getMessage();
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- OneCall Debit Customer Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- OneCall Debit Customer Error End ---", SNAP_LOG_ERROR);
            return false;
        }
    }

    /**
     * Process response from onecall
     *
     * @param  MyPaymentDetail $payment
     * @param  array           $responseArr
     * @return void
     */
    protected function processPaymentResponse($payment, $responseArr)
    {
        $this->log("------ Processing payment response -----", SNAP_LOG_DEBUG);
        $jsonEncodedResponseData = $this->decryptData($responseArr['Data']);        
        $this->log($jsonEncodedResponseData, SNAP_LOG_DEBUG);
        $plainData = json_decode($jsonEncodedResponseData, true);
        
        $payment->remarks = $jsonEncodedResponseData;
        $payment->gatewayrefno = $plainData['TransactionRefId'] ?? $plainData['TransactionId'];

        $initialStatus = $payment->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        // Set status of the payment
        $skip = false;
        $state = $this->app->mygtptransactionManager()->getPaymentDetailStateMachine($payment);
        if (true === $responseArr['HasError'] && $state->can(MyPaymentDetail::STATUS_FAILED)) {
            $action = IObservation::ACTION_REJECT;
            $payment->status = MyPaymentDetail::STATUS_FAILED;
            $payment->failedon = $now;
            $payment->gatewaystatus = 'Failed';
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['gatewaystatus', 'status', 'successon', 'failedon', 'verifiedamount', 'gatewayrefno', 'remarks']);
            $this->notify(new IObservation($payment, $action, $initialStatus, ['response' => $responseArr]));
            $this->handleError($responseArr['Errors']);
        } else if (false === $responseArr['HasError'] && false === $plainData['IsSuccess']) {
            $action = IObservation::ACTION_REJECT;
            $payment->status = MyPaymentDetail::STATUS_FAILED;
            $payment->failedon = $now;
            $payment->gatewaystatus = 'Failed';
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['gatewaystatus', 'status', 'successon', 'failedon', 'verifiedamount', 'gatewayrefno', 'remarks']);
            $this->notify(new IObservation($payment, $action, $initialStatus, ['response' => $responseArr]));
            $this->handleError(gettext('Failed to debit wallet'));
        } else if (false === $responseArr['HasError'] && true === $plainData['IsSuccess']) {
            $action = IObservation::ACTION_CONFIRM;
            $payment->status = MyPaymentDetail::STATUS_SUCCESS;
            $payment->successon = $now;
            $payment->verifiedamount = $plainData['Amount'];
            $payment->gatewaystatus = 'Success';            
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['gatewaystatus', 'status', 'successon', 'failedon', 'verifiedamount', 'gatewayrefno', 'remarks']);
            $this->notify(new IObservation($payment, $action, $initialStatus, ['response' => $responseArr]));
        } else {
            $this->log("Payment {$payment->paymentrefno} response received but no action was taken.", SNAP_LOG_ERROR);
            $this->handleError($jsonEncodedResponseData);
        }
        $this->log("------ Processing payment response END -----", SNAP_LOG_DEBUG);
    }

    protected function handleError($error)
    {
        if (is_array($error) && 0 < count($error)) {
            $error = $error[0];
            $this->log(__CLASS__ . ': ' . $error, SNAP_LOG_ERROR);
        }

        switch ($error) {
            case static::ERR_INSUFFICIENTBALANCE:
                throw \Snap\api\exception\MyGtpWalletInsufficientBalance::fromTransaction(null, ['message' => gettext('Insufficient Balance')]);
                break;
            case static::ERR_INVALIDSESSION:
                throw \Snap\api\exception\MyGtpWalletInvalidSession::fromTransaction(null, ['message' => gettext('Invalid Session')]);
                break;
            default:
                throw \Snap\api\exception\MyGtpWalletException::fromTransaction(null, ['message' => $error]);
                break;
        }
    }

    
    /**
     * Does the actual request to OneCall server to debit customer
     * 
     * @param  string             $encryptedData  The encrypted data used as input params
     * @param  \GuzzleHttp\Client $client         Guzzle client
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendDebitCustomer($encryptedData, $client)
    {
        $url = static::ENDPOINT_CUSTOMER_DEBIT;
        $response = $this->requestPost($url, $encryptedData, $client);

        return $response;
    }

    /**
     * Get the encrypted data for getting customer balance
     *
     * @param  string $customerToken
     * @param  string $customerPin
     * @param  string $amount
     * @param  string $remarks
     * @return string
     */
    protected function getCustomerBalanceData($customerToken)
    {
        $data = [
            'Token'  => $customerToken,
        ];

        return $this->encryptData(json_encode($data));
    }

    /**
     * Get the encrypted data for debitting customer
     *
     * @param  string $customerToken
     * @param  string $customerPin
     * @param  string $amount
     * @param  string $remarks
     * @return string
     */
    protected function encryptDebitCustomerData($customerToken, $customerPin, $amount, $remarks)
    {
        $data = [
            'Token'  => $customerToken,
            'TnxPin' => strval($customerPin),
            'Amount' => strval($amount),
            'Note'   => $remarks
        ];

        return $this->encryptData(json_encode($data));
    }


    /**
     * Get the encrypted data that will be used for getting system token
     *
     * @param  string $apiUsername
     * @param  string $apiPassword
     * @param  string $apiKey
     * @return string
     */
    protected function getSystemLoginData($apiUsername, $apiPassword, $apiKey)
    {
        $data = [
            'username' => $apiUsername,
            'password' => $apiPassword,
            'key'      => $apiKey,
        ];

        return $this->encryptData(json_encode($data));
    }

    protected function encryptData($plainData)
    {
        $this->logDebug(__METHOD__."() Encrypting data: $plainData");

        $key = $this->app->getConfig()->{'mygtp.onecall.publickey'};
        $iv = $this->app->getConfig()->{'mygtp.onecall.publiciv'};        

        if (0 >= strlen($key)) {
            throw new \Exception("Unable to retrieve public key contents");
        }

        if (0 >= strlen($iv)) {
            throw new \Exception("Unable to retrieve public iv contents");
        }
        
        $encryptedData = openssl_encrypt($plainData, 'AES-256-CBC', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($iv));
        return base64_encode($encryptedData);
    }

    protected function decryptData($encryptedData)
    {
        $this->logDebug(__METHOD__."(): Decrypting data: $encryptedData");

        $key = $this->app->getConfig()->{'mygtp.onecall.privatekey'};
        $iv = $this->app->getConfig()->{'mygtp.onecall.privateiv'};

        if (0 >= strlen($key)) {
            throw new \Exception("Unable to retrieve private key contents");
        }

        if (0 >= strlen($iv)) {
            throw new \Exception("Unable to retrieve private iv contents");
        }
        
        $plainData = openssl_decrypt(base64_decode($encryptedData), 'AES-256-CBC', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($iv));
        return $plainData;
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
        $data     = array('input' => $encryptedData);
        $url      = $url . '?' . http_build_query($data);        
        $response = $client->post($url);

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
        $app = App::getInstance();

        $key = "onecall.authtoken";
        $token = null;

        // Request token again if missing from cache
        if ( ($token = $app->getCache($key)) == null) {

            // Request token again if missing from cache
            $plainData = $this->requestAuthToken($app);    
            $token = $plainData['Token'];
            $validity = $plainData['Validity'];

            $datetime1 = new \DateTime('now', $app->getUserTimezone());
            $datetime2 = \DateTime::createFromFormat('Y-m-d H:i:sZ', $validity);
            $diff = $datetime2->getTimestamp() - $datetime1->getTimestamp() - 1;
            $app->setCache($key, $token, $diff);
        }


        if (null == $token) {
            $this->log(__METHOD__ . "() Failed to get authentication token.", SNAP_LOG_ERROR);
        }

        return $token;
    }

    /**
     * Does the actual request to get new system token
     */
    protected function requestAuthToken()
    {
        try {
            $encryptedData = $this->getSystemLoginData($this->apiUsername, $this->apiPassword, $this->apiKey);
            $response      = $this->requestPost($url = static::ENDPOINT_SYSTEM_LOGIN, $encryptedData, $this->httpClientFactory(false));
            
            if (200 == $response->getStatusCode()) {
                $response = json_decode($response->getBody()->getContents(), true);

                if (true === $response['HasError']) {
                    $errors = json_encode($response['Errors']);
                    $this->log(__METHOD__ . "() API return errors: " . $errors, SNAP_LOG_ERROR);
                    throw new \Exception(__CLASS__ . ' API return errors: ', $errors);
                }

                $plainData = json_decode($this->decryptData($response['Data']), true);                
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to {$url} with error " . $e->getMessage() . "\nResponse:".$responseBody, SNAP_LOG_ERROR);
            throw $e;
        }

        return $plainData;
    }

    /** Not implemented */
    protected function signData($unsignedData) { }

    /** Not implemented */
    protected function verifySignedData($signedData, $plainData = null) { }
    
    /** Not implemented */
    function getPaymentStatus($paymentDetail) { }
    
    /** Not implemented */
    function verifyPaymentResponse($response) { }

    /** Not implemented */
    public function handleRequest($params, $postBody) { }
}


?>