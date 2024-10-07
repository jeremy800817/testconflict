<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\gtp;

Use Snap\IApiProcessor;
Use Snap\api\gtp\GtpApiSender;
Use \GuzzleHttp\Client;
Use \GuzzleHttp\Request;
Use \GuzzleHttp\TransferStats;

/**
 * This class implements responding to client request with XML formatted data
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class GtpHttpApiSender extends GtpApiSender
{
    Use \Snap\TLogging;

    protected function httpClientFactory($options = null) {
        $defaults = $this->getHttpClientDefaults();

        // Merge defaults with user-provided options
        if ($options && is_array($options) && !empty($options)) {
            // $defaults = $defaults + $options;
            array_merge_recursive($defaults, $options);
        }

        return new \GuzzleHttp\Client($defaults);
    }

    protected function getHttpClientDefaults() {
        return [
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ];
    }


    /**
     * Main method to response to client
     * 
     * @param  App                      $app           App Class
     * @param  mixed  $responseData Data to send
     * @param  mixed $destination   Depending on sender property, this could be an URL to connect and send data
     */
    function response($app, $responseData, $destination = null, $method = 'POST')
    {
        /*check if responseData only have one array/string. Convert it to array.*/
        if (!is_array($responseData[0])){
            $responseData = array($responseData);
        }
        $body = json_encode($responseData, JSON_PRETTY_PRINT);
        //Added by Devon on 2020/04/30 to allow for mockup / simulation data
        $this->log("##################" . $_SERVER['SERVER_NAME'], SNAP_LOG_DEBUG);
        if(isset($_REQUEST['mockup']) && preg_match('/(development$|local|devon$|dev)/', $_SERVER['SERVER_NAME'])) {
            $this->log("----MBB Response ACTIVATING MOCK UP for ({$_RQUEST['mockup']})-----", SNAP_LOG_ERROR);
            $response = $responseData[0];
            $response['success'] = ('fail' == $_REQUEST['mockup']) ? 'N' : 'Y';
            $response['message'] = ('fail' == $_REQUEST['mockup']) ? 'Mocked Fail' : 'Mocked Success';
            return [ 'requestData' => $body,
                 'url' => $destination['url'], 
                 'responseData' => 'Mocked Up Data: ' . json_encode($response, JSON_PRETTY_PRINT), 
                 'data' => [ $response ]
             ];
        } 
        //End add 2020/04/30
        $clientOptions = isset($destination['options']) ? $destination['options'] : null;
        $client = $this->httpClientFactory($clientOptions);
        try {
            $this->log("----GTP-HTTP- start --request({".$body."})", SNAP_LOG_DEBUG);
            $response = $client->request($method, $destination['url'], [
                'on_stats' => function (TransferStats $stats) {
                    $this->log("----MBB-HTTP- TIME:({".$stats->getTransferTime()."})--URI:({".$stats->getEffectiveUri()."})", SNAP_LOG_DEBUG);
                },
                'body' => $body
            ]);
            $this->log("----MBB-HTTP- end --response({".$response->getBody()."})", SNAP_LOG_DEBUG);
            $responseBody = $response->getBody();
            if('200' == $response->getStatusCode()) {
                $this->log("GtpApiSender sending to $destination with data $body -- received response (".$responseBody.")", SNAP_LOG_DEBUG);
                $data = json_decode($responseBody, true);
            } else {
                $responseBody = "Unexpected response from server with status code: ".$response->getStatusCode().". ({$response->getBody()->getContents()}).";
                $data[] = ['error' => $responseBody, 'success' => 'N' ];
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to MBB {$destination['url']} with error " . $e->getMessage() . "\nData: $body\nResponse:".$responseBody, SNAP_LOG_ERROR);
            // $responseBody = "Error caught: " . $e->getMessage();
            $data[] = ['error' => $responseBody, 'success' => 'N' ];

        }
        return [ 'requestData' => $body,
                 'url' => $destination['url'], 
                 'responseData' => (0 == strlen($responseBody)) ? "(empty)" : $responseBody, 
                 'data' => ($data && is_array($data)) ? $data : array(['success' => 'N']),
                 'statusCode' => $response->getStatusCode()
             ];
    }

    /**
     * Disallow instantiation of the class through new.
     */
    protected function __construct()
    {
    }
}
?>