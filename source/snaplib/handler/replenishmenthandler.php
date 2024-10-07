<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////


Namespace Snap\handler;

USe Snap\App;
use Snap\object\buyback;
use Snap\object\replenishment;
use Snap\InputException;
/**
 *
 * @author Ang <ang@silverstream.my>
 * @version 1.0
 * @package  snap.handler
 */
class replenishmentHandler extends CompositeHandler
{
    public function __construct(App $app)
    {
        parent::__construct('/root/mbb', 'replenishment');
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
        $this->app = $app;
        $currentStore = $app->replenishmentStore();  
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
    /*
    function onPreQueryListing($params,$sqlHandle, $records) {        
        $partnerid=$this->app->getUserSession()->getUser()->partnerid;            
        //no all branch permission available        
        $sqlHandle->andWhere('partnerid', $partnerid);
        return array($params, $sqlHandle, $records);   
     }
     */
    
      /*
        This method is to get data for view details
    */
	function detailview($app, $params) {
		$object = $app->replenishmentfactory()->getById($params['id']);

		$partner = $app->partnerFactory()->getById($object->partnerid);


        if($object->statusexporton == '0000-00-00 00:00:00' || !$$object->statusexporton){
			$statusExportOn = '0000-00-00 00:00:00';
		}else {
			$statusExportOn = $object->statusexporton->format('Y-m-d h:i:s');
		}

        if($object->sapresponseon == '0000-00-00 00:00:00' || !$$object->sapresponseon){
			$sapResponseOn = '0000-00-00 00:00:00';
		}else {
			$sapResponseOn = $object->sapresponseon->format('Y-m-d h:i:s');
		}


        $statusExport = "";
        $sapResponseStatus = '';

		if($object->statusexport > 0) $statusExport = "Yes";
		else $statusExport = 'No';
        if($object->sapresponsestatus > 0) $sapResponseStatus = "Yes";
		else $sapResponseStatus = 'No';

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
			$statusname = 'Completed';
		}else if ($object->status == 3){
			$statusname = 'Failed';
		}else if ($object->status == 4){
			$statusname = 'Process Delivery';
		}else if ($object->status == 5){
			$statusname = 'Cancelled';
		}else {
			$statusname = 'Unidentified';
		}


		$detailRecord['default'] = [ //"ID" => $object->id,
								    'Partner' => $partner->name,
                                    'Partner Code' => $partner->code,
			
                                    'Product' => $object->productname,
                                   
                                    'Replenishmnet No' => $object->replenishmentno,
                                    'SAP Code' => $object->sapwhscode,
                                    'SAP Ref No' => $object->saprefno,
                                    //'Type' => $object->type,
                                    //'Product' => $productname,
                                    
                                    'Serial No' => $object->serialno,
                                    
                                    'Branch Code' => $object->branchcode,
                                    'Branch SAP Code' => $object->branchsapcode,
									
                            
                                    'Status Export' => $statusExport,
                                    'Status Export On' => $statusExportOn,

                                    'Sap Reponse Status' => $sapResponseStatus,
                                    'Status Export On' => $sapResponseOn,
                                   
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
        $items = $this->app->buybackStore()->searchTable()->select()->execute();
        $pendingstatuscount=$confirmedstatuscount=$completedstatuscount=$failedstatuscount=$deliverystatuscount=$reversedstatus=0;
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
            $returnAction = $logisticmanager->createLogisticBuyback($buybackobj, $params['vendor'] ?? null, null, null, $params['salespersonid'] ?? null, $params['dateofdelivery'] ?? null);
            if ($returnAction){
                $return['success'] = true;
                return json_encode($return);
            }
        }else{
            throw new \Snap\InputException(gettext("sorry, no permission"), \Snap\InputException::GENERAL_ERROR, 'permission');
        }
    }

    public function getItemsList($app, $params){
        
        $list = $this->app->replenishmentStore()->searchView()->select()->where('status', 1)->execute();

        $e = [];
        foreach ($list as $y => $x){
            $e[$y] = $x->toArray();
        }

        echo json_encode($e);
    }

    public function addBundleLogistic($app, $params){
        // print_r(json_decode($params['data']));exit;
        $data = json_decode($params['data']);

        $ids = [];
        $samebranch = true;
        foreach ($data as $z => $x){
            $init = $data[0]->branchid;
            if ($x->branchid != $init){
                $samebranch = false;
            }
            array_push($ids, $x->id);
        }

        // print_r(new \DateTime('+8 hours'));exit;
        // print_r($ids);exit;
        $R_list = $this->app->replenishmentStore()->searchTable()->select()->where('id', 'IN', $ids)->andWhere('partnerid', 1)->execute();
        
        if ($samebranch == false){
            $return = [
                'error' => true,
                'errorMessage' => 'Items must be in same branch'
            ];
            return json_encode($return);
        }
        // hdl: replenishment
        // action: addBundleLogistic
        // senderid: 6
        // deliverydate: 2020-12-10T00:00:00
        $returnx = $this->app->LogisticManager()->createLogisticReplenishment(1, $R_list, 8, $params['deliverydate'], null, null, $senderid);
        // $partnerId, $replenishmentList, $vendorId, $deliveryDate, $awbNo = null, $vendorRefNo = null, $senderId = null
        if ($returnx){
            $return = [
                'success' => true,
                'message' => $returnx->id
            ];
        }
        return json_encode($return);
    }
}


?>