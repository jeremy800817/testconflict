<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\object;

Use Snap\App;
Use \Snap\TLogging;
Use \Snap\IEventProcessorData as IEventProcessorData;
Use \Snap\object\baseeventprocessor as baseEventProcessor;
use Snap\util\telegram\TelegramBot;

/**
 * This class is an abstract class that implements a part of event processor function by keepng the actual
 * heavy duty work done in a temporary event job object first to be used later by the processEvent() method.
 * Actual eventProcessor class should inherit from this class and then implement the processEvent() method to
 * provide actual processing logic.  @See EmailEventProcessor for example.
 *
 * @author Cheok <cheok@silverstream.my>
 * @package  snap.base
 */
class TelegramEventProcessor extends baseEventProcessor
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
        $telegram = TelegramBot::getInstance();
        $receiverArr = explode(',', $registeredData->getReceiver());
        foreach ($receiverArr as $receiver) {
            $receiver = trim($receiver, ' ');
            if ($this->validateReceiver($receiver)) {
                $telegram->sendMessage($registeredData->getLogContent(), $receiver);
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

        return true;
    }
}
?>