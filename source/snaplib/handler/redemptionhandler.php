<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////


Namespace Snap\handler;

USe Snap\App;
use Snap\object\Redemption;
use Snap\InputException;
/**
 *
 * @author Shahanas <shahanas@silverstream.my>
 * @version 1.0
 * @package  snap.handler
 */
class redemptionHandler extends CompositeHandler
{
    public function __construct(App $app)
    {
        parent::__construct('/root/mbb;/root/bmmb;/root/bsn;/root/alrajhi;/root/posarrahnu;/root/bursa;', 'redemption');

        $this->mapActionToRights('detailview', 'list');

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

        $this->mapActionToRights("exportExcel", "export");

        $this->mapActionToRights("doLogistics", "add");
        
        $this->app = $app;
        $currentStore = $app->redemptionStore();  
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

    function onPreQueryListing($params, $sqlHandle, $records) {        

        if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
        }   else{
            $partnerid = $this->app->getConfig()->{'gtp.mib.partner.id'}; 
        }
        $sqlHandle->andWhere('partnerid', $partnerid);
        return array($params, $sqlHandle, $records);   
    }

    function onPreListing($objects, $params, $records){
        // print_r($records[1]);

        foreach ($records as $x => $record){
            $printHtml = '';
            $items = json_decode($record['items']);
            foreach ($items as $item){
                $printHtml .= '<tr>';
                $printHtml .= 	'<td style="text-align:center; width:200px">'.$item->serialnumber.'</td>';
                $printHtml .= 	'<td style="text-align:center; width:200px">'.$item->code.'</td>';
                $printHtml .= '</tr>';
            }
            $records[$x]['child'] = $printHtml;
        }
        return $records;
    }
   
    /*
        This method is to get data for view details
    */
	function detailview($app, $params) {
		$object = $app->redemptionfactory()->getById($params['id']);

		$partner = $app->partnerFactory()->getById($object->partnerid);
		$buyername = $app->partnerFactory()->getById($object->buyerid)->name;
		//$partnername = $app->partnerFactory()->getById($object->partnerid)->name;
		//$partnercode = $app->partnerFactory()->getById($object->partnerid)->code;
		$salespersonname = $app->userFactory()->getById($object->salespersonid)->name;
		$productname = $app->productFactory()->getById($object->productid)->name;

        if($object->bookingon == '0000-00-00 00:00:00' || !$$object->bookingon){
			$bookingOn = '0000-00-00 00:00:00';
		}else {
			$bookingOn = $object->bookingon->format('Y-m-d H:i:s');
		}

        if($object->confirmon == '0000-00-00 00:00:00' || !$$object->confirmon){
			$confirmedOn = '0000-00-00 00:00:00';
		}else {
			$confirmedOn = $object->confirmon->format('Y-m-d H:i:s');
		}

		if($object->appointmentdatetime == '0000-00-00 00:00:00' || !$$object->appointmentdatetime){
			$appointmentDateTime = '0000-00-00 00:00:00';
		}else {
			$appointmentDateTime = $object->appointmentdatetime->format('Y-m-d H:i:s');
		}

		if($object->appointmenton == '0000-00-00 00:00:00' || !$object->appointmenton){
			$appointmentOn = '0000-00-00 00:00:00';
		}else {
			$appointmentOn = $object->appointmenton->format('Y-m-d H:i:s');
		}
		//$cancelledOn = $object->cancelon ? $object->cancelon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		//$reconciledOn = $object->reconciledon ? $object->reconciledon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

        if($object->confirmby > 0) $confirmeduser = $app->userFactory()->getById($object->confirmby)->name;
		else $confirmeduser = 'System';
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
		}else if ($object->status == 6){
			$statusname = 'Reversed';
		}else if ($object->status == 7){
			$statusname = 'Failed Delivery';
		}else if ($object->status == 8){
			$statusname = 'Success';
		}else {
			$statusname = 'Unidentified';
		}

        $items = json_decode($object->items);
    	$serialNo = $items[0]->serialnumber;
        $code = $items[0]->code;

		$bookingPrice =  $partner->calculator()->round($object->bookingprice);
        $confirmedPrice =  $partner->calculator()->round($object->confirmprice);
		$weight = $partner->calculator(false)->round($object->totalweight);
		$totalEstValue = $partner->calculator()->round($object->amount);
		$redemptionFee = $partner->calculator()->round($object->redemptionfee);
        $insuranceFee = $partner->calculator()->round($object->insurancefee);
        $handlingFee = $partner->calculator()->round($object->handlingfee);
        $specialDeliveryFee = $partner->calculator()->round($object->specialdeliveryfee);

		$detailRecord['default'] = [ //"ID" => $object->id,
								    'Partner' => $partner->name,
									//'Buyer' => $buyername,
                                    'Partner Reference No' => $object->partnerrefno,
                                    'Redemption No' => $object->redemptionno,
                                    'API Version' => $object->apiversion,
                                    'Type' => $object->type,
                                    //'Product' => $productname,
                                    
                                    'Serial No' => $serialNo,
                                    'Gold Code' => $code,

                                    'Price' => $finalAcePrice,
                                    'Total Weight' => $weight,
                                    'Total Amount' => $totalEstValue,
                                    'Total Items' => $object->totalquantity,
                                    
                                    'Redemption Fee' => $redemptionFee,
                                    'Insurance Fee' => $insuranceFee,
                                    'Handling Fee' => $handlingFee,
                                    'Special Delivery Fee' => $specialDeliveryFee,
                                    
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
                                   
                                    'Booking On' => $bookingOn,
                                    'Booking Price' => $bookingPrice,
                                    'Confirmed On' => $confirmedOn,
                                    'Confirmed Price' => $confirmedPrice,
                                    'Confirmed By' => $confirmeduser,

                                    'Appointment Date' => $appointmentDateTime,
									'Appointment On' => $appointmentOn,

									'Status' => $statusname,
									'Created on' => $object->createdon->format('Y-m-d H:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d H:i:s'),
									'Modified by' => $modifieduser,
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}
    
    function fillform( $app, $params) {		
    }
    function getSummary($app,$params){

        // Get User Partner Id
        // $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;


        // Check for the partner of vault

        if($params['origintype']){

            if('bmmb' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.bmmb.partner.id'};   
            } else if('go' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.go.partner.id'};   
            } else if('one' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.one.partner.id'};   
            }
            //added on 13/12/2021
            else if('onecall' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.onecall.partner.id'};   
            }
            else if('air' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.air.partner.id'};   
            }
            else if('mcash' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.mcash.partner.id'};   
            }
            else if('nubex' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.nubex.partner.id'};   
            }
            else if('toyyib' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.toyyib.partner.id'};   
            }
            else if('hope' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.hope.partner.id'};   
            }
            else if('mbsb' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.mbsb.partner.id'};   
            }
            else if('red' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.red.partner.id'};   
            }
            else if('wavpay' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.wavpay.partner.id'};   
            }
            else if('noor' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.noor.partner.id'};   
            }
            else if('bursa' == $params['origintype']){
                $partnerid=$this->app->getConfig()->{'gtp.bursa.partner.id'};   
            }
            // KOPERASI MODULES
            else if('ktp' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            }
            else if('kopetro' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            }
            else if('kopttr' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            }
            else if('pkbaffi' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
                $custom = true;
            }
            else if('bumira' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            }
            else if('kodimas' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            }
            else if('kgoldaffi' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
                $custom = true;
            }
            else if('koponas' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            }
            else if('waqaf' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            }
            // OTC MODULES
            else if('bsn' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
            }
            else if('alrajhi' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
            }
            else if('posarrahnu' == $params['origintype']){
                $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'}; 
            }
       

            // Search by status if its mib
            // Search by rdm status if its bmmb conversion
            $items = $this->app->myconversionStore()->searchView()->select()->where('rdmpartnerid', $partnerid)->execute();
            $pendingstatuscount=$confirmedstatuscount=$completedstatuscount=$failedstatuscount=$deliverystatuscount=$cancelledstatus=$reversedstatus=$redemptionfaileddeliverycount=0;
            if (count($items) > 0){
                foreach($items as $aItem){     
                    if($aItem->rdmstatus== 0 ){$pendingstatuscount++;};
                    if($aItem->rdmstatus== 1 ){$confirmedstatuscount++;};
                    if($aItem->rdmstatus== 2 ){$completedstatuscount++;};
                    if($aItem->rdmstatus== 3 ){$failedstatuscount++;};				
                    if($aItem->rdmstatus== 4 ){$deliverystatuscount++;};				
                    if($aItem->rdmstatus== 5 ){$cancelledstatus++;};
                    if($aItem->rdmstatus== 6 ){$reversedstatus++;};
                    if($aItem->rdmstatus== 7 ){$redemptionfaileddeliverycount++;};
                }
            
            }      
            
        } else if (!$params['origintype']){
            // Default type = mib
            $partnerid=$this->app->getConfig()->{'gtp.mib.partner.id'};   

            // Search by status if its mib
            // Search by rdm status if its bmmb conversion
            $items = $this->app->redemptionStore()->searchTable()->select()->where('partnerid', $partnerid)->execute();
            $pendingstatuscount=$confirmedstatuscount=$completedstatuscount=$failedstatuscount=$deliverystatuscount=$cancelledstatus=$reversedstatus=$redemptionfaileddeliverycount=0;
            if (count($items) > 0){
                foreach($items as $aItem){     
                    if($aItem->status== 0 ){$pendingstatuscount++;};
                    if($aItem->status== 1 ){$confirmedstatuscount++;};
                    if($aItem->status== 2 ){$completedstatuscount++;};
                    if($aItem->status== 3 ){$failedstatuscount++;};				
                    if($aItem->status== 4 ){$deliverystatuscount++;};				
                    if($aItem->status== 5 ){$cancelledstatus++;};
                    if($aItem->status== 6 ){$reversedstatus++;};
                    if($aItem->status== 7 ){$redemptionfaileddeliverycount++;};
                }
            
            }      
            
        } else {

            throw new \Exception("Partner id does not exists");

        }

        
        echo json_encode([ 'success' => true,'pendingstatuscount' => $pendingstatuscount,'confirmedstatuscount' => $confirmedstatuscount,'completedstatuscount' => $completedstatuscount,'failedstatuscount'=>$failedstatuscount,'deliverystatuscount'=>$deliverystatuscount,'cancelledstatus'=>$cancelledstatus, 'reversedstatus' => $reversedstatus, 'redemptionfaileddeliverycount'=> $redemptionfaileddeliverycount]);  
    }

    public function doLogistics($app,$params){           
        // $permission = $this->app->hasPermission('/root/mbb/redemption/edit'); 
        $permission = $this->app->hasPermission('/all/access');           
        if($permission){	
            $redemptionobj=$this->app->redemptionStore()->getById($params['redemptionid']); 
            $logisticmanager=$this->app->logisticManager();       
            $returnAction = $logisticmanager->createLogisticRedemption($redemptionobj, $params['vendor'] ?? null, null, null, $params['salespersonid'] ?? null, $params['dateofdelivery'] ?? null);
            if ($returnAction){
                $return['success'] = true;
                return json_encode($return);
            }
        }else{
            throw new \Snap\InputException(gettext("sorry, no permission"), \Snap\InputException::GENERAL_ERROR, 'permission');
        }
    }
    function exportExcel($app, $params){
        
        if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $mbbpartnerid = $app->getConfig()->{'gtp.bursa.partner.id'};
            $modulename = 'BURSA_REDEMPTION';
        }else if (isset($params['partnercode']) && 'MIB' === $params['partnercode']){
            $mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};
            $modulename = 'MIB_REDEMPTION';
        }
        

        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);
        
    

        $conditions = ['partnerid' => $mbbpartnerid];

        $prefix = $this->currentStore->getColumnPrefix();
        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if ('status' === $column->index) {
					
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}status` = " . Redemption::STATUS_PENDING . " THEN 'Pending'
                     WHEN `{$prefix}status` = " . Redemption::STATUS_CONFIRMED . " THEN 'Confirmed'
                     WHEN `{$prefix}status` = " . Redemption::STATUS_COMPLETED . " THEN 'Completed'
                     WHEN `{$prefix}status` = " . Redemption::STATUS_FAILED . " THEN 'Failed'
                     WHEN `{$prefix}status` = " . Redemption::STATUS_PROCESSDELIVERY . " THEN 'Process Delivery'
                     WHEN `{$prefix}status` = " . Redemption::STATUS_CANCELLED . " THEN 'Cancelled'
                     WHEN `{$prefix}status` = " . Redemption::STATUS_REVERSED . " THEN 'Reversed'
                     WHEN `{$prefix}status` = " . Redemption::STATUS_FAILEDDELIVERY . " THEN 'Failed Delivery'
                     WHEN `{$prefix}status` = " . Redemption::STATUS_SUCCESS . " THEN 'Success' END as `{$prefix}status`"
                );
                $header[$key]->index->original = $original;
            }

            if ('code' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "JSON_EXTRACT(`{$prefix}items`, '$[*].code')  as `{$prefix}code`"
                );
                $header[$key]->index->original = $original;
            }
            if ('serialnumber' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "JSON_EXTRACT(`{$prefix}items`, '$[*].serialnumber')  as `{$prefix}serialnumber`"
                );
                $header[$key]->index->original = $original;
            }
        }

        $specialRenderer = [
            'decode' => 'json',
            'sqlfield' => 'items',
            'displayfield' => ['sapreturnid','serialnumber', 'code'],
            'separatefield' => ['serialnumber', 'code', 'branchid', 'branchname','status','type','createdon'],
            'isdisplayedinreport' => false
        ];

        $statusRenderer = [
			0 => "Pending",
			1 => "Confirmed",
			2 => "Completed",
			3 => "Failed",
			4 => "Process Delivery",
			5 => "Cancelled",
			6 => "Reversed",
			7 => "Failed Delivery",
            8 => "Success"
		];

        // $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, $specialRenderer, null, $statusRenderer);
        $this->app->reportingManager()->generateExportFileWithJsonFields($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, $specialRenderer);
    }
}
?>