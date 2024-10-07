<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\PartnerService;
use Snap\object\Order;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Azam (azam@@silverstream.my)
 * @version 1.0
 */
class partnerservicehandler extends CompositeHandler
{
    function __construct(App $app)
    {
        // parent::__construct('/root/bmmb/report', 'commission');

        $this->mapActionToRights('list', '/root/gtp/partnerlimits;');
        $this->mapActionToRights('exportExcel', '/root/gtp/partnerlimits;');

        $this->app = $app;

        $this->currentStore = $app->partnerStore()->getRelatedStore('services');
        // $this->currentStore = $app->mykycreminderStore();   $partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $params['id'])->execute();
        $this->addChild(new ext6gridhandler($this, $this->currentStore, 1));
    }

    function onPreQueryListing($params, $sqlHandle, $fields){
            $app = App::getInstance();
   

        return array($params, $sqlHandle, $fields);
    }

   
    /**
	* function to massage data before listing
	**/
	function onPreListing($objects, $params, $records) {

		$app = App::getInstance();
  
		foreach ($records as $key => $record) {
            $partner = $this->app->partnerStore()->getById($records[$key]['partnerid']);
            $product = $this->app->productStore()->getById($records[$key]['productid']);
            
            // Check if have product
            if($product){
              
                $buyamount = $this->app->spotorderManager()->getTotalTransactionWeight($partner, $product, 'CompanySell');
                $sellamount = $this->app->spotorderManager()->getTotalTransactionWeight($partner, $product, 'CompanyBuy');
    
                $buybalance = $records[$key]['dailybuylimitxau'] - $buyamount;
                $sellbalance = $records[$key]['dailyselllimitxau'] - $sellamount;
                // Check if there is response
                // if($return == 1){
                //     $isRunning = 1;
                // }else {
                //     $isRunning = 0;
                // }
    
                //$records[$key]['isrunning'] = $priceprovidermanager->isPriceCollectorRunning($priceproviderobj);
                $records[$key]['buybalance'] = $buybalance;
                $records[$key]['sellbalance'] = $sellbalance;
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

    function exportExcel($app, $params){
		
        $modulename = 'PRODUCT_LIMITS';
        // Start Query
        if (isset($params['partner'])) {
            // $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $modulename = $params['partner'];
        }

        
        $header = json_decode($params["header"]);
        $addPostQueryHeaders = json_decode($params["additionalheader"]);
        $dateRange = json_decode($params["daterange"]);


        $prefix = $this->currentStore->getColumnPrefix();
        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if('createdon' === $column->index){
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "DATE(`{$prefix}createdon`) as `{$prefix}createdon`"
                );
                $header[$key]->index->original = $original;
            }
            // Bool Section
            if ('canbuy' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}canbuy` = " . PartnerService::OPTION_NO . " THEN 'No'
                     WHEN `{$prefix}canbuy` = " . PartnerService::OPTION_YES . " THEN 'Yes' END as `{$prefix}canbuy`"
                );
                $header[$key]->index->original = $original;
            }
            if ('cansell' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}cansell` = " . PartnerService::OPTION_NO . " THEN 'No'
                     WHEN `{$prefix}cansell` = " . PartnerService::OPTION_YES . " THEN 'Yes' END as `{$prefix}cansell`"
                );
                $header[$key]->index->original = $original;
            }
            if ('canqueue' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}canqueue` = " . PartnerService::OPTION_NO . " THEN 'No'
                     WHEN `{$prefix}canqueue` = " . PartnerService::OPTION_YES . " THEN 'Yes' END as `{$prefix}canqueue`"
                );
                $header[$key]->index->original = $original;
            }
            if ('canredeem' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}canredeem` = " . PartnerService::OPTION_NO . " THEN 'No'
                     WHEN `{$prefix}canredeem` = " . PartnerService::OPTION_YES . " THEN 'Yes' END as `{$prefix}canredeem`"
                );
                $header[$key]->index->original = $original;
            }
            if ('includefeeinprice' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}includefeeinprice` = " . PartnerService::OPTION_NO . " THEN 'No'
                     WHEN `{$prefix}includefeeinprice` = " . PartnerService::OPTION_YES . " THEN 'Yes' END as `{$prefix}includefeeinprice`"
                );
                $header[$key]->index->original = $original;
            }
            // End Bools
            if ('status' === $column->index) {
					
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}status` = " . PartnerService::OPTION_NO . " THEN 'Inactive'
                     WHEN `{$prefix}status` = " . PartnerService::OPTION_YES . " THEN 'Active' END as `{$prefix}status`"
                );
                $header[$key]->index->original = $original;
            }
            if ('specialpricetype' === $column->index) {
					
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}specialpricetype` = " . PartnerService::SPECIALTYPE_NONE . " THEN 'None'
                     WHEN `{$prefix}specialpricetype` = " . PartnerService::SPECIALTYPE_AMOUNT . " THEN 'Amount'
                     WHEN `{$prefix}specialpricetype` = " . PartnerService::SPECIALTYPE_GRAM . " THEN 'Gram' END as `{$prefix}specialpricetype`"
                );
                $header[$key]->index->original = $original;
            }
            // if ('buybalance' === $column->index) {
					
            //     $header[$key]->index = $this->currentStore->searchTable(false)->raw(
            //         "SUM(`{$prefix}dailybuylimitxau`-`{$prefix}dailybuylimitxau`) as `{$prefix}buybalance`"
            //     );
            //     $header[$key]->index->original = $original;
            // }
            // if ('sellbalance' === $column->index) {
					
            //     $header[$key]->index = $this->currentStore->searchTable(false)->raw(
            //         "SUM({$prefix}dailyselllimitxau-{$prefix}dailyselllimitxau) as {$prefix}sellbalance`"
            //     );
            //     $header[$key]->index->original = $original;
            // }
        }

        $resultCallback = function ($records) use ($app, $params) {
            $newdatas = [];
            foreach ($records as $key => $record) {

                $partnercode = array_map(function ($parService) {
                    return $parService->partnercode;
                }, $records);

                $productname = array_map(function ($parService) {
                    return $parService->productname;
                }, $records);

                if(isset($partnercode[$key])){
                    $partner = $app->partnerStore()->searchView()->select()->where('code', $partnercode[$key])->one();
                }
                if(isset($productname[$key])){
                    $product = $app->productStore()->searchView()->select()->where('name', $productname[$key])->one();
                }
               
                
                // Check if have product
                if($product){
                  
                    $buyamount = $app->spotorderManager()->getTotalTransactionWeight($partner, $product, 'CompanySell');
                    $sellamount = $app->spotorderManager()->getTotalTransactionWeight($partner, $product, 'CompanyBuy');
        
                    $buybalance = $record->dailybuylimitxau - $buyamount;
                    $sellbalance = $record->dailyselllimitxau - $sellamount;
                    // Check if there is response
                    // if($return == 1){
                    //     $isRunning = 1;
                    // }else {
                    //     $isRunning = 0;
                    // }
        
                    //$records[$key]['isrunning'] = $priceprovidermanager->isPriceCollectorRunning($priceproviderobj);
                    $newdatas[$key]["buybalance"] = $buybalance;
                    $newdatas[$key]["sellbalance"] = $sellbalance;
                    // $record->addChild("sellbalance", $sellbalance);

                    // do process
                    // $store = $this->app->partnerStore()->getRelatedStore('services');
                    // $prefix = $store->getColumnPrefix();
                    // $handler = $store->searchView(true, 1);
                    // $handler = $handler->select([
                    //     $handler->raw("SUM({$prefix}dailybuylimitxau-{$buyamount}) as {$prefix}buybalance"),
                    //     $handler->raw("SUM({$prefix}dailyselllimitxau-{$sellamount}) as {$prefix}sellbalance")
                    // ]);

                    // $records[$index]['buybalance'] = $ledger->amountbalance;
                    // $records[$index]['sellbalance'] = $ledger->xaubalance;
                }
              
    
            }
    
            // $store = $this->app->myledgerStore();
            // $prefix = $store->getColumnPrefix();
            // $handler = $store->searchView(true, 1);
            // $handler = $handler->select([
            //     'achaccountholdercode',
            //     $handler->raw("SUM({$prefix}dailybuylimitxau-{$buyamount}) as {$prefix}buybalance"),
            //     $handler->raw("SUM({$prefix}amountin-{$prefix}amountout) as {$prefix}amountbalance")
            // ]);
            // // $handler->whereIn('achaccountholdercode', $accHolderCodes);
            // $handler->where('achaccountholdercode','IN', $accHolderCodes);
            // $handler->groupBy(['accountholderid']);
    
            // if (isset($params['monthend'])) {
            //     $monthEnd = new \DateTime($params['monthend'], $this->app->getUserTimezone());
            //     $monthEnd = \Snap\common::convertUserDatetimeToUTC($monthEnd);
            //     $handler->where('transactiondate', '>=', '2020-01-01 00:00:00');
            //     $handler->where('transactiondate', '<=', $monthEnd->format('Y-m-d H:i:s'));
            // }
            // $handler->where('status', MyLedger::STATUS_ACTIVE);
            // $balances = $handler->execute();
    
            // $ledgers = [];
            // foreach($balances as $balance) {
            //     $ledgers[$balance->achaccountholdercode] = $balance;
            // }

            // foreach($records as $index => $record) {
            //     $ledger = $ledgers[$records[$index]->accountholdercode];
            //     $records[$index] = $record->toArray();
            //     $records[$index]['amountbalance'] = $ledger->amountbalance;
            //     $records[$index]['xaubalance'] = $ledger->xaubalance;
            //     $records[$index] = $app->myaccountholderStore()->create($records[$index]);
            // }

            return $newdatas;
        };

        // $this->app->reportingManager()->generateAccountReport($this->currentStore, $header, $modulename, null, null, $resultCallback);
        $this->app->reportingManager()->generateExportFileRelatedStore($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', null, null, null, null, $resultCallback, $addPostQueryHeaders);
    }
}
