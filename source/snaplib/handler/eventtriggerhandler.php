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
class eventTriggerHandler extends CompositeHandler {
	function __construct(App $app) {
		parent::__construct('/root', 'developer');
		$this->mapActionToRights('fillform', 'add');
		$this->mapActionToRights('fillform', 'edit');
		$this->mapActionToRights('listeventgrouptype', '/all/access');
		$this->mapActionToRights('listeventactiontype', '/all/access');
		$this->mapActionToRights('listeventmoduletype', '/all/access');

		$this->app = $app;
		$currentStore = $app->eventtriggerfactory();
		$this->addChild(new ext6gridhandler($this, $currentStore, 1));
	}

	function getRights($action) {
		if(in_array($action, ['listeventgrouptype', 'listeventactiontype', 'listeventmoduletype'], true)) {
			return '/all/access';
		}
		return '/root/developer/event';
	}

	function onPrepareSorting($order) {
		$newOrder = [];
		foreach($order as $key => $direction) {
			switch ($key) {
				case 'status_text':
					$key = 'status';
					break;

				case 'storetolog_text':
					$key = 'storetolog';
					break;

				case 'moduleid_text':
					$key = 'moduleid';
					break;

				case 'actionid_text':
					$key = 'actionid';
					break;

				case 'grouptypeid_text':
					$key = 'grouptypeid';
					break;

				default:
					$key = $key;
					break;
			}

			$newOrder[$key] = $direction;
		}
		return $newOrder;
	}

	function onPreListing($objects, $params, $records) {
		// get all modules
		$notificationmanager = $this->app->notificationmanager();
		$getEventModuleMap = $notificationmanager->getEventConfig()->getEventModuleMap($this->app);
		$getEventActionMap = $notificationmanager->getEventConfig()->getEventActionMap($this->app);
		$getEventGroupTypeMap = $notificationmanager->getEventConfig()->getEventGroupTypeMap($this->app);

		// this is for status, storetolog, module, action
		foreach ($records as $key => $record) {
			$records[$key]['status_text'] = ($record['status'] == \Snap\object\snapobject::STATUS_ACTIVE ? "Active" : "Inactive");
			$records[$key]['storetolog_text'] = ($record['storetolog'] == "1" ? "Yes" : "No");

			foreach ($getEventModuleMap as $aModule) {
				if($aModule['id'] == $record['moduleid'])
					$records[$key]['moduleid_text'] = $aModule['module_desc'] . ' - ' .  $aModule['desc'];
			}

			foreach ($getEventActionMap as $aAction) {
				if($aAction['id'] == $record['actionid'])
					$records[$key]['actionid_text'] = $aAction['desc'];
			}

			foreach ($getEventGroupTypeMap as $aGroupType) {
				if($aGroupType['id'] == $record['grouptypeid'])
					$records[$key]['grouptypeid_text'] = $aGroupType['desc'];
			}

			$records[$key]['processor_desc'] = ucfirst(substr(substr($record['processorclass'], 13), 0, -14));
		}

		return $records;
	}

	/**
	* function to populate selection data into form
	**/
	function fillform( $app, $params) {
		// to create grouptype, module and action options
		$notificationmanager = $this->app->notificationmanager();
		$getEventModuleMap = $notificationmanager->getEventConfig()->getEventModuleMap($this->app);
		$getEventActionMap = $notificationmanager->getEventConfig()->getEventActionMap($this->app);
		$getEventGroupTypeMap = $notificationmanager->getEventConfig()->getEventGroupTypeMap($this->app);

		// to get an array of object class
		$objectFolder = $app->getLibPath().'object';
		$objectsArr = scandir($objectFolder);
		$objectsArr = array_values(array_diff($objectsArr, array('.', '..')));
		$fileArr = array();
		foreach( $objectsArr as $key => $object) {
			if(preg_match('/\.php/', $object) && !preg_match('/trigger|event/i', $object)) {
				$fileArr[] = (object)array("key" => $key, "value" => '\Snap\object\\'.preg_replace('/\.php/', '', $object));
			}
		}
		$objectclass = $fileArr;

		// to get an array of manager class
		$managerFolder = $app->getLibPath().'manager';
		$managersArr = scandir($managerFolder);
		$managersArr = array_values(array_diff($managersArr, array('.', '..')));
		foreach( $managersArr as $key => $manager) {
			if(preg_match('/\.php/', $object)) {
				$managersArr[$key] = (object)array("key" => $key, "value" => '\Snap\manager\\'.preg_replace('/\.php/', '', $manager));
			}
		}
		$managerclass = $managersArr;

		// to create a list of eventTemplate options
		$eventmessages = $this->app->eventmessagefactory()->searchTable()->select()->get();
		foreach( $eventmessages as $eventmessage) {
			$eventmessageArr[] = (object)array("id" => $eventmessage->id, "code" => $eventmessage->code);
		}

		// to create matcherclass options
		$matcherclassArr[] = (object)array("classname" => '\Snap\object\DefaultEventTriggerMatcher');
        $matcherclassArr[] = (object)array("classname" => '\Snap\object\MyGtpEventTriggerMatcher');

		// to create processorclass options
		$processorclassArr[] = (object)array("processorname" => '\Snap\object\EmailEventProcessor');
		$processorclassArr[] = (object)array("processorname" => '\Snap\object\TelegramEventProcessor');
		$processorclassArr[] = (object)array("processorname" => '\Snap\object\MyGtpPushEventProcessor');

		echo json_encode([
			'success' => true,
			'objectclass' => $objectclass,
			'managerclass' => $managerclass,
			'grouptypemap' => $getEventGroupTypeMap,
			'modulemap' => $getEventModuleMap,
			'actionmap' => $getEventActionMap,
			'eventmessage' => $eventmessageArr,
			'matcherclass' => $matcherclassArr,
			'processorclass' => $processorclassArr
		]);
	}

	private function genericList($data) {
		$dataArray['records'] = array();
		foreach($data as $value) {
			$dataArray['records'][] = [ 'id' => $value['id'], 'name' => $value['name'], 'desc' => $value['desc']];
		}
		$dataArray['recordsReturned'] = count($dataArray['records']);
		$result = json_encode($dataArray, JSON_PARTIAL_OUTPUT_ON_ERROR);
		echo $result;
	}

	public function listEventModuleType($app, $params)
	{
		$notificationmanager = $this->app->notificationmanager();
		$this->genericList($notificationmanager->getEventConfig()->getEventModuleMap($this->app));
	}

	public function listEventActionType($app, $params)
	{
		$notificationmanager = $this->app->notificationmanager();
		$this->genericList($notificationmanager->getEventConfig()->getEventActionMap($this->app));
	}

	public function listEventGroupType($app, $params)
	{
		$notificationmanager = $this->app->notificationmanager();
		$this->genericList($notificationmanager->getEventConfig()->getEventGroupTypeMap($this->app));
	}
}
?>