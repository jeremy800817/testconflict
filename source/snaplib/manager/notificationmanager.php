<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2017
//
//////////////////////////////////////////////////////////////////////

Namespace Snap\manager;

Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;
Use \Snap\TLogging;
Use \Snap\IObserver;
Use \Snap\IObservable;
Use \Snap\IObservation;
Use \Snap\InputException;
Use \Snap\object\EventJob as eventjob;

/*
 * The responsibility of this class is to provide notifications via emails where appropriate to the admin / person involved
 * upon some pre-defined events firing.
 *
 *
 * @author Chuah <chuah@silverstream.my>
*/
class notificationManager implements IObservable, IObserver {
	/**
	 *  The application class
	 *  @var 
	 */
	Use TLogging;  //Logging traits
	private $app = null;
	private $configObject = null;
	private $notificationEnabled = null;
	function __construct(App $app) {
		$this->app = $app;
	}
	Use \Snap\TObservable;

	/**
	 * This method checks if notification moduled should be enabled to receive events.
	 * @return boolean True means to proceed with event notification.  False otherwise.
	 */
	private function isNotificationEnabled() {
		if( null === $this->notificationEnabled) {
			$this->notificationEnabled = !preg_match('/^off$/i', trim($this->app->getConfig()->{'snap.notification.enable'}));
		}
		return $this->notificationEnabled;
	}

	public function getEventConfig() {
		if( !$this->configObject) {
			$eventConfigClass = $this->app->getConfig()->{'snap.notification.configObject'};
			if( 0 == strlen($eventConfigClass)) {
				throw new InputException('Event config object for application not found.  Please set snap.notification.configobject in the config.ini', InputException::GENERAL_ERROR, '');
			}
			$this->configObject =  new $eventConfigClass;
		}
		return $this->configObject;
	}

	/**
	 * This method will be called by IObserverable object to notify of changes that has been done to it.
	 *
	 * @param  IObservable  $changed The initiating object
	 * @param  IObservation $state   Change information
	 * @return None
	 */
	public function onObservableEventFired(IObservable $changed, IObservation $state) {
		if( ! $this->isNotificationEnabled()) {
			$this->log("[NotificationManager]  Notification module is not enabled.  Skipping onObservableEventFired()", SNAP_LOG_DEBUG);
			return;
		}
		$this->log("[NotificationManager]  Capturing and checking for tasks to do for " . get_class($state->target) . ' with observation action = ' . $observationDesc[$state->action] . ', object id = ' . $state->target->id, SNAP_LOG_DEBUG);
		$eventTriggerStore = $this->app->eventTriggerStore();
		//1.  Collect all triggered events
		$allTriggers = $eventTriggerStore->searchView()->select()->where('status', eventjob::STATUS_ACTIVE)->execute();
		$triggeredEvents = array();
		foreach( $allTriggers as $aTrigger) {
			if( $aTrigger->matchesEvent($this->app, $changed, $state)) {
				$triggeredEvents[] = $aTrigger;
			}
			// $triggeredEvents[] = $allTriggers[2];
		}
		//2.  For all triggered events, create the event messages for them and register with the appropriate processor for job.
		$processorsMap = array();

		// print_r($triggeredEvents);exit;
		// $this->app->dd($triggeredEvents);

		foreach( $triggeredEvents as $aTrigger) {
			if($aTrigger->storetolog) {
				$anEventLog = $aTrigger->generateEventLog($this->app, $changed, $state);
				$this->app->eventLogStore()->save($anEventLog);
			}
			if ($aTrigger->processorclass) {
				// if ($aTrigger->processorClass == $processorsMap['services']){
				// 	$this->callServices($aTrigger->processorClass, $processorsMap['services']);
				// }
				// print_r($processorsMap);exit;
				// print_r($aTrigger->processorclass);exit;

				if(isset($processorsMap[$aTrigger->processorclass])) {
					$processor = $processorsMap[$aTrigger->processorclass];
				} else {
					// always here
					try {
						$eventprocessorcls = $aTrigger->processorclass;
						$processor = new $eventprocessorcls;
					} catch(Exception $e) {
						$processor = null;
					}
					$processorsMap[$aTrigger->processorclass] = $processor;
				}
				// print_r($processorsMap);exit;
				if( $processor && $processor instanceof \Snap\IEventProcessor) {
					$processor->registerEventForProcessing( $this->app, $aTrigger, $changed, $state);
				} else if( $processor) {
					$this->log("[NotificationManager]  The process class [{$aTrigger->processorclass}] from trigger ID {$aTrigger->id} does not implement the IEventProcessor interface.  IGNORED processing this ecvent.", SNAP_LOG_ERROR);
					//throw new InputException('Processor must be of interface IEventProcessor', InputException::GENERAL_ERROR);
				} else $this->log("[NotificationManager]  The process class [{$aTrigger->processorclass}] from trigger ID {$aTrigger->id} can not be created.  IGNORED processing this ecvent.", SNAP_LOG_ERROR);
			}
		}
	}

	private function callServices(){

	}

	/**
	 * This is the scheduler job to process the events that has been notified.  This method will run
	 * through all the pending jobs and use the desired processor to process the job.  Processor can be 
	 * email / telegram or othe communication methods.
	 * 
	 * @return None
	 */
	public function runProcessorJob() {
		$jobStore = $this->app->eventJobStore();
		$triggerObj = $this->app->eventTriggerStore()->create();
		$logObj = $this->app->eventLogStore()->create();
		$subscriberObj = $this->app->eventSubscriberStore()->create();
		$jobsByProcessor = [];
		$pendingJobs = $jobStore->searchTable()->select()->where('status', eventjob::STATUS_PENDING)->execute();
		foreach( $pendingJobs as $aJob) {
			//We will set all the jobs to completed first to avoid running duplicate.
			$aJob->status = eventjob::STATUS_COMPLETED;
			$jobStore->save($aJob);
			//Also group the jobs by the processor to use.
			$jobsByProcessor[$aJob->processorclass][] = $aJob;
		}
		foreach( $jobsByProcessor as $processorClass => $jobs) {
			$jobProcessor = new $processorClass;
			foreach( $jobs as $aJob) {
				if( 'json' == $aJob->processordataclass) $processorData = json_decode($aJob->processordata);
				else {
					$processorData = new $aJob->processordataclass($this->app, $triggerObj, $logObj, $subscriberObj);
					$processorData->fromCache(base64_decode($aJob->processordata));
				}
				$jobProcessor->processEvent( $this->app, $processorData);
			}
		}
	}
    
    public function subscribeToNotification($params)
    {
        if (!$this->app->getOtcChecker()) {
            return json_encode(['success' => false, 'error' => "checker maker config is off or user don't have permission"]);
        }
        $path = $this->app->getConfig()->{'otc.notification.subscribe.path'};
        $partnerid = ($params['partnerid']) ? $params['partnerid'] : $this->app->getUserSession()->getUser()->partnerid;
        if ($path) {
            // Do an internal redirect to the stream path
            $fullPath = $path . $partnerid;
            header("X-Accel-Redirect: $fullPath");
            header('X-Accel-Buffering: no');
        }
        return json_encode(['success' => true, 'fullpath' => $fullPath]);
    }
    
    public function postToNotificationChannel($params)
    {
        $bSuccess = false;
        $message = 'otc.notification.publish.path is not exists in config';
        if ($this->app->getConfig()->isKeyExists('otc.notification.publish.path')) {
            $baseUrl = $this->app->getConfig()->{'otc.notification.publish.path'};
            $partnerid = ($params['partnerid']) ? $params['partnerid'] : $this->app->getUserSession()->getUser()->partnerid;
            $fullUrl = $baseUrl  . $partnerid;
            $data = array(
                'title' => $params['title'],
                'body' => $params['body'],
                'url' => $params['url']
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fullUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);

            if (curl_errno($ch)) {
                $this->app->log("Unable to POST to notification stream", SNAP_LOG_DEBUG);
                $message = 'error no: ' . curl_errno($ch) . ', error: ' . curl_error($ch);
            } else {
                $bSuccess = true;
                $message = $server_output;
            }
            
            curl_close ($ch);
        }
        
        return json_encode(['success' => $bSuccess, 'message' => $message]);
        
    }
}
?>
