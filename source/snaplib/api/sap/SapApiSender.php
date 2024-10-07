<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\sap;

Use \Snap\App;
Use Snap\IApiProcessor;
Use Snap\api\param\ApiParam;
Use \GuzzleHttp\Client;
Use \GuzzleHttp\Request;
Use \GuzzleHttp\TransferStats;

/**
 * Factory class that generically implements response sending information back to client
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.sap
 */
class SapApiSender implements \Snap\IApiSender
{
    Use \Snap\TLogging;

    /**
     * This is the factory method that will instantiate the appropriate API param class to handle the request.
     * @param  String $responseType  Type of response desired for data
     * @param  String $version       Version number that we would like the params to get
     * @return SapApiSender derived class
     */
    static function getInstance($responseType, $version)
    {
        if(is_array($version) && isset($version['version'])) {
            $version = $version['version'];
        }
        $originalClassName = array_pop(explode('\\', __CLASS__));
        $className = preg_replace( '/'.$originalClassName.'/',
                                    substr($originalClassName, 0, 3) . $responseType . substr($originalClassName, 3),
                                   __CLASS__);
        $classNameVersion = $className . preg_replace('/\./', '_', $version);
        if(class_exists($classNameVersion)) {
            \Snap\App::getInstance()->logDebug("Instantiating Api Sender $classNameVersion");
            return new $classNameVersion;
        }
        if(class_exists($className)) {
            \Snap\App::getInstance()->logDebug("Instantiating Api Sender $className");
            return new $className;
        }
        App::getInstance()->logDebug("Instantiating Api Sender $className");
        return new self;
    }

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
            $this->log("----SAP Response ACTIVATING MOCK UP for ({$_REQUEST['mockup']})-----", SNAP_LOG_ERROR);
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
            $responseTime = 0;
            $this->log("----SAP-HTTP- start --request({".$body."})", SNAP_LOG_DEBUG);
            $response = $client->request($method, $destination['url'], [
                'on_stats' => function (TransferStats $stats) {
                    $this->log("----SAP-HTTP- TIME:({".$stats->getTransferTime()."})--URI:({".$stats->getEffectiveUri()."})", SNAP_LOG_DEBUG);
                    $responseTime = $stats->getTransferTime();
                },
                'body' => $body
            ]);
            // $this->notifySapSlowResponse($responseTime, $body);
            
            $this->log("----SAP-HTTP- end --response({".$response->getBody()."})", SNAP_LOG_DEBUG);
            $responseBody = $response->getBody();
            if('200' == $response->getStatusCode()) {
                $this->log("SapApiSender sending to $destination with data $body -- received response (".$responseBody.")", SNAP_LOG_DEBUG);
                $data = json_decode($responseBody, true);
            } else {
                $responseBody = "Unexpected response from server with status code: ".$response->getStatusCode().". ({$response->getBody()->getContents()}).";
                $data[] = ['error' => $responseBody, 'success' => 'N' ];
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to SAP {$destination['url']} with error " . $e->getMessage() . "\nData: $body\nResponse:".$responseBody, SNAP_LOG_ERROR);
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
    private function __construct()
    {
    }

    /**
     * action on SAP Slow response
     */
    private function notifySapSlowResponse($responseTime, $requestBody, $maxTime = 14){
        if ($responseTime >= $maxTime){
            // action email or reject, direct send cancel response
        }
    }
}
?>