<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\IEventTrigger;
use Snap\IObservation;
use Snap\IObservable;
use Snap\object\MyGtpEventConfig;

/**
 * This is the MyGtp event trigger matcher class to determine if a trigger is activated.
 * Session Group Type is ignored when using this matcher.
 * Events are required to be passed in IObservation::$otherParams with key 'event'
 */
class MyGtpEventTriggerMatcher extends DefaultEventTriggerMatcher
{
    use \Snap\TLogging;

    /**
     * Get all MyGtp Events
     * @return array
     */
    protected function getMyGtpEvents()
    {
        $rClass = new \ReflectionClass(MyGtpEventConfig::class);
        $constants = $rClass->getConstants();
        $lists = [];
        foreach ($constants as $key => $constant) {
            if (false !== strstr($key, "EVENT_")) {
                $lists[] = $constant;
            }
        }

		return $lists;
    }

    /**
     * This method will return a boolean value indicating if the trigger matches the current event being notified.
     * @param  Snap\App     $app       The application class
     * @param  IObservable  $generator The source of event being generated.  Usually a manager.
     * @param  IObservation $target    The event information as a Observation object
     * @return boolean                 True if this trigger is activated by the event coming in.
     */
    public function matchesEvent($app, IEventTrigger $trigger, IObservable $generator, IObservation $target)
    {
        $targetObject = $target->target;

        if ((0 < strlen($trigger->observableclass) && strtolower('\\' . get_class($generator)) != strtolower($trigger->observableclass)) ||
            (-1 != $trigger->oldstatus && $target->startState != $trigger->oldstatus) ||   //does not match old status
            (-1 != $trigger->newstatus && $target->target->status != $trigger->newstatus) ||  //does not match new status
            $target->action != $trigger->actionid ||  //does not match action type
            (0 < strlen($trigger->objectclass) && strtolower('\\' . get_class($targetObject)) != strtolower($trigger->objectclass)) || //does not match object type
            (0 < strlen($trigger->evalcode) && ! eval($trigger->evalcode)) ||
            (isset($target->otherParams['event']) && !in_array($target->otherParams['event'], $this->getMyGtpEvents())) // is not included in events
           ) {
            return false;
        }

        return true;
    }
}
