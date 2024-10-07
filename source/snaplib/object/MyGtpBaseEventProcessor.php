<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\App;
use Snap\IObservation;
use Snap\IObservable;
use Snap\IEventTrigger;
use Snap\IEventProcessorData as IEventProcessorData;
use Snap\object\EventJob as EventJob;
use Snap\object\baseEventProcessor as BaseEventProcessor;

/**
 * This class extends BaseEventProcessor class to provide custom implementation for MyGtp
 */
abstract class MyGtpBaseEventProcessor extends BaseEventProcessor
{
	protected $processorDataClass = null;

	function __construct($processorDataClass = 'Snap\object\MyGtpEventProcessorData')
	{
		$this->processorDataClass = $processorDataClass;
	}

	/**
	 * This method override the method in BaseEventProcessor as we need extra parameters for the Processor data
	 *
	 * @param  App  			$app
	 * @param  IEventTrigger 	$trigger
	 * @param  IObservable 		$generator
	 * @param  IObservation 	$target
	 * @return void
	 */
	function registerEventForProcessing($app, IEventTrigger $trigger, IObservable $generator, IObservation $target) /*: IEventProcessorData*/
	{
		$subscribers = $trigger->getEventSubscriber($app, $target);

		if ($subscribers !== null || (isset($target->otherParams['receiver']) && !empty($target->otherParams['receiver']))) {

			// Placeholder
			if ($subscribers === null) {
				$subscribers = $app->eventSubscriberStore()->create();
			}

			$receiver = $target->otherParams['receiver'];
			$eventLog = $trigger->generateEventLog($app, $generator, $target);
			$processorDataCls = $this->processorDataClass;
			if (strpos($receiver, "@")) {
                $senderEmail = $app->getConfig()->{'snap.mailer.senderemail'};
                $senderName = $app->getConfig()->{'snap.mailer.sendername'};
				if (isset($target->otherParams['sendername']) && !empty($target->otherParams['sendername'])){
					$senderName = $target->otherParams['sendername'];
				}
				if (isset($target->otherParams['senderemail']) && !empty($target->otherParams['senderemail'])){
					$senderEmail = $target->otherParams['senderemail'];
				}
				// check if attachment is set
				if (isset($target->otherParams['attachment']) && !empty($target->otherParams['attachment'])){
					$attachment = $target->otherParams['attachment'];
				}else{
					$attachment = null;
				}
				// Check if BCC is set
				if (isset($target->otherParams['bccemail']) && !empty($target->otherParams['bccemail'])){
					$bccEmail = $target->otherParams['bccemail'];

					// Pass bccemail
					$processorData = new $processorDataCls($app, $trigger, $eventLog, $subscribers, $receiver, $senderEmail, $senderName, $bccEmail, $attachment);
				}else{
					$processorData = new $processorDataCls($app, $trigger, $eventLog, $subscribers, $receiver, $senderEmail, $senderName, null, $attachment);
				}
               
            } else {
                $processorData = new $processorDataCls($app, $trigger, $eventLog, $subscribers, $receiver);
            }


			$eventJob = $app->eventJobStore()->create([
				'processorclass' => get_class($this),
				'processordataclass' => $this->processorDataClass,
				'processordata' => base64_encode($processorData->toCache()),
				'status' => EventJob::STATUS_PENDING
			]);

			$app->eventJobStore()->save($eventJob);
		}
	}
}
