<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

use Snap\IObservation;
use Snap\object\EventJob;
use Snap\object\MyGtpEventConfig;
use Snap\object\MyLocalizedContent;
use Snap\object\MyPushNotification;
use Snap\object\MyToken;

/**
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 03-Nov-2020
 */

final class PushNotificationManagerTest extends AuthenticatedTestCase
{
    /** @var \Snap\manager\MyGtpPushNotificationManager */
    private static $manager;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$manager = self::$app->mygtppushnotificationManager();

        // Push notification trigger
        $eventtrigger = self::$app->eventtriggerStore()->create([
            'grouptypeid'   => 100,
            'moduleid'      => 100,
            'actionid'      => IObservation::ACTION_VERIFY,
            'matcherclass'  => '\Snap\object\MyGtpEventTriggerMatcher',
            'processorclass'=> '\Snap\object\MyGtpPushEventProcessor',
            'messageid'     => 0,
            'observableclass'=> '\Snap\manager\MyGtpPushNotificationManager',
            'oldstatus'     => '-1',
            'newstatus'     => '-1',
            'objectclass'   => '\Snap\object\MyAccountHolder',
            'storetolog'    => 0,
            'groupidfieldname' => '',
            'evalcode'      => '',
            'status'        => 1
        ]);
        $eventtrigger = self::$app->eventtriggerStore()->save($eventtrigger);
    }

    public function testPushJobAdded()
    {
        $eventjobOldMax = self::$app->eventjobStore()->searchTable(false)->select()->max('id');

        // Bandaid due to vw_testeventtrigger requiring test_eventmessage
        self::$app->eventMessageStore();

        $push = self::$app->mypushnotificationStore()->create([
            'language'  => MyLocalizedContent::LANG_ENGLISH,
            'eventtype'=> MyPushNotification::TYPE_EKYC_FAIL,
            'code'      => 'FAIL001',
            'title'     => 'Test fail title',
            'body'      => 'Test fail body',
            'rank'      => 100,
            'validfrom' => '1970-01-01 00:00:00',
            'validto'   => '2099-01-01 00:00:00',
            'status'    => MyPushNotification::STATUS_ACTIVE
        ]);
        $push = self::$app->mypushnotificationStore()->save($push);
        

        $token = self::$app->mytokenStore()->create([
            'type' => MyToken::TYPE_PUSH,
            'token'=> self::$app->getConfig()->{'mygtp.test.fcmdevicetoken'},
            'accountholderid'   => self::$accountHolder->id,
            'status'    => 1,
            'expireon'  => '2099-01-01 00:00:00'
        ]);
        $token = self::$app->mytokenStore()->save($token);

        // Artifically create a submission fail event
        self::$manager->onObservableEventFired(self::$app->mygtpaccountManager(), 
            new IObservation(
                self::$app->mykycsubmissionStore()->create(),
                IObservation::ACTION_OTHER,
                1,
                [
                    'event' => MyGtpEventConfig::EVENT_EKYC_RESULT_FAILED,
                    'accountholderid'   => self::$accountHolder->id,
                    'usejob'    => 1
                ])
        );

        $latestJob = self::$app->eventjobStore()->searchTable()->select()
                                    ->orderBy('id', 'DESC')
                                    ->one();

        $this->assertGreaterThan($eventjobOldMax, $latestJob->id);
        return $latestJob;
    }

    /**
     * A working token can be added to config file to test the push notification
     * @depends testPushJobAdded
     */
    function testRunPushNotificationJob($latestJob) {
        $this->markTestSkipped("Should be in integration/external test");
        self::$app->notificationManager()->runProcessorJob();

        $latestJob = self::$app->eventjobStore()->getById($latestJob->id);
        $this->assertEquals(EventJob::STATUS_COMPLETED, $latestJob->status);
    }

    public function testDataObject()
    {
        $this->markTestSkipped("Should be in integration/external test");
        $testToken = self::$app->getConfig()->{'mygtp.test.fcmdevicetoken'};
        $pushNotification = self::$app->mypushnotificationStore()->create([
            'language'  => MyLocalizedContent::LANG_ENGLISH,
            'eventtype' => 'TEST_EVENT_TYPE',
            'title'     => "Test Data Object",
            'body'      => "Test body",
            'code'      => "TESTDATAOBJ",
            'validfrom' => '1970-01-01 00:00:00',
            'validto'   => '2099-01-01 00:00:00',
            'rank'      => 100,
            'status'    => MyPushNotification::STATUS_ACTIVE,
        ]);
        $pushNotification = self::$app->mypushnotificationStore()->save($pushNotification);

        self::$app->mygtppushNotificationManager()
                  ->doSendNotification([$testToken], $pushNotification, MyLocalizedContent::LANG_ENGLISH, [
                      'test'    => 1,
                      'test2'   => 2
                  ]);
    }
}