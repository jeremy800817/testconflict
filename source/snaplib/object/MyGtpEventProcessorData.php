<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\object\EventTrigger;
use Snap\object\EventLog;
use Snap\object\eventsubscriber as EventSubscriber;

/**
 * This class extends the DefaultEventProcessorData
 */
class MyGtpEventProcessorData extends DefaultEventProcessorData
{
    public $trigger = null;
    public $log = null;
    public $subscriber = null;
    public $receiver = null;
    public $senderemail = null;
    public $bccemail = null;
    public $sendername = null;
    public $attachment = null;
    private $app = null;

    public function __construct(\Snap\App $app, EventTrigger $trigger, EventLog $eventLog, EventSubscriber $eventSubscriber, string $receiver = null, string $senderEmail = null, string $senderName = null, string $bccEmail = null, array $attachment = null)
    {
        $this->app = $app;
        $this->trigger = $trigger;
        $this->log = $eventLog;
        $this->subscriber = $eventSubscriber;
        $this->receiver = $receiver;
        $this->senderemail = $senderEmail;
        $this->sendername = $senderName;
        $this->bccemail = $bccEmail;
        $this->attachment = $attachment;
    }

    /**
     * This method override the original method in DefaultEventProcessorData. It will return all the
     * receiver from subscriber and the extra receiver
     *
     * @return string
     */
    public function getReceiver()
    {
        $receiver = $this->receiver;
        if (0 < strlen($this->subscriber->receiver)) {
            $receiver .= ',' . $this->subscriber->receiver;
        }
        return $receiver;
    }

    /**
     * This method will implement serializing the object into a cacheable string for optimum storage.
     * @return String
     */
    public function toCache()
    {
        $cacheStr =  $this->trigger->toCache() . "=|=" . $this->log->toCache() . "=|=" . $this->subscriber->toCache() . "=|=" .  $this->receiver;
        if (0 < strlen($this->senderemail)) {
            $cacheStr .= "=|=" . $this->senderemail . "=|=" . $this->sendername;
            $cacheStr  .= "=|=" . $this->bccemail;
        }

        return $cacheStr;
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
        $this->receiver = $params[3];

        if (4 < count($params)) {
            $this->senderemail = $params[4];
            $this->sendername = $params[5];
            // Check bcc params
            if( 6 < count($params)){
                $this->bccemail = $params[6];
            }
        }
    }

    /**
     * Return address to use for sending email
     * 
     * @return string|null 
     */
    public function getSenderEmail()
    {
        return $this->senderemail;
    }

    /**
     * Return name to use for sending email
     * 
     * @return string|null 
     */
    public function getSenderName()
    {
        return $this->sendername;
    }

    /**
     * Return address to use for sending email
     * 
     * @return string|null 
     */
    public function getBCCEmail()
    {
        $bccemail = $this->bccemail;
        if (0 < strlen($this->subscriber->bccemail)) {
            $bccemail .= ',' . $this->subscriber->bccemail;
        }
        return $bccemail;
    }

    /**
     * Return attachment
     * 
     * @return string|null 
     */
    public function getAttachment()
    {
        return $this->attachment;
    }
}
