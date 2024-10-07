<?php

use Snap\IObservation;
use Snap\manager\MyGtpAccountManager;
use Snap\object\EventTrigger;
use Snap\object\MyAccountHolder;
use Snap\object\MyGtpEmailEventProcessor;
use Snap\object\MyGtpEventTriggerMatcher;
use Snap\object\MyKYCResult;
use Snap\object\MyKYCSubmission;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 22-Dec-2020
*/

class EventTriggerMatcherTest extends BaseTestCase {

    public function testCanMatchEvent() {
        $eventMessageStore = self::$app->eventmessageStore();
        $eventTriggerStore = self::$app->eventtriggerStore();

        $now = new \DateTime();
        $accHolder = self::createDummyAccountHolder();
        $accMgr = \Mockery::mock(MyGtpAccountManager::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();
        $accMgr->attach(self::$app->notificationManager());

        $eventTrigger = $eventTriggerStore->create([
            'grouptypeid'   => 1,
            'moduleid'      => 1,
            'actionid'      => IObservation::ACTION_OTHER,
            'matcherclass'  => MyGtpEventTriggerMatcher::class,
            'processorclass'=> '\\'. MyGtpEmailEventProcessor::class,
            'messageid'     => 1,
            'observableclass' => '\\'. get_class($accMgr),
            'oldstatus'     => -1,
            'newstatus'     => -1,
            'objectclass'   => '\\' . MyAccountHolder::class,
            'storetolog'    => 0,
            'groupidfieldname'  => '',
            'evalcode'      => '',
            'status'        => EventTrigger::STATUS_ACTIVE
        ]);
        $eventTrigger = $eventTriggerStore->save($eventTrigger);

        $obj = new \stdClass;
        $obj->result = MyKYCResult::RESULT_FAILED;
        $accMgr->processKycResult($accHolder, $obj);
        $eventJob = self::$app->eventjobStore()->searchTable()->select()
                                ->where('processorclass', MyGtpEmailEventProcessor::class)
                                ->where('createdon', '>=', $now->format('Y-m-d H:i:s'))
                                ->execute();

        $this->assertNotNull($eventJob);
    }
}