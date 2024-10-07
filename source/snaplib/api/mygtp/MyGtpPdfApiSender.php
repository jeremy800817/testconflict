<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */
namespace Snap\api\mygtp;

use Snap\api\mygtp\MyGtpApiSender;

/**
 * This class implements responding to client request with JSON formatted data
 *
 * @author  Azam <azam@silverstream.my>
 * @version 1.0
 * @package snap.api.gtp
 */
class MyGtpPdfApiSender extends MyGtpApiSender
{
    /**
     * Main method to response to client
     * 
     * @param  App   $app          App Class
     * @param  mixed $responseData Data to send
     * @param  mixed $destination  Depending on sender property, this could be an URL to connect and send data
     */
    function response($app, $responseData, $destination = null)
    {
        $length = strlen($responseData);
        header('Content-Type: application/pdf');
        header("Content-Length: $length");
        if ($destination['attachment']) {
            $filename = $destination['filename'];
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Cache-Control: no-cache");
        } else {
            header("Content-Disposition: inline");
        }
        echo $responseData;

        return json_encode($responseData);
    }

    /**
     * Disallow instantiation of the class through new.
     */
    protected function __construct()
    {
    }
}
