<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\MyAnnouncement;
use Snap\object\MyLocalizedContent;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author  Azam <azam@silverstream.my>
 * @version 1.0
 */
class myannouncementhandler extends CompositeHandler
{
    protected $app;

    function __construct(App $app)
    {
        parent::__construct('/root/system', 'myannouncement');

        $this->mapActionToRights('list', 'list');
        $this->mapActionToRights('fillform', 'add');
        $this->mapActionToRights('fillform', 'edit');
        $this->mapActionToRights('approveannouncement', 'approve');
        $this->mapActionToRights('disableannouncement', 'disable');

        $this->app = $app;

        $announcementStore = $app->myannouncementStore();
        $this->addChild(new ext6gridhandler($this, $announcementStore, 1));
    }

    /**
     * Function to massage data before add / update
     *
     * @param  MyAnnouncement $object
     * @param  array $params
     * @return MyAnnouncement
     */
    function onPreAddEditCallback($object, $params)
    {
        return $object;
    }

    /**
     * Function to update data after add/update
     *
     * @param  MyAnnouncement $savedRec
     * @param  array $params
     * @return MyAnnouncement
     **/
    function onPostAddEditCallback($savedRec, $params)
    {
        if (isset($params['myAnnouncementTranslationParams']) && !empty($params['myAnnouncementTranslationParams'])) {
            $translations = json_decode($params['myAnnouncementTranslationParams']);
            $languages = [];

            // Save content for each language
            foreach ($translations as $translation) {
                if (!$translation->title || !$translation->content || !$translation->language) {
                    continue;
                }

                $savedRec->language = $translation->language;
                $savedRec->title = $translation->title;
                $savedRec->content = $translation->content;
                $languages[] = $translation->language;
                $this->app->myannouncementStore()->save($savedRec);
            }

            $savedRec->syncContent($languages);

        }

        return $savedRec;
    }

    /**
     * Function to masssage data before listing
     *
     * @param  MyAnnouncement[] $objects
     * @param  array $params
     * @param  array $records
     * @return void
     */
    function onPreListing($objects, $params, $records)
    {
        $localizedObjects = [];

        foreach ($objects as $object) {
            $localizedObjects[$object->id] = $object;
        }

        array_walk($records, function (&$record, $key) use ($localizedObjects) {
            $localizedObject = $localizedObjects[$record['id']];
            $record['locales'] = implode(', ', $localizedObject->getAvailableLanguages());
        });

        return $records;
    }

    /**
     * function to populate selection data into form
     **/
    function fillform($app, $params)
    {
        $announcementTypeArr = [
            (object)array("type" => MyAnnouncement::TYPE_ANNOUNCEMENT),
            (object)array("type" => MyAnnouncement::TYPE_PUSH)
        ];

        $languageArr = [
            (object)array("type" => MyLocalizedContent::LANG_ENGLISH),
            (object)array("type" => MyLocalizedContent::LANG_BAHASA),
            (object)array("type" => MyLocalizedContent::LANG_CHINESE),
        ];



        if (0 < $params['id']) {
            /** @var \Snap\object\MyAnnouncement */
            $announcement = $app->myannouncementStore()->getById($params['id']);
            $localizedContents = $announcement->getAvailableContents();
            $localizedContentRecords = [];
            if ($localizedContents && !empty($localizedContents)) {
                foreach ($localizedContents as $localizedContent) {
                    $data = array_merge((array) json_decode($localizedContent->data), ['language' => $localizedContent->language]);
                    $localizedContentRecords[] = $data;
                }
            }
        }

        echo json_encode([
            'success' => true,
            'type' => $announcementTypeArr,
            'language' => $languageArr,
            'translations' => $localizedContentRecords,
        ]);
    }

    /**
     * Function to massage data before the action is executed
     *
     * @param  App    $app
     * @param  string $action
     * @param  array  $params
     * @return mixed
     */
    function doAction($app, $action, $params)
    {
        switch ($action) {
            case 'add':
                $params['status'] = MyAnnouncement::STATUS_PENDING;
                break;
            // case 'delete':
            //     $params['statusdel'] = true;
            default:
                break;
        }
        return parent::doAction($app, $action, $params);
    }

    function approveannouncement($app, $params)
    {
        try {
            $app->mygtpannouncementManager()->approveAnnouncement($params['id'], $app->getUserSession()->getUser()->id);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    function disableannouncement($app, $params)
    {
        try {
            $app->mygtpannouncementManager()->disableAnnouncement($params['id'], $app->getUserSession()->getUser()->id);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }
}
