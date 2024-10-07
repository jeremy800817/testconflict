<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\mygtp;


/**
 * Factory class that generically implements response sending information back to client
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class MyGtpApiSender implements \Snap\IApiSender
{

    /**
     * This is the factory method that will instantiate the appropriate API param class to handle the request.
     * @param  String $responseType  Type of response desired for data
     * @param  String $version       Version number that we would like the params to get
     * @return MyGtpApiSender derived class
     */
    static function getInstance($responseType, $version)
    {
        if(is_array($version) && isset($version['version'])) {
            $version = $version['version'];
        }
        $originalClassName = array_pop(explode('\\', __CLASS__));
        $className = preg_replace( '/'.$originalClassName.'/',
                                    substr($originalClassName, 0, 5) . $responseType . substr($originalClassName, 5),
                                   __CLASS__);
        if(class_exists($className)) {
            \Snap\App::getInstance()->logDebug("Instantiating Api Sender $className");
            return new $className;
        }
        $className .=  preg_replace('/\./', '_', $version);
        if(class_exists($className)) {
            \Snap\App::getInstance()->logDebug("Instantiating Api Sender $className");
            return new $className;
        }
        \Snap\App::getInstance()->logDebug("Instantiating Api Sender $className");
        return new self;
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