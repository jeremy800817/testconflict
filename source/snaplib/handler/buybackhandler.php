<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////


Namespace Snap\handler;

USe Snap\App;
use Snap\object\Buyback;
use Snap\InputException;
/**
 *
 * @author Ang <ang@silverstream.my>
 * @version 1.0
 * @package  snap.handler
 */
class buybackHandler extends CompositeHandler
{
    function __construct(App $app)
    {
        parent::__construct('/root/mbb;/root/pos;/root/tekun;/root/koponas;/root/sahabat;', 'buyback');
        $this->mapActionToRights("detailview", "list");
        $this->mapActionToRights("list", "list");
        $this->mapActionToRights("getSummary", "list");        
        $this->mapActionToRights("add", "add");
        $this->mapActionToRights("edit", "edit");
        $this->mapActionToRights("delete", "delete");
        $this->mapActionToRights("freeze", "freeze");
        $this->mapActionToRights("unfreeze", "unfreeze");
        $this->mapActionToRights("isunique", "add");
        $this->mapActionToRights("viewdetail", "viewprofile");
        $this->mapActionToRights("fillform", "edit");
        $this->mapActionToRights("fillform", "add");

        $this->mapActionToRights("doLogistics", "add");
        $this->mapActionToRights("getItemsList", "add");
        $this->mapActionToRights("addBundleLogistic", "add");
        $this->mapActionToRights("exportExcel", "export");

        $this->app = $app;
        $currentStore = $app->buybackStore();  
        $this->currentStore = $currentStore;  
        $this->addChild(new ext6gridhandler($this, $currentStore,1));
    }  
   
    /**
     * This method will determine is this particular handler is able to handle the action given.
     *
     * @param  App    $app    The application object (for getting user session etc to test?)
     * @param  String $action The action name to be handled
     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
     */

    function onPreAddEditCallback($object, $params) {   
        return $object;
    }

    function onPreQueryListing($params,$sqlHandle, $records) {        
        $app = App::getInstance();
        $mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};
        $sqlHandle->andWhere('partnerid', $mbbpartnerid);
        return array($params, $sqlHandle, $fields);  
    }
   
    function onPreListing($objects, $params, $records){
        // print_r($records[1]);

        foreach ($records as $x => $record){
            $printHtml = '';
            $items = json_decode($record['items']);
            foreach ($items as $item){
                if ($item->sapreturnid){
                    $printHtml .= '<tr>';
                    $printHtml .= 	'<td style="text-align:center; width:200px">'.$item->serialnumber.'</td>';
                    $printHtml .= 	'<td style="text-align:center; width:200px">'.intval($item->weight).'</td>';
                    $printHtml .= '</tr>';
                }else{
                    $printHtml .= '<tr>';
                    $printHtml .= 	'<td style="text-align:center; width:200px">'.$item->serialno.'</td>';
                    $printHtml .= 	'<td style="text-align:center; width:200px">'.$item->denomination.'</td>';
                    $printHtml .= '</tr>';
                }
            }
            $records[$x]['child'] = $printHtml;
        }
        return $records;
    }
    	
    /*
        This method is to get data for view details
    */
	function detailview($app, $params) {
		$object = $app->buybackfactory()->getById($params['id']);

		$partner = $app->partnerFactory()->getById($object->partnerid);
		$buyername = $app->partnerFactory()->getById($object->buyerid)->name;
		//$partnername = $app->partnerFactory()->getById($object->partnerid)->name;
		//$partnercode = $app->partnerFactory()->getById($object->partnerid)->code;
        $reconciledbyname = $app->userFactory()->getById($object->reconciledby)->name;
		$salespersonname = $app->userFactory()->getById($object->salespersonid)->name;
		$productname = $app->productFactory()->getById($object->productid)->name;


        if($object->confirmon == '0000-00-00 00:00:00' || !$$object->confirmon){
			$confirmedOn = '0000-00-00 00:00:00';
		}else {
			$confirmedOn = $object->confirmon->format('Y-m-d h:i:s');
		}

        if($object->collectedon == '0000-00-00 00:00:00' || !$$object->collectedon){
			$collectedOn = '0000-00-00 00:00:00';
		}else {
			$collectedOn = $object->collectedon->format('Y-m-d h:i:s');
		}

		if($object->appointmentdatetime == '0000-00-00 00:00:00' || !$$object->appointmentdatetime){
			$appointmentDateTime = '0000-00-00 00:00:00';
		}else {
			$appointmentDateTime = $object->appointmentdatetime->format('Y-m-d h:i:s');
		}

		if($object->appointmenton == '0000-00-00 00:00:00' || !$object->appointmenton){
			$appointmentOn = '0000-00-00 00:00:00';
		}else {
			$appointmentOn = $object->appointmenton->format('Y-m-d h:i:s');
		}

        $isReconciled = "";

		if($object->reconciled > 0) $isReconciled = "Yes";
		else $isReconciled = 'No';

		//$cancelledOn = $object->cancelon ? $object->cancelon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		//$reconciledOn = $object->reconciledon ? $object->reconciledon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		// Status
		if ($object->status == 0){
			$statusname = 'Pending';
		}else if ($object->status == 1){
			$statusname = 'Confirmed';
		}else if ($object->status == 2){
			$statusname = 'Process Collect';
		}else if ($object->status == 3){
			$statusname = 'Completed';
		}else if ($object->status == 4){
			$statusname = 'Failed';
		}else if ($object->status == 5){
			$statusname = 'Reversed';
		}else {
			$statusname = 'Unidentified';
		}

        $items = json_decode($object->items);
    	$serialNo = $items[0]->serialno;
        $denomination = $items[0]->denomination;

	    $confirmedPrice =  $partner->calculator()->round($object->confirmprice);
		$weight = $partner->calculator()->round($object->totalweight);
		$totalEstValue = $partner->calculator()->round($object->amount);
		$buybackFee = $partner->calculator()->round($object->buybackfee);
        $insuranceFee = $partner->calculator()->round($object->insurancefee);
        $handlingFee = $partner->calculator()->round($object->handlingfee);
        $specialDeliveryFee = $partner->calculator()->round($object->specialdeliveryfee);

		$detailRecord['default'] = [ //"ID" => $object->id,
								    'Partner' => $partner->name,
									//'Buyer' => $buyername,
                                    'Partner Reference No' => $object->partnerrefno,
                                    'Buyback No' => $object->buybackno,
                                    'API Version' => $object->apiversion,
                                    //'Type' => $object->type,
                                    //'Product' => $productname,
                                    
                                    'Serial No' => $serialNo,
                                    'Denomination' => $denomination,

                                    'Total Weight' => $weight,
                                    'Total Items' => $object->totalquantity,
                                    'Total Amount' => $totalEstValue,
                                    'Buyback Fee' => $buybackFee,
                                    'Insurance Fee' => $insuranceFee,
                                    'Handling Fee' => $handlingFee,
                                    'Special Delivery Fee' => $specialDeliveryFee,
                                    
                                    'Branch Code' => $object->branchcode,
                                    'Branch SAP Code' => $object->branchsapcode,
                                    'Remarks' => $object->remarks,
									'Brand' => $object->xaubrand,
                                    'Delivery Address 1' => $object->deliveryaddress1,
                                    'Delivery Address 2' => $object->deliveryaddress2,
                                    'Delivery Address 3' => $object->deliveryaddress3,
                                    'Delivery Postcode' => $object->deliverypostcode,
                                    'Delivery State' => $object->deliverystate,

                                    'Delivery Contact Name 1' => $object->deliverycontactname1,
                                    'Delivery No 1' => $object->deliverycontactno1,
                                    'Delivery Contact Name 2' => $object->deliverycontactname2,
                                    'Delivery No 2' => $object->deliverycontactno2,

                                    'Confirmed Price' => $confirmedPrice,
                                    'Confirmed On' => $confirmedOn,
                                    'Collected On' => $collectedOn,

                                    'Reconciled' => $isReconciled,
                                    'Reconciled By'=> $reconciledbyname,
                                    'Reconciled SAP Ref No'=> $object->reconciledsaprefno,
                                    'UUID'=> $object->uuid,
                                   
                                    'Appointment Date' => $appointmentDateTime,
									'Appointment On' => $appointmentOn,

									'Status' => $statusname,
									'Created on' => $object->createdon->format('Y-m-d h:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d h:i:s'),
									'Modified by' => $modifieduser,
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}
   
    function fillform( $app, $params) {		
    }
    function getSummary($app,$params){
        if ($params['partner']){
            if ($params['partner'] == "mib"){
                $partnerid = [$this->app->getConfig()->{"gtp.mib.partner.id"}];
            }
            if ($params['partner'] == "pos"){
                $partnerid = $this->app->getConfig()->{"gtp.mib.partner.id"};
                $partnerid = [
                    $app->getConfig()->{'gtp.pos1.partner.id'},
                    $app->getConfig()->{'gtp.pos2.partner.id'},
                    $app->getConfig()->{'gtp.pos3.partner.id'},
                    $app->getConfig()->{'gtp.pos4.partner.id'}
                ];
            }else if ($params['partner'] == "tekun"){
                $partnerid = [
                    $app->getConfig()->{'gtp.tekun1.partner.id'},
                    $app->getConfig()->{'gtp.tekun2.partner.id'},
                ];
            }else if ($params['partner'] == "koponas"){
                $partnerid = [
                    $app->getConfig()->{'gtp.koponas1.partner.id'},
                    $app->getConfig()->{'gtp.koponas2.partner.id'},
                ];
            }else if ($params['partner'] == "sahabat"){
                $partnerid = [
                    $app->getConfig()->{'gtp.sahabat1.partner.id'},
                    $app->getConfig()->{'gtp.sahabat2.partner.id'},
                ];
            }
        }
        $items = $this->app->buybackStore()->searchTable()->select()->where('partnerid', 'IN', $partnerid)->execute();
        $pendingstatuscount=$confirmedstatuscount=$completedstatuscount=$failedstatuscount=$deliverystatuscount=$reversedstatus=0;
        // _WARINING , please CHANGE to COUNT() SQL Func
        if (count($items) > 0){
            foreach($items as $aItem){     
                if($aItem->status== 0 ){$pendingstatuscount++;};
				if($aItem->status== 1 ){$confirmedstatuscount++;};
				if($aItem->status== 2 ){$deliverystatuscount++;};
				if($aItem->status== 3 ){$completedstatuscount++;};				
				if($aItem->status== 4 ){$failedstatuscount++;};				
				if($aItem->status== 5 ){$reversedstatus++;};
            }
           
        }      
        echo json_encode([ 'success' => true,'pendingstatuscount' => $pendingstatuscount,'confirmedstatuscount' => $confirmedstatuscount,'completedstatuscount' => $completedstatuscount,'failedstatuscount'=>$failedstatuscount,'deliverystatuscount'=>$deliverystatuscount,'reversedstatus'=>$reversedstatus]);  
    }

    public function doLogistics($app,$params){           
        $permission = $this->app->hasPermission('/root/mbb/buyback/edit');      
        if($permission){	
            $buybackobj=$this->app->buybackStore()->getById($params['id']); 
            $logisticmanager=$this->app->logisticManager();       
            //($partnerId, $buybackList, $vendorId, $deliveryDate, $awbNo = null, $vendorRefNo = null, $senderId = null){
            $returnAction = $logisticmanager->createLogisticBuyback($params['partnerid'], $buybackobj, null, null, $params['salespersonid'] ?? null, $params['dateofdelivery'] ?? null);
            if ($returnAction){
                $return['success'] = true;
                return json_encode($return);
            }
        }else{
            throw new \Snap\InputException(gettext("sorry, no permission"), \Snap\InputException::GENERAL_ERROR, 'permission');
        }
    }

    public function getItemsList($app, $params){
        $mibPartnerId = $app->getConfig()->{'gtp.mib.partner.id'};
        $list = $this->app->buybackStore()->searchView()->select()->where('status', Buyback::STATUS_ACTIVE)->andWhere('partnerid', $mibPartnerId)->execute();

        $e = [];
        foreach ($list as $y => $x){
            $e[$y] = $x->toArray();
            $e[$y]['html_list'] = $x->buybackno.' - '.$x->branchname;
        }

        echo json_encode($e);
    }

    public function addBundleLogistic($app, $params){
        // print_r(json_decode($params['data']));exit;
        $data = json_decode($params['data']);

        $ids = [];
        $samebranch = true;

        try{
            foreach ($data as $z => $x){
                $init = $data[0]->branchid;
                if ($x->branchid != $init){
                    $samebranch = false;
                }
                array_push($ids, $x->id);
            }
            
            // print_r(new \DateTime('+8 hours'));exit;
            // print_r($ids);exit;
            $B_list = $this->app->buybackStore()->searchTable()->select()->where('id', 'IN', $ids)->andWhere('partnerid', $params['partnerid'])->execute();
            
            if ($samebranch == false){
                $return = [
                    'error' => true,
                    'errorMessage' => 'Items must be in same branch'
                ];
                return json_encode($return);
            }
            $vendorId = 0;
            if ($params['partnerid'] == $this->app->getConfig()->{'gtp.mib.partner.id'}){
                $aceCourier = $this->app->tagStore()->getByField('value', 'CourAce');
                $vendorId = $aceCourier->id;
            }
            $returnx = $this->app->LogisticManager()->createLogisticBuyback($params['partnerid'], $B_list, $vendorId, $params['deliverydate'], '' , '' ,$params['salesmanid']);
    
            if ($returnx){
                $return = [
                    'success' => true,
                    'message' => $returnx->id
                ];
            }
		}catch(\Exception $e){
			$return['success'] = false;
			$return['message'] = $e->getMessage();
		}

        
        return json_encode($return);
    }

    function exportExcel($app, $params){
        
        //$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};

        // Start Query
        if (isset($params['partnercode']) && 'POS' === $params['partnercode']) {
            $partnerId = [
                $app->getConfig()->{'gtp.pos1.partner.id'},
                $app->getConfig()->{'gtp.pos2.partner.id'},
                $app->getConfig()->{'gtp.pos3.partner.id'},
                $app->getConfig()->{'gtp.pos4.partner.id'}
            ];
            $modulename = 'POS_BUYBACK';

            $conditions = ["partnerid", "IN", $partnerId];

        }else if (isset($params['partnercode']) && 'TEKUN' === $params['partnercode']) {
            $partnerId = [
                $app->getConfig()->{'gtp.tekun1.partner.id'},
                $app->getConfig()->{'gtp.tekun2.partner.id'}
            ];
            $modulename = 'TEKUN_BUYBACK';

            $conditions = ["partnerid", "IN", $partnerId];

        }else if (isset($params['partnercode']) && 'MIB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mib.partner.id'};
            $modulename = 'MIB_BUYBACK';

            $conditions = ['partnerid' => $partnerId];

        }
        else if (isset($params['partner']) && 'TEKUN' === $params['partner']) {
			$partnerId = [
                $app->getConfig()->{'gtp.tekun1.partner.id'},
                $app->getConfig()->{'gtp.tekun2.partner.id'},
            ];
            $modulename = 'TEKUN_BUYBACK';
        }
		else if (isset($params['partner']) && 'KOPONAS' === $params['partner']) {
            $partnerId = [
                $app->getConfig()->{'gtp.koponas1.partner.id'},
                $app->getConfig()->{'gtp.koponas2.partner.id'},
            ];
            $modulename = 'KOPONAS_BUYBACK';
        }
		else if (isset($params['partner']) && 'SAHABAT' === $params['partner']) {
            $partnerId = [
                $app->getConfig()->{'gtp.sahabat1.partner.id'},
                $app->getConfig()->{'gtp.sahabat2.partner.id'},
            ];
            $modulename = 'SAHABAT_BUYBACK';
        }
        
        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);
        

        
        $statusRenderer = [
			0 => "Pending",
			1 => "Confirmed",
			2 => "Process Collect",
			3 => "Completed",
			4 => "Failed",
			5 => "Reversed"
		];

        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null, $statusRenderer);
    }
}