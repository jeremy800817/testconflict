<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
namespace Snap\api\sap;

use Snap\api\sap\SapApiSender1_0;
use Snap\api\sap\SapApiProcessor1_0;

/**
 * This class implements responding to client request with JSON formatted data
 *
 * @author Cheok <cheok@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class SapPdfApiSender1_0 extends SapApiSender1_0
{

    /**
     * Main method to response to client
     * 
     * @param  App                      $app           App Class
     * @param  mixed  $responseData Data to send
     * @param  mixed $destination   Depending on sender property, this could be an URL to connect and send data
     */
    function response($app, $responseData, $destination = null, $method = 'POST')
    {
        $response = parent::response($app, $responseData, $destination, $method);
        
        if($destination[SapApiProcessor1_0::IS_HTTP]) {
            echo json_encode($response, JSON_PRETTY_PRINT);
        }

        return $response;
        
    }


    function request($method, $uri, $options = [])
    {
        $isHttp = $options[SapApiProcessor1_0::IS_HTTP] ?? false;
        unset($options[SapApiProcessor1_0::IS_HTTP]);

        $this->logDebug(__CLASS__ . " : Sending $method request to $uri\n");

        try {
            $response = parent::request($method, $uri, $options);
            // $bodyContent = $response->getBody()->getContents();
            // $statusCode = $response->getStatusCode();

            // $isSuccess = 299 >= $statusCode && 200 <= $statusCode;

            $bodyContent = $response['responseData'];
            if ($bodyContent) {
                if ($isHttp) {
                    $length = $response->getHeader('Content-Length')[0];
                    $disposition = $response->getHeader('Content-Disposition')[0];
                    header('Content-Type: application/pdf');
                    header("Content-Length: $length");
                    header("Content-Disposition: $disposition");
                    echo $bodyContent;
                }

            } else {
                $this->logDebug(__CLASS__ . " : Response {$response->getStatusCode()} received from $uri with body ({$response->getBody()->getContents()}).");
            }
        } catch ( \GuzzleHttp\Exception\RequestException $e) {
            $isSuccess= false;

            $response = $e->getResponse();
            $responseBody = $bodyContent = $e->getResponse()->getBody()->getContents();
            $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to SAP {$uri} with error " . $e->getMessage() . "\nResponse:".$responseBody, SNAP_LOG_ERROR);
        }

        return ['data' => $bodyContent];
        
    }

    /**
     * Disallow instantiation of the class through new.
     */
    protected function __construct()
    {
    }
}
?>