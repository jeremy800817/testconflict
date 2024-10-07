<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2017
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Chuah <chuah@silverstream.my>
 * @version 1.0
 */
class eventsubscriberHandler extends CompositeHandler {

	function __construct(App $app) {
		parent::__construct('/root/system/event', 'event_subscription');
		$this->mapActionToRights('fillform', 'add');
		$this->mapActionToRights('fillform', 'edit');
		$this->mapActionToRights('delete', 'delete');
		$this->mapActionToRights('detailview', 'list');

		$this->app = $app;
		$eventsubscriberStore = $app->eventsubscriberfactory();
		$this->addChild(new ext6gridhandler($this, $eventsubscriberStore));

	}

	function onPreListing($objects, $params, $records) {
		// get all modules
		$notificationmanager = $this->app->notificationmanager();
		$getEventModuleMap = $notificationmanager->getEventConfig()->getEventModuleMap($this->app);
		$getEventActionMap = $notificationmanager->getEventConfig()->getEventActionMap($this->app);
		$eventTriggers = $this->app->eventtriggerStore()->searchTable()->select(['id', 'moduleid', 'actionid', 'processorclass'])->where('status', \Snap\object\snapobject::STATUS_ACTIVE)->execute();

		// get all branches
		$allBranches = $this->app->partnerStore()->getRelatedStore("branches")->searchTable()->select(['id', 'name', 'code'])->where('status', \Snap\object\snapobject::STATUS_ACTIVE)->execute();
		$int = 0;
		$data = array();
		$newRecords = array();

		foreach ($getEventModuleMap as $aModule) {
			foreach ($getEventActionMap as $aAction) {
				foreach ($eventTriggers as $aTrigger) {
					if ($aModule['id'] == $aTrigger->moduleid && $aAction['id'] == $aTrigger->actionid) {
						foreach ($allBranches as $aBranch) {
							// trigger id
							$data[$int]['trigger_id'] = $aTrigger->id;

							// module
							$data[$int]['module_id'] = $aModule['id'];
							$data[$int]['module_desc'] = $aModule['module_desc'] . ' - ' . $aModule['desc'];

							// branch
							$data[$int]['branch_id'] = $aBranch->id;
							$data[$int]['branch_name'] = $aBranch->name;
							$data[$int]['branch_code'] = $aBranch->code;

							// action
							$data[$int]['action_id'] = $aAction['id'];
							$data[$int]['action_desc'] = $aAction['desc'];

							// processor
							// substr -> \Snap\object\...email...eventprocessor
							$data[$int]['processorclass'] = ucfirst(substr(substr($aTrigger->processorclass, 13), 0, -14));

							// receiver
							foreach ($records as $aSubscriber) {
								if ($aBranch->id == $aSubscriber['groupid'] && $aTrigger->id == $aSubscriber['triggerid']) {
									$data[$int]['receiver'] = $aSubscriber['receiver'];
									$data[$int]['object_id'] = $aSubscriber['id'];
									break;
								}
								else {
									$data[$int]['receiver'] = '';
									$data[$int]['object_id'] = '';
								}
							}
							
							$int++;
						}
					}
				}
			}
		}

		foreach ($data as $k => $d) {
			// trigger
			$newRecords[$k]['trigger_id'] = $d['trigger_id'];

			// module
			$newRecords[$k]['module_id'] = $d['module_id'];
			$newRecords[$k]['module_desc'] = $d['module_desc'];

			// branch
			$newRecords[$k]['branch_id'] = $d['branch_id'];
			$newRecords[$k]['branch_name'] = $d['branch_name'];
			$newRecords[$k]['branch_code'] = $d['branch_code'];

			// action
			$newRecords[$k]['action_id'] = $d['action_id'];
			$newRecords[$k]['action_desc'] = $d['action_desc'];

			$newRecords[$k]['processorclass'] = $d['processorclass'];
			$newRecords[$k]['receiver'] = $d['receiver'];
			$newRecords[$k]['object_id'] = $d['object_id'];
		}

		unset($records);
		$records = $newRecords;

		return $records;
	}

	function onPreAddEditCallback($object, $params) {
		$object = $object->addEventSubscriber( $params['objectid'], $params['triggerid'], $params['groupid'], $params['receiver'] );
		return $object;
	}

	function fillform( $app, $params) {
		// Get the data.
		$record = array();
		if($params['object_id'] != "") {
			$object = $app->eventsubscriberfactory()->getById($params['object_id']);
			$record = $object->toArray();
		} else {
			$record = $app->eventsubscriberfactory()->create([ 'id' => 0, 'status' => 1 ])->toArray();
			$record['triggerid'] = $params['trigger_id'];
			$record['groupid'] = $params['branch_id'];
			$record['receiver'] = $params['receiver'];
		}

		//Response back to the client
		echo json_encode([ 'success' => true,
			'objectid' => $record['id'], 
			'triggerid' => $record['triggerid'], 
			'groupid' => $record['groupid'], 
			'receiver' => $record['receiver']
		]);
	}

	function detailview($app, $params) {
		if ($params['object_id'] != "") {
			$object = $app->eventsubscriberfactory()->getById($params['object_id']);

			if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
			else $modifieduser = 'System';

			if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
			else $createduser = 'System';

			$createdon = $object->createdon->format('Y-m-d H:i:s');
			$modifiedon = $object->modifiedon->format('Y-m-d H:i:s');
		} else {
			$modifieduser = "";
			$createduser = "";
			$createdon = "";
			$modifiedon = "";
		}

		$detailRecord = [ 
			'ID' => $params['object_id'],
			'Branch' => $params['branch_name'],
			'Branch Code' => $params['branch_code'],
			'Module' => $params['module_desc'],
			'Action' => $params['action_desc'],
			'Receiver' => $params['receiver'],
			'Created on' => $createdon,
			'Created by' => $createduser,
			'Modified on' => $modifiedon,
			'Modified by' => $modifieduser
		];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}
}
