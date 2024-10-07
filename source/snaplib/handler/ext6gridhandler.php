<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use Snap\InputException;
Use Snap\sqlrecorder as sqlRecorder;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Wayne <wayne@silverstream.my>
 * @version 1.0
 */

class ext6gridhandler extends BaseHandler {
	private $parent = null;
	private $currentStore = null;
	private $currentView = null;

	function __construct($parent, $store, $viewIndex = 0) {
		$this->parent = $parent;
		$this->currentStore = $store;
		$this->currentView = $viewIndex;
	}

	function getRights($action) {
		//Returns the sub-rights of the module
		$rights = 'no-rights';
	 	switch($action) {
	 		case 'list':
				$rights = 'list';
				break;
	 		case 'add':
				$rights = 'add';
				break;
	 		case 'delete':
				$rights = 'delete';
				break;
	 		case 'edit':
				$rights = 'edit';
				break;
	 	}
	 	return $rights;
	}

	/**
	 * This method will determine is this particular handler is able to handle the action given.
	 * 
	 * @param  String $action The action name to be handled
	 * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
	 */
	 function canHandleAction($app, $action) {
	 	//Updated by Devon on 2017/5/18 to proper implement of handling action	 	
	 	switch($action) {
	 		case 'list':
	 		case 'add':
	 		case 'delete':
	 		case 'edit':
	 			return true;
	 	}
	 	return false;
	 	//End Update by Devon on 2017/5/18	 	
	 }

	function doAction( $app, $action, $params) {
		if ($this->canHandleAction($app, $action)) {
			if ($action == 'list') $this->doListing($params);
			elseif ($action == 'add' || $action == 'edit') $this->doAddEdit($params);
			elseif ($action == 'delete') $this->doDelete($params);
		}
	}

	function getNameValues($filters, $recorder) {
		$filters = json_decode($filters);
		if (count($filters) > 0) {
			foreach ($filters as $filter) {
				if (strlen($filter->value) == 0 && !is_array($filter->value)) continue;
				$filterValue = $filter->value;

				$operator = '=';
				if ($filter->operator == '>') $operator = '>';
				elseif ($filter->operator == '<') $operator = '<';
				elseif ($filter->operator == '>=') $operator = '>=';
				elseif ($filter->operator == '<=') $operator = '<=';
				elseif ($filter->operator == 'eq') $operator = '=';
				elseif ($filter->operator == '!=') $operator = '<>';
				elseif ($filter->operator == 'like') {
					$operator = 'like';
					$filterValue = '%'.$filterValue.'%';
				}
				elseif ($filter->operator == 'BETWEEN') {
					// ext cant support same property value
					// hydrahon no `between` operator
					// modify to >= and <=
					// can ONLY have 2 value
					// first value must be on first value
					if (is_array($filter->value) && count($filter->value) == 2){
						$recorder->andWhere( $filter->property, ">=", $filter->value[0]);
						$recorder->andWhere( $filter->property, "<=", $filter->value[1]);
						continue;
					}
					continue;
				}
				$recorder->andWhere( $filter->property, $operator, $filterValue);
			}
		}
	}

	function doListing($params) {
		$dataArray = array();
		$dataArray['records'] =	array();
		$dataArray['recordsReturned'] =	0;
		$records = array();
		$nameValues	= array();

		if ($this->currentStore != null) {
			// limit of records per page
			if ($params['limit'] == 0) $params['limit']	= 9999;
			$dataArray['pageSize'] = intval($params['limit']);

			// start offset for the records
			$dataArray['startIndex'] = $params['start'] = intval($params['start']);

			// sorting info for the records
			$sorts = json_decode($params['sort']);
			$params['sort'] = $sorts[0]->property;
			$params['dir'] = strtolower($sorts[0]->direction);
			$dataArray['sort'] = $params['sort'];
			$dataArray['direction'] = $params['dir'];

			// prepare the sorting
			$orderby = array();
			if (count($sorts) > 0) {
				foreach ($sorts as $sort) {
					$orderby[$sort->property] = strtolower($sort->direction);
				}
			}
			if (method_exists($this->parent, 'onPrepareSorting')) {
				$orderby = $this->parent->onPrepareSorting($orderby);
			}

			// get the select fields if have
			$fields = array();
			if (strlen($params['colfields']) > 0) {
				$regs = explode(',', $params['colfields']);
				foreach ($regs as $reg) {
					$reg = trim($reg);
					$fields[] = $reg;
				}
			}

			//Updated by Devon on 2017/8/26 to allow for our standard SQL querying method for filtering etc.			
			$mainRecorder = new sqlRecorder();
			$filterRecorder = new sqlRecorder();
			$hdlConditionRecorder = new sqlRecorder();
			// prepare the namevalues from column filter
			$this->getNameValues($params['filter'], $filterRecorder);
			if($filterRecorder->hasRecording()) $mainRecorder->andWhere( $filterRecorder);
			//Added by Devon on 2020/06/09 to force filtering of partner id for user
			$userPartnerId = \Snap\App::getInstance()->getUsersession()->getUser()->partnerid;
			if(0 < $userPartnerId && in_array('partnerid', $this->currentStore->create()->getFields())) {
				$mainRecorder->andWhere('partnerid', $userPartnerId);
			}
			// if ($this->app->hasPermission('/root/trading/salesman/show_all_partner')){
			// 	$salesmanPartners = $this->app->partnerStore()->select(["id"])->where('salespersonid', \Snap\App::getInstance()->getUsersession()->getUser()->id);
			// 	$mainRecorder->andWhere('partnerid', 'in', array($salesmanPartners));
			// }
			// ace_Admin _partnerid 0
			// ace_salesman _partnerid 0
			// cus_login _partnerid 1
			// ace_cus_salesman_acc _salesmanid 10
			//End add 2020/06/09
			// allow to update the nameValues conditions and fields
			if (method_exists($this->parent, 'onPreQueryListing')) {
				list($params, $nameValues, $fields) = $this->parent->onPreQueryListing($params, $hdlConditionRecorder, $fields);
				if($hdlConditionRecorder->hasRecording()) $mainRecorder->andWhere( $hdlConditionRecorder);
			}
			//End Update by Devon on 2017/8/26

			// get the total records for the pagination
			//Updated by Devon on 2017/8/24 to fix issue of error if user filter with view data
			$currentStoreHandler = $this->currentStore->searchView(false, $this->currentView);
			// $currentStoreHandler = $this->currentStore->searchTable(false);
			//End Update by Devon on 2017/8/24			
			$currentStoreHandler = $currentStoreHandler->select([$currentStoreHandler->raw('COUNT(*) AS cnt')]);
			$currentStoreHandler = $mainRecorder->replayTo($currentStoreHandler);  //'replay' the SQL conditions for the current db handle
			$totalRecords = $currentStoreHandler->execute();
			$dataArray['totalRecords'] = $totalRecords[0]['cnt'];

			// get the records for the listing purpose
			$currentStoreHandler = $this->currentStore->searchView(true, $this->currentView);
			$currentStoreHandler = $currentStoreHandler->select($fields);
			$currentStoreHandler = $mainRecorder->replayTo($currentStoreHandler); //'replay' the SQL conditions for the current db handle
			if (count($orderby) > 0) $currentStoreHandler->orderBy($orderby);
			$currentStoreHandler->limit($dataArray['startIndex'], $dataArray['pageSize']);
			$objects = $currentStoreHandler->execute();
			if (is_array($objects))	{
				$bFields = (count($fields) > 0) ? true: false;
				$objFields = $objects[0]->getFields();
				if ($this->currentView > 0) {
					$objViewFields = $objects[0]->getViewFields();
					if (count($objViewFields) > 0) $objFields = array_merge($objFields, $objViewFields);
				}

				foreach ($objects as $oneObject) {
					$oneRecord = array();
					$excludeFields = array('content');
					foreach ($objFields as $oneObjField) {
						if ($bFields && !in_array($oneObjField, $fields)) continue;
						$data = $oneObject->{$oneObjField};
						if (!in_array($oneObjField, $excludeFields) && preg_match('/<.*>/i', $data)) {
							$data = htmlspecialchars($data, ENT_QUOTES);
						}
						if ($oneObject->{$oneObjField} instanceof \DateTime) $data = $oneObject->{$oneObjField}->format("c");
						$oneRecord[$oneObjField] = $data;
					}
					$records[] = $oneRecord;
				}

				// allow to update the records
				if (method_exists($this->parent, 'onPreListing')) {
					$records = $this->parent->onPreListing($objects, $params, $records);
				}
			}

			$dataArray['query'] = json_encode($params);
			$dataArray['records'] =	$records;
			$dataArray['recordsReturned'] =	count($records);
		}

		// json the response data and send to listing
		//Modified by Devon on 2018/4/24 to output error and also just output partial data when error encountered.
		// echo json_encode($dataArray);
		$result = json_encode($dataArray, JSON_PARTIAL_OUTPUT_ON_ERROR);
		switch (json_last_error()) {
			case JSON_ERROR_DEPTH:
				$this->log(__CLASS__."::doListing() json_encode error: the maximum stack depth has been exceeded.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$this->log(__CLASS__."::doListing() json_encode error: Occurs with underflow or with the modes mismatch..", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_CTRL_CHAR:
				$this->log(__CLASS__."::doListing() Control character error, possibly incorrectly encoded.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_SYNTAX:
				$this->log(__CLASS__."::doListing() Syntax error.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_UTF8:
				$this->log(__CLASS__."::doListing() Malformed UTF-8 characters, possibly incorrectly encoded.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_RECURSION:
				$this->log(__CLASS__."::doListing() The object or array passed to json_encode() include recursive references and cannot be encoded. If the JSON_PARTIAL_OUTPUT_ON_ERROR option was given, NULL will be encoded in the place of the recursive reference.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_INF_OR_NAN:
				$this->log(__CLASS__."::doListing() The value passed to json_encode() includes either NAN or INF. If the JSON_PARTIAL_OUTPUT_ON_ERROR option was given, 0 will be encoded in the place of these special numbers.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_UNSUPPORTED_TYPE:
				$this->log(__CLASS__."::doListing() A value of an unsupported type was given to json_encode(), such as a resource. If the JSON_PARTIAL_OUTPUT_ON_ERROR option was given, NULL will be encoded in the place of the unsupported value.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_INVALID_PROPERTY_NAME:
				$this->log(__CLASS__."::doListing() A key starting with \u0000 character was in the string passed to json_decode() when decoding a JSON object into a PHP object.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_UTF16:
				$this->log(__CLASS__."::doListing() Single unpaired UTF-16 surrogate in unicode escape contained in the JSON string passed to json_encode().", SNAP_LOG_CRITICAL);
				break;

		}
		echo $result;
		//End Modified by Devon on 2018/4/24		
	}

	function doAddEdit($params) {
		$bSuccess =	false;
		$id = 0;
		$errMsg = '';
		$fieldErr = '';

		try {
			if ($this->currentStore != null && count($params) > 0) {
				// create new record if there is no id given else get the record based on the id given
				if (intval($params['id']) > 0) $record = $this->currentStore->getById($params['id']);
				else $record = $this->currentStore->create();
				$objFields = $record->getFields();

				// assign the value to the respective fields
				foreach ($params as $key => $value) {
					if ($key == 'id' || !in_array($key, $objFields)) continue;
					$record->{$key} = $value;
				}

				// allow to do something before add / edit
				if (method_exists($this->parent, 'onPreAddEditCallback')) {
					$record = $this->parent->onPreAddEditCallback($record, $params);
					if ($record instanceof \Snap\object\SnapObject) {
					} else {
						throw new \Exception(__CLASS__.'::onPreAdditCallback() expects return of object instance. Error found in handler '.get_class($this->parent), SNAP_LOG_CRITICAL);
					}
				}

				// save the record into the store
				//Added by Devon on 2017/5/15 to check for validity first.
				try {
					$record->isValid();
				} catch(\Snap\InputException $e) {
					$this->log("Error in validating " . get_class($record) . " with id {$record->id}.  Error is " . $e->getMessage(), SNAP_LOG_INFO);
					echo json_encode(['success' => false, 'errmsg' => $e->getMessage(), 'field' => $e->getErrorField()]);
					return;
				}
				//End Add by Devon on 2017/5/15
				
				$savedRec = $this->currentStore->save($record);
				if ($savedRec != null && $savedRec->id > 0) {
					$bSuccess = true;
					$id = $savedRec->id;

					// allow to do something after record is saved
					if (method_exists($this->parent, 'onPostAddEditCallback')) {
						$this->parent->onPostAddEditCallback($savedRec, $params);
					}
				}
			}
		} catch (\Exception $e) {
			$this->log("Exception caught in " . get_class($record) . " with id {$record->id}.  Error is " . $e->getMessage(), SNAP_LOG_INFO);
			$bSuccess = false;
			// $fieldErr = $e->getErrorField();
			$errMsg = $e->getMessage();
		}
		
		// json the response data and send to form
		$response = array(
			'success' => $bSuccess,
			'id' => $id,
			'field' => $fieldErr,
			'errmsg' => $errMsg
		);
		echo json_encode($response);
	}

	function doDelete($params) {
		$bSuccess = false;
		$errMsg = '';

		try {
			if ($this->currentStore != null && count($params) > 0) {
				$ids = explode(',',	$params['ids']);
				foreach ($ids as $id) {
					$record = $this->currentStore->getById($id);

					// allow to do something before delete
					if (method_exists($this->parent, 'onPreDeleteCallback')) {
						$this->parent->onPreDeleteCallback($record, $params);
					}

					// delete the record
					if (array_key_exists('statusdel', $params) && $params['statusdel'] == true) {
						$record->status = $this->currentStore->quoteData(\Snap\object\SnapObject::STATUS_INACTIVE);
						$deletedRec = $this->currentStore->save($record);
					} else $deletedRec = $this->currentStore->delete($record);
					if ($deletedRec) {
						$bSuccess = true;

						// allow to do something after delete
						if (method_exists($this->parent, 'onPostDeleteCallback')) {
							$this->parent->onPostDeleteCallback($record, $params);
						}
					}
				}
			}
		} catch (\Exception $e) {
			$errMsg = $e->getMessage();
		}

		// json the response data and send to form
		$response = array(
			'success' => $bSuccess,
			'errmsg' => $errMsg
		);
		echo json_encode($response);
	}

	private function doJsonEncoding( $result) {
		// json the response data and send to listing
		//Modified by Devon on 2018/4/24 to output error and also just output partial data when error encountered.
		$result = json_encode($result, JSON_PARTIAL_OUTPUT_ON_ERROR);
		switch (json_last_error()) {
			case JSON_ERROR_DEPTH:
				$this->log(__CLASS__."::doListing() json_encode error: the maximum stack depth has been exceeded.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$this->log(__CLASS__."::doListing() json_encode error: Occurs with underflow or with the modes mismatch..", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_CTRL_CHAR:
				$this->log(__CLASS__."::doListing() Control character error, possibly incorrectly encoded.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_SYNTAX:
				$this->log(__CLASS__."::doListing() Syntax error.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_UTF8:
				$this->log(__CLASS__."::doListing() Malformed UTF-8 characters, possibly incorrectly encoded.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_RECURSION:
				$this->log(__CLASS__."::doListing() The object or array passed to json_encode() include recursive references and cannot be encoded. If the JSON_PARTIAL_OUTPUT_ON_ERROR option was given, NULL will be encoded in the place of the recursive reference.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_INF_OR_NAN:
				$this->log(__CLASS__."::doListing() The value passed to json_encode() includes either NAN or INF. If the JSON_PARTIAL_OUTPUT_ON_ERROR option was given, 0 will be encoded in the place of these special numbers.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_UNSUPPORTED_TYPE:
				$this->log(__CLASS__."::doListing() A value of an unsupported type was given to json_encode(), such as a resource. If the JSON_PARTIAL_OUTPUT_ON_ERROR option was given, NULL will be encoded in the place of the unsupported value.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_INVALID_PROPERTY_NAME:
				$this->log(__CLASS__."::doListing() A key starting with \u0000 character was in the string passed to json_decode() when decoding a JSON object into a PHP object.", SNAP_LOG_CRITICAL);
				break;
			case JSON_ERROR_UTF16:
				$this->log(__CLASS__."::doListing() Single unpaired UTF-16 surrogate in unicode escape contained in the JSON string passed to json_encode().", SNAP_LOG_CRITICAL);
				break;
		}
		return $result;
		//End Modified by Devon on 2018/4/24				
	}
}