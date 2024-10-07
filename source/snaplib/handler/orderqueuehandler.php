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
class orderqueueHandler extends CompositeHandler {

	function __construct(App $app) {

		//parent::__construct('/root/gtp;/root/mbb/;', 'ftrorder');

		//$this->mapActionToRights('fillform', '/root/gtp/ftrorder/add');
		//$this->mapActionToRights('fillform', 'edit');
		$this->mapActionToRights('detailview', '/root/gtp/ftrorder/list;/root/mbb/ftrorder/list;');
		$this->mapActionToRights('detailviewmobile', '/root/gtp/ftrorder/list;/root/mbb/ftrorder/list;');

		$this->mapActionToRights('list', '/root/gtp/ftrorder/list;/root/mbb/ftrorder/list;');

		
		$this->mapActionToRights('cancelFutureOrder', '/root/gtp/ftrorder/cancel;/root/mbb/ftrorder/cancel');
		$this->app = $app;

		$orderqueueStore = $app->orderqueueFactory();
		$this->addChild(new ext6gridhandler($this, $orderqueueStore, 1));
	}

	function onPreQueryListing($params, $sqlHandle, $fields){

		$app = App::getInstance();

		$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};

				
		$bmmbpartnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
		
		$gopartnerid = $this->app->getConfig()->{'gtp.go.partner.id'};

		$onepartnerid = $this->app->getConfig()->{'gtp.one.partner.id'};

		$onecallpartnerid = $this->app->getConfig()->{'gtp.onecall.partner.id'};
		
		$mcashpartnerid = $this->app->getConfig()->{'gtp.mcash.partner.id'};

		$nubexpartnerid = $this->app->getConfig()->{'gtp.nubex.partner.id'};

		//added on 13/12/2021
		$ktppartnerid = $this->app->getConfig()->{'gtp.ktp.partner.id'};

		if($params['type']){
			// $sqlHandle->andWhere('partnerid', '!=' , $mbbpartnerid)
			// 		->andWhere('ordertype', $params['type']);
			$sqlHandle->andWhere('partnerid', '!=' , $mbbpartnerid)
						->andWhere('partnerid', '!=' , $bmmbpartnerid)
						->andWhere('partnerid', '!=' , $gopartnerid)
						->andWhere('partnerid', '!=' , $onepartnerid)
						->andWhere('partnerid', '!=' , $onecallpartnerid)
						->andWhere('partnerid', '!=' , $mcashpartnerid)
						->andWhere('partnerid', '!=' , $nubexpartnerid)
						//added on 13/12/2021
						->andWhere('partnerid', '!=' , $ktppartnerid)
					->andWhere('ordertype', $params['type']);
		}else {
			$sqlHandle->andWhere('partnerid', '!=' , $mbbpartnerid)
					->andWhere('partnerid', '!=' , $bmmbpartnerid)
					->andWhere('partnerid', '!=' , $gopartnerid)
					->andWhere('partnerid', '!=' , $onepartnerid)
					->andWhere('partnerid', '!=' , $onecallpartnerid)
					->andWhere('partnerid', '!=' , $mcashpartnerid)
					->andWhere('partnerid', '!=' , $nubexpartnerid)
					//added on 13/12/2021
					->andWhere('partnerid', '!=' , $ktppartnerid);
		}
		
  
        return array($params, $sqlHandle, $fields);
    }

	function detailview($app, $params) {
		$object = $app->orderqueueFactory()->getById($params['id']);

		$partner = $app->partnerFactory()->getById($object->partnerid);
		
		$buyername = $app->partnerFactory()->getById($object->buyerid)->name;
		$salespersonname = $app->userFactory()->getById($object->salespersonid)->name;
		$productname = $app->productFactory()->getById($object->productid)->name;

		$reconciledbyname = $app->userFactory()->getById($object->reconciledby)->name;
		$cancelbyname = $app->userFactory()->getById($object->cancelby)->name;
		//$confirmbyname = $app->userFactory()->getById($object->confirmby)->name;

		//
	
		if($object->reconciled > 0) $reconciled = "Yes";
		else $reconciled = 'No';

		if($object->byweight > 0) $byWeight = "Weight";
		else $byWeight = 'Amount';

		

		//$expireedOn = $object->expireon ? $object->expireon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		//$cancelledOn = $object->cancelon ? $object->cancelon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		//$matchOn = $object->matchon ? $object->matchon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		//$reconciledOn = $object->reconciledon ? $object->reconciledon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

		if($object->expireon == '0000-00-00 00:00:00' || !$object->expireon){
			$expireedOn = '0000-00-00 00:00:00';
		}else {
			$expireedOn = $object->expireon->format('Y-m-d h:i:s');
		}

		if($object->matchon == '0000-00-00 00:00:00' || !$object->matchon){
			$matchOn = '0000-00-00 00:00:00';
		}else {
			$matchOn = $object->matchon->format('Y-m-d h:i:s');
		}

		if($object->cancelledOn == '0000-00-00 00:00:00' || !$object->cancelledOn){
			$cancelledOn = '0000-00-00 00:00:00';
		}else {
			$cancelledOn = $object->cancelledOn->format('Y-m-d h:i:s');
		}

		if($object->reconciledOn == '0000-00-00 00:00:00' || !$object->conreconciledOnfirmon){
			$reconciledOn = '0000-00-00 00:00:00';
		}else {
			$reconciledOn = $object->reconciledOn->format('Y-m-d h:i:s');
		}

		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		
		// Status
		if ($object->status == 0){
			$statusname = 'Pending';
		}else if ($object->status == 1){
			$statusname = 'Active';
		}else if ($object->status == 2){
			$statusname = 'Fulfilled';
		}else if ($object->status == 3){
			$statusname = 'Matched';
		}else if ($object->status == 4){
			$statusname = 'Pending Cancel';
		}else if ($object->status == 5){
			$statusname = 'Cancelled';
		}else {
			$statusname = 'Unidentified';
		}

		$weight = $partner->calculator(false)->round($object->xau);
		$totalEstValue = $partner->calculator()->round($object->amount);
		


		$detailRecord['default'] = [ //"ID" => $object->id,
									'Order ID' => $object->orderid,
									"Partner" => $partner->name,
									'Buyer' => $buyername,
									"Partner Ref ID" => $object->partnerrefid,
									'Order Queue No' => $object->orderqueueno,
									"Salesperson" => $salespersonname,
									'API Version' => $object->apiversion,
									"Order Type" => $object->ordertype,
									'Queue Type' => $object->queuetype,
									"Expire On" => $expireedOn,
									'Product' => $productname,
									"Price Target" => $object->pricetarget,
									'Booked By' => $byWeight,
									"Xau" => $weight,
									'Amount' => $totalEstValue,
									"Remarks" => $object->remarks,
									'Cancelled On' => $cancelledOn,
									'Cancel By' => $cancelbyname,
									'Match Price ID' => $object->matchpriceid,
									"Matched On" => $matchOn,
									'Notify URL' => $object->notifyurl,
									'Notify Match URL' => $object->notifymatchurl,
									'Success Notify URL' => $object->successnotifyurl,
									"Reconciled" => $reconciled,
									'Reconciled On' => $reconciledOn,
									'Reconciled By' => $reconciledbyname,

									'Status' => $statusname,
									'Created on' => $object->createdon->format('Y-m-d h:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d h:i:s'),
									'Modified by' => $modifieduser,
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

	function detailviewmobile($app, $params) {
		$user = $app->getUserSession()->getUser();
		$object = $app->orderqueueFactory()->searchTable()->select()->where('id', $params['id'])->andWhere('partnerid', $user->partnerid)->one();
		if (!$object){
			echo json_encode(['success' => false, 'errorMessage' => 'Invalid order selection. Please contact administrative']);exit;
		}

		$partner = $app->partnerFactory()->getById($object->partnerid);
		
		$buyername = $app->partnerFactory()->getById($object->buyerid)->name;
		$salespersonname = $app->userFactory()->getById($object->salespersonid)->name;
		$productname = $app->productFactory()->getById($object->productid)->name;

		$reconciledbyname = $app->userFactory()->getById($object->reconciledby)->name;
		$cancelbyname = $app->userFactory()->getById($object->cancelby)->name;
		//$confirmbyname = $app->userFactory()->getById($object->confirmby)->name;

		//
	
		if($object->reconciled > 0) $reconciled = "Yes";
		else $reconciled = 'No';

		if($object->byweight > 0) $byWeight = "Weight";
		else $byWeight = 'Amount';

		

		//$expireedOn = $object->expireon ? $object->expireon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		//$cancelledOn = $object->cancelon ? $object->cancelon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		//$matchOn = $object->matchon ? $object->matchon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		//$reconciledOn = $object->reconciledon ? $object->reconciledon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

		if($object->expireon == '0000-00-00 00:00:00' || !$object->expireon){
			$expireedOn = '0000-00-00 00:00:00';
		}else {
			$expireedOn = $object->expireon->format('Y-m-d h:i:s');
		}

		if($object->matchon == '0000-00-00 00:00:00' || !$object->matchon){
			$matchOn = '0000-00-00 00:00:00';
		}else {
			$matchOn = $object->matchon->format('Y-m-d h:i:s');
		}

		if($object->cancelledOn == '0000-00-00 00:00:00' || !$object->cancelledOn){
			$cancelledOn = '0000-00-00 00:00:00';
		}else {
			$cancelledOn = $object->cancelledOn->format('Y-m-d h:i:s');
		}

		if($object->reconciledOn == '0000-00-00 00:00:00' || !$object->conreconciledOnfirmon){
			$reconciledOn = '0000-00-00 00:00:00';
		}else {
			$reconciledOn = $object->reconciledOn->format('Y-m-d h:i:s');
		}

		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		
		// Status
		if ($object->status == 0){
			$statusname = 'Pending';
		}else if ($object->status == 1){
			$statusname = 'Active';
		}else if ($object->status == 2){
			$statusname = 'Fulfilled';
		}else if ($object->status == 3){
			$statusname = 'Matched';
		}else if ($object->status == 4){
			$statusname = 'Pending Cancel';
		}else if ($object->status == 5){
			$statusname = 'Cancelled';
		}else {
			$statusname = 'Unidentified';
		}

		// Set 
    	if ($object->type == 'CompanySell'){
			$display_type = 'Company Sell';
			$totalestname = 'Total Customer Buy';
		}else if ($object->type == 'CompanyBuy'){
			$display_type = 'Company Buy';
			$totalestname = 'Total Customer Sell';
		}else {
			$display_type = '-';
			$totalestname = 'Total Value';
		}

		$weight = $partner->calculator(false)->round($object->xau);
		$totalEstValue = $partner->calculator()->round($object->amount);
		
		$detailRecord['default'] = [ 
			'Partner' => $partner->name,
			'Buyer' => $buyername,
			'Order Queue No' => $object->orderqueueno,
			'Queue Type' => $display_type,
			"Price Target" => $object->pricetarget,
			'Xau' => $weight,
			'Amount' => $totalEstValue,
			"Expire On" => $expireedOn,
			"Matched On" => $matchOn,
			'Booked By' => $byWeight,
			'Product' => $productname,
			'Cancelled On' => $cancelledOn,
			'Status' => $statusname,
			'Created on' => $object->createdon->format('Y-m-d H:i:s'),
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

	
	function cancelFutureOrder($app, $params){		

		$partner = $app->partnerStore()->getById($params['partnerid']);
		//$beforefromdate = date("Y-m-d", $params['summaryfromdate']/1000).' 00:00:00';
		
		//$buysellcode= $params['summarytype']==1?$partner->sapcompanybuycode1:$partner->sapcompanysellcode1;		
		$apiVersion = $params['apiversion'];		
		$refid = $params['refid'];	
		$notifyUrl = $params['notifyurl'];	
		$reference = $params['reference'];	
		$timeStamp = $params['timestamp'];	
		//$futureordermanager=$this->app->futureorderManager();	
		try{
			//print_r($params);
			$cancel= $app->futureorderManager()->cancelFutureOrder($partner, null, null,  $params['id']);		
			
			if ($cancel){
				//$orderQueue = $app->futureorderManager()->cancelFutureOrder($decodedData['partner'], $version, $requestParams['ref_id'], $notifyUrl, $requestParams['reference'], $requestParams['timestamp']);
				
				$confirmCancelFutureOrder = $app->futureorderManager()->confirmCanceledFutureOrder($cancel);
				if (!$confirmCancelFutureOrder){
					echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);   
				}
			}else{
				echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);   
			}
		
			echo json_encode(['success' => true, 'field' => '', ]);      
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
		
	}


}
