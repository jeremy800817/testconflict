<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;
Use Snap\IEventTrigger;
Use Snap\IEventTriggerMatcher;
Use Snap\object\eventsubscriber;
Use Snap\IObservation;
Use Snap\IObservable;

/**
 * Encapsulates the eventsubscriber table on the database
 *
 *  @property-read 	int          $id                 Primary Key
 *  @property 		int          $grouptypeid        The group or category type that it subscribes for
 *  @property  		double       $moduleid           Module ID from the IEventConfig interface
 *  @property 		int          $actionid           Available action id from the IEventConfig interface
 *  @property 		int          $matcherclass       IEventTriggerMatcher class to use to check for match.  Default is the 'defaultEventTriggerMatcher'
 *  @property 		int          $processorclass     IEventProcessor implemented class used to process when the event is triggered.
 *  @property 		int          $messageid          Message to use to communicate the event to users.
 *  @property 		int          $messageid          Message to use to communicate the event to users.
 *  @property 		int          $observation        Observation type
 *  @property 		int          $oldstatus          The old status of the object before event triggered
 *  @property 		int          $newstatus          The current status of the object when event is triggeed
 *  @property 		int          $observationclass   Class name of the object that should trigger this
 *  @property 		int          $storetolog         indicate if this trigger should be stored into the eventlog for viewing next time.
 *  @property 		int          $groupidfieldname   Member name in object that referes to the group id
 *  @property 		int          $evalcode           Additional evaluation to be done to see if the event matches this trigger.
 *  @property 		\Datetime    $createdon          Time this record is created
 *  @property 		\Datetime    $modifiedon         Time this record is last modified
 *  @property 		int          $status             Status active(1), suspended(2)
 *  @property 		int          $createdby          User ID
 *  @property 		int          $modifiedby         User ID
 *
 * @author Devon
 * @version 1.0
 * @created 2017/8/25 10:24 AM
 * @package  snap.base
 */
class EventTrigger extends SnapObject implements IEventTrigger
{
    /**
     * Reset all values in $this->members array  this is where the object member
     * variables get initialized to its default values inherited class should
     * implement this abstract function
     */
    public function reset()
    {
        $this->members = array(
            'id' => null,
            'grouptypeid' => null,
            'moduleid' => null,
            'actionid' => null,
            'matcherclass' => null,
            'processorclass' => null,
            'messageid' => null,
            'observableclass' => null,
            'oldstatus' => null,
            'newstatus' => null,
            'objectclass' => null,
            'storetolog' => null,
            'groupidfieldname' => null,
            'evalcode' => null,
            'createdon' => null,
            'modifiedon' => null,
            'status' => null,
            'createdby' => null,
            'modifiedby' => null,
        );
        $this->viewMembers = array(
            'messagecode' => null,
        );
    }

    /**
     * Check if all values in $this->members array is valid  this is where the object
     * member variables get validated for legal values inherited class should
     * implement this abstract function
     * 
     * @return	true if all member data has valid values. Otherwise false.
     */
    public function isValid()
    {
        return true;
    }

    /**
     * This method will generate an eventlog object formatted as required
     * @param  Snap\App       $app     The application class
     * @param  IObservable  $generator The source of event being generated.  Usually a manager.
     * @param  IObservation $target    The event information as a Observation object
     * 
     * @return Snap\object\eventlog    The eventlog object containing the messages
     */
    public function generateEventLog($app, IObservable $generator, IObservation $target)
    {
        $message = $app->eventMessageStore()->getById($this->messageid);
        $eventLog = $message->apply($app, $this, $target);
        return $eventLog;
    }

    /**
     * This method will return a boolean value indicating if the trigger matches the current event being notified.
     * @param  Snap\App       $app     The application class
     * @param  IObservable  $generator The source of event being generated.  Usually a manager.
     * @param  IObservation $target    The event information as a Observation object
     * 
     * @return boolean                 True if this trigger is activated by the event coming in.
     */
    public function matchesEvent($app, IObservable $generator, IObservation $target)
    {
        static $matcherContainer = [];
        if (isset($matcherContainer[$this->members['matcherclass']])) {
            $matcher = $matcherContainer[$this->members['matcherclass']];
        } else {
            $matcher = new $this->members['matcherclass']();
            $matcherContainer[$this->members['matcherclass']] = $matcher;
        }
        return $matcher->matchesEvent($app, $this, $generator, $target);
    }

    /**
     * Returns the event subscriber for this trigger
     * @param  App          $app    Application class
     * @param  IObservation $target The observation user group that it should belong to.
     * @return eventSubscriber               event subsriber class
     */
    public function getEventSubscriber($app, IObservation $target)
    {
        // $sessionUserGroupId = $app->notificationManager()->getEventConfig()->getSessionGroupId($app);
        $targetUserGroupId = $target->target->{$this->members['groupidfieldname']};
        $results = $app->eventsubscriberStore()->searchTable()->select()->where('triggerid', $this->id)->andWhere('groupid', $targetUserGroupId)->execute();

        return $results[0];
    }

    /**
     * Returns the processor that will be used to process / notify about this event.
     * @return eventProcessor   
     */
    public function getEventProcessor()
    {
        static $processorMap = [];
        if (! isset($this->members['processorclass'])) {
            $processorMap[$this->members['processorclass']] = new $this->members['processorclass'];
        }
        return $processorMap[$this->members['processorclass']];
    }

    /**
     * This method will register or update the information about the subscriber that is interested in this trigger.
     * 
     * @param  int    $groupId  ID of the group that it belongs to.
     * @param  string $receiver Receiver information to pass along.
     */
    public function registerEventSubscriber($groupId, $receiver)
    {
        $object = $this->getRelatedStore('subscriber')->searchView()->where('trigger', $this->id)->andWhere('groupid', $groupId)->execute();
        if (! $object || 0 == $object[0]->id) {
            $object = $this->getRelatedStore('subscriber')->create(['triggerid' => $this->id, 'groupid' => $groupid, 'receiver' => $receiver, 'status' => SnapObject::STATUS_ACTIVE]);
        } else {
            $object = $object[0];
            $object->receiver = $receiver;
        }
        $this->getRelatedStore('subscriber')->save($object);
    }
}
?>