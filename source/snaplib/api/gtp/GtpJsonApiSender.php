<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\gtp;

Use Snap\IApiProcessor;
Use Snap\api\gtp\GtpApiSender;

/**
 * This class implements responding to client request with XML formatted data
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class GtpJsonApiSender extends GtpApiSender
{

    /**
     * Main method to response to client
     * 
     * @param  App                      $app           App Class
     * @param  mixed  $responseData Data to send
     * @param  mixed $destination   Depending on sender property, this could be an URL to connect and send data
     */
    function response($app, $responseData, $destination = null)
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