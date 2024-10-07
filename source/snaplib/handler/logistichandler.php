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
use Snap\object\Logistic;
use Snap\object\Redemption;
use Snap\object\Replenishment;
use Snap\object\Buyback;
Use \Snap\common;

/**
 * This class is meant to be a base claszs for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class logisticHandler extends CompositeHandler {

	function __construct(App $app) {
		
		$this->app = $app;

		//parent::__construct('/root/gtp;/root/mbb;/root/bmmb', 'logistic');
		$this->mapActionToRights('fillform', '/root/gtp/logistic/add;/root/mbb/logistic/add;/root/bmmb/logistic/add;/root/go/logistic/add;/root/one/logistic/add;/root/onecall/logistic/add;/root/air/logistic/add;/root/mcash/logistic/add;/root/ktp/logistic/add;/root/kopetro/logistic/add;/root/kopttr/logistic/add;/root/pkbaffi/logistic/add;/root/toyyib/logistic/add;/root/nubex/logistic/add;/root/hope/logistic/add;/root/mbsb/logistic/add;/root/red/logistic/add;/root/kodimas/logistic/add;/root/kgoldaffi/logistic/add;/root/koponas/logistic/add;/root/wavpay/logistic/add;/root/noor/logistic/add;/root/gtp/logistic/add;/root/gtp/logistic/add;/root/bsn/logistic/add;/root/alrajhi/logistic/add;/root/posarrahnu/logistic/add;/root/waqaf/logistic/add;/root/igold/logistic/add;/root/kasih/logistic/add;/root/bursa/logistic/add;');

		$this->mapActionToRights('fillform', '/root/gtp/logistic/edit;/root/mbb/logistic/edit;/root/bmmb/logistic/edit;/root/go/logistic/edit;/root/one/logistic/edit;/root/onecall/logistic/edit;/root/air/logistic/edit;/root/mcash/logistic/edit;/root/ktp/logistic/edit;/root/kopetro/logistic/edit;/root/kopttr/logistic/edit;/root/pkbaffi/logistic/edit;/root/toyyib/logistic/edit;/root/nubex/logistic/edit;/root/hope/logistic/edit;/root/mbsb/logistic/edit;/root/red/logistic/edit;/root/kodimas/logistic/edit;/root/kgoldaffi/logistic/edit;/root/wavpay/logistic/edit;/root/koponas/logistic/edit;/root/noor/logistic/edit;/root/bsn/logistic/edit;/root/alrajhi/logistic/edit;/root/posarrahnu/logistic/edit;/root/waqaf/logistic/edit;/root/igold/logistic/edit;/root/kasih/logistic/edit;/root/bursa/logistic/edit;');

		$this->mapActionToRights('detailview', '/root/gtp/logistic/list;/root/mbb/logistic/list;/root/bmmb/logistic/list;/root/go/logistic/list;/root/one/logistic/list;/root/onecall/logistic/list;/root/air/logistic/list;/root/mcash/logistic/list;/root/ktp/logistic/list;/root/kopetro/logistic/list;/root/kopttr/logistic/list;/root/pkbaffi/logistic/list;/root/toyyib/logistic/list;/root/nubex/logistic/list;/root/hope/logistic/list;/root/mbsb/logistic/list;/root/red/logistic/list;/root/kodimas/logistic/list;/root/kgoldaffi/logistic/list;/root/koponas/logistic/list;/root/wavpay/logistic/list;/root/noor/logistic/list;/root/bsn/logistic/list;/root/alrajhi/logistic/list;/root/posarrahnu/logistic/list;/root/waqaf/logistic/list;/root/igold/logistic/list;/root/kasih/logistic/list;/root/bursa/logistic/list;');
        /*
		$this->mapActionToRights('fillform', 'add');
		$this->mapActionToRights('fillform', 'edit');
		$this->mapActionToRights('addToOrder', 'add');
		$this->mapActionToRights('approveOrder', 'approve');
		$this->mapActionToRights('rejectOrder', 'approve');
		$this->mapActionToRights('deliverOrder', 'edit');
		$this->mapActionToRights('completedOrders', 'edit'); */

		$this->mapActionToRights('list', '/root/gtp/logistic/list;/root/mbb/logistic/list;/root/bmmb/logistic/list;/root/go/logistic/list;/root/one/logistic/list;/root/onecall/logistic/list;/root/air/logistic/list;/root/mcash/logistic/list;/root/ktp/logistic/list;/root/kopetro/logistic/list;/root/kopttr/logistic/list;/root/pkbaffi/logistic/list;/root/toyyib/logistic/list;/root/nubex/logistic/list;/root/hope/logistic/list;/root/mbsb/logistic/list;/root/red/logistic/list;/root/kodimas/logistic/list;/root/kgoldaffi/logistic/list;/root/koponas/logistic/list;/root/wavpay/logistic/list;/root/noor/logistic/list;/root/bsn/logistic/list;/root/alrajhi/logistic/list;/root/posarrahnu/logistic/list;/root/waqaf/logistic/list;/root/igold/logistic/list;/root/kasih/logistic/list;/root/bursa/logistic/list;');

		$this->mapActionToRights('updateLogisticStatus', '/root/gtp/logistic/add;/root/mbb/logistic/add;/root/bmmb/logistic/add;/root/go/logistic/add;/root/one/logistic/add;/root/onecall/logistic/add;/root/air/logistic/add;/root/mcash/logistic/add;/root/ktp/logistic/add;/root/kopetro/logistic/add;/root/kopttr/logistic/add;/root/pkbaffi/logistic/add;/root/toyyib/logistic/add;/root/nubex/logistic/add;/root/hope/logistic/add;/root/mbsb/logistic/add;/root/red/logistic/add;/root/kodimas/logistic/add;/root/kgoldaffi/logistic/add;/root/koponas/logistic/add;/root/wavpay/logistic/add;/root/noor/logistic/add;/root/bsn/logistic/add;/root/alrajhi/logistic/add;/root/posarrahnu/logistic/add;/root/waqaf/logistic/add;/root/igold/logistic/add;/root/kasih/logistic/add;/root/bursa/logistic/add;');

		$this->mapActionToRights('updateLogisticInformation', '/root/gtp/logistic/add;/root/mbb/logistic/add;/root/bmmb/logistic/add;/root/go/logistic/add;/root/one/logistic/add;/root/onecall/logistic/add;/root/air/logistic/add;/root/mcash/logistic/add;/root/ktp/logistic/add;/root/kopetro/logistic/add;/root/kopttr/logistic/add;/root/pkbaffi/logistic/add;/root/toyyib/logistic/add;/root/nubex/logistic/add;/root/hope/logistic/add;/root/mbsb/logistic/add;/root/red/logistic/add;/root/kodimas/logistic/add;/root/kgoldaffi/logistic/add;/root/koponas/logistic/add;/root/wavpay/logistic/add;/root/noor/logistic/add;/root/bsn/logistic/add;/root/alrajhi/logistic/add;/root/posarrahnu/logistic/add;/root/waqaf/logistic/add;/root/igold/logistic/add;/root/kasih/logistic/add;/root/bursa/logistic/add;');

		$this->mapActionToRights('updateLogisticAttempts', '/root/gtp/logistic/add;/root/mbb/logistic/add;/root/bmmb/logistic/add;/root/go/logistic/add;/root/one/logistic/add;/root/onecall/logistic/add;/root/air/logistic/add;/root/mcash/logistic/add;/root/ktp/logistic/add;/root/kopetro/logistic/add;/root/kopttr/logistic/add;/root/pkbaffi/logistic/add;/root/toyyib/logistic/add;/root/nubex/logistic/add;/root/hope/logistic/add;/root/mbsb/logistic/add;/root/red/logistic/add;/root/kodimas/logistic/add;/root/kgoldaffi/logistic/add/root/koponas/logistic/add;/root/wavpay/logistic/add;/root/noor/logistic/add;/root/bsn/logistic/add;/root/alrajhi/logistic/add;/root/posarrahnu/logistic/add;/root/waqaf/logistic/add;/root/igold/logistic/add;/root/kasih/logistic/add;/root/bursa/logistic/add;');
															
		$this->mapActionToRights('updateAceSalesmanToDelivery', '/root/gtp/logistic/add;/root/mbb/logistic/add;/root/bmmb/logistic/add;/root/go/logistic/add;/root/one/logistic/add;/root/onecall/logistic/add;/root/air/logistic/add;/root/mcash/logistic/add;/root/ktp/logistic/add;/root/kopetro/logistic/add;/root/kopttr/logistic/add;/root/pkbaffi/logistic/add;/root/toyyib/logistic/add;/root/nubex/logistic/add;/root/hope/logistic/add;/root/mbsb/logistic/add;/root/red/logistic/add;/root/kodimas/logistic/add;/root/kgoldaffi/logistic/add;/root/koponas/logistic/add;/root/wavpay/logistic/add;/root/noor/logistic/add;/root/bsn/logistic/add;/root/alrajhi/logistic/add;/root/posarrahnu/logistic/add;/root/waqaf/logistic/add;/root/igold/logistic/add;/root/kasih/logistic/add;/root/bursa/logistic/add;');

		$this->mapActionToRights('getPrintDocuments', '/root/gtp/logistic/list;/root/mbb/logistic/list;/root/bmmb/logistic/list;/root/go/logistic/list;/root/one/logistic/list;/root/onecall/logistic/list;/root/air/logistic/list;/root/mcash/logistic/list;/root/ktp/logistic/list;/root/kopetro/logistic/list;/root/kopttr/logistic/list;/root/pkbaffi/logistic/list;/root/toyyib/logistic/list;/root/nubex/logistic/list;/root/hope/logistic/list;/root/mbsb/logistic/list;/root/red/logistic/list;/root/kodimas/logistic/list;/root/kgoldaffi/logistic/list;/root/koponas/logistic/list;/root/wavpay/logistic/list;/root/noor/logistic/list;/root/bsn/logistic/list;/root/alrajhi/logistic/list;/root/posarrahnu/logistic/add;/root/waqaf/logistic/list;/root/igold/logistic/list;/root/kasih/logistic/list;/root/bursa/logistic/list;');

		$this->mapActionToRights('documentHTML', '/root/gtp/logistic/list;/root/mbb/logistic/list;/root/bmmb/logistic/list;/root/go/logistic/list;/root/one/logistic/list;/root/onecall/logistic/list;/root/air/logistic/list;/root/mcash/logistic/list;/root/ktp/logistic/list;/root/kopetro/logistic/list;/root/kopttr/logistic/list;/root/pkbaffi/logistic/list;/root/toyyib/logistic/list;/root/nubex/logistic/list;/root/hope/logistic/list;/root/mbsb/logistic/list;/root/red/logistic/list;/root/kodimas/logistic/list;/root/kgoldaffi/logistic/list;/root/koponas/logistic/list;/root/wavpay/logistic/list;/root/noor/logistic/list;/root/bsn/logistic/list;/root/alrajhi/logistic/list;/root/posarrahnu/logistic/add;/root/waqaf/logistic/list;/root/igold/logistic/list;/root/kasih/logistic/list;/root/bursa/logistic/list;');

		$this->mapActionToRights('printawb', '/root/gtp/logistic/list;/root/mbb/logistic/list;/root/bmmb/logistic/list;/root/go/logistic/list;/root/one/logistic/list;/root/onecall/logistic/list;/root/air/logistic/list;/root/mcash/logistic/list;/root/ktp/logistic/list;/root/kopetro/logistic/list;/root/kopttr/logistic/list;/root/pkbaffi/logistic/list;/root/toyyib/logistic/list;/root/nubex/logistic/list;/root/hope/logistic/list;/root/mbsb/logistic/list;/root/red/logistic/list;/root/kodimas/logistic/list;/root/kgoldaffi/logistic/list;/root/koponas/logistic/list;/root/wavpay/logistic/list;/root/noor/logistic/list;/root/bsn/logistic/list;/root/alrajhi/logistic/list;/root/posarrahnu/logistic/add;/root/waqaf/logistic/list;/root/igold/logistic/list;/root/kasih/logistic/list;/root/bursa/logistic/list;');

		$this->mapActionToRights('getShipmentStatus', '/root/gtp/logistic/list;/root/mbb/logistic/list;/root/bmmb/logistic/list;/root/go/logistic/list;/root/one/logistic/list;/root/onecall/logistic/list;/root/air/logistic/list;/root/mcash/logistic/list;/root/ktp/logistic/list;/root/kopetro/logistic/list;/root/kopttr/logistic/list;/root/pkbaffi/logistic/list;/root/toyyib/logistic/list;/root/nubex/logistic/list;/root/hope/logistic/list;/root/mbsb/logistic/list;/root/red/logistic/list;/root/kodimas/logistic/list;/root/kgoldaffi/logistic/list;/root/koponas/logistic/list;/root/wavpay/logistic/list;/root/noor/logistic/list;/root/bsn/logistic/list;/root/alrajhi/logistic/list;/root/posarrahnu/logistic/add;/root/waqaf/logistic/list;/root/igold/logistic/list;/root/kasih/logistic/list;/root/bursa/logistic/list;');

		$this->mapActionToRights('getShipmentDetails', '/root/gtp/logistic/list;/root/mbb/logistic/list;/root/bmmb/logistic/list;/root/go/logistic/list;/root/one/logistic/list;/root/onecall/logistic/list;/root/air/logistic/list;/root/mcash/logistic/list;/root/ktp/logistic/list;/root/kopetro/logistic/list;/root/kopttr/logistic/list;/root/pkbaffi/logistic/list;/root/toyyib/logistic/list;/root/nubex/logistic/list;/root/hope/logistic/list;/root/mbsb/logistic/list;/root/red/logistic/list;/root/kodimas/logistic/list;/root/kgoldaffi/logistic/list;/root/koponas/logistic/list;/root/wavpay/logistic/list;/root/bsn/logistic/list;/root/alrajhi/logistic/list;/root/posarrahnu/logistic/add;/root/waqaf/logistic/list;/root/igold/logistic/list;/root/kasih/logistic/list;/root/bursa/logistic/list;');

		$this->mapActionToRights('callCourierCrawler', '/root/gtp/logistic/list;/root/mbb/logistic/list;/root/bmmb/logistic/list;/root/go/logistic/list;/root/one/logistic/list;/root/onecall/logistic/list;/root/air/logistic/list;/root/mcash/logistic/list;/root/ktp/logistic/list;/root/kopetro/logistic/list;/root/kopttr/logistic/list;/root/pkbaffi/logistic/list;/root/toyyib/logistic/list;/root/nubex/logistic/list;/root/hope/logistic/list;/root/mbsb/logistic/list;/root/red/logistic/list;/root/kodimas/logistic/list;/root/kgoldaffi/logistic/list;/root/koponas/logistic/list;/root/wavpay/logistic/list;/root/noor/logistic/list;/root/bsn/logistic/list;/root/alrajhi/logistic/list;/root/posarrahnu/logistic/add;/root/waqaf/logistic/list;/root/igold/logistic/list;/root/kasih/logistic/list;/root/bursa/logistic/list;');
		


		$logisticStore = $app->logisticFactory();
		$this->addChild(new ext6gridhandler($this, $logisticStore, 1));
	}

	/*
		This method is to get the Category to be listing in the form
	*/
/*

    function fillform( $app, $params) {
        $ordertype = \Snap\object\Logistic::getOrdertype();
        echo json_encode( ['success' => true,  'type' => $type]);
    }
*/
/*
	function onPreAddEditCallback($object, $params) {
		$object->approvedon  = new \DateTime();
		$object->status = 2;
		$object->referenceno = time().rand(10*45, 100*98);
		$object->orderon = new \DateTime();
		$existingOrderlist = $object->getOrderDelivery();

		if($object->patientid != 0) $object->patientid = $params['patientid'];
		else $object->patientid = 0;

		//validate user to enter expected delivery list
		$clientOrdersData = json_decode($params['orderlist'], true);
		if(empty($clientOrdersData)) {
			throw new \Snap\InputException(gettext("Please insert expected quantity and date list"), \Snap\InputException::GENERAL_ERROR, 'orderlist');
			return;
		}
		$orderData = array();
		$count = 0;

		//adding quantity in expected deliver to match with total order quantity
		foreach($clientOrdersData as $aOrder) {
			$totalOrderQuantity += $aOrder['expectedquantity'];
		}
		if($totalOrderQuantity != $params['orderquantity']) {
			throw new \Snap\InputException(gettext("Total order does not match."), \Snap\InputException::GENERAL_ERROR, 'orderlist');
			return;
		}

		//add expected delivery list to orderdelivery data
		foreach($clientOrdersData as $aOrder) {
			$expecteddeliveryon = date('Y-m-d H:i:s', strtotime($aOrder['expecteddeliveryon']));
			//echo $expectedDate;


			$orderDeliveryArr = array(
				'expecteddeliveryon' => $expecteddeliveryon,
				'expectedquantity' => $aOrder['expectedquantity']
				);
			//var_dump($orderDeliveryArr);
			if($aOrder['id'] <= 0) {
				//$orderDelivery = $object->getStore()->getRelatedStore('orderdelivery')->getById($aOrder['id']);
				$orderDelivery = $object->getOrderDeliveryByID($aOrder['id']);
				$object->addOrderDelivery($orderDeliveryArr);
			} else $orderData[$aOrder['id']]= $aOrder;
		}

		//update existing expected delivery list
		foreach($existingOrderlist as $aOrderlist) {
			if(!isset($orderData[$aOrderlist->id])) {
				$object->removeOrderDelivery($aOrderlist);
			} else {
				$aOrderlist->expectedquantity = $orderData[$aOrderlist->id]['expectedquantity'];
				$aOrderlist->expecteddeliveryon = \DateTime::createFromFormat(\DateTime::W3C, $orderData[$aOrderlist->id]['expecteddeliveryon']);

				$object->updateOrderDelivery($aOrderlist);
			}
		}

		if(0 == $params['id'] || !$permission){
			$object->requestquantity = $params['orderquantity'];
			$object->deliveredquantity = 0;
			$object->defectivequantity = 0;
			$object->approvedon  = new \DateTime('1970-01-02');
			$object->completedon  = new \DateTime('1970-01-02');
		}

		return $object;
	}

	function addToOrder($app,$params){
		$orderid = $params['id'];

		//if hq, get branch from selected option. else grab default branch of user
		if(!isset($params['branchid'])) $branchid = $app->getUserSession()->getUser()->branchid;
   		else $branchid = $params['branchid'];

   		//parameter to pass to inventorymanager
		$patientid = $params['patientid'];
		$stationid = $params['stationid'];
		$inventorycatid = $params['inventorycatid'];
		$orderquantity = $params['orderquantity'];
		$orderon = new \DateTime();
		$notes = $params['notes'];

		//add new
		if(0 == $orderid){
			$orderStore = $app->orderfactory();
			$manager = $app->inventorymanager();
			$createOrder = $manager->createNewOrder($branchid,$stationid, $patientid, $inventorycatid, $orderquantity, $orderon, $notes);
			$clientOrdersData = json_decode($params['orderlist'], true);
			$orderData = array();
			$count = 0;

			foreach($clientOrdersData as $aOrder) {
				$orderDeliveryArr = array(
					'expecteddeliveryon' => $aOrder['expecteddeliveryon'],
					'expectedquantity' => $aOrder['expectedquantity']
				);
				if($aOrder['id'] <= 0) {
					$createOrder->addOrderDelivery($orderDeliveryArr);
					$orderStore->save($createOrder);
				} else $orderData[$aOrder['id']]= $aOrder;
			}
		} else { //edit order
			$object = $app->orderfactory()->getById($params['id']);
			$object->editOrder($orderid, $branchid, $stationid, $patientid, $inventorycatid, $orderquantity, $notes,$orderDelivery);

		}
	}

	function approveOrder($app,$params){
		//create unique referenceno for summary display later
		$getReference = time().rand(10*45, 100*98);

		$paramsArray = explode(',', $params['gridData']);
		foreach($paramsArray as $aParams){
			$convertToArray = explode('|', $aParams);
			if(empty($convertToArray[1])){
				throw new \Snap\InputException(gettext("Please insert p/o number."), \Snap\InputException::GENERAL_ERROR, 'ponum');
				return;
			}
			$orderParams = array(
				'id' => $convertToArray[0],
				/*'inventorycatid' => $convertToArray[1],
				'branchid' => $convertToArray[2],
				'branchname' => $convertToArray[3],
				'patientid' => $convertToArray[4],
				'patientname' => $convertToArray[5],
				'notes' => $convertToArray[6],
				'orderon' => $convertToArray[7],
				'inventorycatname' => $convertToArray[8],*//*
				'ponum' => $convertToArray[1],
				'orderquantity' => $convertToArray[2],
				'referenceno' => $getReference
				);
			$order = json_decode (json_encode ($orderParams), FALSE);
			$orderApproved = $app->inventorymanager()->approvedOrder($order);
		}
	}

	function rejectOrder($app,$params){
		$order = json_decode (json_encode ($params), FALSE);
		$rejectOrder = $app->inventorymanager()->rejectOrder($order);
		if($rejectOrder) echo json_encode(array('success' => true));
	}

	function deliverOrder($app,$params){
		$order = $app->orderfactory()->getById($params['orderid']);

		$app->inventorymanager()->orderDelivered($order, $params['id'], $params['actualdeliveryon'], $params['actualquantity'], $params['actualdefect'], $params['note'], $params['invoiceno'], $params['deliveryno']);
	}

	function completedOrders($app,$params){
		$object = $app->orderfactory()->getById($params['id']);

		$orderDelivery = $object->getOrderDelivery();
		foreach ($orderDelivery as $aOrderDelivery)
		{
			if($aOrderDelivery->actualquantity == 0) $totalDelgivery += $aOrderDelivery->expectedquantity;
			$object->completedOrderDelivery($aOrderDelivery->id);
		}
		$params['totalQuantity'] = $totalDelivery;
		$order = json_decode (json_encode ($params), FALSE);
		$completedOrder = $app->inventorymanager()->completedOrder($order);
		if($completedOrder) echo json_encode(array('success' => true));
	}

	function fillform( $app, $params) {
		$permission = $this->app->hasPermission('/root/hq/columns/show_all_branches');
		$patientToList = array();
		if(!$permission) {
			$branchId = $this->app->getUserSession()->getUser()->branchid;
			$allPatient = $app->patientfactory()->searchTable()->select(['id', 'name', 'nric', 'branchid'])->where('branchid',$branchId)->andWhere('status', 1)->execute();
		} else {
			$allPatient = $app->patientfactory()->searchTable()->select(['id', 'name', 'nric', 'branchid'])->where('status', 1)->execute();
		}

		foreach( $allPatient as $aAllPatient) {
			$patientToList[] = $aAllPatient->toArray();
		}

		$record = array();
		$ordersArray = array();

		$inventorycatToList = array();
		$allInventorycat = $app->inventorycatfactory()->searchTable()->select()->where('status', 1)->execute();
		foreach( $allInventorycat as $aAllInventorycat) {
			$inventorycatToList[] = $aAllInventorycat->toArray();
		}

		$stationToList = array();
		$allStation = $app->stationfactory()->searchTable()->select()->where('status', 1)->execute();
		foreach( $allStation as $aAllStation) {
			$stationToList[] = $aAllStation->toArray();
		}

		if($params['id']){ //edit form. already have id
			$object = $app->orderfactory()->getById($params['id']);

			foreach($object->getOrderDelivery() as $oneOrder) {
				$ordersArray[] = ['id' => $oneOrder->id, 'expectedquantity' => $oneOrder->expectedquantity, 'expecteddeliveryon' => $oneOrder->expecteddeliveryon->format('Y-m-d h:i:s')];
			}
		}
		else {
			$record = $app->orderfactory()->create(['id' => 0])->toArray();
		}

		echo json_encode( ['success' => true, 'record' => $record, 'inventorycatToList' => $inventorycatToList, 'patientToList' => $patientToList,'stationToList' => $stationToList, 'orders' => $ordersArray]);
	} */


    function fillform($app, $params) {

		$userId = $this->app->getUserSession()->getUser()->id;
		$user=$this->app->userStore()->getById($userId);
		$userType = $this->app->getUserSession()->getUser()->type;
		
		$statushtml = $this->getShipmentStatus($app, $params);

		// Get Logistic 
		$logistic=$this->app->logisticStore()->getById($params['id']);

		if($logistic->deliverydate == '0000-00-00 00:00:00' || !$logistic->deliverydate){
			$deliveryDate = '0000-00-00 00:00:00';
		}else {
			$deliveryDate = $logistic->deliverydate->format('Y-m-d H:i:s');
		}


		$status = \Snap\object\Logistic::getBoStatus($user);
		//echo json_encode( ['success' => true,  'status' => $status, 'usertype' => $userType, 'statushtml' => $statushtml]);
		echo json_encode( ['success' => true,  'status' => $status, 'usertype' => $userType, 'deliverydate' => $deliveryDate, 'statushtml' => $statushtml]);
	}



	public function updateLogisticStatus($app,$params){
		//print_r($params);


		/*
        $logisticobj=$this->app->logisticStore()->getById($params['id']);
        $logisticmanager=$this->app->logisticManager();


        $logisticmanager->logisticStatus($logisticobj, intval($params['status']), $logisticobj->senderId ?? null, $logisticobj->receivedPerson ?? null, $params['remarks'] ?? null);
		*/

		//print_r( intval($params['status']));
		// Save form fill when status is changed



		try{

			$logisticobj=$this->app->logisticStore()->getById($params['id']);
			$logisticmanager=$this->app->logisticManager();
			$return = $logisticmanager->logisticStatus($logisticobj, intval($params['status']), $logisticobj->senderId, null, $params['remarks']);



			$awbno = $params['awbno'];
			$pickup = $params['isPickup'];
			//$deliverydateobj = date("Y-m-d", $params['deliverydate']);
			//$deliverydate = date("Y-m-d", strtotime($deliverydateobj.  ' + 1 days')).' 00:00:00';
			$deliverydate = $params['deliverydate'];
			//$delivery$params['remarks']);

			$logistic_input = $app->logisticFactory()->getById($params['id']);
			if($params['senderid'] != 0){
				
				// Get previous salesman name
				$previousSalesmanName = $app->userFactory()->getById($logistic_input->senderid)->name;
				// Get new salesman name
				$newSalesmanName = $app->userFactory()->getById($params['senderid'])->name;

				// if same means salesman is not changed
				if($logistic_input->senderid != $params['senderid']){
					$return = $logisticmanager->createLogisticLog($params['id'], $params['status'], 'Private', 'Salesman change from '.$previousSalesmanName.' to '.$newSalesmanName);
				}

				$logistic_input->senderid = $params['senderid'];
				
			} 
			$logistic_input->awbno = $awbno;
			$logistic_input->deliverydate = $deliverydate;
			$logisticmanager_saved = $app->logisticFactory()->save($logistic_input);
			echo json_encode(['success'=>true,'errorMessage'=>'']);

		}catch(\Exception $e){
		/*
			$message = $e->getMessage();
			$return = [
				"success" => 0,
				"status" => 0,
				"error" => 1,
				"error_message" => $message
			];
			echo json_encode($return);*/

			// Revisited later for improvements
			if($params['status'] == $logistic_input->status){
				$this->log(__CLASS__.": Thrown error from logistichandler: " . $e->getMessage(), SNAP_LOG_ERROR);
	            echo json_encode(['success'=>false,'errorMessage'=>'Status has not been changed '. $e->getMessage()]);
				//echo ('Status has not been changed. '.  $e->getMessage());
			}else {
				$this->log(__CLASS__.": Thrown error from logistichandler: " . $e->getMessage(), SNAP_LOG_ERROR);
	            echo json_encode(['success'=>false,'errorMessage'=> $e->getMessage()]);
				//echo ('Invalid sequence of action upon status change.');
			}


		}
		/*
		$return = [
			"success" => 1,
			"status" => 1,
			"error" => 0,
		];
		echo json_encode($return);
*/


    }

	public function updateLogisticInformation($app,$params){
		

		/*
		$logisticobj=$this->app->logisticStore()->getById($params['id']);
		$logisticmanager=$this->app->logisticManager();


		$logisticmanager->logisticStatus($logisticobj, intval($params['status']), $logisticobj->senderId ?? null, $logisticobj->receivedPerson ?? null, $params['remarks'] ?? null);
		*/

		//print_r( intval($params['status']));
		// Save form fill when status is changed



		try{
						
			$awbno = $params['awbno'];
			//$deliverydateobj = date("Y-m-d", $params['deliverydate']);
			$deliverydate = $params['deliverydate'];
			//$delivery$params['remarks']);			

			$logistic_input = $app->logisticFactory()->getById($params['id']);

			if($params['senderid'] != 0){
				
				// Get previous salesman name
				$previousSalesmanName = $app->userFactory()->getById($logistic_input->senderid)->name;
				// Get new salesman name
				$newSalesmanName = $app->userFactory()->getById($params['senderid'])->name;
				
				// if same means salesman is not changed
				if($logistic_input->senderid != $params['senderid']){
			
					$time = date('Y-m-d H:i:s', time());
					$logisticmanager=$this->app->logisticManager();
					//$time = common::convertUserDatetimeToUTC(new \DateTime($time));
				
					$return = $logisticmanager->createLogisticLog($params['id'], $params['status'], 'Private', 'Salesman change from '.$previousSalesmanName.' to '.$newSalesmanName);
				}

				$logistic_input->senderid = $params['senderid'];
				
			} 
			
			$logistic_input->awbno = $awbno;
			$logistic_input->deliverydate = $deliverydate;
			$logisticmanager_saved = $app->logisticFactory()->save($logistic_input);
			echo json_encode(['success'=>true,'errorMessage'=>'']);

		}catch(\Exception $e){			
		/*
			$message = $e->getMessage();
			$return = [
				"success" => 0,
				"status" => 0,
				"error" => 1,
				"error_message" => $message
			];
			echo json_encode($return);*/

			if($params['status'] == $logisticobj->status){
				$this->log(__CLASS__.": Thrown error from logistichandler: " . $e->getMessage(), SNAP_LOG_ERROR);
				echo json_encode(['success'=>false,'errorMessage'=>'Invalid action, make sure form fields are correct '.  $e->getMessage()]);
				//echo ('Invalid action, make sure form fields are correct '.  $e->getMessage());
			}else {
				$this->log(__CLASS__.": Thrown error from logistichandler: " . $e->getMessage(), SNAP_LOG_ERROR);
				echo json_encode(['success'=>false,'errorMessage'=>'Invalid action']);
				//echo ('Invalid action');
			}


		}
		/*
		$return = [
			"success" => 1,
			"status" => 1,
			"error" => 0,
		];
		echo json_encode($return);
	*/


	}

	public function updateAceSalesmanToDelivery($app,$params){

		try{

			
			$salespersonid = $params['salespersonid'];

			$logistic_input = $app->logisticFactory()->getById($params['id']);

			$oldsenderid = $logistic_input->senderid;

			$logistic_input->senderid = $salespersonid;

			$logistic_input->sentby = $salespersonid;

			$prev = $this->app->userStore()->getById($oldsenderid);
			$previousSalesmanName = $prev->name;
			$new = $this->app->userStore()->getById($salespersonid);
			$newSalesmanName = $new->name;
			$this->app->LogisticManager()->createLogisticLog($params['id'], 'Remarks', 'Private', 'Salesman change from '.$previousSalesmanName.' to '.$newSalesmanName);
			
			$logisticmanager_saved = $app->logisticFactory()->save($logistic_input);

		}catch(\Exception $e){

			if($params['status'] == $logisticobj->status){
				$this->log(__CLASS__.": Thrown error from logistichandler: " . $e->getMessage(), SNAP_LOG_ERROR);
				echo json_encode(['success'=>false,'errorMessage'=>$e->getMessage()]);
				//echo ('Invalid action, make sure form fields are correct '.  $e->getMessage());
			}else {
				$this->log(__CLASS__.": Thrown error from logistichandler: " . $e->getMessage(), SNAP_LOG_ERROR);
				echo json_encode(['success'=>false,'errorMessage'=>$e->getMessage()]);
				//echo ('Invalid action');
			}


		}
	}
	
	public function updateLogisticAttempts($app,$params){
		try{
			/*
			$permission = $this->app->hasPermission('/root/mib/logistic/edit');
			if(!$permission){
				//throw new \Snap\InputException(gettext("Sorry, no permission"), \Snap\InputException::GENERAL_ERROR, 'permission');
				echo json_encode(['success'=>false,'errorMessage'=>'You dont have permission to do this operation']);
			}*/

			$logisticobj = $app->logisticStore()->getById($params['id']);

			$return = $app->LogisticManager()->attemps($logisticobj);
			
			if ($return){
				$output['success'] = true;
				$output['logistic'] = $return;
			}

			echo json_encode($output);
			
		}catch(\Exception $e){
			$output = [
				// "error" => 1,
				"success" => false,
				//"errmsg" => $e->getMessage()
				"errorMessage" => 'Maximum number of attempts reached.'
			];

			echo json_encode($output);
		}
    }


	function callCourierCrawler($app, $params){
		try{
			$app->logisticManager()->courierStatusCrawler();
			$return['success'] = true;
		}catch(\Exception $e){
			$return['success'] = false;
		}
		return json_encode($return);
    }

/*
    function onPreQueryListing($params, $sqlHandle, $fields){
        //Filter list by branch
        $userType = $this->app->getUserSession()->getUser()->type;
        //$permission = $this->app->hasPermission('/root/mib/logistic');

        $userId = $this->app->getUserSession()->getUser()->id;


        // For logistics
        // At the moment only sales and operator is involved
        if($userType == "Sale"){
            $sqlHandle->andWhere('senderid', $userId); //Pending as a default
        } else if ($userType == "Operator") {
            // Blank as there are no filters


        } else if ($userType == "Trader") {
            exit;
        } else if ($userType == "Customer") {
            exit;
        } else if ($userType == "Referral") {
            exit;
        } else if ($userType == "Agent") {
            exit;
        }


		//if(!$permission) $sqlHandle->andWhere('branchid', $branchId);
        /*
        if(!$permission){
            $sqlHandle->andWhere('branchid', $branchId);
        }


        return array($params, $sqlHandle, $fields);
    }
*/

	/**
	* function to massage data before listing
	**/
	function onPreListing($objects, $params, $records) {

		$app = App::getInstance();

		$userType = $this->app->getUserSession()->getUser()->type;

		foreach ($records as $key => $record) {
			// Acquire progress percentage.
			if($record['status'] <= 7){
				$records[$key]['statusbar'] = $record['status'] / 7;
			}else {
				$records[$key]['statusbar'] = 0;
			}

			$records[$key]['usertype'] = $userType;

			if($record['status'] == 0){
				$records[$key]['status_text'] = "Pending";
			} else if($record['status'] == 1){
				$records[$key]['status_text'] = "Processing";
			} else if($record['status'] == 2){
				$records[$key]['status_text'] = "Packing";
			} else if($record['status'] == 3){
				$records[$key]['status_text'] = "Packed";
			} else if($record['status'] == 4){
				$records[$key]['status_text'] = "Collected";
			} else if($record['status'] == 5){
				$records[$key]['status_text'] = "In Transit";
			} else if($record['status'] == 6){
				$records[$key]['status_text'] = "Delivered";
			}else if($record['status'] == 7){
				$records[$key]['status_text'] = "Completed";
			}else if($record['status'] == 8){
				$records[$key]['status_text'] = "Failed";
			}else {
				$records[$key]['status_text'] = "Missing";
			}

			// Do logistic filter and check
			// Check type
			if($record['type'] == Logistic::TYPE_REDEMPTION){
				//
				$redemption =$this->app->redemptionStore()->getById($records[$key]['id']); 
				$records[$key]['typeno'] = $redemption->redemptionno;
			}else if($record['type'] == Logistic::TYPE_REPLENISHMENT){
				$replenishment = $app->replenishmentfactory()->getById($records[$key]['id']);
				$records[$key]['typeno'] = $replenishment->replenishmentno;
	
			}else if($record['type'] == Logistic::TYPE_BUYBACK){
				// Get id from buybacklogistic
				$buybackIds = [];
				$buybacklogistics = $this->app->buybackLogisticStore()->searchTable()->select()->where("logisticid", $records[$key]['id'])->execute();
				foreach ($buybacklogistics as $buybacklogistic){
					array_push($buybackIds, $buybacklogistic->buybackid);
				}
				$buybacks = $this->app->buybackStore()->searchTable()->select()->whereIn("id", $buybackIds)->execute();
				$buybackno = [];
				foreach($buybacks as $buyback){
					array_push($buybackno, $buyback->buybackno);
				}
				$records[$key]['typeno'] = $buybackno;
			}
		}
		
		return $records;
	}
    /*
        This method is to get data for view details
    */
	function detailview($app, $params) {
		//$object = $app->logisticfactory()->getById($params['id']);
		$object = $app->logisticfactory()->searchView()->select()->where('id', $params['id'])->one();
		//$vendorname = $app->userFactory()->getById($object->vendorid)->name;
		$sendername = $app->userFactory()->getById($object->senderid)->name;
		$sentByName = $app->userFactory()->getById($object->sentby)->name;
		$deliveredByName = $app->userFactory()->getById($object->deliveredby)->name;

		/*
		if ($object->vendorid == 1){
			$vendorname = 'Ace Logistic';
		}else if ($object->vendorid == 2){
			$vendorname = 'GDEX';
		}else {
			$vendorname = 'Unidentified';
		}*/

		// Status
		if ($object->status == 0){
			$statusname = 'Pending';
		}else if ($object->status == 1){
			$statusname = 'Processing';
		}else if ($object->status == 2){
			$statusname = 'Packing';
		}else if ($object->status == 3){
			$statusname = 'Packed';
		}else if ($object->status == 4){
			$statusname = 'Collected';
		}else if ($object->status == 5){
			$statusname = 'In Transit';
		}else if ($object->status == 6){
			$statusname = 'Delivered';
		}else if ($object->status == 7){
			$statusname = 'Completed';
		}else if ($object->status == 8){
			$statusname = 'Failed';
		}else if ($object->status == 9){
			$statusname = 'Missing';
		}else {
			$statusname = 'Unidentified';
		}

		$nullDate = '1970-01-02 00:00:00';
	
		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';
		
		// Set Sent On
		//$sentOn = $object->senton ? $object->senton->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		//$deliveredOn = $object->deliveredon ? $object->deliveredon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		//$deliveryDate = $object->deliverydate ? $object->deliverydate->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

		if($object->senton == '0000-00-00 00:00:00' || !$object->senton){
			$sentOn = '0000-00-00 00:00:00';
		}else {
			$sentOn = $object->senton->format('Y-m-d H:i:s');
		}

		if($object->deliveredon == '0000-00-00 00:00:00' || !$object->deliveredon){
			$deliveredOn = '0000-00-00 00:00:00';
		}else {
			$deliveredOn = $object->deliveredon->format('Y-m-d H:i:s');
		}

		if($object->deliverydate == '0000-00-00 00:00:00' || !$object->deliverydate){
			$deliveryDate = '0000-00-00 00:00:00';
		}else {
			$deliveryDate = $object->deliverydate->format('Y-m-d H:i:s');
		}


		//print_r( $object->deliverydate->format('Y-m-d h:i:s'));
		//if (!preg_match('/[1-9]/', $object->deliverydate->format('Y-m-d h:i:s'))) print_r( 'No date set.');

		//print_r($object);
		//$sentDate = $object->deliverydate->format('Y-m-d h:i:s');
		
		//if($sentDate != $nullDate) $sentOn = $sentDate;
		//else $sentOn = '0000-00-00 00:00:00';



		$detailRecord['default'] = [ //"ID" => $object->id,
                                    'Type' => $object->type,
                                    $object->type.' ID' => $object->typeid,
                                    'Vendor' => $object->vendorname,
                                    'Sender' => $sendername,
                                    'AWB / DO No' => $object->awbno,
                                    'Contact Name 1' => $object->contactname2,
                                    'Contact Name 2' => $object->contactname2,
                                    'Contact No 1' => $object->contactno1,
                                    'Contact No 2' => $object->contactno2,
                                    'Address 1' => $object->address1,
                                    'Address 2' => $object->address2,
                                    'Address 3' => $object->address3,
                                    'City' => $object->city,
                                    'Postcode' => $object->postcode,
                                    'State' => $object->state,
                                    'Country' => $object->country,
                                    //'From Branch ID' => $object->frombranchid,
                                    //'To branch Id' => $object->tobranchid,
                                    'Sent On' => $sentOn,
                                    'Sent By' => $sentByName,
                                    //'Received Person' => $object->receivedperson,
                                    'Delivered On' => $deliveredOn,
                                    'Delivered By' => $deliveredByName,
                                    'Delivery Date' => $deliveryDate,
                                    'Attempts' => $object->attemps,
                                    'Status' => $statusname,
									'Created on' => $object->createdon->format('Y-m-d H:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d H:i:s'),
									'Modified by' => $modifieduser,
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

    /*

	function onPreListing($objects, $params, $records) {

		foreach ($records as $key => $record) {
			$records[$key]['status_text'] = ($record['status'] == "1" ? "Active" : "Inactive");
		}

		return $records;
	}*/

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

	function getPrintDocuments($app, $params){
		// $logisticId = 3; // redemption
		// $logisticId = 34; // replenishments
		$logisticId = $params['id'];
		$logistic = $this->app->LogisticStore()->getById($logisticId);

		try{
			$type = $logistic->type;

			if ($type == Logistic::TYPE_REDEMPTION){
				$documentTitle = 'Deliver Note';

				// $list = $this->app->redemptionStore()->searchView()->select()->where('id',80)->one();
				$list = $this->app->redemptionStore()->searchView()->select()->where('id', $logistic->typeid)->one();

				$redemptionType = $list->type;
				if (in_array($redemptionType, [Redemption::TYPE_DELIVERY,Redemption::TYPE_SPECIALDELIVERY, Redemption::TYPE_APPOINTMENT])){
					$addressFrom = '
					No. 19-1, Jalan USJ 10/1D,
					47620 Subang Jaya, Selangor
					';
					$addressTo = $this->getLogisticDeliveryAddress($logistic);
				}
				if ($redemptionTyp == Redemption::TYPE_BRANCH){
					// LOST no logistic is made; wont happen here
				}

				$generateNoPrefix = 'DN';

			}else if ($type == Logistic::TYPE_REPLENISHMENT){
				$documentTitle = 'Consignment Note';
				
				$replenishmentids = [];
				$replenishmentList = $this->app->replenishmentLogisticStore()->searchTable()->select()->where('logisticid', $logistic->id)->execute();
				if (!$replenishmentList){
					throw new \Exception("Invalid Replenishment Logistic");
				}
				foreach ($replenishmentList as $replenishment){
					array_push($replenishmentids, $replenishment->replenishmentid);
				}
				$replenishmentList = $this->app->replenishmentStore()->searchView()->select()->where('id', 'IN', $replenishmentids)->execute();
				$list = $replenishmentList;

				$addressFrom = '
				No. 19-1, Jalan USJ 10/1D,
				47620 Subang Jaya, Selangor
				';
				$addressTo = $this->getBranchAddress($logistic->tobranchid);

				$generateNoPrefix = 'CN';
				
			}else if ($type == Logistic::TYPE_BUYBACK){
				$documentTitle = 'Consignment Note (Buyback)';

				$addressFrom = $this->getBranchAddress($logistic->frombranchid);
				$addressTo = '
				No. 19-1, Jalan USJ 10/1D,
				47620 Subang Jaya, Selangor
				';

				$generateNoPrefix = 'CN';

				$buybacks = $this->app->buybackLogisticStore()->searchTable()->select()
					->where('logisticid', $logistic->id)
					->orderby('id', 'ASC')
					->execute();
				foreach ($buybacks as $x => $buybackLogistic){
					$buyback = $this->app->buybackStore()->searchTable()->select()
					->where('id', $buybackLogistic->buybackid)
					->one();
					$items = json_decode($buyback->items);
					$list[] = array_values($items);
				}
				$list = $list;
			}
			// $branchAddress = $this->getBranchAddress();
			// $addressTo = $branchAddress;
			$noteNo_Logistic_ID = $logistic->id;
			$noteDate = $logistic->createdon;
			$content = $this->documentHTML($list, $type, $noteDate, $noteNo_Logistic_ID, $addressTo, $addressFrom, $documentTitle, $generateNoPrefix, $deliveryOrder);	

			echo $content;
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
	}

	private function getLogisticDeliveryAddress($logistic, $html = true){
		$address = '';
		if ($html){
			if ($logistic->contactname1){
				$address .= $logistic->contactname1.'<br>';
			}
		}
		$address .= $logistic->address1.',';
		$address .= $logistic->address2.',';
		if ($logistic->address3){
			$address .= $logistic->address3.',';
		}
		$address .= $logistic->city ? $logistic->city.',' : '';
		$address .= $logistic->postcode ? $logistic->postcode : '';
		$address .= $logistic->state ? $logistic->state.',' : '';
		$address .= $logistic->country ? $logistic->country : '';

		return $address;
	}

	private function getBranchAddress($branchid){
		$branch = $this->app->partnerStore()->getRelatedStore('branches');;
		$branch = $branch->getById($branchid);

		$address = $branch->address.',';
		$address .= ($branch->postcode) ? $branch->postcode.',' : '';
		$address .= ($branch->city) ? $branch->city : '';
		return $address;
	}


	private function documentHTML($lists, $type, $noteDate, $noteNo_Logistic_ID, $addressTo, $addressFrom, $documentTitle, $generateNoPrefix, $deliveryOrder = null){
		$format = '%s%05d';
		$noteNo = strtoupper(sprintf($format, $generateNoPrefix, $noteNo_Logistic_ID));

		if ($type == Logistic::TYPE_REDEMPTION){
			$lists = json_decode($lists->items);
			//"code":"GS-999-9-5g","serialnumber":"SN5-0032","weight":"5.000000"
			foreach ($lists as $i => $list){
				$lists[$i]->productname = $list->code;
				$lists[$i]->serialno = $list->serialnumber;
				// $lists[$i]->weight = $list->weight;
			}
		}
		if ($type == Logistic::TYPE_REPLENISHMENT){
			
		}
		if ($type == Logistic::TYPE_BUYBACK){
			
			foreach ($lists as $i => $list){
				// $list = array_filter($list);
				// print_r($list[$i]);exit;
				$lists[$i]->productname = $item->code;
				$lists[$i]->serialno = $item->serialnumber;
				// $lists[$i]->weight = $list->weight;
			}
			
		}
		
		$html = '
		<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>ACEGTP: '.$documentTitle.'</title>
			</head>
			<body>
				
				<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:22pt;"><span style="font-family:Cambria;">ACE Capital Growth Sdn. Bhd.</span></p>
				<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:18pt;"><span style="font-family:Cambria;">'.$documentTitle.'</span></p>
				<div style="text-align:center; ">
					<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					<table cellpadding="0" cellspacing="0" style="margin: auto; border:0.75pt solid #000000; border-collapse:collapse;">
						<tbody>
							<tr style="height:0.05pt;">
								<td style="width:202.1pt; border-right-style:solid; border-right-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">Date: </span>'.$noteDate->format('d-M-Y').'</p>
								</td>';
								if ($documentTitle == 'Consignment Note (Buyback)'){
									$html .= '
									<td style="width:202.1pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">Consignment Note No: '.$noteNo.'</span></p>
									</td>';
								}else{
									$html .= '
									<td style="width:202.1pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">'.$documentTitle.' No: '.$noteNo.'</span></p>
									</td>';
								}
								$html .= '
								
							</tr>
						</tbody>
					</table>
					<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #000000; border-collapse:collapse;">
						<tbody>
							<tr style="height:0.05pt;">
								<td style="width:202.1pt; border-right-style:solid; border-right-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">From:</span></p>
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;">
										'.$addressFrom.'
									</p>
								</td>
								<td style="width:202.1pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">To:</span></p>
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;">
										'.$addressTo.'
									</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #000000; border-collapse:collapse;">
						<tbody>
						<tr style="height:0.05pt;">
							<td style="width:22.95pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
								<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">No</span></p>
							</td>
							<td style="width:123.9pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
								<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Product Type</span></p>
							</td>
							<td style="width:140.1pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
								<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Serial Number</span></p>
							</td>
							<td style="width:95.65pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
								<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Quantity</span></p>
							</td>
						</tr>

						';
						foreach ($lists as $y => $list){
							// if (gettype($list) == 'object'){
							// 	$list = $list[$y];
							// }
							if ($type == Logistic::TYPE_BUYBACK){
								foreach ($list as $item){
									$y++;
									$html .= '
										<tr style="height:0.05pt;">
											<td style="width:22.95pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
												<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$y.'</span></p>
											</td>
											<td style="width:123.9pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
												<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$item->code.'</span></p>
											</td>
											<td style="width:140.1pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
												<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$item->serialnumber.'</span></p>
											</td>
											<td style="width:95.65pt; border-top-style:solid; border-top-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
												<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">1</span></p>
											</td>
										</tr>
									';
								}
							}else{
								$y++;
								$html .= '
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$y.'</span></p>
										</td>
										<td style="width:123.9pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$list->productname.'</span></p>
										</td>
										<td style="width:140.1pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$list->serialno.'</span></p>
										</td>
										<td style="width:95.65pt; border-top-style:solid; border-top-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">1</span></p>
										</td>
									</tr>
								';
							}
						}
						
						$html .= '
							
						</tbody>
					</table>
					<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					';
					if ($documentTitle == 'Deliver Note' || $documentTitle == 'Consignment Note'){
						$html .= '
						<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #ffffff; border-collapse:collapse;">
						<tbody>
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" style="border:0.75pt solid #ffffff; border-collapse:collapse;">
								<tbody>
									
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Stored By</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">……………………………………..</span></p>
										</td>
										
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
								</tbody>
							</table>
							</td>
							<td>
								<table cellpadding="0" cellspacing="0" style="border:0.75pt solid #ffffff; border-collapse:collapse;">
								<tbody>
									
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Received By</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">……………………………………..</span></p>
										</td>
										
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
								</tbody>
							</table>
							</td>
							<td>
								<table cellpadding="0" cellspacing="0" style="border:0.75pt solid #ffffff; border-collapse:collapse;">
								<tbody>
									
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Pick Up By</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">……………………………………..</span></p>
										</td>
										
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
								</tbody>
							</table>
							</td>
						</tr>
						</tbody>
					</table>
						';
					}else if ($documentTitle == 'Consignment Note (Buyback)'){
						$html .= '
						<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #ffffff; border-collapse:collapse;">
						<tbody>
						<tr>
							<td>
								<table cellpadding="0" cellspacing="0" style="border:0.75pt solid #ffffff; border-collapse:collapse;">
								<tbody>
									
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Pick Up By</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">……………………………………..</span></p>
										</td>
										
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
								</tbody>
							</table>
							</td>
							<td>
								<table cellpadding="0" cellspacing="0" style="border:0.75pt solid #ffffff; border-collapse:collapse;">
								<tbody>
									
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Released By</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">……………………………………..</span></p>
										</td>
										
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
								</tbody>
							</table>
							</td>
							<td>
								<table cellpadding="0" cellspacing="0" style="border:0.75pt solid #ffffff; border-collapse:collapse;">
								<tbody>
									
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Stored By</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">……………………………………..</span></p>
										</td>
										
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
										</td>
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
									<tr style="height:0.05pt;">
										<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
											<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
										</td>
										
										
									</tr>
								</tbody>
							</table>
							</td>
						</tr>
						</tbody>
					</table>
						';
					}else{
						$html .= 
					
					'
					<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #ffffff; border-collapse:collapse;">
						<tbody>
							
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Received By</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;..</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
						</tbody>
					</table>
					';
					}
					$html .= '
				</div>

			</body>
			</html>
		';

		return $html;
	}




	public function printawb($app, $params){
		$logistic = $app->logisticStore()->getById($params['id']);

		if (!$logistic->vendorid){
			throw new \Exception("Invalid Courier Type");
		}
		$vendor = $this->app->tagStore()->getById($logistic->vendorid);
		if (Logistic::VENDOR_GDEX_VALUE == $vendor->value){
			return $app->LogisticManager()->gdexApiGetConsignmentImage($logistic);
		}else{
			throw new \Exception("Invalid Courier Type to print Air Way Bill.");
		}
	}

	public function getShipmentStatus($app, $params){
		$logisticId = $params['id'];
		$logistic = $this->app->logisticStore()->getById($logisticId);

		// $vendorGdex = $this->app->tagStore()->getByField('value', Logistic::VENDOR_GDEX_VALUE);
		// $vendorLineClear = $this->app->tagStore()->getByField('value', Logistic::VENDOR_LINCLEAR_VALUE);
		// $vendor_with_api = [$vendorGdex->id, $vendorLineClear->id];

		// if (!in_array($logistic->vendorid, $vendor_with_api)){
		// 	$return = [
		// 		'success' => false,
		// 		'errorMessage' => 'Invalid Courier Selection.',
		// 	];
		// }

		$vendorValue = $this->app->tagStore()->getById($logistic->vendorid);

		if ($vendorValue->value == Logistic::VENDOR_GDEX_VALUE){
			$return = $this->app->logisticManager()->gdexApiGetShipmentStatusDetail($logistic);
		}
		if ($vendorValue->value == Logistic::VENDOR_LINCLEAR_VALUE){
			$return = $this->app->logisticManager()->lineclearApiGetShipmentStatusDetail($logistic);
		}
		if ($vendorValue->value == Logistic::VENDOR_ACEDELIVERY_VALUE || $vendorValue->value == Logistic::VENDOR_JNT_VALUE){
			$return = $this->app->logisticManager()->aceDeliveryGetShipmentStatusDetail($logistic);
		}
		// print_r($return);exit;

		// $logs = $this->app->logisticLogStore()->searchTable()->select()->where('logisticid', $logistic->id)->execute();

		// standardise format as
		// TIME, API_STATUS, ACE_STATUS
		$html = $this->generateStatusTable($return);

		if ($html){
			$return = [
				'success' => true,
				'html' => $html,
			];
		}

		return $return;
	}

	private function generateStatusTable($array){
		$html = '';
		$html .= '<table>';
		$html .= '<tr>';
			$html .= '<td>Time</td> <td>API Status</td> <td>ACE Status</td>';
		$html .= '</tr>';
		foreach ($array as $row){
			$html .= '<tr>';
				$html .= '<td>'.$row['time'].'</td> <td>'.$row['api_status_text'].'</td> <td>'.$row['ace_status'].'</td>';
			$html .= '</tr>';
			}
		$html .= '</table>';
		return $html;
	}

	public function getShipmentDetails($app, $params){
		$logisticId = $params['id'];
		$logistic = $this->app->logisticStore()->getById($logisticId);

		$vendorValue = $this->app->tagStore()->getById($logistic->vendorid);
		$return = '';
		if ($logistic->type == Logistic::TYPE_REDEMPTION){
			$redemption = $this->app->redemptionStore()->searchTable()->select()
				->where('id', $logistic->typeid)
				->one();
			$items = json_decode($redemption->items);
			foreach ($items as $x => $item){
				$return .= 'Item '.($x+1).': '.$item->code.', Serial Number ('.$item->serialnumber.')<br>';
				// $item['serialnumber'];
				// $item['weight'];
			}
		}
		if ($logistic->type == Logistic::TYPE_REPLENISHMENT){
			$replenishments = $this->app->replenishmentLogisticStore()->searchTable()->select()
				->where('logisticid', $logistic->id)
				->orderby('id', 'ASC')
				->execute();
			foreach ($replenishments as $x => $replenishmentLogistic){
				$replenishment = $this->app->replenishmentStore()->searchTable()->select()
				->where('id', $replenishmentLogistic->replenishmentid)
				->one();
				$product = $this->app->productStore()->getById($replenishment->productid);
				$return .= 'Item '.($x+1).': '.$product->code.', Serial Number ('.$replenishment->serialno.')<br>';
			}
		}
		if ($logistic->type == Logistic::TYPE_BUYBACK){
			// mib buyback collection
			$buybacks = $this->app->buybackLogisticStore()->searchTable()->select()
				->where('logisticid', $logistic->id)
				->orderby('id', 'ASC')
				->execute();
			foreach ($buybacks as $x => $buybackLogistic){
				$buyback = $this->app->buybackStore()->searchTable()->select()
				->where('id', $buybackLogistic->buybackid)
				->one();
				$items = json_decode($buyback->items);
				foreach ($items as $x => $item){
					$return .= 'Item '.($x+1).': '.$item->code.', Serial Number ('.$item->serialnumber.') - BuybackNo: '.$buyback->buybackno.'<br>';
					// $item['serialnumber'];
					// $item['weight'];
				}
			}
		}
		$info = '';
		$sender_html = '';
		$branch_html = '';
		$delivery_html = '';
		$courier = $this->app->tagStore()->getById($logistic->vendorid);
		if ($logistic->senderid){
			$sender = $this->app->userStore()->getById($logistic->senderid);
			$sender_html = 'Send By: '.$sender->name.' - '.$sender->phone.'<br>';
		}
		if ($logistic->frombranchid || $logistic->tobranchid){
			// $from_branch = $this->getBranchAddress($logistic->frombranchid)
			$branch_html .= '
				Branch: <br>
				From: '.$this->getBranchAddress($logistic->frombranchid).'<br>
				To: '.$this->getBranchAddress($logistic->tobranchid).'<br><br>
			';
		}
		if ($logistic->contactname1){
			$delivery_html .= '
				Delivery Address: '.$this->getLogisticDeliveryAddress($logistic).'
			';
		}else{
			// some direct address from branch
			$delivery_html .= '
				Delivery Address: '.$this->getLogisticDeliveryAddress($logistic).'
			';
		}
		$info .= '
			Shipment: '.$logistic->type.'<br>
			Courier: '.$courier->description.'<br>
			Courier REF: '.($logistic->vendorrefno ? $logistic->vendorrefno : '-').'<br>
			Courier AWB: '.($logistic->awbno ? $logistic->awbno : '-').'<br><br>
			'.$sender_html.'
			'.$branch_html.'
			'.$delivery_html.'
		';

		$html = $info.'<br><br>ITEMS:<br>'.$return;

		// $html = $this->generateStatusTable($return);

		if ($html){
			$return = [
				'success' => true,
				'html' => $html,
			];
		}

		return json_encode($return);
	}

}
