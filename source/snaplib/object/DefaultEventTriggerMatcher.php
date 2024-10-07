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
 * This is the system default event trigger matcher class to determine if a trigger is activated.
 */
class DefaultEventTriggerMatcher implements IEventTriggerMatcher
{
    Use \Snap\TLogging;

    /**
     * This method will return a boolean value indicating if the trigger matches the current event being notified.
     * @param  Snap\App       $app     The application class
     * @param  IObservable  $generator The source of event being generated.  Usually a manager.
     * @param  IObservation $target    The event information as a Observation object
     * @return boolean                 True if this trigger is activated by the event coming in.
     */
    public function matchesEvent($app, IEventTrigger $trigger, IObservable $generator, IObservation $target)
    {
        $targetObject = $target->target;
        $groupTypeId = $app->notificationManager()->getEventConfig()->getSessionGroupTypeId($app);
        // $groupId = $app->notificationManager()->getEventConfig()->getSessionGroupId($app);

        if ((0 < strlen($trigger->observableclass) && strtolower('\\' . get_class($generator)) != strtolower($trigger->observableclass)) ||
            (-1 != $trigger->oldstatus && $target->startState != $trigger->oldstatus) ||   //does not match old status
            (-1 != $trigger->newstatus && $target->target->status != $trigger->newstatus) ||  //does not match new status
            $target->action != $trigger->actionid ||  //does not match action type
            ($trigger->grouptypeid > 0 && $groupTypeId != $trigger->grouptypeid) ||  //does not match for the group type (E.g.  operator, merchant etc)
            (0 < strlen($trigger->objectclass) && strtolower('\\' . get_class($targetObject)) != strtolower($trigger->objectclass)) || //does not match object type
            (0 < strlen($trigger->evalcode) && ! eval($trigger->evalcode))) {
            return false;
        }
        return true;
    }
}
?>