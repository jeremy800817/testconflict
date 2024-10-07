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

/**
 * This class is an abstract class that implements a part of event processor function by keepng the actual
 * heavy duty work done in a temporary event job object first to be used later by the processEvent() method.
 * Actual eventProcessor class should inherit from this class and then implement the processEvent() method to
 * provide actual processing logic.  @See EmailEventProcessor for example.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.base
 */
class SmsEventProcessor extends baseEventProcessor
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
        $sms = new \Snap\manager\SmsManager($app);
        $this->log("SMS service: ".$registeredData->getReceiver()." - ".$registeredData->getLogContent()." : start", SNAP_LOG_DEBUG);
        $receiverArr = explode(',', $registeredData->getReceiver());
        foreach ($receiverArr as $receiver) {
            $receiver = trim($receiver, ' ');
            $this->log("SMS service: ".$receiver." : processing", SNAP_LOG_DEBUG);
            if ($this->validateReceiver($receiver)) {
                $this->log("SMS service: ".$receiver." : validating", SNAP_LOG_DEBUG);
                $sms->phoneNumber = $receiver;
                $sms->messagePayLoad = $registeredData->getLogContent();
                if (!$sms->send()){
                    $this->log("Unable to sent SMS number: ".$sms->phoneNumber." payload: ".$sms->messagePayLoad, SNAP_LOG_ERROR);
                    throw new \Exception("Unable to sent SMS number: ".$sms->phoneNumber." payload: ".$sms->messagePayLoad);
                }else{
                    $this->log("SMS service: ".$receiver." : success", SNAP_LOG_DEBUG);
                }
            }
        }
    }

    /**
     * Validates if the receiver provided is in the correct format.
     * @param  string $receiver The receiver format valid for the processor
     * @return boolean          true if valid.  false otherwise
     */
    public function validateReceiver($receiver)
    {
        // validate phone number
        // if (! filter_var($receiver, FILTER_VALIDATE_PHONE)) {
        //     $this->log("[ProcessorJob]  Incorrect phone format {$receiver}.  IGNORED processing this phone.", SNAP_LOG_ERROR);
        //     return false;
        // }
        return true;
    }
}
?>