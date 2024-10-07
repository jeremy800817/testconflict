<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Snap\object\Announcement;
use Snap\object\Attachment;
use Snap\object\MyAccountHolder;
use Snap\object\MyAnnouncement;
use Snap\object\MyAnnouncementTheme;
use Snap\object\MyLocalizedContent;
use Snap\object\MyToken;
use Snap\TLogging;

/**
 * This class handles announcement management
 *
 * @author  Azam <azam@silverstream.my>
 * @version 1.0
 * @created 18-Nov-2020
 */
class MyGtpAnnouncementManager
{
    use TLogging;

    /** @var \Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get announcements active in GTP Core
     * 
     * @return Announcement[]
     */
    public function getCoreActiveAnnouncements($partner = false)
    {
        $now = new \DateTime();
        if ($partner){
            $partnerid = $partner->id;
        }else{
            $partnerid = 0;
        }

        $announcements = $this->app->announcementStore()->searchTable()->select()
            ->where('displaystarton', '<=', $now->format('Y-m-d H:i:s'))
            ->andWhere('displayendon', '>', $now->format('Y-m-d H:i:s'))
            ->andWhere('status', Announcement::STATUS_ACTIVE)
            ->andWhere('partnerid', $partnerid)
            ->orderBy('rank', 'DESC')
            ->execute();

        return $announcements;
    }

    /**
     * Get attachment for an announcement
     * 
     * @return Attachment|null
     */
    public function getAnnouncementAttachment($announcementId, $attachmentId)
    {
        $attachment = $this->app->announcementStore()->getRelatedStore('attachment')
                        ->searchTable()->select()
                        ->where('id', $attachmentId)
                        ->andWhere('sourcetype', Attachment::ANNOUNCEMENT_SOURCE)
                        ->andWhere('sourceid', $announcementId)
                        ->one();
        return $attachment;
    }

    /**
     * Push all queued announcement to the account holders
     *
     * @return bool
     */
    public function pushQueuedAnnouncements()
    {

        /** @var MyGtpPushNotificationManager $pushNotificationManager */
        $pushNotificationManager = $this->app->mygtppushnotificationManager();

        $now = new \DateTime();
        $queuedAnnouncements = $this->app->myannouncementStore()->searchTable()
            ->select()
            ->where('displaystarton', '<=', $now->format('Y-m-d H:i:s'))
            ->where('displayendon', '>=', $now->format('Y-m-d H:i:s'))
            ->where('status', MyAnnouncement::STATUS_QUEUED)
            ->where('type',   MyAnnouncement::TYPE_PUSH)
            ->get();

        if (!$queuedAnnouncements) {
            $this->logDebug(__CLASS__ . ": Skipping " . __METHOD__ . " due to no active queued announcement was found.");
            return false;
        }

        // Get the recepients device ids
        $receipients = $this->getAnnouncementReceipients();

        try {
            foreach ($queuedAnnouncements as $announcement) {
                foreach ($receipients as $language => $deviceIds) {
                    $this->logDebug("Preparing to send announcement id {$announcement->id} with language $language.");
                    $pushNotificationManager->doSendAnnouncement($deviceIds, $announcement, $language);
                }

                $announcement->status = MyAnnouncement::STATUS_COMPLETED;
                $this->app->myannouncementStore()->save($announcement);
            }
        } catch (\Throwable $th) {
            $this->log(__METHOD__ . "(): Failed to send queued announcement with error " . $th->getMessage(), SNAP_LOG_ERROR);
            return false;
        }

        return true;
    }

    /**
     * Set the announcement status as approved
     *
     * @param  MyAnnouncement $announcement The announcement to set as approved
     * @param  int            $userId       The user who approved the announcement
     * @return MyAnnouncement
     */
    public function approveAnnouncement($announcementId, $userId)
    {
        $now = new \DateTime("now", $this->app->getUserTimezone());
        $now = $now->format("Y-m-d H:i:s");
        $announcement = $this->app->myannouncementStore()->getById($announcementId);

        $state = $this->getStateMachine($announcement);
        if (!$state->can(MyAnnouncement::STATUS_APPROVED)) {
            throw new \Exception("Invalid action for the announcement");
        }

        if (MyAnnouncement::TYPE_PUSH == $announcement->type) {
            $announcement->status     = MyAnnouncement::STATUS_QUEUED;
        } else {
            $announcement->status     = MyAnnouncement::STATUS_APPROVED;
        }

        $announcement->approvedon = $now;
        $announcement->approvedby = $userId;

        return $this->app->myannouncementStore()->save($announcement);
    }

    /**
     * Set the announcement status as inactive / disabled
     *
     * @param  MyAnnouncement $announcement The announcement to set as inactive
     * @param  int            $userId       The user who disabled the announcement
     * @return MyAnnouncement
     */
    public function disableAnnouncement($announcementId, $userId)
    {
        $now = new \DateTime("now", $this->app->getUserTimezone());
        $now = $now->format("Y-m-d H:i:s");
        $announcement = $this->app->myannouncementStore()->getById($announcementId);

        $state = $this->getStateMachine($announcement);
        if (!$state->can(MyAnnouncement::STATUS_INACTIVE)) {
            throw new \Exception("Invalid action for the announcement");
        }
        $announcement->status     = MyAnnouncement::STATUS_INACTIVE;
        $announcement->disabledon = $now;
        $announcement->disabledby = $userId;

        return $this->app->myannouncementStore()->save($announcement);
    }

    /**
     * Get the current active announcement in the active theme as html
     *
     * @param  string $language
     * @return string
     */
    public function getAnnouncements($language = null)
    {
        try {
            // If language is not specified
            if (!in_array($language, [MyAccountHolder::LANG_BM, MyAccountHolder::LANG_CN, MyAccountHolder::LANG_EN])) {
                $language = MyLocalizedContent::LANG_ENGLISH;
            }

            $now = new \DateTime();

            /** @var MyAnnouncementTheme */
            $theme = $this->app->myannouncementthemeStore()
                ->searchTable()
                ->select()
                ->where('displaystarton', '<=', $now->format('Y-m-d H:i:s'))
                ->where('displayendon', '>=', $now->format('Y-m-d H:i:s'))
                ->where('validfrom', '<=', $now->format('Y-m-d H:i:s'))
                ->where('validto', '>=', $now->format('Y-m-d H:i:s'))
                ->where('status',  MyAnnouncementTheme::STATUS_ACTIVE)
                ->orderBy('rank', 'desc')
                ->one();

            /** @var MyAnnouncement[] */
            $announcements = $this->app->myannouncementStore()
                ->searchTable()
                ->select()
                ->where('displaystarton', '<=', $now->format('Y-m-d H:i:s'))
                ->where('displayendon', '>=', $now->format('Y-m-d H:i:s'))
                ->where('status', MyAnnouncement::STATUS_APPROVED)
                ->where('type', MyAnnouncement::TYPE_ANNOUNCEMENT)
                ->orderBy('displaystarton', 'desc')
                ->get();

            // Load all the announcements content for the language
            foreach ($announcements as $index => $announcement) {
                $announcement->getContentIn($language, true);

                // Skip empty announcements
                if (!$announcement->title || !$announcement->content) {
                    unset($announcements[$index]);
                }
            }

            $announcements = array_merge($this->getGeneralAnnouncements(), $announcements);
            // usort($announcements, function ($a, $b) {
            //     $time1 = $a->displaystarton->format('Y-m-d H:i:s');
            //     $time2 = $b->displaystarton->format('Y-m-d H:i:s');
            //     if (strtotime($time1) < strtotime($time2))
            //         return 1;
            //     else if (strtotime($time1) > strtotime($time2))
            //     return -1;
            //     else
            //         return 0;
            // });

            // If there is not active theme at the moment
            if (!$theme) {
                $this->log(__METHOD__ . '() No active theme found', SNAP_LOG_ERROR);
                throw new \Exception('No active theme found');
            }

            $this->log(__METHOD__ . '() Theme found, using ' . $theme->name . '  template for announcements ', SNAP_LOG_DEBUG);
            return $this->applyTemplate($announcements, $theme->template);
        } catch (\Exception $e) {

            $this->log(__METHOD__ . '() Unable to get announcements with error message: ' . $e->getMessage(), SNAP_LOG_ERROR);
            throw $e;
        }
    }

    /**
     * This method will return the announcement state machine to manage the different states of the announcement process.
     * 
     * @return Finite/StateMachine/StateMachine 
     */
    public function getStateMachine($announcement)
    {
        $stateMachine = new \Finite\StateMachine\StateMachine;
        $config       = [
            'property_path' => 'status',
            'states' => [
                MyAnnouncement::STATUS_PENDING   => ['type' => 'initial', 'properties' => []],
                MyAnnouncement::STATUS_APPROVED  => ['type' => 'normal', 'properties' => []],
                MyAnnouncement::STATUS_QUEUED    => ['type' => 'normal', 'properties' => []],
                MyAnnouncement::STATUS_INACTIVE  => ['type' => 'final', 'properties' => []],
                MyAnnouncement::STATUS_COMPLETED => ['type' => 'final', 'properties' => []],
            ],
            'transitions' => [
                MyAnnouncement::STATUS_APPROVED => [
                    'from' => [MyAnnouncement::STATUS_PENDING, MyAnnouncement::STATUS_APPROVED],
                    'to' => MyAnnouncement::STATUS_APPROVED
                ],
                MyAnnouncement::STATUS_QUEUED   => [
                    'from' => [MyAnnouncement::STATUS_PENDING, MyAnnouncement::STATUS_APPROVED],
                    'to' => MyAnnouncement::STATUS_QUEUED
                ],
                MyAnnouncement::STATUS_INACTIVE => [
                    'from' => [MyAnnouncement::STATUS_PENDING, MyAnnouncement::STATUS_APPROVED, MyAnnouncement::STATUS_QUEUED],
                    'to' => MyAnnouncement::STATUS_INACTIVE
                ],
                MyAnnouncement::STATUS_COMPLETED => [
                    'from' => [MyAnnouncement::STATUS_QUEUED],
                    'to' => MyAnnouncement::STATUS_COMPLETED
                ],
            ]
        ];
        $loader = new \Finite\Loader\ArrayLoader($config);
        $loader->load($stateMachine);
        $stateMachine->setStateAccessor(new \Finite\State\Accessor\PropertyPathStateAccessor($config['property_path']));
        $stateMachine->setObject($announcement);
        $stateMachine->initialize();
        return $stateMachine;
    }

    protected function getGeneralAnnouncements()
    {
        $now = new \DateTime();

        /** @var Announcement[] */
        $announcements = $this->app->announcementStore()
            ->searchTable()
            ->select()
            ->where('displaystarton', '<=', $now->format('Y-m-d H:i:s'))
            ->where('displayendon', '>=', $now->format('Y-m-d H:i:s'))
            ->where('status', Announcement::STATUS_ACTIVE)
            ->orderBy('displaystarton', 'desc')
            ->get();

        foreach ($announcements as $announcement) {
            $announcement->content = $announcement->description;
        }

        return $announcements;
    }

    /**
     * Apply the template for the announcement
     *
     * @param  array  $announcements
     * @param  string $template
     *
     * @return string
     */
    protected function applyTemplate(array $announcements, $template)
    {
        preg_match('/((?<=##BLOCKSTART##)[\s\S]*?(?=##BLOCKEND##))/', $template, $matches);

        $tags = ['##ANNOUNCEMENTTITLE##', '##ANNOUNCEMENTCONTENT##'];

        $replacements = '';
        foreach ($announcements as $announcement) {
            $fillers = [$announcement->title, $announcement->content];
            $replacements .= str_replace($tags, $fillers, $matches[0]);
        }

        $emptyReplacements = '';
        if (empty($announcements)) {
            preg_match('/((?<=##EMPTYSTART##)[\s\S]*?(?=##EMPTYEND##))/', $template, $emptyMatches);
            $tags = ['##EMPTYPLACEHOLDER##'];
            $fillers = [gettext('No announcement at the moment')];
            $emptyReplacements = str_replace($tags, $fillers, $emptyMatches[0]);
        }

        $template = preg_replace('/##EMPTYSTART##[\s\S]*##EMPTYEND##/', $emptyReplacements, $template);
        return preg_replace('/##BLOCKSTART##[\s\S]*##BLOCKEND##/', $replacements, $template);
    }

    /**
     * This method retrives all the receipients of the push notification
     *
     * @return array
     */
    protected function getAnnouncementReceipients()
    {
        $accountHolders = $this->app->myaccountholderStore()
            ->searchTable()
            ->select(['id', 'preferredlang'])
            ->where('status', MyAccountHolder::STATUS_ACTIVE)
            ->get();

        $now = new \DateTime();
        $languageTargets = [];
        $deviceIds = [];

        // Group account holders by language
        foreach ($accountHolders as $accountHolder) {
            $languageTargets[$accountHolder->preferredlang ?? MyAccountHolder::LANG_EN][] = $accountHolder->id;
        }

        // Group device ids by language
        foreach ($languageTargets as $language => $target) {
            $this->logDebug(__METHOD__ . ": Grouping receipients for language: {$language}.");
            $tokenObjects = $this->app->mytokenStore()
                ->searchTable()
                ->select()
                ->whereIn('accountholderid', $target)
                ->andWhere('type', MyToken::TYPE_PUSH)
                ->andWhere('expireon', '>', $now->format('Y-m-d H:i:s'))
                ->andWhere('status', MyToken::STATUS_ACTIVE)
                ->get();

            // Get token from token object
            foreach ($tokenObjects as $tokenObject) {
                $deviceIds[$language][] = $tokenObject->token;
            }
        }

        return $deviceIds;
    }
}
