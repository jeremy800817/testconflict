<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
namespace Snap\api\gtp;

use \Snap\App;

class GtpApiSender1_0p extends GtpApiSender {

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
        $retried = false;
        $client = $client = $this->httpClientFactory($options);
        $response = $client->request($method, $uri);
        
        if (!$retried && 401 == $response->getStatusCode()) {
            // Retry due to outdated/wrong token
            $this->resetAuthToken();
            $response = $client->request($method, $uri);
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