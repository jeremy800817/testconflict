<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\gtp;

Use Snap\IApiProcessor;
Use Snap\api\param\ApiParam;

/**
 * Factory class that generically implements response sending information back to client
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class GtpApiSender implements \Snap\IApiSender
{
    Use \Snap\TLogging;
    /**
     * This is the factory method that will instantiate the appropriate API param class to handle the request.
     * @param  String $responseType  Type of response desired for data
     * @param  String $version       Version number that we would like the params to get
     * @return GtpApiSender derived class
     */
    static function getInstance($responseType, $version)
    {
        if(is_array($version) && isst($version['version'])) {
            $version = $version['version'];
        }
        $originalClassName = array_pop(explode('\\', __CLASS__));
        $className = preg_replace( '/'.$originalClassName.'/',
                                    substr($originalClassName, 0, 3) . $responseType . substr($originalClassName, 3),
                                   __CLASS__);
        $classNameVersion = $className . preg_replace('/\./', '_', $version);
        if(class_exists($classNameVersion)) {
            \Snap\App::getInstance()->logDebug("Instantiating Api Sender 01 $classNameVersion");
            return new $classNameVersion;
        }
        if(class_exists($className)) {
            \Snap\App::getInstance()->logDebug("Instantiating Api Sender 02 $className");
            return new $className;
        }
        \Snap\App::getInstance()->logDebug("Instantiating Api Sender 03 $className");
        return new self;
    }

    protected function httpClientFactory($url,$options = null) {
        $defaults = $this->getHttpClientDefaults($url,$options);
        // Merge defaults with user-provided options
        if ($options && is_array($options) && !empty($options)) {
            // $defaults = $defaults + $options;
            array_merge_recursive($defaults, $options);
        }
        return new \GuzzleHttp\Client($defaults);
    }

    /**
     * Main method to response to client
     * 
     * @param  App                      $app           App Class
     * @param  mixed  $responseData Data to send
     * @param  mixed $destination   Depending on sender property, this could be an URL to connect and send data
     */
    function response($app, $responseData, $destination = null)
    {
        throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                    'action_type' => $apiParam->getActionType(),
                    'processor_class' => __CLASS__]);
    }

    /**
     * Disallow instantiation of the class through new.
     */
    private function __construct()
    {
    }
}
?>