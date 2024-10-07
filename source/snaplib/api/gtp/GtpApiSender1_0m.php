<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
namespace Snap\api\gtp;

use \Snap\App;

class GtpApiSender1_0m extends GtpApiSender {

    /**
     * Get authentication token from MBB.
     * 
     * @return string Token
     * 
     */
    function getAuthToken() {
        $ip = \Snap\Common::getRemoteIP();
        if(0 == strlen($ip)) {
            $ip = 'localhost';
        }
        $app = App::getInstance();
        $key = "mbb.authtoken";
        $token = null;
        $url = $app->getConfig()->{'gtp.mbb.token.url'};
        $clientid = $app->getConfig()->{'gtp.mbb.clientid'};
        $clientsecret = $app->getConfig()->{'gtp.mbb.clientsecret'};
        $requestBody = '{"grant_type":"client_credentials","scope":"orders","client_id":"'.$clientid.'","client_secret":"'.$clientsecret.'"}'; 
        
        // Request token again if missing from cache
        if ( ($token = $app->getCache($key)) == null) {
            try {
                $client = new \GuzzleHttp\Client($this->getHttpClientDefaults($url,$options,false));

                $response = $client->request('POST', $url, [
                    'json' => [
                        'grant_type'    => 'client_credentials',
                        'scope'    => 'orders',
                        'client_id'      => $clientid,
                        'client_secret'      => $clientsecret
                    ]
                ]);

                if (200 == $response->getStatusCode()) {
                    $response = json_decode($response->getBody()->getContents(), true);
                    $token = $response['responseData']['access_token'];

                    $app->setCache($key, $token, $response['responseData']['expires_in']);

                    /*CREATE APILOG FOR REFERENCES*/
                    $log = $app->apiLogStore()->create([
                        'type' => 'GTP',
                        'systeminitiate' => 0,
                        'fromip' => $ip,
                        'requestdata' => "URL: " . $url . "\nRequest Body:\n" . $requestBody,
                        'responsedata' => $response,
                        'status' => 1
                    ]);
                    $app->apiLogStore()->save($log);
                }
                else {
                    $this->log(__METHOD__ . "() Failed to get authentication token from MBB endpoint.\n", SNAP_LOG_ERROR);
                    // Handle non OK
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
                $this->log(__METHOD__ . "() Unable to connect to MBB {$url} with error " . $e->getMessage() . "\nResponse:".$responseBody, SNAP_LOG_ERROR);
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => $responseBody]);
                // $responseBody = "Error caught: " . $e->getMessage();
            }
        } else {
            $this->log("Token already exist. No need to trigger get token endpoint.\n", SNAP_LOG_DEBUG);
        }

        if (null == $token) {
            $this->log(__METHOD__ . "() Failed to get authentication token.\n", SNAP_LOG_ERROR);
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Failed to get authentication token.']);
        }
        $this->log("Current token value => ".$token."\n", SNAP_LOG_DEBUG);
        return $token;
    }

    private function resetAuthToken() {
        $key = "mbb.authtoken";
        $this->log(__CLASS__.": Resetting authentication token.", SNAP_LOG_DEBUG);
        App::getInstance()->delCache($key);
    }

    private function getSequenceRandom($length) { //function for 5 sequence no  in X-MB-E2E-Id
        $result = '';
        for($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }
        return $result;
    }

    protected function getHttpClientDefaults($url,$options,$useToken = true)
    {
        $app = App::getInstance(); 
        $urlToken = $app->getConfig()->{'gtp.mbb.token.url'}; 
        $clientid = $app->getConfig()->{'gtp.mbb.clientid'}; 
        $clientsecret = $app->getConfig()->{'gtp.mbb.clientsecret'}; 
        $timestamp = strtotime("now"); 
        $microseconds = round(microtime(true) * 1000);
        $timestampMsgId = date('ymdhis',strtotime("now")); 
        $messageId = 'ACE'.$timestampMsgId.$this->getSequenceRandom(5);
        /*get private key*/
        $pkeypath = $app->getConfig()->{'gtp.ace.privatekey'};
        //$pubkeypath = $app->getConfig()->{'gtp.mbb.publickey'};
        $fp=fopen($pkeypath,"r");
        $priv_key=fread($fp,8192);
        fclose($fp);
        // $passphrase is required if your key is encoded (suggested)
        //$passphrase = 123123; // optional
        //$res = openssl_get_privatekey($priv_key);
        $res = openssl_pkey_get_private(file_get_contents($pkeypath));
        if(!$res) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Failed to get private key => '.openssl_error_string()]);
        }
        /*get private key end*/

        $defaults =  [
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];

        if ($useToken) { 
            $defaults['headers']['X-MB-Signed-Headers'] = 'X-MB-Client-Id;Authorization;X-MB-Timestamp'; 
            $defaults['headers']['X-MB-Signature-Alg'] = 'RSA-SHA256'; 
            $defaults['headers']['X-MB-Client-Id'] = $clientid; 
            $defaults['headers']['Authorization'] = 'Bearer '.$this->getAuthToken(); 

            $encodedURL = urlencode($url); 
            $headerClientId = 'X-MB-Client-Id='.$clientid;
            $headerAuthorization = 'Authorization='.$app->getCache('mbb.authtoken');
            $headerTimestamp = 'X-MB-Timestamp='.$microseconds;
            $requestBody = '{"version":"'.$options['json']['version'].'","future_ref_id":"'.$options['json']['future_ref_id'].'","price_id":"'.$options['json']['price_id'].'","total_price":"'.$options['json']['total_price'].'","future_order_flag":"'.$options['json']['future_order_flag'].'","fo_trans_type":"'.$options['json']['fo_trans_type'].'","tran_date":"'.$options['json']['tran_date'].'","status":"'.$options['json']['status'].'","msg":"'.$options['json']['msg'].'"}';
            $concatenateString = 'PUT;'.$encodedURL.';'.$headerClientId.';'.$headerAuthorization.';'.$headerTimestamp.';'.$requestBody;
            openssl_sign($concatenateString, $signature, $res, OPENSSL_ALGO_SHA256);
            if(!$signature) {
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Failed to create signature => '.openssl_error_string()]);
            }

            $finalValue = base64_encode($signature);

            $defaults['headers']['X-MB-Timestamp'] = $microseconds; 
            $defaults['headers']['X-MB-Signature-Value'] = $finalValue; 
            $defaults['headers']['X-MB-E2E-Id'] = $messageId; 
            $this->log("Base string for match future notification is ".$concatenateString."\n", SNAP_LOG_DEBUG); 
        } else {
            $requestBody = '{"grant_type":"client_credentials","scope":"orders","client_id":"'.$clientid.'","client_secret":"'.$clientsecret.'"}'; 
            $encodedURL = urlencode($urlToken); 
            $concatenateString = 'POST;'.$encodedURL.';X-MB-Timestamp='.$microseconds.';'.$requestBody; 
            openssl_sign($concatenateString, $signature, $res, OPENSSL_ALGO_SHA256);
            if(!$signature) {
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Failed to create signature => '.openssl_error_string()]);
            }
            $finalValue = base64_encode($signature);

            $defaults['headers']['X-MB-Signed-Headers'] = 'X-MB-Timestamp'; 
            $defaults['headers']['X-MB-Signature-Alg'] = 'RSA-SHA256'; 
            $defaults['headers']['X-MB-Timestamp'] = $microseconds; 
            $defaults['headers']['X-MB-Signature-Value'] = $finalValue; 
            $defaults['headers']['X-MB-E2E-Id'] = $messageId;
            $this->log("Base string for match future notification is ".$concatenateString."\n", SNAP_LOG_DEBUG);
        }

        openssl_free_key($res); 
        $this->log("Http Header request is ".json_encode($defaults)."\n", SNAP_LOG_DEBUG);

        return $defaults;
    }

    /**
     * Wrapper for POST request using Guzzle library. Authorization token is automatically added to the request. 
     * 
     * 
     * @param string $uri               The destination URL    
     * @param string $body              The body of the POST request  
     * @param array  $requestOptions    The request options
     * 
     * @throws \GuzzleHttp\Exception\RequestException
     * @return \Psr\Http\Message\ResponseInterface $response
     */
    function requestPost($uri, $body, $requestOptions = []) {
        $options = array_merge([
            'body' => $body,
        ], $requestOptions);

        $response = $this->request('POST', $uri, $options);
        return $response;
    }


    /**
     * Wrapper for GET request using Guzzle library. Authorization token is automatically added to the request.
     * 
     * @param string    $uri        The destination URL
     * @param array     $parameters Parameters for the request
     * @param array     $options    The request options
     * 
     * @throws \GuzzleHttp\Exception\RequestException
     * @return \Psr\Http\Message\ResponseInterface $response
     */
    function requestGet($uri, $parameters = [], $options = []) {
        if (!empty($parameters)) {
            $uri = $uri . "?" . http_build_query($parameters);
        }

        $response = $this->request('GET', $uri, $options);
        return $response;
    }

    /**
     * Create and send an HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     * 
     * @throws \GuzzleHttp\Exception\RequestException
     * @return \Psr\Http\Message\ResponseInterface $response
     */
    function request($method, $uri, $options = []) {
        $app = App::getInstance(); 
        $client = $client = $this->httpClientFactory($uri,$options); 
        $this->logDebug(__CLASS__ . " : Sending $method request to $uri\n"); 
        $response = $client->request($method, $uri, $options);

        // Reset auth token & retry if 401 HTTP Error was returned 
        if (!$retried && 401 == $response->getStatusCode()) {
            $this->logDebug(__CLASS__ . " : Response 401 received from $uri. Retrying again.."); 
            //$this->resetAuthToken(); 
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Response '.$response->getStatusCode().' received from '.$uri.' with body ('.$response->getBody()->getContents().')']);
            $response = $client->request($method, $uri); 
        } else if (200 != $response->getStatusCode()) { 
            $this->logDebug(__CLASS__ . " : Response {$response->getStatusCode()} received from $uri with body ({$response->getBody()->getContents()})."); 
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Response '.$response->getStatusCode().' received from '.$uri.' with body ('.$response->getBody()->getContents().')']);
        } else {
            $response = json_decode($response->getBody()->getContents(), JSON_PRETTY_PRINT);
            $response['url'] = $uri;
            $arrayResponse = (array) $response;
            if($response['txnStatus']['code'] != 'AOK200') {
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Error in response => '.json_encode($arrayResponse)]);
            }
            $this->logDebug(__CLASS__ . " : Response {$response['txnStatus']['code']} received from $uri with body (".json_encode($arrayResponse).")."); 
        }
        return $response;
    }

    /**
     * Main method to response to client
     * 
     * Example of $destination.
     * $destination = [
     *      'url'   => 'http://www.example.com',
     *      'options' => [
     *          'headers' => $headers
     *      ]
     * ]
     * 
     * @param  App                      $app           App Class
     * @param  mixed  $responseData  Data to send in request body
     * @param  mixed  $destination   Depending on sender property, this could be an URL to connect and send data
     * @param  string $method        Request method
     */
    function response($app, $responseData, $destination = null, $method = 'POST') {
        $retried = false;
        $response = parent::response($app, $responseData, $destination, $method);
        
        if (!$retried && 401 == $response['code'])  {
            $this->resetAuthToken();
            $response = parent::response($app, $responseData, $destination, $method);
        }

        return $response;
    }

}