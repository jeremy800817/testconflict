<?php
/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Azam <azam@silverstream.my>
* @version 1.0
* @created 06-09-2021
*/

namespace Snap\api\payout;

use Snap\api\exception\GeneralException;
use Snap\App;
use Snap\IObservation;
use Snap\object\MyAccountHolder;
use Snap\object\MyDisbursement;

class OneCallPayout extends BasePayout 
{
    protected const ENDPOINT_SYSTEM_LOGIN      = 'https://test.onecall.my/api/Home/LoginApi/LoginExternal';
    protected const ENDPOINT_CUSTOMER_CREDIT   = 'https://test.onecall.my/api/Home/ExternalApi/CustomerCredit';

    // List of know error
    protected const ERR_INVALIDSESSION = '-1. Invalid Session';
    protected const ERR_EXPIREDSESSION = 'Object reference not set to an instance of an object.';

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
            $this->log("------ OneCall credit customer request -----", SNAP_LOG_DEBUG);
            $response = $this->creditCustomer($disbursement);
            $this->log("------ OneCall credit customer request END -----", SNAP_LOG_DEBUG);
            
            if (200 == $response->getStatusCode()) {
                $response = $response->getBody()->getContents();
                $this->log("------ OneCall credit customer response -----", SNAP_LOG_DEBUG);
                $this->log($response, SNAP_LOG_DEBUG);
                $responseArr = json_decode($response, true);                
                $this->processDisbursementResponse($disbursement, $responseArr);
                $this->log("------ OneCall credit customer response END -----", SNAP_LOG_DEBUG);
            }

            return true;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $err = $e->getMessage();
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- OneCall Credit Customer Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- OneCall Credit Customer Error End ---", SNAP_LOG_ERROR);
            return false;
        }
    }

    /**
     * Process response from onecall
     *
     * @param  MyDisbursement $disbursement
     * @param  array           $responseArr
     * @return void
     */
    protected function processDisbursementResponse($disbursement, $responseArr)
    {
        $this->log("------ Processing disbursement response -----", SNAP_LOG_DEBUG);
        $jsonEncodedResponseData = $this->decryptData($responseArr['Data']);        
        $this->log($jsonEncodedResponseData, SNAP_LOG_DEBUG);
        $plainData = json_decode($jsonEncodedResponseData, true);
        
        $disbursement->remarks = $jsonEncodedResponseData;
        $disbursement->gatewayrefno = $plainData['TransactionRefId'] ?? $plainData['TransactionId'];

        $initialStatus = $disbursement->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());
        
        if (false === $responseArr['HasError'] && true === $plainData['IsSuccess']) {
            $this->log(__METHOD__ . ": Success payment response received for {$disbursement->refno}, IsSuccess: {$plainData['IsSuccess']}, TransactionId: {$plainData['TransactionId']}" , SNAP_LOG_DEBUG);
            $action = IObservation::ACTION_CONFIRM;            
            $disbursement->disbursedon = $now;
            $disbursement->verifiedamount = $plainData['Amount'];
            $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['disbursedon', 'verifiedamount', 'gatewayrefno', 'remarks']);
        } elseif (false === $responseArr['HasError'] && false === $plainData['IsSuccess']) {
            // For sell we throw errors and dont retry
            $this->log(__METHOD__ . ": Failed payment response received for {$disbursement->refno}, IsSuccess: {$plainData['IsSuccess']}, TransactionId: {$plainData['TransactionId']}" , SNAP_LOG_ERROR);
            $this->handleError(gettext('Unknown error for OneCall transactionid: ' . $plainData['TransactionId']));
        } else {
            // For sell we throw errors and dont retry
            $this->log(__METHOD__ . ": Failed payment response received for {$disbursement->refno}, IsSuccess: {$plainData['IsSuccess']}, TransactionId: {$plainData['TransactionId']}" , SNAP_LOG_ERROR);
            $this->handleError($responseArr['Errors']);
        }
        $this->notify(new IObservation($disbursement, $action, $initialStatus, ['response' => $responseArr]));
    }

    protected function handleError($error)
    {
        if (is_array($error) && 0 < count($error)) {
            $error = $error[0];
        }

        switch ($error) {            
            case static::ERR_INVALIDSESSION:
            case static::ERR_EXPIREDSESSION:
                throw \Snap\api\exception\MyGtpWalletInvalidSession::fromTransaction(null, ['message' => gettext('Invalid Session')]);
                break;
            default:
                throw \Snap\api\exception\MyGtpWalletException::fromTransaction(null, ['message' => $error]);
                break;
        }
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
        // One Call Payout should not reach here
        throw GeneralException::fromTransaction([], [
            'message'   => "Not implemented"
        ]);
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
        $customerData = json_decode(base64_decode($disbursement->token), true);        
        $data['Amount'] = number_format(floatval($disbursement->amount) + floatval($disbursement->fee), 2, '.', '');
        $data['Note'] = $disbursement->refno . ' ' . $disbursement->transactionrefno;
        $data['signedData'] = $this->encryptCreditCustomerData($data['token'] = $customerData['token'], $customerData['pin'], $data['Amount'], $data['Note']);        
        $jsonEncodedRequestData = json_encode($data);
        $this->logApiRequest($jsonEncodedRequestData, $disbursement);
        $this->log($jsonEncodedRequestData, SNAP_LOG_DEBUG);
        
        $now = new \DateTime('now', $this->app->getUserTimezone());
        $disbursementStore = $this->app->mydisbursementStore();
        $disbursement->requestedon = $now;
        $disbursement->signeddata = $data['signedData'];
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
        $url = static::ENDPOINT_CUSTOMER_CREDIT;
        $response = $this->requestPost($url, $encryptedData, $client);

        return $response;
    }

    /**
     * Get the encrypted data for crediting customer
     *
     * @param  string $customerToken
     * @param  string $customerPin
     * @param  string $amount
     * @param  string $remarks
     * @return string
     */
    protected function encryptCreditCustomerData($customerToken, $customerPin = null, $amount, $remarks)
    {
        $data = [
            'Token'  => $customerToken,
            'TnxPin' => $customerPin ?? '0',
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

            $datetime1 = new \DateTime('now');
            $datetime2 = \DateTime::createFromFormat('Y-m-d H:i:sZ', $validity);
            $diff = $datetime2->getTimestamp() - $datetime1->getTimestamp();
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
    
}
?>