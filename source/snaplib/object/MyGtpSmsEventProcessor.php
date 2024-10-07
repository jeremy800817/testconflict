<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

namespace Snap\object;

use Snap\api\exception\ApiParamPhoneInvalid;
use Snap\api\param\validator\MyGtpApiParamValidator;
use Snap\App;
use Snap\TLogging;
use Snap\IEventProcessorData;
use Snap\object\MyGtpBaseEventProcessor;

class MyGtpSmsEventProcessor extends MyGtpBaseEventProcessor
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
        $this->app = $app;
        $sms = new \Snap\manager\SmsManager($app);
        $receiverArr = explode(',', $registeredData->getReceiver());
        foreach ($receiverArr as $receiver) {
            $receiver = trim($receiver, ' ');
            if ($this->validateReceiver($receiver)) {
                // Skip the '+' character
                $sms->phoneNumber = substr($receiver, 1);
                $sms->messagePayLoad = $registeredData->getLogContent();
                if (!$sms->send()){
                    throw new \Error();
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
        $phoneValidator = new MyGtpApiParamValidator($this->app);
        try {
            $passed = $phoneValidator->pass("phone|mobile-my", "phone", $receiver, []);
        } catch (ApiParamPhoneInvalid $e) {
            $this->log("[MyGtpSmsEventProcessor]  Incorrect phone number format {$receiver}.  IGNORED processing this sms.", SNAP_LOG_ERROR);
            $passed = false;
        }
        return $passed;
    }
}
