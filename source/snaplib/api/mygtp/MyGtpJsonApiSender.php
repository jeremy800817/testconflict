<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\mygtp;


/**
 * This class implements responding to client request with JSON formatted data
 *
 * @author Cheok <cheok@silverstream.my>
 */
class MyGtpJsonApiSender extends MyGtpApiSender
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
            header('Content-Type: application/json;charset=utf-8');
            echo json_encode($responseData);
        }

        return json_encode($responseData);
        
    }

    /**
     * Disallow instantiation of the class through new.
     */
    protected function __construct()
    {
    }
}
?>