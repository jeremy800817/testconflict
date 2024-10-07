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
class ordercancelHandler extends CompositeHandler {

	function __construct(App $app) {

		parent::__construct('/root/trading', 'futureordercancel');

		$this->mapActionToRights('fillform', 'add');
		//$this->mapActionToRights('fillform', 'edit');
		//$this->mapActionToRights('detailview', 'list');

		$this->app = $app;

		$ordercancelStore = $app->ordercancelFactory();
		$this->addChild(new ext6gridhandler($this, $ordercancelStore, 1));
	}



	function detailview($app, $params) {
		$object = $app->orderqueueFactory()->getById($params['id']);

		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		$detailRecord['default'] = [ //"ID" => $object->id,
									'Order ID' => $object->orderid,
									"Partner" => $partnername,
									'Buyer' => $buyername,
									"Partner Ref ID" => $object->partnerrefid,
									'Order Queue No' => $object->orderqueueno,
									"Salesperson" => $salespersonname,
									'API Version' => $object->apiversion,
									"Order Type" => $object->ordertype,
									'Queue Type' => $object->queuetype,
									"Expire On" => $object->expireon->format('Y-m-d h:i:s'),
									'Product' => $productname,
									"Price Target" => $object->pricetarget,
									'By Weight' => $object->byweight,
									"Xau" => $object->xau,
									'Amount' => $object->amount,
									"Remarks" => $object->remarks,
									'Cancel On' => $object->cancelon->format('Y-m-d h:i:s'),
									'Cancel By' => $cancelbyname,
									'Match Price ID' => $object->matchpriceid,
									"Match On" => $object->matchon->format('Y-m-d h:i:s'),
									'Notify URL' => $object->notifyurl,
									'Notify Match URL' => $object->notifymatchurl,
									'Success Notify URL' => $object->successnotifyurl,
									"Reconciled" => $object->reconciled,
									'Reconciled On' => $object->reconciledon->format('Y-m-d h:i:s'),
									'Reconciled By' => $reconciledbyname,

									'Status' => $params['status_text'],
									'Created on' => $object->createdon->format('Y-m-d h:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d h:i:s'),
									'Modified by' => $modifieduser,
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

	function onPreQueryListing($params, $sqlHandle, $fields){
		//$branchId = $this->app->getUserSession()->getUser()->branchid;
		//$permission = $this->app->hasPermission('/root/hq/columns/show_all_branches');


		$sqlHandle->andWhere('status', 4);

		return array($params, $sqlHandle, $fields);
	}



/*
	function onPreListing($objects, $params, $records) {

		foreach ($records as $key => $record) {
			$records[$key]['status_text'] = ($record['status'] == "1" ? "Active" : "Inactive");
		}

		return $records;
	}*/
/*
    function onPreQueryListing($params, $sqlHandle, $fields){
		$branchId = $this->app->getUserSession()->getUser()->branchid;
		$permission = $this->app->hasPermission('/root/hq/columns/show_all_branches');


		if(empty($params['filter'])) $sqlHandle->andWhere('status', 4); //Pending as a default

		return array($params, $sqlHandle, $fields);
	}

    function onPreListing($objects, $params, $records) {
        $arrOrdDelivery = array();
		$arrOrdReference = array();

		foreach ($objects as $key => $object) {
			$orderDelivery = $object->getOrderCancel();
			foreach ($orderDelivery as $key => $aOrderDelivery)
			{
				if(0 != $aOrderDelivery->actualquantity) $status = 'Received';
				else $status = 'Waiting';
				if($aOrderDelivery->actualdeliveryon != null) $dateActDelOn = $aOrderDelivery->actualdeliveryon->format("Y-m-d");
				else $dateActDelOn = '0000-00-00 00:00:00';
				$arrOrdDelivery[$aOrderDelivery->orderid][] = array(
					'id' => $aOrderDelivery->id,
					'orderid' => $aOrderDelivery->orderid,
					'expecteddeliveryon' => $aOrderDelivery->expecteddeliveryon->format("Y-m-d"),
					'expectedquantity' => $aOrderDelivery->expectedquantity,
					'invoiceno' => $aOrderDelivery->invoiceno,
					'deliveryno' => $aOrderDelivery->deliveryno,
					'actualquantity' => $aOrderDelivery->actualquantity,
					'actualdefect' => $aOrderDelivery->actualdefect,
					'actualdeliveryon' => $dateActDelOn,
					'note' => $aOrderDelivery->note,
					'status' => $status
				);

			}

			$orderRefNo = $object->getOrderReference();
			foreach($orderRefNo as $key=>$aOrderRefNo){
				$arrOrdReference[$key] = $aOrderRefNo;
			}
		}

		foreach ($records as $key => $record) {
			if(array_key_exists($record['id'], $arrOrdDelivery)) {
				$records[$key]['orderdeliverydata'] = json_encode($arrOrdDelivery[$record['id']]);
			}
			if(array_key_exists($record['referenceno'], $arrOrdReference)) {
				$records[$key]['summaryorder'] = json_encode($arrOrdReference[$record['referenceno']]);
			}
		}

		return $records;
    } */


}
