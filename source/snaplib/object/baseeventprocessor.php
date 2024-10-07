<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2017
//
//////////////////////////////////////////////////////////////////////

Namespace Snap\object;

Use Snap\App;
Use \Snap\IObservation;
Use \Snap\IObservable;
Use \Snap\IEventTrigger;
Use \Snap\IEventProcessor as IEventProcessor;
Use \Snap\IEventProcessorData as IEventProcessorData;
Use \Snap\object\EventJob as eventjob;

/*
 * This class is an abstract class that implements a part of event processor function by keepng the actual
 * heavy duty work done in a temporary event job object first to be used later by the processEvent() method.
 * Actual eventProcessor class should inherit from this class and then implement the processEvent() method to
 * provide actual processing logic.  @See EmailEventProcessor for example.
 *
 * @author Devon Koh <devon@silverstream.my>
*/
abstract class baseEventProcessor implements IEventProcessor {
	private $processorDataClass = null;

	function __construct( $processorDataClass = 'Snap\object\DefaultEventProcessorData') {
		$this->processorDataClass = $processorDataClass;
	}

	function registerEventForProcessing( $app, IEventTrigger $trigger, IObservable $generator, IObservation $target) /*: IEventProcessorData*/ {
        //echo 'shit';exit;
        $subscribers = $trigger->getEventSubscriber( $app, $target);
        // print_r($subscribers);exit;
		if ($subscribers != NULL) {
			$eventLog = $trigger->generateEventLog( $app, $generator, $target);
			$processorDataCls = $this->processorDataClass;
			$processorData = new $processorDataCls($app, $trigger, $eventLog, $subscribers);

			$eventJob = $app->eventJobStore()->create([
				'processorclass' => get_class($this),
				'processordataclass' => $this->processorDataClass,
				'processordata' => base64_encode($processorData->toCache()),
				'status' => eventjob::STATUS_PENDING			
			]);
			$app->eventJobStore()->save($eventJob);
		}
	}

	/**
	 * This method is used to actually do processing for the event that has been triggered.   Implementation will be
	 * specific to the processor that is implementing it.
	 * 
	 * @param  App              $app            Application object
	 * @param  IEventProcessorData $registeredData The data that will be used for performing the process.
	 * @return None                              none
	 */
	abstract function processEvent( $app, IEventProcessorData $registeredData);
}
?>