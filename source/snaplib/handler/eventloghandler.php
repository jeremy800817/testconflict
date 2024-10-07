<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;
Use \Snap\store\dbdatastore as DbDatastore;
USe Snap\App;
/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Waynelee <waynelee@silverstream.my>
 * @version 1.0
 */
class eventLogHandler extends CompositeHandler {
	function __construct(App $app) {
		parent::__construct('/root/system/event', 'event_log');
		$this->mapActionToRights('detailview', 'list');

		$this->app = $app;
		$currentStore = $app->eventlogfactory();
		$this->addChild(new ext6gridhandler($this, $currentStore, 1));
	}
		
	function onPreListing($objects, $params, $records) {
		$notificationmanager = $this->app->notificationmanager();
		$getEventModuleMap = $notificationmanager->getEventConfig()->getEventModuleMap($this->app);
		$getEventActionMap = $notificationmanager->getEventConfig()->getEventActionMap($this->app);
		$getEventGroupTypeMap = $notificationmanager->getEventConfig()->getEventGroupTypeMap($this->app);

		// this is for status
		foreach ($records as $key => $record) {
			$records[$key]['status_text'] = ($record['status'] == \Snap\object\snapobject::STATUS_ACTIVE ? "Active" : "Inactive");

			foreach ($getEventModuleMap as $aModule) {
				if($aModule['id'] == $record['moduleid'])
					$records[$key]['moduleid_text'] = $aModule['desc'];
			}

			foreach ($getEventActionMap as $aAction) {
				if($aAction['id'] == $record['actionid'])
					$records[$key]['actionid_text'] = $aAction['desc'];
			}

			foreach ($getEventGroupTypeMap as $aGroupType) {
				if($aGroupType['id'] == $record['grouptypeid'])
					$records[$key]['grouptypeid_text'] = $aGroupType['desc'];
			}
		}		

		return $records;
	}

	/**
	* function to provide detail properties
	**/
	function detailview($app, $params) {
		$object = $app->eventlogfactory()->getById($params['id']);
		$objectTrigger = $app->eventtriggerfactory()->getById($object->triggerid);
		$branchName = $app->branchFactory()->getById($object->groupid)->name;
		$notificationmanager = $this->app->notificationmanager();
		$getEventModuleMap = $notificationmanager->getEventConfig()->getEventModuleMap($this->app);
		$getEventActionMap = $notificationmanager->getEventConfig()->getEventActionMap($this->app);
		$getEventGroupTypeMap = $notificationmanager->getEventConfig()->getEventGroupTypeMap($this->app);

		foreach ($getEventGroupTypeMap as $aGroupType) {
			if($aGroupType['id'] == $objectTrigger->grouptypeid)
				$detailRecord['default']['Group Type'] = $aGroupType['desc'];
		}
		$detailRecord['default']['Branch Name'] = $branchName;
		foreach ($getEventModuleMap as $aModule) {
			if($aModule['id'] == $objectTrigger->moduleid)
				$detailRecord['default']['Module'] = $aModule['desc'];
		}
		foreach ($getEventActionMap as $aAction) {
			if($aAction['id'] == $objectTrigger->actionid)
				$detailRecord['default']['Action'] = $aAction['desc'];
		}
		$detailRecord['default']['Subject'] = $object->subject;
		$detailRecord['default']['Object ID'] = $object->objectid;
		$detailRecord['default']['Reference'] = $object->reference;
		$detailRecord['default']['Send to'] = $object->sendto;
		$detailRecord['default']['Send On'] = $object->sendon->format('Y-m-d H:i:s');
		// $detailRecord['default']['Status'] = ($object->status == \Snap\object\snapobject::STATUS_ACTIVE ? "Active" : "Inactive");
		$detailRecord['log']['Details'] = $object->log;

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}
}