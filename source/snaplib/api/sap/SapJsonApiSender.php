<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\sap;

Use Snap\IApiProcessor;
Use Snap\api\sap\SapApiSender;

/**
 * This class implements responding to client request with XML formatted data
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class SapJsonApiSender extends SapApiSender
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
        
        if(!$destination) {
            echo json_encode($responseData, JSON_PRETTY_PRINT);
        } else {
            return json_encode($responseData, JSON_PRETTY_PRINT);
        }
    }

    /**
     * Disallow instantiation of the class through new.
     */
    protected function __construct()
    {
    }
}
?>