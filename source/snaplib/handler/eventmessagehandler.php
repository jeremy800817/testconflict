<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2017
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
USe Snap\App;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Chuah <chuah@silverstream.my>
 * @version 1.0
 */
class eventmessageHandler extends CompositeHandler {

	function __construct(App $app) {
		parent::__construct('/root/system/event', 'event_message');
		$this->mapActionToRights('fillform', 'add');
		$this->mapActionToRights('fillform', 'edit');
		$this->mapActionToRights('delete', 'delete');
		$this->mapActionToRights('detailview', 'list');

		$this->app = $app;
		$eventmessasgeStore = $app->eventmessagefactory();
		$this->addChild(new ext6gridhandler($this, $eventmessasgeStore));

	}

	function onPreListing($objects, $params, $records) {
		foreach ($records as $key => $record) {
			$records[$key]['shorten_content'] = (strlen($record['content']) > 99) ? substr(htmlspecialchars($record['content']), 0, 100).'...' : htmlspecialchars($record['content']);
			$records[$key]['content'] = $record['content'];
			$records[$key]['status_text'] = ($record['status'] == "1" ? "Active" : "Inactive");
		}

		return $records;
	}

	// function onPreAddEditCallback($object, $params) {
	// 	$eventmessasgeStore = $this->app->eventmessagefactory();

	// 	if ($params['id'] != 0) {
	// 		$eventmessasge = $object->editEventMessage( $params['code'], $params['replacetext'], $params['subject'], $params['contentfull']);
	// 	} else {
	// 		$eventmessasge = $object->addEventMessage( $params['code'], $params['replacetext'], $params['subject'], $params['contentfull']);
	// 		$eventmessasgeStore->save($eventmessasge);
	// 	}

	// 	return $object;
	// }

	function fillform( $app, $params) {
		// Get the data.
		$record = array();
		if($params['id']) {
			$object = $app->eventmessagefactory()->getById($params['id']);
			$record = $object->toArray();

			$replacelist = explode(",", $object->replace);
			foreach($replacelist as $repList) {
				$replace = explode("||", $repList);
				$record['replacelist'][] = [ 'name' => $replace[0], 'value' => $replace[1] ];
			}
		} else {
			$record['replacelist'] = [];
		}

		//Response back to the client
		echo json_encode([ 'success' => true, 'record' => $record ]);
	}

	function detailview($app, $params) {
		$object = $app->eventmessagefactory()->getById($params['id']);

		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		$detailRecord = [ 
			"ID" => $object->id,
			'Name' => $object->code,
			'Tags' => $object->replace,
			'Subject' => $object->subject,
			'Body' => $object->content,
			'Status' => ($object->status == 1) ? 'Active' : 'Inactive',
			'Created on' => $object->createdon->format('Y-m-d H:i:s'),
			'Created by' => $createduser,
			'Modified on' => $object->modifiedon->format('Y-m-d H:i:s'),
			'Modified by' => $modifieduser
		];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}
}
