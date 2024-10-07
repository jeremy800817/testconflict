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
class miborderqueueHandler extends orderqueueHandler {

	
	function __construct(App $app) {

		// $this->mapActionToRights('fillform', '/root/mbb/ftrorder/add');
		$this->mapActionToRights('list', '/root/mbb/ftrorder/list');
		$this->mapActionToRights('detailview', '/root/mbb/ftrorder/list');
		$this->mapActionToRights('exportExcel', '/root/mbb/ftrorder/export');

		$this->app = $app;

		$orderqueueStore = $app->orderqueueFactory();
		$this->currentStore = $orderqueueStore;
		$this->addChild(new ext6gridhandler($this, $orderqueueStore, 1));
	}

	function onPreQueryListing($params, $sqlHandle, $fields){
  
		$app = App::getInstance();

		$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};


		if($params['type']){
			$sqlHandle->andWhere('partnerid', $mbbpartnerid)
					->andWhere('ordertype', $params['type']);
		}else {
			$sqlHandle->andWhere('partnerid', $mbbpartnerid);
		}
		

        return array($params, $sqlHandle, $fields);
    }



	function detailview($app, $params) {
		$object = $app->orderqueueFactory()->getById($params['id']);

		$buyername = $app->partnerFactory()->getById($object->buyerid)->name;
		$partnername = $app->partnerFactory()->getById($object->partnerid)->name;
		$partnercode = $app->partnerFactory()->getById($object->partnerid)->code;
		$salespersonname = $app->userFactory()->getById($object->salespersonid)->name;
		$productname = $app->productFactory()->getById($object->productid)->name;

		$reconciledbyname = $app->userFactory()->getById($object->reconciledby)->name;
		$cancelbyname = $app->userFactory()->getById($object->cancelby)->name;
		//$confirmbyname = $app->userFactory()->getById($object->confirmby)->name;

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
									"Expire On" => $object->expireon,
									'Product' => $productname,
									"Price Target" => $object->pricetarget,
									'By Weight' => $object->byweight,
									"Xau" => $object->xau,
									'Amount' => $object->amount,
									"Remarks" => $object->remarks,
									'Cancel On' => $object->cancelon,
									'Cancel By' => $cancelbyname,
									'Match Price ID' => $object->matchpriceid,
									"Match On" => $object->matchon,
									'Notify URL' => $object->notifyurl,
									'Notify Match URL' => $object->notifymatchurl,
									'Success Notify URL' => $object->successnotifyurl,
									"Reconciled" => $object->reconciled,
									'Reconciled On' => $object->reconciledon,
									'Reconciled By' => $reconciledbyname,

									'Status' => $params['status_text'],
									'Created on' => $object->createdon->format('Y-m-d h:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d h:i:s'),
									'Modified by' => $modifieduser,
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

	function onPreListing($objects, $params, $records) {

		foreach ($records as $key => $record) {

			if($record['status'] == "0"){

				$records[$key]['status_text'] = "Pending";

			} else if($record['status'] == "1"){

				$records[$key]['status_text'] = "Active";

			} else if($record['status'] == "2"){

				$records[$key]['status_text'] = "Fulfilled";

			} else if($record['status'] == "3"){

				$records[$key]['status_text'] = "Matched";

			} else if($record['status'] == "4"){

				$records[$key]['status_text'] ="Pending Cancel";

			} else if($record['status'] == "5"){

				$records[$key]['status_text'] = "Cancelled";

			} else if($record['status'] == "6"){

				$records[$key]['status_text'] = "Expired";

			}
			//$records[$key]['status'] = ($record['status'] == "1" ? "Active" : "Inactive");
		}

		return $records;
	}

    function exportExcel($app, $params){

        $mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};

        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);

        $modulename = 'MIB_ORDERQUEUE';

        $conditions = ['partnerid' => $mbbpartnerid];

        $statusRenderer = [
            0 => "Pending",
            1 => "Active",
            2 => "Full Filled",
            3 => "Matched",
            4 => "Pending Cancel",
            5 => "Cancelled",
            6 => "Expired"
        ];

        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null, $statusRenderer);
    }
}
