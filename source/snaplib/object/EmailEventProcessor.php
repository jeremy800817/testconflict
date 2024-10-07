<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\object;

Use Snap\App;
Use \Snap\IObservation;
Use \Snap\IObservable;
Use \Snap\TLogging;
Use \Snap\IEventTrigger;
Use \Snap\IEventProcessor as IEventProcessor;
Use \Snap\IEventProcessorData as IEventProcessorData;
Use \Snap\object\baseeventprocessor as baseEventProcessor;
Use \Snap\object\EventJob as eventjob;
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * This class is an abstract class that implements a part of event processor function by keepng the actual
 * heavy duty work done in a temporary event job object first to be used later by the processEvent() method.
 * Actual eventProcessor class should inherit from this class and then implement the processEvent() method to
 * provide actual processing logic.  @See EmailEventProcessor for example.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.base
 */
class EmailEventProcessor extends baseEventProcessor
{
    Use TLogging;  //Logging traits
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

        $mailer->isHTML();
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
        if (! filter_var($receiver, FILTER_VALIDATE_EMAIL)) {
            $this->log("[ProcessorJob]  Incorrect email format {$receiver}.  IGNORED processing this email.", SNAP_LOG_ERROR);
            return false;
        }
        return true;
    }
}
?>