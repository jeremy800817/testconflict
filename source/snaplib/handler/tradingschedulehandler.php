<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Rahmah(rahmah@silverstream.my)
 * @version 1.0
 */
class tradingscheduleHandler extends CompositeHandler {

	function __construct(App $app) {
		// parent::__construct('/', 'tradingschedule');
		// $this->mapActionToRights('list', 'list');

		parent::__construct('/root/system', 'tradingschedule');

		$this->mapActionToRights('fillform', 'add');
		$this->mapActionToRights('fillform', 'edit');
		$this->mapActionToRights('detailview', 'list');

		$this->app = $app;

		$tradingscheduleStore = $app->tradingscheduleFactory();
		$this->addChild(new ext6gridhandler($this, $tradingscheduleStore, 1));
	}


    /**
     * This method will determine is this particular handler is able to handle the action given.
     *
     * @param  App    $app    The application object (for getting user session etc to test?)
     * @param  String $action The action name to be handled
     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
     */
    public function canHandleAction($app, $action)
    {
        return true;
	}


	/*
        This method is to get the Type to be listing in the form
    */
	function fillform( $app, $params) {
		$type = \Snap\object\TradingSchedule::getType();
		echo json_encode( ['success' => true,  'type' => $type]);
	}

	/*
        This method is to get data for view details
    */
	function detailview($app, $params) {
		$object = $app->tradingschedulefactory()->getById($params['id']);

		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		$detailRecord['default'] = [ "ID" => $object->id,
									//'Category ID' => $object->categoryid,
									'Category Name' => $params['categoryname'],
									'Type' => $object->type,
									'Start At' => $object->startat->format('Y-m-d h:i:s'),
									'End At' => $object->endat->format('Y-m-d h:i:s'),
									'Created on' => $object->createdon->format('Y-m-d h:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d h:i:s'),
									'Modified by' => $modifieduser,
									'Status' => $params['status_text'],
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

	function onPreListing($objects, $params, $records) {

		$app = App::getInstance();

		foreach ($records as $key => $record) {
			$records[$key]['status_text'] = ($record['status'] == "1" ? "Active" : "Inactive");

			$appttasks = $app->tradingschedulefactory()->getById($record['id']);

			// Acquire type for trading schedule
			$type = $appttasks->type;

			$appttaskids = array_map(function($a){return $a->id;}, $appttasks);
			// Get type
			//print_r($appttasks[0]->id);
			$records[$key]['type'] = ($type);

		}

		return $records;
	}

	function onPreAddEditCallback($object, $params){
		$startAt = new \DateTime($params['startat'], $this->app->getUserTimeZone());
		$endAt = new \DateTime($params['endat'], $this->app->getUserTimeZone());
		$object->startat = $startAt;
		$object->endat = $endAt;
		return $object;
	}
}
