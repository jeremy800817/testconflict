<?php

namespace Snap\object;

use Snap\IEventProcessorData;
use Snap\IEventTrigger;
use Snap\IObservable;
use Snap\IObservation;
use Snap\object\EventJob;
use Snap\object\MyAccountHolder;
use Snap\object\MyGtpBaseEventProcessor;
use Snap\TLogging;
use Snap\utils\pushnotification\SnapFcmClient;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 05-Nov-2020
*/


class MyGtpPushEventProcessor extends MyGtpBaseEventProcessor
{
    use TLogging;
    /**
     * Creates a ProcessorData object and uses it to generate an EventJob
     * Required target properties: notificationid, language
     *
     * @param  App				$app
     * @param  IEventTrigger	$trigger
     * @param  IObservable		$generator
     * @param  IObservation		$target
     * @return void
     */
    function registerEventForProcessing($app, IEventTrigger $trigger, IObservable $generator, IObservation $target)
    {
        // We do not use static subscribers, so just create a placeholder object
        $subscribers = $app->eventSubscriberStore()->create();

        // Get the push tokens to be stored in the job
        /** @var MyAccountHolder $accHolder */        
        $accHolder = $target->target;
        if (! $accHolder instanceof MyAccountHolder) {
            if (isset($target->otherParams['accountholder']) && $target->otherParams['accountholder'] instanceof MyAccountHolder) {
                $accHolder = $target->otherParams['accountholder'];
            } else {
                $accHolder = $app->myaccountholderStore()->getById($target->otherParams['accountholderid']);
            }
        }

        $tokens = $accHolder->getDevicePushTokens();
        if (0 < count($tokens)) {
            $receiver = $this->generateReceiversFromToken($tokens);
        } else {
            $settings = $app->mypartnersettingStore()->getByField('partnerid', $accHolder->partnerid);
            if (filter_var($settings->enablepushnotification, FILTER_VALIDATE_BOOLEAN)) {
                // Only log when push notification enabled to avoid flooding log 
                $this->log(__CLASS__.": AccountHolder does not have push token associated.", SNAP_LOG_ERROR);
            }

            return;
        }

        // Save notification data into event log
        $data = json_encode($target->otherParams['data']);
        $eventLog = $app->eventLogStore()->create([
            'triggerid' => $trigger->id,
            'groupid' => $target->target->{$trigger->groupidfieldname},
            'objectid' => $target->target->id,
            'reference' => $target->otherParams['notificationid'],
            'subject' => $target->otherParams['language'],
            'log' =>  $data ? $data : '',
            'sendon' => new \DateTime(),
            'status' => SnapObject::STATUS_ACTIVE,
        ]);
        // $eventLog = $trigger->generateEventLog($app, $generator, $target);
        // $eventLog->reference = $target->otherParams['notificationid'];
        // $eventLog->subject = $target->otherParams['language'];

        $processorDataCls = $this->processorDataClass;
        $processorData = new $processorDataCls($app, $trigger, $eventLog, $subscribers, $receiver);

        $eventJob = $app->eventJobStore()->create([
            'processorclass' => get_class($this),
            'processordataclass' => $this->processorDataClass,
            'processordata' => base64_encode($processorData->toCache()),
            'status' => EventJob::STATUS_PENDING
        ]);

        $app->eventJobStore()->save($eventJob);
    }

    protected function generateReceiversFromToken(array $tokens) {
        $tokenStrs = [];
        foreach ($tokens as $token) {
            $tokenStrs[] = $token->token;
        }
        $receiver = implode(",", $tokenStrs);       
        return $receiver;
    }

    /**
     * This method is used to actually do processing for the event that has been triggered.   Implementation will be
     * specific to the processor that is implementing it.
     *
     * @param  \Snap\App              	$app            Application object
     * @param  IEventProcessorData  $registeredData The data that will be used for performing the process.
     * @return void
     */
    public function processEvent($app, IEventProcessorData $registeredData)
    {
        $receiverArr = explode(',', $registeredData->getReceiver());
        $mypush = $app->mypushnotificationStore()->getById($registeredData->getReference());

        if (0 < count($receiverArr)) {
            $app->mygtppushnotificationManager()
                ->pushToTokens($receiverArr, $mypush, $registeredData->log->subject, json_decode($registeredData->log->log));
        }
    }

    /**
     * Validates if the receiver provided is in the correct format.
     * @param  string $receiver The receiver format valid for the processor
     * @return boolean          true if valid.  false otherwise
     */
    public function validateReceiver($receiver)
    {
        if (0 < strlen($receiver)) {
            $this->log(__CLASS__.": AccountHolder does not have push token associated.", SNAP_LOG_ERROR);
            return false;
        }
        return true;
    }
}