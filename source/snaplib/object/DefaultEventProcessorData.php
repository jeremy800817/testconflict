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
Use \Snap\object\EventTrigger as EventTrigger;
Use \Snap\object\EventLog as EventLog;
Use \Snap\object\eventsubscriber as EventSubscriber;
Use \Snap\IEventProcessorData as IEventProcessorData;

/**
 * This is the default EventProcessorData object that will interface with the EventProcessor to get the
 * required data to be used for processing the event.  Basic implementation should be sufficient to store
 * all required fields and it stores the EventTrigger, EventLog and EventSubscriber objects to an EventJob object.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.base
 */
class DefaultEventProcessorData implements IEventProcessorData
{
    public $trigger = null;
    public $log = null;
    public $subscriber = null;
    private $app = null;

    public function __construct(\Snap\App $app, EventTrigger $trigger, EventLog $eventLog, EventSubscriber $eventSubscriber)
    {
        $this->app = $app;
        $this->trigger = $trigger;
        $this->log = $eventLog;
        $this->subscriber = $eventSubscriber;
    }
    
    public function getSubject()
    {
        return $this->log->subject;
    }

    public function getLogContent()
    {
        if ($this->log) {
            return $this->log->log;
        }
        return '';
    }

    public function getObjectId()
    {
        return $this->log->objectid;
    }

    public function getReference()
    {
        return $this->log->reference;
    }

    public function getEventGroupTypeId()
    {
        return $this->trigger->grouptypeid;
    }

    public function getEventGroupId()
    {
        return $this->log->groupid;
    }

    public function getEventModuleId()
    {
        return $this->trigger->moduleid;
    }

    public function getEventObjectId()
    {
        return $this->log->objectid;
    }

    public function getReceiver()
    {
        return $this->subscriber->receiver;
    }

    /**
     * This method will implement serializing the object into a cacheable string for optimum storage.
     * @return String
     */
    public function toCache()
    {
        return $this->trigger->toCache() . "=|=" . $this->log->toCache() . "=|=" . $this->subscriber->toCache();
    }

    /**
     * This method will need to implement expanding the object back to its original from the cached data provided.
     * @param  string $data The original data provided in toCache()
     * @return void
     */
    public function fromCache($data)
    {
        $params = explode("=|=", $data);
        $this->trigger = $this->app->eventTriggerStore()->create();
        $this->trigger->fromCache($params[0]);
        $this->log = $this->app->eventLogStore()->create();
        $this->log->fromCache($params[1]);
        $this->subscriber = $this->app->eventSubscriberStore()->create();
        $this->subscriber->fromCache($params[2]);
    }
}
?>