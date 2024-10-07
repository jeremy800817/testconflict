<?php

namespace Snap\api\mygtp;
/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 07-Dec-2020
*/

class MyGtpHtmlApiSender extends MyGtpApiSender
{

    /**
     * Responds in HTML. 
     * If $destination is an array, it will use parameters given in the array to build the response
     * 
     * @param \Snap\App     $app                Snap application
     * @param string        $responseData       The HTML content
     * @param array|null    $destination        Additional parameters
     * 
     * @return string
     */
    public function response($app, $responseData, $destination = null)
    {
        $respCode = $destination['response_code'] ?? 200;
        $contentType = "Content-Type: " . ($destination['content_type'] ?? 'text/html');
        $endDestination = $destination['destination'];

        if (!$endDestination) {
            http_response_code(intval($respCode));
            header($contentType);

            echo $responseData;
        }

        return $responseData;
    }
}