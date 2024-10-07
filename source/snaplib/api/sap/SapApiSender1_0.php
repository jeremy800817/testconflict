<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
namespace Snap\api\sap;

use \Snap\App;

class SapApiSender1_0 extends SapApiSender {

    /**
     * Get authentication token from SAP.
     * 
     * @return string Token
     * 
     */
    function getAuthToken() {
        $app = App::getInstance();
        $key = "sap.authtoken";
        $token = null;

        // Request token again if missing from cache
        if ( ($token = $app->getCache($key)) == null) {
            try {
                $url = $app->getConfig()->{'gtp.sap.token.url'};
                $username = $app->getConfig()->{'gtp.sap.username'};
                $password = $app->getConfig()->{'gtp.sap.password'};

                $client = new \GuzzleHttp\Client($this->getHttpClientDefaults(false));
                $response = $client->request('POST', $url, [
                    'form_params' => [
                        'grant_type'    => 'password',
                        'username'      => $username,
                        'password'      => $password
                    ]
                ]);

                if (200 == $response->getStatusCode()) {
                    $response = json_decode($response->getBody()->getContents(), true);
                    $token = $response['access_token'];

                    $app->setCache($key, $token, $response['expires_in']);
                }
                else {
                    // Handle non OK
                }


            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
                $this->log(__METHOD__ . "() Unable to connect to SAP {$url} with error " . $e->getMessage() . "\nResponse:".$responseBody, SNAP_LOG_ERROR);
                // $responseBody = "Error caught: " . $e->getMessage();
            }
        }

        if (null == $token) {
            $this->log(__METHOD__ . "() Failed to get authentication token.\n", SNAP_LOG_ERROR);
        }

        return $token;
    }

    protected function resetAuthToken() {
        $key = "sap.authtoken";
        $this->log(__CLASS__.": Resetting authentication token.", SNAP_LOG_DEBUG);
        App::getInstance()->delCache($key);
    }

    protected function getHttpClientDefaults($useToken = true)
    {
        $defaults =  [
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];

        if ($useToken) {
            $defaults['headers']['Authorization'] = 'Bearer ' . $this->getAuthToken();
        }

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
        $client = $client = $this->httpClientFactory();

        $this->logDebug(__CLASS__ . " : Sending $method request to $uri\n");
        $response = $client->request($method, $uri, $options);
        
        // Reset auth token & retry if 401 HTTP Error was returned
        if (401 == $response->getStatusCode()) {
            $this->logDebug(__CLASS__ . " : Response 401 received from $uri. Retrying again..");

            $this->resetAuthToken();
            $response = $client->request($method, $uri, $options);
        } else if (200 != $response->getStatusCode()) {
            $this->logDebug(__CLASS__ . " : Response {$response->getStatusCode()} received from $uri with body ({$response->getBody()->getContents()}).");
        }
        
        $responseBody = $response->getBody()->getContents();
        $data = json_decode($responseBody);
        // return $response;
        return [ 'requestData' => json_encode($options['form_params']),
            'url' => $uri, 
            'responseData' => (0 == strlen($responseBody)) ? "(empty)" : $responseBody, 
            'data' => ($data) ? $data : array(['actionSuccess' => 'N']),
            'statusCode' => $response->getStatusCode()
        ];
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
        $response = parent::response($app, $responseData, $destination, $method);
        
        // Reset auth token & retry if 401 HTTP Error was returned
        if (401 == $response['statusCode'])  {
            $this->resetAuthToken();
            $response = parent::response($app, $responseData, $destination, $method);
        }

        return $response;
    }

}