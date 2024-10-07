<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\App;
use Snap\TLogging;
use Snap\IEventProcessorData;
use Snap\object\MyGtpBaseEventProcessor;

class MyGtpEmailEventProcessor extends MyGtpBaseEventProcessor
{
    use TLogging;  //Logging traits
    /**
     * This method is used to actually do processing for the event that has been triggered.   Implementation will be
     * specific to the processor that is implementing it.
     *
     * @param  App              	$app            Application object
     * @param  IEventProcessorData  $registeredData The data that will be used for performing the process.
     * @return void
     */
    public function processEvent($app, IEventProcessorData $registeredData)
    {
        $mailer = $app->getMailer();
        $receiverArr = explode(',', $registeredData->getReceiver());
     
        foreach ($receiverArr as $receiver) {
            $receiver = trim($receiver, ' ');
            if ($this->validateReceiver($receiver)) {
                $mailer->addAddress($receiver);
            }
        }
        $mailer->isHtml(true);
        if (method_exists($registeredData, "getSenderEmail")) {
            $email = call_user_func([$registeredData, "getSenderEmail"]);
            $name = call_user_func([$registeredData, "getSenderName"]);
            if (0 < strlen($email) && 0 < strlen($name)) {
                $mailer->setFrom($email, $name);
            }
            // $mailer->addReplyTo($email, $name);
        }
        if (method_exists($registeredData, "getBCCEmail")) {
            $bccemail = call_user_func([$registeredData, "getBCCEmail"]);
            $bccArr = explode(',', $bccemail);
            foreach ($bccArr as $bcc) {
                $bcc = trim($bcc, ' ');
                if ($this->validateReceiver($bcc)) {
                    $mailer->addBCC($bcc);
                }
            }
        }

        if (method_exists($registeredData, "getAttachment")) {
            $attachment = call_user_func([$registeredData, "getAttachment"]);
            if ($attachment) {
                $mailer->addStringAttachment($attachment['file']->output('S'), $attachment['filename'].'pdf', $encoding = 'base64', $type = 'application/pdf' );
            }
        }
        $mailer->Subject = $registeredData->getSubject();
        $mailer->Body    = $registeredData->getLogContent();
        // $mailer->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mailer->send();
    }

    /**
     * Validates if the receiver provided is in the correct format.
     * @param  string $receiver The receiver format valid for the processor
     * @return boolean          true if valid.  false otherwise
     */
    public function validateReceiver($receiver)
    {
        if (!filter_var($receiver, FILTER_VALIDATE_EMAIL)) {
            $this->log("[ProcessorJob]  Incorrect email format {$receiver}.  IGNORED processing this email.", SNAP_LOG_ERROR);
            return false;
        }
        return true;
    }
}
