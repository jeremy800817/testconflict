<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;
use Snap\object\order;
use Snap\InputException;
use Snap\object\account;
use Snap\object\rebateConfig;


/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class miborderHandler extends orderHandler {

	
	function __construct(App $app) {
		parent::__construct($app);

		$orderStore = $app->orderFactory();

		$this->currentStore = $orderStore;

		$this->mapActionToRights('exportExcel', '/root/mbb/order/export;/root/bursa/order/export;');
		$this->mapActionToRights('detailview', '/root/mbb/order/list;/root/bursa/order/list');
		$this->mapActionToRights('cancelOrder', '/root/mbb/order/cancel;/root/bursa/order/cancel');
		
		$this->addChild(new ext6gridhandler($this, $orderStore, 1));
	}

	
	function onPreQueryListing($params, $sqlHandle, $fields){
		$app = App::getInstance();
		$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};

		if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
		}else{
			$sqlHandle->andWhere('partnerid', $mbbpartnerid);
		}
	
  
        return array($params, $sqlHandle, $fields);
    }


	function onPreListing($objects, $params, $records) {

		foreach ($records as $key => $record) {
			$records[$key]['status_text'] = ($record['status'] == "1" ? "Active" : "Inactive");
		}

		return $records;
	}

	
	/*function onPreQueryListing($params, $sqlHandle, $fields){
		$branchId = $this->app->getUserSession()->getUser()->branchid;
		$permission = $this->app->hasPermission('/root/hq/columns/show_all_branches');


		if(!$permission) {
			$sqlHandle->andWhere('branchid', $branchId);
			//if(empty($params['filter'])) $sqlHandle->orderby('status'); //Pending as a default
		} else {
			if(empty($params['filter'])) $sqlHandle->andWhere('status', 2); //Pending as a default
		}

		return array($params, $sqlHandle, $fields);
	}*/
/*
	function onPreListing($objects, $params, $records) {
		$arrOrdDelivery = array();
		$arrOrdReference = array();

		foreach ($objects as $key => $object) {
			$orderDelivery = $object->getOrderDelivery();
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

	function exportExcel($app, $params){
		if ($params["partnercode"] == 'corepartner'){
			$core_exclude_partners = [];
			$not_core_partners = $app->partnerStore()->searchTable()->select()->where('corepartner', 0)->execute();
			foreach ($not_core_partners as $arr){
				array_push($core_exclude_partners, $arr->id);
			}
			$modulename = 'COREPARTNER_ORDER';
			$conditions = ['partnerid', 'NOT IN', $core_exclude_partners];
		}else if ($params["partnercode"] == 'BURSA'){
			$partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
			$modulename = 'BURSA_ORDER';
			$conditions = ['partnerid' => $partnerId];
		}else{
			$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};
			$modulename = 'MIB_ORDER';
			$conditions = ['partnerid' => $mbbpartnerid];
		}

        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);

		$statusRenderer = [
			0 => "Pending",
			1 => "Confirmed",
			2 => "Pending Payment",
			3 => "Pending Cancel",
			4 => "Reversal",
			5 => "Completed",
			6 => "Expired"
		];

        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null, $statusRenderer);
    }
}
