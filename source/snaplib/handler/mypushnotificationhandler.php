<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use Snap\App;


/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class mypushnotificationHandler extends CompositeHandler {

    private $store = null;

	function __construct(App $app) {
		parent::__construct('/root/system;', 'pushnotification');      

		$this->mapActionToRights('list', 'list');
		$this->mapActionToRights('fillform', 'add');
        $this->mapActionToRights('fillform', 'edit');

        // content grid
		$this->mapActionToRights('listcontent', 'list');

		$this->app = $app;

		$this->store = $myPushNotificationStore = $app->mypushnotificationFactory();
		$this->addChild(new ext6gridhandler($this, $myPushNotificationStore));
	}

	/**
	* function to populate selection data into form
	**/
	function fillform( $app, $params) {
		
		/*
		$pushEvents = $this->app->mypushnotificationeventStore()->searchTable()->select()->execute();
        $events=array();
        foreach($pushEvents as $pushEvent) {        
            $events[]= array( 'id' => $pushEvent->id, 'code' => $pushEvent->eventtype);
		}    
		*/

		$events = \Snap\object\MyPushNotification::getType();        

		echo json_encode([
			'success' => true,
            'events' => $events,
		]);
    }


    /**
     * Get list of content for selected push notification
     */
    function listcontent($app, $params)
    {
        /** @var \Snap\object\MyPushNotification $push */
        $push = $app->mypushnotificationStore()->getById($params['id']);

        /** @var \Snap\object\MyLocalizedContent[] $contents */
        $contents = $push->getAvailableContents();
        $data = [];

        foreach ($contents as $content) {
            $push->language = $content->language;
            $data[] = [
                'language' => $content->language,
                'title'    => $push->title,
                'body'     => $push->body
            ];
        }

        echo json_encode(['success' => true, 'contents' => $data]);
    }

    /**
     * Processes localized content
     * 
     * @param \Snap\object\MyPushNotification $record   The saved push notification
     * @param array    $params      The params
     */
    function onPostAddEditCallback($record, $params)
    {
        if (0 < strlen($params['contentparam'])) {
            $contents = json_decode($params['contentparam'], true);
        }

        // Save updated languages
        $processedLangs = [];
        foreach ($contents as $content) {
            $record->language = $content['language'];
            $record->title = $content['title'];
            $record->body = $content['body'];
            $record = $this->store->save($record);
            
            $processedLangs[] = $content['language'];
        }

        // If we actually saved anything
        if (0 < count($processedLangs)) {
            $availableLangs = $record->getAvailableLanguages();
            $diff = array_diff($availableLangs, $processedLangs);

            // Cleanup removed content
            foreach ($diff as $lang) {
                $record->deleteContent($lang);
            }
        }

    }
    

}
