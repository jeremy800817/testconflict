<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use DateTime;
use Snap\IObservable;
use Snap\IObservation;
use Snap\IObserver;
use Snap\object\MyAccountHolder;
use Snap\object\MyGoldTransaction;
use Snap\object\MyAnnouncement;
use Snap\object\MyGtpEventConfig;
use Snap\object\MyKYCSubmission;
use Snap\object\MyLocalizedContent;
use Snap\object\MyPriceAlert;
use Snap\object\MyPushNotification;
use Snap\TObservable;
use Snap\TLogging;
use Snap\util\pushnotification\SnapFcmClient;

/**
 * This class handles the logic for MyGTP push notifications.
 * Currently using Firebase as the push service provider
 *
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 02-Nov-2020
 */
class MyGtpPushNotificationManager implements IObservable, IObserver
{
    use TLogging;
    use TObservable;

    /** @var Snap\App $app */
    private $app = null;

    /** @var SnapFcmClient $client */
    private $client = null;

    /** @var array $data */
    private $data;

    public function __construct($app)
    {
        $this->app = $app;
        $this->client = new SnapFcmClient($app);
        $this->data['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';
    }

    /**
     * 
     * Handles events to send push notification to accountholder.
     * $eventtype variable needs to be set to the defined types and also set in BO
     * 
     * Required parameters in otherParams to trigger push notification:
     * event            =>  MyGtpEventConfig::EVENT_*
     * accountholderid  =>  $accountholder->id
     * 
     * EventTrigger mechanism should be sufficient for now (with workarounds),
     * else need to implement a proper notification/event engine
     * 
     *
     * Current limitations are:
     * 1. Unable to set trigger rule on columns other than 'status' (Might be solvable with db normalization)
     * 2. Unable to set dynamic message content
     * 3. Unable to set dynamic recipients
     *
     **/
    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {

        $ekycFailEvents = [ MyGtpEventConfig::EVENT_EKYC_RESULT_FAILED,
                            MyGtpEventConfig::EVENT_EKYC_VERIFICATION_FAILED ];

        // Check if notification was sent from EKYC process
        if ($changed instanceof MyGtpAccountManager && $state->target instanceof MyKYCSubmission) {
            if ($state->isOtherAction()) {
                if (in_array($state->otherParams['event'], $ekycFailEvents)) {
                    // Account holder failed EKYC process
                    $eventtype = MyPushNotification::TYPE_EKYC_FAIL;
                } else if (MyGtpEventConfig::EVENT_EKYC_RESULT_PASSED == $state->otherParams['event']) {
                    // Account holder passed EKYC process
                    $eventtype = MyPushNotification::TYPE_EKYC_PASS;
                }
                
            }
        } 
        else if ($changed instanceof MyGtpAccountManager && $state->target instanceof MyAccountHolder) {
            if ($state->isVerifyAction()) {
                if (MyGtpEventConfig::EVENT_REMIND_INCOMPLETE_PROFILE == $state->otherParams['event']) {
                    $eventtype = MyPushNotification::TYPE_INCOMPLETE_PROFILE;
                }
            }

            if ($state->isApproveAction()) {
                if (MyGtpEventConfig::EVENT_PEP_PASSED == $state->otherParams['event']) {
                    $eventtype = MyPushNotification::TYPE_PEP_PASSED;
                }
            }

            if ($state->isRejectAction()) {
                if (MyGtpEventConfig::EVENT_PEP_FAILED == $state->otherParams['event']) {
                    $eventtype = MyPushNotification::TYPE_PEP_FAILED;
                }
            }

            if ($state->isFreezeAction()) {
                if (MyGtpEventConfig::EVENT_REMIND_DORMANT_ACCOUNT == $state->otherParams['event']) {
                    $eventtype = MyPushNotification::TYPE_DORMANT_ACCOUNT;
                }
            }
        }
        else if ($changed instanceof MyGtpPriceAlertManager && $state->target instanceof MyPriceAlert) {
            
            $eventtype = $state->target->isBuyAlert() ? MyPushNotification::TYPE_PRICE_MATCH_BUY : MyPushNotification::TYPE_PRICE_MATCH_SELL;

        } else if ($changed instanceof MyGtpTransactionManager && $state->target instanceof MyGoldTransaction) {
            if ($state->isConfirmAction()) {
                $eventtype = MyPushNotification::TYPE_GOLDTRANSACTION_CONFIRMED;
            }

        }

        if ($eventtype) {
            $response = $this->sendNotificationFromEvent($eventtype, $state);

            if ($response) {
                if ($state->target instanceof MyPriceAlert) {
                    $state->target->senton = new \DateTime('now', $this->app->getUserTimezone());
                    $this->app->mypricealertStore()->save($state->target, ['senton']);
                }
            }
        }
    }

    /**
     * Queue/send notification from observation
     * @param string       $event     The event
     * @param IObservation $state     The observation
     */
    private function sendNotificationFromEvent($event, $state)
    {
            $push = $this->getPushNotificationFromEvent($event);
            if (! $push) {
                $this->log("Unable to find MyPushNotification from event {$event}", SNAP_LOG_INFO);
                return;
            }
            
            $accHolder = $this->app->myaccountholderStore()->getById($state->otherParams['accountholderid']);
            
            // Check if push notification is enabled
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $accHolder->partnerid);
            if (!$settings->enablepushnotification) {
                $this->log("Push Notification is not enabled for this partner {$accHolder->partnercode}", SNAP_LOG_DEBUG);
                return;
            }
            // End Check

            if ($state->otherParams['usejob']) {
                // Pass to notificationmanager for event processing
                $this->addPushNotificationJob($push, $accHolder);
            } else {
                // Push immediately
                $ret = $this->pushToAccountHolder($accHolder, $push, array_merge($this->data, $state->otherParams['data'] ?? []), $accHolder->preferredlang);
                return $ret;
            }
    }

    /**
     * Gets a valid push notification for the event from pushnotificationeventmap
     *
     * @param string $event             The event stored in database
     *
     * @return MyPushNotification
     */
    public function getPushNotificationFromEvent($event)
    {
        $now = new \DateTime();
        $push = $this->app->mypushnotificationStore()->searchTable()->select()
                        ->where('eventtype', $event)
                        ->andWhere('validfrom', '<=', $now->format('Y-m-d H:i:s'))
                        ->andWhere('validto', '>', $now->format('Y-m-d H:i:s'))
                        ->andWhere('status', MyPushNotification::STATUS_ACTIVE)
                        ->orderBy('rank', 'DESC')
                        ->one();

        return $push;
    }

    /**
     * Queues a push notification job to be processed later
     *
     * @param MyPushNotification    $push           The Push notification object
     * @param MyAccountHolder       $accHolder      The account holder
     *
     * @return void
     */
    public function addPushNotificationJob($push, $accHolder)
    {
        $observation = new IObservation($accHolder, IObservation::ACTION_VERIFY, $accHolder->kycstatus);
        $observation->otherParams['event'] = MyGtpEventConfig::EVENT_SEND_PUSH;
        $observation->otherParams['notificationid'] = $push->id;
        $observation->otherParams['language'] = $accHolder->preferredlang;
        $this->notify($observation);
    }

    /**
     * Push a notification to a single account holder
     * @param MyAccountHolder    $accountHolder    The account holder
     * @param MyPushNotification $push             The push notification object
     * @param array              $data             Additional data to push along with the notification
     * @param string             $language         The language for the push notification
     * @param boolean            $runJob           Whether to run a separate process to send the push notification or not
     *
     * @return array    Response from FCM
     */
    public function pushToAccountHolder(MyAccountHolder $accountHolder, MyPushNotification $push, $data = [],
                                        string $language = MyLocalizedContent::LANG_ENGLISH)
    {
        $tokenObjs = $accountHolder->getDevicePushTokens();
        $tokens = [];

        foreach ($tokenObjs as $tokenObj) {
            $tokens[] = $tokenObj->token;
        }

        return $this->pushToTokens($tokens, $push, $language, $data);
    }

    /**
     * Push a notification to multiple account holders
     * @param MyAccountHolder[]  $accountHolder    An array of account holders
     * @param MyPushNotification $push             The push notification object
     * @param array              $data             Additional data to push along with the notification
     * @param string             $language         The language for the push notification
     * @param boolean            $runJob           Whether to run a separate process to send the push notification or not
     *
     * @return array    Response from FCM
     */
    public function pushToAccountHolders(array $accountHolders, MyPushNotification $push, $data = [],
                                         string $language = MyLocalizedContent::LANG_ENGLISH)
    {
        $tokens = [];
        foreach ($accountHolders as $accHolder) {
            $tokenObjs = $accHolder->getDevicePushTokens();
            foreach ($tokenObjs as $tokenObj) {
                $tokens[] = $tokenObj->token;
            }
        }
        return $this->pushToTokens($tokens, $push, $language, $data);
    }

    /**
     * Push using device token directly
     * @param string[] $tokens     The device tokens to push to
     *
     * @return array|null   Null if $runJob is true, else returns response array from FCM
     */
    public function pushToTokens($tokens, MyPushNotification $myPush, string $language, $data = [])
    {
        if (!is_array($tokens)) {
            $tokens = [ $tokens ];
        }

        return $this->doSendNotification($tokens, $myPush, $language, $data);
    }

    /**
     * This is the function that will perform the sending
     * @param array                     $deviceIds
     * @param int|MyPushNotification    $myPush         The ID or PushNotification object
     * @param string                    $language       The language for the push notification
     * @param array                     $data           Optional data
     *
     * @return array
     */
    public function doSendNotification($deviceIds, $myPush, string $language, $data = [])
    {
        if (! count($deviceIds)) {
            $this->logDebug(__CLASS__.": Skipping ".__METHOD__." due to no deviceIds.");
            return [];
        }

        if (! $myPush instanceof MyPushNotification) {
            $myPush = $this->app->mypushnotificationStore()->getById($myPush);
        }

        if (! $myPush) {
            throw new \Exception(__CLASS__.": Unable to find the push notification record.");
        }
        $myPush->language = $language;
        if (! $myPush->getLocalizedContent()) {
            $this->logDebug(__CLASS__ . ": Skipping " . __METHOD__ . " due to empty localized content.");
            return [];
        }

        /** @var \Fcm\Push\Notification */
        $notification = $this->client->pushNotification($myPush->title, $myPush->body)
                             ->addRecipient($deviceIds);

        if (0 < strlen($myPush->icon)) {
            $notification = $notification->setIcon($myPush->icon);
        }

        if (0 < strlen($myPush->sound)) {
            $notification = $notification->setSound($myPush->sound);
        }

        if (0 < count($data)) {
            $notification->addDataArray($data);
        }

        $this->logDebug("Sending notification id {$myPush->id} with language $language.");
        $response = $this->client->send($notification);
        return $response;
    }

    /**
     * This is the function that will perform the sending for announcement
     * @param array                     $deviceIds
     * @param int|MyAnnouncement        $myPush         The ID or Announcement object
     * @param string                    $language       The language for the push notification
     * @param array                     $data           Optional data
     *
     * @return array
     */
    public function doSendAnnouncement($deviceIds, $myAnnouncement, string $language, $data = [])
    {
        if (!count($deviceIds)) {
            $this->logDebug(__CLASS__ . ": Skipping " . __METHOD__ . " due to no deviceIds.");
            return [];
        }

        if (!$myAnnouncement instanceof MyAnnouncement) {
            $myAnnouncement = $this->app->myannouncementStore()->getById($myAnnouncement);
        }

        if (!$myAnnouncement) {
            throw new \Exception(__CLASS__ . ": Unable to find the push announcement record.");
        }
        $myAnnouncement->language = $language;

        /** @var \Fcm\Push\Notification */
        $notification = $this->client->pushNotification($myAnnouncement->title, $myAnnouncement->content)
            ->addRecipient($deviceIds);

        if (0 < count($data)) {
            $notification->addDataArray($data);
        }

        $this->logDebug("Sending push announcement id {$myAnnouncement->id} with language $language.");
        $response = $this->client->send($notification);
        return $response;
    }

    public function doTriggerPushForEvent($event, $accountHolder, $targetId = null)
    {

        $target = $accountHolder;
        $action = \Snap\IObservation::ACTION_OTHER;

        switch ($event) {
            case MyPushNotification::TYPE_EKYC_FAIL:
            case MyPushNotification::TYPE_EKYC_PASS:
            case MyPushNotification::TYPE_INCOMPLETE_PROFILE:
            case MyPushNotification::TYPE_PEP_PASSED:
            case MyPushNotification::TYPE_PEP_FAILED:
            case MyPushNotification::TYPE_GOLDTRANSACTION_CONFIRMED:
                $otherParams = array('accountholderid' => $accountHolder->id, 'data' => [
                    'remarks' => $target->remarks
                ]);
                break;
            case MyPushNotification::TYPE_PRICE_MATCH_BUY:
            case MyPushNotification::TYPE_PRICE_MATCH_SELL:
                $target = $this->app->mypricealertStore()->getById($targetId);
                $otherParams = array(
                    'accountholderid' => $accountHolder->id,
                    'data' => [
                        'id' => intval($target->id),
                        'price' => floatval($target->amount),
                        'type' => $target->type == MyPriceAlert::TYPE_BUY ? 'Buy' : 'Sell',
                        'date' => $target->createdon->format('Y-m-d H:i:s'),
                        'last_triggered' => !is_null($target->lasttriggeredon) ? $target->lasttriggeredon->format('Y-m-d H:i:s') :  '',
                        'expiry' => $target->createdon->format('Y-m-d H:i:s'),
                        'remarks' => $target->remarks
                    ]
                );
                break;
            default:
                
                throw new \Exception("Unknown push notification for event type ({$event}).");
                break;
        }

        $initialStatus = 1;
        $state = new \Snap\IObservation(
            $target,
            $action,
            $initialStatus,
            $otherParams
        );

        $this->sendNotificationFromEvent($event, $state);

    }

}