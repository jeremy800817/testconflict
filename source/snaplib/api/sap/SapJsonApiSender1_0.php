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
class SapJsonApiSender1_0 extends SapApiSender1_0
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

    /**
     * Disallow instantiation of the class through new.
     */
    protected function __construct()
    {
    }
}
?>