<?php

namespace Snap\api\mygtp;
/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 14-Dec-2020
*/

class MyGtpAttachmentApiSender extends MyGtpApiSender
{

    /**
     * Responds in HTML. 
     * If $destination is an array, it will use parameters given in the array to build the response
     * 
     * @param \Snap\App     $app                Snap application
     * @param Attachment    $attachment       The attachment
     * @param array|null    $destination        Additional parameters
     * 
     * @return string
     */
    public function response($app, $attachment, $destination = null)
    {
        if(!$destination) {
            if (0 < strlen($attachment->filename)) {
                if (strlen($attachment->mimetype) == 0) $mimetype = 'text/plain';
                else $mimetype = $attachment->mimetype;
                header('Content-Type: '.$mimetype);
                header('Content-Length: '.$attachment->filesize);
                header("Content-Disposition: inline; filename=\'{$attachment->filename}\'");
                header('Content-Transfer-Encoding: binary');
                echo $attachment->data;
            }
        }

        return $attachment->filename;
    }
}