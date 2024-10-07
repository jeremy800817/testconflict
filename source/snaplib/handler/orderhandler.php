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

use Spipu\Html2Pdf\Html2Pdf;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class orderHandler extends CompositeHandler {
	function __construct(App $app) {
		
		//parent::__construct('/root/gtp;/root/mbb;/root/bmmb;/root/go;/root/one;', 'order');
		$this->mapActionToRights('detailview', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');

		$this->mapActionToRights('detailviewmobile', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');

		$this->mapActionToRights('doSpotOrder', '/root/gtp/order/add;/root/mbb/order/add;/root/bmmb/order/add;/root/go/order/add;/root/one/order/add;/root/onecall/order/add;/root/air/order/add;/root/mcash/order/add;/root/ktp/order/add;/root/nubex/order/add;/root/bsn/order/add;/root/alrajhi/order/add;/root/posarrahnu/order/add;');

		$this->mapActionToRights('doFutureOrder', '/root/gtp/order/add;/root/mbb/order/add;/root/bmmb/order/add;/root/go/order/add;/root/one/order/add;/root/onecall/order/add;/root/air/order/add;/root/mcash/order/add;/root/ktp/order/add;/root/nubex/order/add;/root/bsn/order/add;/root/alrajhi/order/add;/root/posarrahnu/order/add;');

		$this->mapActionToRights('doSpotOrderSpecial', '/root/gtp/order/add;/root/mbb/order/add;/root/bmmb/order/add;/root/go/order/add;/root/one/order/add;/root/onecall/order/add;/root/air/order/add;/root/mcash/order/add;/root/ktp/order/add;/root/nubex/order/add;/root/bsn/order/add;/root/alrajhi/order/add;/root/posarrahnu/order/add;');
		
		$this->mapActionToRights('getOrderStatements', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');

		$this->mapActionToRights('getOrderStatementsForCustomer', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');

		$this->mapActionToRights('getUnfulfilledStatements', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');

		$this->mapActionToRights('getUnfulfilledStatementsForCustomer', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');

		$this->mapActionToRights('sendToSap', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');

		
		$this->mapActionToRights('printSpotOrder', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');

		$this->mapActionToRights('printFutureOrder', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');

		$this->mapActionToRights('cancelOrder', '/root/gtp/order/cancel;/root/bmmb/order/cancel;/root/go/order/cancel;/root/one/order/cancel;/root/onecall/order/cancel;/root/air/order/cancel;/root/mcash/order/cancel;/root/ktp/order/cancel;/root/nubex/order/cancel;/root/bsn/order/cancel;/root/alrajhi/order/cancel;/root/posarrahnu/order/cancel;');

		//$this->mapActionToRights('isHideSendToSap', '/root/gtp/order/cancel');
												 

		$this->mapActionToRights('fillSapForm', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');
		/*
		$this->mapActionToRights('addToOrder', 'add');
		$this->mapActionToRights('approveOrder', 'approve');
		$this->mapActionToRights('rejectOrder', 'approve');
		$this->mapActionToRights('deliverOrder', 'edit');
		$this->mapActionToRights('completedOrders', 'edit'); */

		$this->mapActionToRights('list', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');

		$this->mapActionToRights('exportZip', '/root/gtp/order/list;/root/bmmb/order/list;/root/go/order/list;/root/one/order/list;/root/onecall/order/list;/root/air/order/list;/root/mcash/order/list;/root/ktp/order/list;/root/nubex/order/list;/root/bsn/order/list;/root/alrajhi/order/list;/root/posarrahnu/order/list;');

		$this->app = $app;

		$orderStore = $app->orderFactory();
		
		$this->addChild(new ext6gridhandler($this, $orderStore, 1));
	}
	function getOrderStatements($app, $params){		
		$partnerid=$this->app->getUserSession()->getUser()->partnerid;
		$partner = $app->partnerStore()->getById($partnerid);
		$beforefromdate = date("Y-m-d", $params['summaryfromdate']/1000).' 00:00:00';
		$fromdate = date("Y-m-d", strtotime($beforefromdate.  ' + 1 days')).' 00:00:00'; 
		$beforetodate = date("Y-m-d", $params['summarytodate']/1000).' 00:00:00';
		$todate = date("Y-m-d", strtotime($beforetodate.  ' + 1 days')).' 00:00:00'; 
		$buysellcode= $params['summarytype']==1?$partner->sapcompanybuycode1:$partner->sapcompanysellcode1;		
		$param = array(
			"code"  =>	$buysellcode,
			"startDate" => $fromdate,			
			"endDate"  => $todate
			);
		$version = "1.0";		
		$apimanager=$this->app->apiManager();	
		try{
			$content=$apimanager->sapGetStatement($version,$param);							
			header('Content-Type: application/pdf');
			header('Content-Length: '.strlen($content));
			header('Content-disposition: inline; filename="summary.pdf"');
			header('Cache-Control: public, must-revalidate, max-age=0');
			header('Pragma: public');			
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			// echo content[0];
			echo $content;
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
		
	}
	function getUnfulfilledStatements($app, $params){		
		$partnerid=$this->app->getUserSession()->getUser()->partnerid;
		$partner = $app->partnerStore()->getById($partnerid);				
		$buycode= $partner->sapcompanybuycode1;				
		$apimanager=$this->app->apiManager();	
		try{
			$result=$apimanager->sapGetOpenPo("1.0", true, $buycode);				
			$resultwithnewkey["records"]=$result['opnlist'];			
			//echo json_encode($resultwithnewkey);	
			$limit= $params['start']+$params['limit'];		
			$page = $params['page']; 
			$total = count($resultwithnewkey["records"]);   
			$limit = 50; 
			$totalPages = ceil( $total/ $limit );
			$page = max($page, 1);			
			$offset = ($page - 1) * $limit;
			if( $offset < 0 ) $offset = 0;
			$filteredrecords['records'] = array_slice($resultwithnewkey['records'], $offset,$limit);			
			if(isset($params['sort'])){
				$sortparams=json_decode($params['sort']);							
				$property=$sortparams[0]->property;
				$direction=$sortparams[0]->direction;					
				$column = array_column($filteredrecords['records'], $property);
				if($direction=='DESC'){
					array_multisort($column, SORT_DESC, $filteredrecords['records']);
				}else{
					array_multisort($column,SORT_ASC , $filteredrecords['records']);
				}
			}			
			$filteredrecords['totalRecords']=$total;
			echo json_encode($filteredrecords);	
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
	}

	function doSpotOrder($app, $params){   
        if($this->app->hasPermission('/root/gtp/order/add')){             
            $partnerid=$this->app->getUserSession()->getUser()->partnerid;
            $partner = $app->partnerStore()->getById($partnerid);  
            $product=$this->app->productStore()->getById($params['productitem']);           
            $spotordermanager=$this->app->spotorderManager(); 
            //$hash = hash('sha256', $partnerid+time());    
			//$partnerrefno=$partnerid+time(); 
			$partnerrefno=strval($partnerid).time(); 
            $apiversion="MANUAL"; 
            $transactiontype=($params['sellorbuy']=='sell')?\Snap\object\Order::TYPE_COMPANYSELL:\Snap\object\Order::TYPE_COMPANYBUY;         
            $uuid=$params['uuid'];
            $futureorderrefno=NULL;
			$ordertype=($params['amount'] != '' && $params['amount'] !=  is_null()) ? 'amount' : (($params['weight'] != '' && $params['weight'] !=  is_null()) ? 'weight' : NULL);
            $ordervalue=($ordertype=='amount')?$params['amount']:(($ordertype=='weight')?$params['weight']:NULL);
            $lockedinprice=$params['sellorbuy']=='sell'?$params['sellprice']:($params['sellorbuy']=='buy'?$params['buyprice']:'');
            $ordertotal="";
            $notifyurl="";
            $reference=NULL;
			$timestamp=date('Y-m-d H:i:s');  
            try{
                $returns= $spotordermanager->bookOrder($partner, $apiversion, $partnerrefno, $transactiontype, $product, $uuid, $futureorderrefno, $ordertype, $ordervalue, $lockedinprice, $ordertotal, $notifyurl, $reference, $timestamp);
				// Use toArray()
				// $finalAcePrice =  $partner->calculator()->round($returns->price);
				// $weight = $partner->calculator(false)->round($returns->xau);
				// $totalEstValue = $partner->calculator()->round($returns->amount);
				// $orderFee = $partner->calculator()->round($returns->fee);

			   
				// GTP SETTINGS 3 DP
				$finalAcePrice = number_format($returns->price,3);
				$weight = number_format($returns->xau,3);
				$totalEstValue = number_format($returns->amount,3);
				$orderFee = number_format($returns->fee,3);

				$finalAcePrice = $finalAcePrice + $orderFee;
				
				$results[] = array(	'id' => $returns->id, 
									'partnerid' => $returns->partnerid, 
									'partnerrefid' => $returns->partnerrefid, 
									'orderno' => $returns->orderno, 
									'pricestreamid' => $returns->pricestreamid, 
									'salespersonid' => $returns->salespersonid,
									'apiversion' => $returns->apiversion, 
									'type' => $returns->type, 
									'productid' => $returns->productid,
									'isspot' => $returns->isspot,
									'price' => $finalAcePrice,
									'byweight' => $returns->byweight,
									'xau' => $weight,
									'amount' => $totalEstValue,
									'fee' => $orderFee,
									'remarks' => $returns->remarks,
									'bookingon' => $returns->bookingon,
									'bookingprice' => $returns->bookingprice,
									'bookingpricestreamid' => $returns->bookingpricestreamid,
									'notifyurl' => $returns->notifyurl,
									'reconciled' => $returns->reconciled,
									'status' => $returns->status, );

				echo json_encode(['success' => true, 'return' => $results]);
            }catch(\Exception $e){
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
            }    
        }else{
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No Permission']);
        }
	}
	
	function doFutureOrder($app, $params){   
        if($this->app->hasPermission('/root/gtp/order/add')){     
            $partnerid=$this->app->getUserSession()->getUser()->partnerid;
            $partner = $app->partnerStore()->getById($partnerid);  
            $product=$this->app->productStore()->getById($params['foproductitem']); 
            $apiversion="MANUAL"; 
			//$futureRefid=$partnerrefno=$partnerid+time();
			$futureRefid=strval($partnerid).time();
            $trxType=($params['fosellprice']!=is_null())?\Snap\object\Order::TYPE_COMPANYSELL:($params['fobuyprice']!=is_null()?\Snap\object\Order::TYPE_COMPANYBUY:null);  
            $orderType=$params['foamount'] != is_null() ? 'amount' : ($params['foweight'] != is_null() ? 'weight' : NULL);            
            $orderValue=($orderType=='amount')?$params['foamount']:(($orderType=='weight')?$params['foweight']:NULL);            
            $expectedMatchingPrice=($params['fosellprice']!=is_null())?$params['fosellprice']:($params['fobuyprice']!=is_null()?$params['fobuyprice']:null);
            $goodTillDate=null;
            $notifyUrl="";
            $matchNotifyUrl="";
            $reference=null;
			$timeStamp=date('Y-m-d H:i:s'); 
			$futureordermanager=$this->app->futureorderManager();
            try{
                $return = $futureordermanager->createFutureOrder($partner, $apiVersion, $futureRefid, $trxType, $product, $orderType, $orderValue, 
                        $expectedMatchingPrice, $goodTillDate, $notifyUrl, $matchNotifyUrl, $reference, $timeStamp);
                $return = $futureordermanager->confirmCreateFutureOrder($return);
				echo json_encode(['success' => true, 'return' => $return->id]);
            }catch(\Exception $e){
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
            }
           
        }else{
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No Permission']);            
        }
	}

	function doSpotOrderSpecial($app, $params){   
        if($this->app->hasPermission('/root/gtp/order/add')){       
			$user=$this->app->userStore()->getById($params['customerid']);      			
			$partnerid=$params['partnerid'];
            $partner = $app->partnerStore()->getById($partnerid);  
            $product=$this->app->productStore()->getById($params['productitem']);           
            $spotordermanager=$this->app->spotorderManager(); 
            //$hash = hash('sha256', $partnerid+time());    
			//$partnerrefno=$partnerid+time(); 
			$partnerrefno=($params['reference']) ? $params['reference'] : strval($partnerid).time(); 
            $apiversion="MANUAL"; 
            $transactiontype=($params['sellorbuy']=='sell')?\Snap\object\Order::TYPE_COMPANYSELL:\Snap\object\Order::TYPE_COMPANYBUY;         
            $uuid=$params['uuid'];
            $futureorderrefno=NULL;
            $ordertype=($params['amount'] != '' && $params['amount'] !=  is_null()) ? 'amount' : (($params['weight'] != '' && $params['weight'] !=  is_null()) ? 'weight' : NULL);
            $ordervalue=($ordertype=='amount')?$params['amount']:(($ordertype=='weight')?$params['weight']:NULL);
            $lockedinprice=$params['sellorbuy']=='sell'?$params['sellprice']:($params['sellorbuy']=='buy'?$params['buyprice']:'');
            $ordertotal="";
            $notifyurl="";
            $reference=($params['reference']) ? $params['reference'] : NULL;
			$timestamp=date('Y-m-d H:i:s');  

			$sharedgv_partner = false;
			if ($partner->sharedgv == 1){
				$sharedgv_partner = true;
			}

            try{

				$check_ref_no = $this->app->orderStore()->searchTable()->select('id')->where('partnerrefid', $params['reference'])->count();
				if ($check_ref_no){
					throw new \Exception("Duplicate Reference No.");
				}

				// mbb special order
				if ($params['partnerid'] == $this->app->getConfig()->{"gtp.mib.partner.id"}){
					if ($params['sellorbuy'] == 'sell'){
						$companyBuy = false;
					}else{
						$companyBuy = true;
					}
					$apiversion = '1.0m';
					// get pricevalidation id - start
					$priceValidation = $this->app->PriceManager()->getSpotPrice($partner, $product, $partnerrefno, $companyBuy, $apiversion);
					$uuid = $priceValidation->uuid;
					$lockedinprice = $priceValidation->price;
					$reference = $reference = 'Special order - '.$reference;
					// get pricevalidation id - end
					$returns = $spotordermanager->bookOrder($partner, $apiversion, $partnerrefno, $transactiontype, $product, $uuid, $futureorderrefno, $ordertype, $ordervalue, $lockedinprice, $ordertotal, $notifyurl, $reference, $timestamp);
					// $returns = $spotordermanager->confirmBookOrder($return);

					// $finalAcePrice =  $partner->calculator()->round($returns->price);
					// $weight = $partner->calculator(false)->round($returns->xau);
					// $totalEstValue = $partner->calculator()->round($returns->amount);
					// $orderFee = $partner->calculator()->round($returns->fee);
	
				   
		
					$finalAcePrice = number_format($returns->price,3);
					$weight = number_format($returns->xau,3);
					$totalEstValue = number_format($returns->amount,2);
					$orderFee = number_format($returns->fee,3);

					// Set back to 2 
					$finalAcePrice = $finalAcePrice + $orderFee;
					$finalAcePrice = number_format($finalAcePrice,2);

					// Use toArray()
					$results[] = array(	'id' => $returns->id, 
									'partnerid' => $returns->partnerid, 
									'partnerrefid' => $returns->partnerrefid, 
									'orderno' => $returns->orderno, 
									'pricestreamid' => $returns->pricestreamid, 
									'salespersonid' => $returns->salespersonid,
									'apiversion' => $returns->apiversion, 
									'type' => $returns->type, 
									'productid' => $returns->productid,
									'isspot' => $returns->isspot,
									'price' => $finalAcePrice,
									'byweight' => $returns->byweight,
									'xau' => $returns->xau,
									'amount' => $returns->amount,
									'fee' => $returns->fee,
									'remarks' => $returns->remarks,
									'bookingon' => $returns->bookingon,
									'bookingprice' => $returns->bookingprice,
									'bookingpricestreamid' => $returns->bookingpricestreamid,
									'notifyurl' => $returns->notifyurl,
									'reconciled' => $returns->reconciled,
									'status' => $returns->status, );
				}
				// sharedgv partner, all userinput must less than generated;
				else if ($sharedgv_partner){
					// gopayz use weight only, if pass in rm convert it into gram - formula chopoff , 3 decimal only
					// rm 500 / gold price = weight, weight chop to 3 decimal then only round.
					if ($params['sellorbuy'] == 'sell'){
						$companyBuy = false;
					}else{
						$companyBuy = true;
					}
					if ($ordertype == 'weight'){

					}
					if ($ordertype == 'amount'){
						$ordertype = 'weight';
						$input_amount = floatval($params['amount']);
						$convert_amount = $input_amount / $lockedinprice;
						$final_converted_amount = floor($convert_amount * 1000) / 1000;
						$ordervalue = $final_converted_amount;
					}



					$apiversion = '1.0my';
					// get pricevalidation id - start
					// $priceValidation = $this->app->PriceManager()->getSpotPrice($partner, $product, $partnerrefno, $companyBuy, $apiversion);
					// $uuid = $priceValidation->uuid;
					// $lockedinprice = $priceValidation->price;
					$reference = $reference = 'Special order - '.$reference;
					// get pricevalidation id - end
					$return = $spotordermanager->bookOrder($partner, $apiversion, $partnerrefno, $transactiontype, $product, $uuid, $futureorderrefno, $ordertype, $ordervalue, $lockedinprice, $ordertotal, $notifyurl, $reference, $timestamp);
					$returns = $spotordermanager->confirmBookOrder($return);

					// $finalAcePrice =  $partner->calculator()->round($returns->price);
					// $weight = $partner->calculator(false)->round($returns->xau);
					// $totalEstValue = $partner->calculator()->round($returns->amount);
					// $orderFee = $partner->calculator()->round($returns->fee);
	
				   
		
					$finalAcePrice = number_format($returns->price,3);
					$weight = number_format($returns->xau,3);
					$totalEstValue = number_format($returns->amount,2);
					$orderFee = number_format($returns->fee,3);

					// Set back to 2 
					$finalAcePrice = $finalAcePrice + $orderFee;
					$finalAcePrice = number_format($finalAcePrice,2);

					// insert partner Ledger
					if ($returns){
						$this->logDebug("Creating partner ledger for special order $returns->partnerrefno");
						$partnerLedger = $this->app->myledgerStore()->create([
							'accountholderid'   => 0,
							'partnerid'         => $returns->partnerid,
							'refno'             => $returns->partnerrefid,
							// 'typeid'            => $returns->id,
							'transactiondate'   => $returns->createdon->format('Y-m-d H:i:s'),
							'remarks'  			=> 'Special Spot Order',
							'status'            => \Snap\object\MyLedger::STATUS_ACTIVE
						]);
						
						// Set debit/credit depending if it is a buy or sell
						if (\Snap\object\Order::TYPE_COMPANYBUY == $returns->type) {
							$partnerLedger->type   = \Snap\object\MyLedger::TYPE_PROMO;
							$partnerLedger->credit = $returns->xau;
							$partnerLedger->debit  = 0.00;
						} else {
							$partnerLedger->type   = \Snap\object\MyLedger::TYPE_PROMO;
							$partnerLedger->debit  = $returns->xau;
							$partnerLedger->credit = 0.00;
						}
						$partnerLedger = $this->app->myledgerStore()->save($partnerLedger);
					}
					// insert partner Ledger

					// Use toArray()
					$results[] = array(	'id' => $returns->id, 
									'partnerid' => $returns->partnerid, 
									'partnerrefid' => $returns->partnerrefid, 
									'orderno' => $returns->orderno, 
									'pricestreamid' => $returns->pricestreamid, 
									'salespersonid' => $returns->salespersonid,
									'apiversion' => $returns->apiversion, 
									'type' => $returns->type, 
									'productid' => $returns->productid,
									'isspot' => $returns->isspot,
									'price' => $finalAcePrice,
									'byweight' => $returns->byweight,
									'xau' => $returns->xau,
									'amount' => $returns->amount,
									'fee' => $returns->fee,
									'remarks' => $returns->remarks,
									'bookingon' => $returns->bookingon,
									'bookingprice' => $returns->bookingprice,
									'bookingpricestreamid' => $returns->bookingpricestreamid,
									'notifyurl' => $returns->notifyurl,
									'reconciled' => $returns->reconciled,
									'status' => $returns->status, );
				}else{
					// core partner
					$returns= $spotordermanager->bookOrder($partner, $apiversion, $partnerrefno, $transactiontype, $product, $uuid, $futureorderrefno, $ordertype, $ordervalue, $lockedinprice, $ordertotal, $notifyurl, $reference, $timestamp);

					// $finalAcePrice =  $partner->calculator()->round($returns->price);
					// $weight = $partner->calculator(false)->round($returns->xau);
					// $totalEstValue = $partner->calculator()->round($returns->amount);
					// $orderFee = $partner->calculator()->round($returns->fee);
	
				   
		
					$finalAcePrice = number_format($returns->price,3);
					$weight = number_format($returns->xau,3);
					$totalEstValue = number_format($returns->amount,2);
					$orderFee = number_format($returns->fee,3);

					// Set back to 2 
					$finalAcePrice = $finalAcePrice + $orderFee;
					$finalAcePrice = number_format($finalAcePrice,2);
					
					// Use toArray()
					$results[] = array(	'id' => $returns->id, 
									'partnerid' => $returns->partnerid, 
									'partnerrefid' => $returns->partnerrefid, 
									'orderno' => $returns->orderno, 
									'pricestreamid' => $returns->pricestreamid, 
									'salespersonid' => $returns->salespersonid,
									'apiversion' => $returns->apiversion, 
									'type' => $returns->type, 
									'productid' => $returns->productid,
									'isspot' => $returns->isspot,
									'price' => $finalAcePrice,
									'byweight' => $returns->byweight,
									'xau' => $returns->xau,
									'amount' => $returns->amount,
									'fee' => $returns->fee,
									'remarks' => $returns->remarks,
									'bookingon' => $returns->bookingon,
									'bookingprice' => $returns->bookingprice,
									'bookingpricestreamid' => $returns->bookingpricestreamid,
									'notifyurl' => $returns->notifyurl,
									'reconciled' => $returns->reconciled,
									'status' => $returns->status, );
				}
				
                echo json_encode(['success' => true, 'return' => $results]);
            }catch(\Exception $e){
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
            }    
        }else{
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No Permission']);
        }
	}
	/* function getOrderStatements($app, $params){		
	
		$partnerid=$this->app->getUserSession()->getUser()->partnerid;
		$partner = $app->partnerStore()->getById($partnerid);
		$beforefromdate = date("Y-m-d", $params['summaryfromdate']/1000).' 00:00:00';
		$fromdate = date("Y-m-d", strtotime($beforefromdate.  ' + 1 days')).' 00:00:00'; 
		$beforetodate = date("Y-m-d", $params['summarytodate']/1000).' 00:00:00';
		$todate = date("Y-m-d", strtotime($beforetodate.  ' + 1 days')).' 00:00:00'; 
		$buysellcode= $params['summarytype']==1?$partner->sapcompanybuycode1:$partner->sapcompanysellcode1;		
		$param = array(
			"code"  =>	$buysellcode,
			"startDate" => $fromdate,			
			"endDate"  => $todate
			);
		$version = "1.0";		
		$apimanager=$this->app->apiManager();	
		try{
			$content=$apimanager->sapGetStatement($version,$param);							
			header('Content-Type: application/pdf');
			header('Content-Length: '.strlen($content[0]));
			header('Content-disposition: inline; filename="summary.pdf"');
			header('Cache-Control: public, must-revalidate, max-age=0');
			header('Pragma: public');			
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			echo $content[0];
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
		
	}
 */
	function getOrderStatementsForCustomer($app, $params){		
		$partnerid=$params['partnerid'];
		$partner = $app->partnerStore()->getById($partnerid);
		$beforefromdate = date("Y-m-d", $params['summaryfromdate']/1000).' 00:00:00';
		$fromdate = date("Y-m-d", strtotime($beforefromdate.  ' + 1 days')).' 00:00:00'; 
		$beforetodate = date("Y-m-d", $params['summarytodate']/1000).' 00:00:00';
		$todate = date("Y-m-d", strtotime($beforetodate.  ' + 1 days')).' 00:00:00'; 
		$buysellcode= $params['summarytype']==1?$partner->sapcompanybuycode1:$partner->sapcompanysellcode1;		
		$param = array(
			"code"  =>	$buysellcode,
			"startDate" => $fromdate,			
			"endDate"  => $todate
			);
		$version = "1.0";		
		$apimanager=$this->app->apiManager();	
		try{
			$content=$apimanager->sapGetStatement($version,$param);							
			header('Content-Type: application/pdf');
			header('Content-Length: '.strlen($content));
			header('Content-disposition: inline; filename="summary.pdf"');
			header('Cache-Control: public, must-revalidate, max-age=0');
			header('Pragma: public');			
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			echo $content;
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
		
	}

	/* function getUnfulfilledStatements($app, $params){		
		$partnerid=$this->app->getUserSession()->getUser()->partnerid;
		$partner = $app->partnerStore()->getById($partnerid);				
		$buycode= $partner->sapcompanybuycode1;				
		$apimanager=$this->app->apiManager();	
		try{
			$result=$apimanager->sapGetOpenPo("1.0", false, $buycode);		
			// Additional
			$resultwithnewkey["records"]=$result['opnlist'];	
		
			//print_r("no return");
			echo json_encode($resultwithnewkey);	
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
	}
 */
	function getUnfulfilledStatementsForCustomer($app, $params){	
		/*
		$customer = $this->app->userStore()->searchTable()->select()
					->where(['id' => $params['customerid']])
					->limit(1)
					->execute();
		*/
		$partnerid= $params['partnerid'];//$customer[0]->partnerid;
		$partner = $app->partnerStore()->getById($partnerid);				
		$buycode= $partner->sapcompanybuycode1;				
		$apimanager=$this->app->apiManager();	
		try{
			$result=$apimanager->sapGetOpenPo("1.0", false, $buycode);		
			// Additional
			$resultwithnewkey["records"]=$result['opnlist'];	
		
			//print_r("no return");
			echo json_encode($resultwithnewkey);	
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
	}

	function sendToSap($app, $params){
		try{
			$orders = $app->orderStore()->searchTable()->select()->where('id', 'IN', $params['id'])->execute();
			foreach ($orders as $order){
				$return = $this->app->spotOrderManager()->postOrderToSAP($order);
			}
			if ($return){
				//return $return;
				echo json_encode(['success' => true, 'return' => $return]);
			}else{
				throw new \Exception("Failed to submit to SAP");
			}
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
	}

	/*
	public function printSpotOrderBuy($app, $params){

		$username = $this->app->getUserSession()->getUser()->name;
		$usertype = $this->app->getUserSession()->getUser()->type;
		$userpartnerid = $this->app->getUserSession()->getUser()->partnerid;

		$partnerobj = $this->app->partnerStore()->getById($userpartnerid);
		//$partnerobj->salespersonid;
		$userobj = $this->app->userStore()->getById($partnerobj->salespersonid);
		$salespersonname = $userobj->name;
		$customername = $username;
        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', 3);
        $html2pdf->pdf->SetDisplayMode('fullpage');

		$now = new \DateTime(gmdate('Y-m-d\TH:i:s'));
        $now = \Snap\common::convertUTCToUserDatetime($now);

        $grnPdf = '
        
        <table style="width: 100%;" >
            <tr>
                <td style="width: 60%;">
				<div style="font-weight:bold;font-size:1.5rem">ACE - Buy Order</div>
				<span><p>Order No. 						: '. $params['orderid'] . '</p></span>
				<span><p>Customer Name 					: '. $customername . '</p></span>
				<span><p>Salesperson					: '. $salespersonname . '</p></span>
				<span><p>Product						: '. $params['product'] . '</p></span>
				<span><p>Ace Buy Final Price (RM/g)     : '. $params['finalprice'] . '</p></span>
				<span><p>Total est. Value (RM) 		    : '. $params['totalestvalue'] . '</p></span>
				<span><p>Weight (g)			 		    : '. $params['xauweight'] . '</p></span>
				<span><p>Date				 		    : '. $now->format('Y-m-d H:i:s') . '</p></span>
                
                </td>
                
            </tr>
        </table>
        ';
        $grnPdf = $this->completePdf($grnPdf);


        // ob_start();
        // include dirname(__FILE__).'/../../vendor/spipu/html2pdf/examples/res/example15.php';
        // $content = ob_get_clean();
        // $html2pdf->writeHTML($content);
        
        
        $html2pdf->writeHTML($grnPdf);
        return $html2pdf->output('FILENAME.pdf');
	}*/

	public function printSpotOrder($app, $params){

		
		$spotordermanager=$this->app->spotorderManager();
		
		// Check if Buy Or Sell
		try{

			if(!$params['customerid']){
				$orderPdf = $spotordermanager->printSpotOrder($params['orderid']);
			}else{
				$orderPdf = $spotordermanager->printSpotOrder($params['orderid'], $params['customerid']);
			}
			if ($orderPdf){
				$order = $this->app->orderStore()->getById($params['orderid']);
				$developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
				$filename = 'ACE_Order_'.$order->orderno;
				if ($developmentEnv){
					$orderPdf = '<div style="font-size: 20px;">----------------DEMO----------------</div><br>'.$orderPdf;
					$filename = 'DEMO_'.$filename;
				}
			}else{
				echo 'Invalid Order. Please Contact Administrative.';
				return;
			}
	
			$html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', 3);
            $html2pdf->pdf->SetDisplayMode('fullpage');
			
			$orderPdf = $this->completePdf($orderPdf);


			// ob_start();
			// include dirname(__FILE__).'/../../vendor/spipu/html2pdf/examples/res/example15.php';
			// $content = ob_get_clean();
			// $html2pdf->writeHTML($content);
			
			$html2pdf->pdf->setTitle($filename);
			$html2pdf->writeHTML($orderPdf);

			echo json_encode(['success' => true, 'return' => $html2pdf->output($filename.'.pdf')]);
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
	   
       
       
        //return $html2pdf->output('FILENAME.pdf');
	}


	
	/*
	public function printSpotOrderSellSpecial($app, $params){

		$username = $this->app->getUserSession()->getUser()->name;
		$usertype = $this->app->getUserSession()->getUser()->type;
		$userpartnerid = $this->app->getUserSession()->getUser()->partnerid;

		$partnerobj = $this->app->partnerStore()->getById($userpartnerid);
		//$partnerobj->salespersonid;
		$userobj = $this->app->userStore()->getById($params['customerid']);
		$salespersonname = $username;
		$customername = $userobj->name;
        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', 3);
        $html2pdf->pdf->SetDisplayMode('fullpage');

		$now = new \DateTime(gmdate('Y-m-d\TH:i:s'));
        $now = \Snap\common::convertUTCToUserDatetime($now);


        $grnPdf = '
        
        <table style="width: 100%;" >
            <tr>
                <td style="width: 60%;">
				<div style="font-weight:bold;font-size:1.5rem">ACE - Sell Order</div>
				<span><p>Order No. 						: '. $params['orderid'] . '</p></span>
				<span><p>Customer Name 					: '. $customername . '</p></span>
				<span><p>Salesperson					: '. $salespersonname . '</p></span>
				<span><p>Product						: '. $params['product'] . '</p></span>
				<span><p>Ace Sell Final Price (RM/g)     : '. $params['finalprice'] . '</p></span>
				<span><p>Total est. Value (RM) 		    : '. $params['totalestvalue'] . '</p></span>
				<span><p>Weight (g)			 		    : '. $params['xauweight'] . '</p></span>
				<span><p>Date				 		    : '. $now->format('Y-m-d H:i:s') . '</p></span>
                
                </td>
                
            </tr>
        </table>
        ';
        $grnPdf = $this->completePdf($grnPdf);


        // ob_start();
        // include dirname(__FILE__).'/../../vendor/spipu/html2pdf/examples/res/example15.php';
        // $content = ob_get_clean();
        // $html2pdf->writeHTML($content);
        
        
        $html2pdf->writeHTML($grnPdf);
        return $html2pdf->output('FILENAME.pdf');
	}*/
	
	public function printFutureOrder($app, $params){
		
		if($params['buyorsell']== 'sell'){
			$title = 'ACE - Buy Order';
			$orderlabel = 'FutureBuy-';
			$matchpricelabel = 'Ace Sell To Match Price (RM/g)';
		}else {
			$title = 'ACE - Sell Order';
			$orderlabel = 'FutureSell-';
			$matchpricelabel = 'Ace Buy To Match Price (RM/g)';
		}
        $username = $this->app->getUserSession()->getUser()->name;
		$usertype = $this->app->getUserSession()->getUser()->type;
		$userpartnerid = $this->app->getUserSession()->getUser()->partnerid;

		$partnerobj = $this->app->partnerStore()->getById($userpartnerid);
		//$partnerobj->salespersonid;
		$userobj = $this->app->userStore()->getById($partnerobj->salespersonid);
		$salespersonname = $userobj->name;
		$customername = $username;
        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', 3);
        $html2pdf->pdf->SetDisplayMode('fullpage');

		$now = new \DateTime(gmdate('Y-m-d\TH:i:s'));
        $now = \Snap\common::convertUTCToUserDatetime($now);

        $grnPdf = '
        <table style="width: 100%;" >
            <tr>
                <td style="width: 60%;">
				<div style="font-weight:bold;font-size:1.5rem">'. $title .'</div>
				<span><p>Order No. 						: '. $orderlabel . $params['orderid'] . '</p></span>
				<span><p>Customer Name 					: '. $customername . '</p></span>
				<span><p>Salesperson					: '. $salespersonname . '</p></span>
				<span><p>Product						: '. $params['product'] . '</p></span>
				<span><p>'. $matchpricelabel . '     : '. $params['matchingprice'] . '</p></span>
				<span><p>Total est. Value (RM) 		    : '. $params['totalestvalue'] . '</p></span>
				<span><p>Weight (g)			 		    : '. $params['xauweight'] . '</p></span>
				<span><p>Date				 		    : '. $now->format('Y-m-d H:i:s') . '</p></span>
                
                </td>
                
            </tr>
        </table>
        ';
        $grnPdf = $this->completePdf($grnPdf);


        // ob_start();
        // include dirname(__FILE__).'/../../vendor/spipu/html2pdf/examples/res/example15.php';
        // $content = ob_get_clean();
        // $html2pdf->writeHTML($content);
        
        
        $html2pdf->writeHTML($grnPdf);
        return $html2pdf->output('FILENAME.pdf');
	}
	
	/* 
	* This is for mbb
	*/

	/*
	function cancelOrder($app, $params){		

		$partner = $app->partnerStore()->getById($params['partnerid']);
		$order = $app->orderStore()->getById($params['id']);
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
			$cancel= $app->spotorderManager()->cancelOrder($partner, $order, $notifyUrl, $reference, $timestamp);		
			
			if ($cancel){
				//$orderQueue = $app->futureorderManager()->cancelFutureOrder($decodedData['partner'], $version, $requestParams['ref_id'], $notifyUrl, $requestParams['reference'], $requestParams['timestamp']);
				
				$confirmCancelOrder = $app->spotorderManager()->confirmCancelOrder($cancel);
				if (!$confirmCancelOrder){
					echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);   
				}
			}else{
				echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);   
			}
		
			echo json_encode(['success' => true, 'field' => '', ]);      
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
		
	}*/
	
	/*
	* This is for GTP
	*/

	/*
		const STATUS_PENDING = 0;
		const STATUS_CONFIRMED = 1;
		const STATUS_PENDINGPAYMENT = 2;
		const STATUS_PENDINGCANCEL = 3;
		const STATUS_CANCELLED = 4;
		const STATUS_COMPLETED = 5;
		const STATUS_EXPIRED = 6;
	

	}*/

	function cancelOrder($app,$params){  
		$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};

		//$items = $this->app->orderStore()->searchTable()->select()->whereIn('id', $params['id'])->execute();
		$item = $params['id'][0];
		$order = $this->app->orderStore()->getById($item);
		if($item){
			try{
				if ($order->partnerid == $mbbpartnerid){
					throw new \Exception("Invalid Partner ID for cancellation.");
				}
				$return = $app->spotorderManager()->cancelOrderGTP($order);
				echo json_encode([ 'success' => true, 'message' => $return['message']]);  
			}catch(\Exception $e){
				echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
			}             
		}else{
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);  
		} 
	
    }

	/*
	function isHideSendToSap($app, $params){		

		
		try{
		  
			$userType = $this->app->getUserSession()->getUser()->type;
			$userId = $this->app->getUserSession()->getUser()->id;
            $user=$this->app->userStore()->getById($userId);

			if($user->isSale()){
				$hide = 0;
			} else if ($user->isOperator()) {
				$hide = 0;
			} else if ($user->isTrader()) {
				$hide = 0;
			} else if ($user->isCustomer()) {
				$hide = 1;
			} else if ($user->isReferral()) {
				$hide = 1;
			} else if ($user->isAgent()) {
				$hide = 1;
			}
			if ($hide != 0){
				echo json_encode(['success' => true, 'hide' => true]);   
			}else{
				echo json_encode(['success' => true, 'hide' => false]);   
			}
		  
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
		
	}*/

	function fillSapForm( $app, $params) {

			// ********************************************** Check and acquire vendor customer SAP codes ***************************************************************** //
				
			$apimanager=$this->app->apiManager();
			$requestvendor = array(
				'version' => '1.0',
				'action' => 'partnerlist',
				'option' => 'vendor',
				//'code' => 'VTK108',

			);
			
			$requestcustomer = array(
				'version' => '1.0',
				'action' => 'partnerlist',
				'option' => 'customer',
				//'code' => 'VTK108',

			);


			//$username = $app->getConfig()->{'gtp.sap.username'};
			//$password = $app->getConfig()->{'gtp.sap.password'};
			$apireturnvendor = $apimanager->sapBusinessList('1.0', $requestvendor);
			$apireturncustomer = $apimanager->sapBusinessList('1.0', $requestcustomer);
		
			// Extract ocrd data out
			$apifilteredvendor = $apireturnvendor['ocrd'];
			$apifilteredcustomer = $apireturncustomer['ocrd'];
		
			$apicodesvendor = array();
			$apicodescustomer = array();
		
			foreach ($apifilteredvendor as $key=>$result) {

					$codename = $apifilteredvendor[$key]['cardCode']." ".$apifilteredvendor[$key]['cardName'];
					//print_r($apifiltered[$key]['cardCode']);
					$apicodesvendor[] = array("id" => $apifilteredvendor[$key]['cardCode'], "name" => $apifilteredvendor[$key]['cardName']);


			}

			foreach ($apifilteredcustomer as $key=>$result) {

					$codename = $apifilteredcustomer[$key]['cardCode']." ".$apifilteredcustomer[$key]['cardName'];
					//print_r($apifiltered[$key]['cardCode']);
					$apicodescustomer[] = array("id" => $apifilteredcustomer[$key]['cardCode'], "name" => $apifilteredcustomer[$key]['cardName']);

			} 
			// *********************************************** End Checking ********************************************************************** //

		
		
		echo json_encode([ 'success' => true, 'apicodesvendor' => $apicodesvendor, 'apicodescustomer' => $apicodescustomer]);
	}

	private function completePdf($content){
        $f = 1;
        
        $wrap_start = '<page backtop="10mm" backbottom="10mm" backleft="20mm" backright="20mm">
		<page_header>
			<img style="width: 100%;" src="src/resources/images/AcgLetterheader.png">
		
		</page_header>
		<br>
		<br>
		<br>
		<br>
		<br>
		<br>';
        $wrap_end = '</page>';
        
        //$header = $this->getHeader();
        //$footer = $this->getFooter();

        

        $html = $wrap_start.$header.$footer.$content.$wrap_end;

        return $html;
	}
	
	private function getHeader($draft = false){
        $header = '
            <page_header>
                
                <table style="width: 100%; border: solid 1px black;">
                    <tr>
                        <td style="text-align: left;    width: 33%">html2pdf</td>
                        <td style="text-align: center;    width: 34%">Test dheader</td>
                        <td style="text-align: right;    width: 33%">'.date("Y").'</td>
                    </tr>
                </table>
            </page_header>
        ';
        

        return $header;
    }

    private function getFooter($draft = false){
        $footer = '
            <page_footer>
            '.date('d/m/Y').'
            </page_footer>
        ';
        
        return $footer;
	}
	
	/*
	function getUnfulfilledStatements($app, $params){		
		$partnerid=$this->app->getUserSession()->getUser()->partnerid;
		$partner = $app->partnerStore()->getById($partnerid);				
		$buycode= $partner->sapcompanybuycode1;				
		$apimanager=$this->app->apiManager();	
		try{
			$result=$apimanager->sapGetOpenPo("1.0", true, $buycode);				
			$resultwithnewkey["records"]=$result['opnlist'];			
			//echo json_encode($resultwithnewkey);	
			$limit= $params['start']+$params['limit'];		
			$page = $params['page']; 
			$total = count($resultwithnewkey["records"]);   
			$limit = 50; 
			$totalPages = ceil( $total/ $limit );
			$page = max($page, 1);			
			$offset = ($page - 1) * $limit;
			if( $offset < 0 ) $offset = 0;
			$filteredrecords['records'] = array_slice($resultwithnewkey['records'], $offset,$limit);			
			if(isset($params['sort'])){
				$sortparams=json_decode($params['sort']);							
				$property=$sortparams[0]->property;
				$direction=$sortparams[0]->direction;					
				$column = array_column($filteredrecords['records'], $property);
				if($direction=='DESC'){
					array_multisort($column, SORT_DESC, $filteredrecords['records']);
				}else{
					array_multisort($column,SORT_ASC , $filteredrecords['records']);
				}
			}			
			$filteredrecords['totalRecords']=$total;
			echo json_encode($filteredrecords);	
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
	}*/
	
/*
    public function doSpotOrder($app,$params){           
        $permission = $this->app->hasPermission('/root/dg999/order/edit');      
        if($permission){	

			print_r('=================');
			print_r($params);
			// Select Partner
			$userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
			$partner = $this->app->partnerStore()->getById($userPartnerId);

			$apiVersion = "1.0m";
			$partnerrefno = $userPartnerid+time();  
			//$transactiontype = ($params['sellorbuy']=='sell')?Order::TYPE_COMPANYSELL:Order::TYPE_COMPANYBUY;
			$transactiontype = Order::TYPE_COMPANYSELL;
			// Select Product
			$product = $this->app->productStore()->getById(3);
			$uuid = $params['uuid'];
			$futureorderrefno=NULL;

			// Weight or Amount
			$ordertype=($params['amount'] != '' && $params['amount'] != 'null') ? 'amount' : (($params['weight'] != '' && $params['weight'] != 'null') ? 'weight' : NULL);
			//$orderType = 'weight';
			// Either Weight or amount
			$ordervalue=($ordertype=='amount')?$params['amount']:(($ordertype=='weight')?$params['weight']:NULL);
			//$orderValue = $params['totalvalue'];

			$lockedinprice=$params['sellorbuy']=='sell'?$params['sellprice']:($params['sellorbuy']=='buy'?$params['buyprice']:'');
			//$lockedinPrice = ;
			$orderTotal = '';
			$notifyurl="";
            $reference=NULL;
			$timeStamp = date('Y-m-d H:i:s');

			//$params['acebuyprice'],
			
			

			//$return = $app->LogisticManager()->attemps($logisticobj);

			$return = $app->SpotOrderManager()->bookOrder($partner, $apiVersion, $partnerrefno, $transactiontype, $product, $uuid, $futureorderrefno, $orderType, $orderValue, $lockedinprice, $orderTotal, $notifyUrl, $reference, $timeStamp, $unlimited = false);
			
            //$redemptionobj=$this->app->redemptionStore()->getById($params['id']); 
            //$logisticmanager=$this->app->logisticManager();       
            //$logisticmanager->createLogisticRedemption($redemptionobj, $params['vendor'] ?? null, null, null, $params['salespersonid'] ?? null, $params['dateofdelivery'] ?? null);
        }else{
            throw new \Snap\InputException(gettext("sorry, no permission"), \Snap\InputException::GENERAL_ERROR, 'permission');
        }
	}*/
	
	/*
	public function oldDoSpotOrder($app,$params){           
		
		// This is just a placeholder. 
        $permission = $this->app->hasPermission('/root/dg999/order/edit');      
        if($permission){	
			
			$order = $this->app->orderStore()->create([
                "orderid" => 1,
                "bookingpricestreamid" => $params['uuid'],
                "orderno" => 1,
                "bookingprice" => $params['acebuyprice'],
                "partnerrefid" => 1,
                "type" => $type,
                "apiversion" => '1.0m',
                "amount" => $params['totalvalue'], 
				"isspot" => $items, 
				"byweight" => $params['totalxauweight'], 
                "remarks" => $remarks,
                "status" => Redemption::STATUS_PENDING, // init status pending
            ]);
            $order = $this->app->orderStore()->save($order);
                
           
        }else{
            throw new \Snap\InputException(gettext("sorry, no permission"), \Snap\InputException::GENERAL_ERROR, 'permission');
        }
    }*/
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
			if($aOrderDelivery->actualquantity == 0) $totalDelivery += $aOrderDelivery->expectedquantity;
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

	/*
        This method is to get data for view details
    */
	function detailview($app, $params) {
		$object = $app->orderfactory()->getById($params['id']);

		$partner = $app->partnerFactory()->getById($object->partnerid);
		$buyername = $app->partnerFactory()->getById($object->buyerid)->name;
		//$partnername = $app->partnerFactory()->getById($object->partnerid)->name;
		//$partnercode = $app->partnerFactory()->getById($object->partnerid)->code;
		$salespersonname = $app->userFactory()->getById($object->salespersonid)->name;
		$productname = $app->productFactory()->getById($object->productid)->name;

		$reconciledbyname = $app->userFactory()->getById($object->reconciledby)->name;
		$cancelbyname = $app->userFactory()->getById($object->cancelby)->name;
		$confirmbyname = $app->userFactory()->getById($object->confirmby)->name;

		$isSpot = "";

		if($object->isspot > 0) $isSpot = "Yes";
		else $isSpot = 'No';

		$byWeight = "";
		
		if($object->byweight > 0) $byWeight = "Weight";
		else $byWeight = 'Amount';

		if($object->reconciled > 0) $isReconciled = "Yes";
		else $isReconciled = 'No';

		

		$bookingOn = $object->bookingon ? $object->bookingon->format('Y-m-d H:i:s') : '0000-00-00 00:00:00';
		//$confirmedOn = $object->confirmon ? $object->confirmon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

		if($object->confirmon == '0000-00-00 00:00:00' || !$object->confirmon){
			$confirmedOn = '0000-00-00 00:00:00';
		}else {
			$confirmedOn = $object->confirmon->format('Y-m-d H:i:s');
		}

		if($object->cancelledOn == '0000-00-00 00:00:00' || !$object->cancelledOn){
			$cancelledOn = '0000-00-00 00:00:00';
		}else {
			$cancelledOn = $object->cancelledOn->format('Y-m-d H:i:s');
		}

		if($object->reconciledOn == '0000-00-00 00:00:00' || !$object->conreconciledOnfirmon){
			$reconciledOn = '0000-00-00 00:00:00';
		}else {
			$reconciledOn = $object->reconciledOn->format('Y-m-d H:i:s');
		}
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
			$statusname = 'PendingPayment';
		}else if ($object->status == 3){
			$statusname = 'PendingCancel';
		}else if ($object->status == 4){
			$statusname = 'Reversal';
		}else if ($object->status == 5){
			$statusname = 'Completed';
		}else if ($object->status == 6){
			$statusname = 'Expired';
		}else {
			$statusname = 'Unidentified';
		}
		
		// Set 
    	if ($object->type == 'CompanySell'){
			$totalestname = 'Total Customer Buy';
		}else if ($object->type == 'CompanyBuy'){
			$totalestname = 'Total Customer Sell';
		}else {
			$totalestname = 'Total Value';
		}

		// $finalAcePrice =  $partner->calculator()->round($object->price);
		// $weight = $partner->calculator(false)->round($object->xau);
		// $totalEstValue = $partner->calculator()->round($object->amount);
		// $orderFee = $partner->calculator()->round($object->fee);

		// $bookingPrice =  $partner->calculator()->round($object->bookingprice);
		// $confirmPrice = $partner->calculator(false)->round($object->confirmprice);
		// $cancelPrice = $partner->calculator()->round($object->cancelprice);

		// Get partner id
		// If object partner id is mbb
		// Do 2 decimal point filter
		$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};
		
		if($object->partnerid == $mbbpartnerid){
			// All mbb partner
			$finalAcePrice = number_format($object->price,2);
			$weight = number_format($object->xau,3);
			$totalEstValue = number_format($object->amount,2);
			$orderFee = number_format($object->fee,2);
	
			
		
	
			$bookingPrice = number_format($object->bookingPrice,2);
			$confirmPrice = number_format($object->confirmPrice,2);
			$cancelPrice = number_format($object->cancelPrice,2);
		}else{
			// All gtp partner
			$finalAcePrice = number_format($object->price,3);
			$weight = number_format($object->xau,3);
			$totalEstValue = number_format($object->amount,3);
			$orderFee = number_format($object->fee,3);

			$bookingPrice = number_format($object->bookingPrice,3);
			$confirmPrice = number_format($object->confirmPrice,3);
			$cancelPrice = number_format($object->cancelPrice,3);
		}
		


		// $finalAcePrice = number_format($finalAcePrice,2);
		// $weight = number_format($weight,3);
		// $totalEstValue = number_format($totalEstValue,2);
		// $orderFee = number_format($orderFee,2);

		$detailRecord['default'] = [ //"ID" => $object->id,
								    'Partner' => $partner->name,
									'Buyer' => $buyername,
                                    'Partner Reference ID' => $object->partnerrefid,
                                    'Order No' => $object->orderno,
                                    'Price Stream ID' => $object->pricestreamid,
                                    'Salesperson' => $salespersonname,
                                    'API Version' => $object->apiversion,
                                    'Type' => $object->type,
                                    'Product' => $productname,
                                    'Is Spot' => $isSpot,
                                    'Price' => $finalAcePrice,
                                    'Xau' => $weight,
                                    $totalestname => $totalEstValue,
                                    'Fee' => $orderFee,
                                    'Remarks' => $object->remarks,
									'Booked By' => $byWeight,
                                    'Booking On' => $bookingOn,
                                    'Booking Price' => $bookingPrice,
                                    'Booking Price Stream ID' => $object->bookingpricestreamid,
                                    'Confirm On' => $confirmedOn,
                                    'Confirm By' => $confirmbyname,
                                    'Confirm Price Stream ID' => $object->confirmpricestreamid,
                                    'Confirm Price' => $confirmPrice,
                                    'Confirm Reference' => $object->confirmreference,
									'Cancel On' => $cancelledOn,
                                    'Cancel By' => $cancelbyname,
                                    'Cancel Price Stream ID' => $object->cancelpricestreamid,
                                    'Cancel Price' => $cancelPrice,
                                    'Notify URL' => $object->notifyurl,
									'Reconciled' => $isReconciled,
									'Reconciled On' => $reconciledOn,
                                    'Reconciled By' => $reconciledbyname,

									'Status' => $statusname,
									'Created on' => $object->createdon->format('Y-m-d H:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d H:i:s'),
									'Modified by' => $modifieduser,
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

	function detailviewmobile($app, $params) {
		$user = $app->getUserSession()->getUser();
		$object = $app->orderfactory()->searchTable()->select()->where('id', $params['id'])->andWhere('partnerid', $user->partnerid)->one();
		if (!$object){
			echo json_encode(['success' => false, 'errorMessage' => 'Invalid order selection. Please contact administrative']);exit;
		}

		$partner = $app->partnerFactory()->getById($object->partnerid);
		if ($object->buyerid){
			$buyername = $app->partnerFactory()->getById($object->buyerid)->name;
		}else{
			$buyername = '-';
		}
		if ($object->salespersonid){
			$salespersonname = $app->userFactory()->getById($object->salespersonid)->name;
		}else{
			$salespersonname = '-';
		}
		$productname = $app->productFactory()->getById($object->productid)->name;

		$isSpot = "";

		if($object->isspot > 0) $isSpot = "Yes";
		else $isSpot = 'No';

		$byWeight = "";
		
		if($object->byweight > 0) $byWeight = "Weight";
		else $byWeight = 'Amount';

		if($object->reconciled > 0) $isReconciled = "Yes";
		else $isReconciled = 'No';

		$bookingOn = $object->bookingon ? $object->bookingon->format('Y-m-d H:i:s') : '0000-00-00 00:00:00';


		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		// Status
		if ($object->status == 0){
			$statusname = 'Pending';
		}else if ($object->status == 1){
			$statusname = 'Confirmed';
		}else if ($object->status == 2){
			$statusname = 'PendingPayment';
		}else if ($object->status == 3){
			$statusname = 'PendingCancel';
		}else if ($object->status == 4){
			$statusname = 'Reversal';
		}else if ($object->status == 5){
			$statusname = 'Completed';
		}else if ($object->status == 6){
			$statusname = 'Expired';
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

		// Get partner id
		// If object partner id is mbb
		// Do 2 decimal point filter
		$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};
		
		
		$finalAcePrice = number_format($object->price,3);
		$weight = number_format($object->xau,3);
		$totalEstValue = number_format($object->amount,3);
		$orderFee = number_format($object->fee,3);

		$bookingPrice = number_format($object->bookingPrice,3);
		$confirmPrice = number_format($object->confirmPrice,3);
		$cancelPrice = number_format($object->cancelPrice,3);
		
		$detailRecord['default'] = [ 
			'Partner' => $partner->name,
			'Buyer' => $buyername,
			'Order No' => $object->orderno,
			'Salesperson' => $salespersonname,
			'Type' => $display_type,
			'Product' => $productname,
			'Price' => $finalAcePrice,
			'Xau' => $weight,
			$totalestname => $totalEstValue,
			'Fee' => $orderFee,
			'Booking On' => $bookingOn,
			'Booking Price' => $bookingPrice,
			'Status' => $statusname,
			'Created on' => $object->createdon->format('Y-m-d H:i:s'),
		];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

	function onPreListing($objects, $params, $records) {

		foreach ($records as $key => $record) {
			$records[$key]['status_text'] = ($record['status'] == "1" ? "Active" : "Inactive");
		
            // if key has priceprovider
            if($records[$key]['priceprovidercode']){
				$result = explode('.',$records[$key]['priceprovidercode']);
                $records[$key]['priceprovidername'] = $result[1];
            }
		}

		return $records;
	}

	function onPreQueryListing($params, $sqlHandle, $fields){

		$app = App::getInstance();
		$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};

		$userType = $this->app->getUserSession()->getUser()->type;
		//$permission = $this->app->hasPermission('/root/mib/logistic');		
		$userid = $this->app->getUserSession()->getUser()->id;

		$user=$this->app->userStore()->getById($userid);	

		$userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
        // At the moment only sales and operator is involved
        if($user->isSale() || $user->isOperator() || $user->isTrader()){
           	// Old filter above for non mbb modules
			//$sqlHandle->andWhere('partnerid', '!=' , $mbbpartnerid);
			
			$core_exclude_partners = [];
			$not_core_partners = $this->app->partnerStore()->searchTable()->select()->where('corepartner', 0)->execute();
			foreach ($not_core_partners as $arr){
				array_push($core_exclude_partners, $arr->id);
			}

			// New filter
			
				// $bmmbpartnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
	
		
				// $gopartnerid = $this->app->getConfig()->{'gtp.go.partner.id'};
		
	
				// $onepartnerid = $this->app->getConfig()->{'gtp.one.partner.id'};

				// $onecallpartnerid = $this->app->getConfig()->{'gtp.onecall.partner.id'};
				
				// $airpartnerid = $this->app->getConfig()->{'gtp.air.partner.id'};

				// $mcashpartnerid = $this->app->getConfig()->{'gtp.mcash.partner.id'};

				// //added on 13/12/2021
				// $ktppartnerid = $this->app->getConfig()->{'gtp.ktp.partner.id'}; //later need to change the partner id into the real one
			
	
			// $sqlHandle->where('partnerid', '!=' , $mbbpartnerid)
			// 		   ->andWhere('partnerid', '!=' , $bmmbpartnerid)
			// 		   ->andWhere('partnerid', '!=' , $gopartnerid)
			// 		   ->andWhere('partnerid', '!=' , $onepartnerid)
			// 		   ->andWhere('partnerid', '!=' , $onecallpartnerid)
			// 		   ->andWhere('partnerid', '!=' , $airpartnerid)
			// 		   ->andWhere('partnerid', '!=' , $mcashpartnerid)
			// 		   //added on 13/12/2021
			// 		   ->andWhere('partnerid', '!=' , $ktppartnerid);

			$sqlHandle->where('partnerid', 'NOT IN', $core_exclude_partners);
			
        } else {
			// filter by customer own partnerid
			$sqlHandle->andWhere('partnerid', $userPartnerId);
			
        }
		//$sqlHandle->andWhere('partnerid', '!=' , 1);
  
        return array($params, $sqlHandle, $fields);
	}

	function exportZip($app, $params){
        
        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);
        // $params['summary']

		// Start Query
		if ($params["partnercode"] == 'corepartner'){
			$core_exclude_partners = [];
			$not_core_partners = $app->partnerStore()->searchTable()->select()->where('corepartner', 0)->execute();
			foreach ($not_core_partners as $arr){
				array_push($core_exclude_partners, $arr->id);
			}
			$modulename = 'COREPARTNER_ORDER';
			$conditions = ['partnerid', 'NOT IN', $core_exclude_partners];
		}else{
			$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};
			$modulename = 'MIB_ORDER';
			$conditions = ['partnerid' => $mbbpartnerid];
		}

        $prefix = $app->orderFactory()->getColumnPrefix();
        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if ('status' === $column->index) {
                
                // $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                //     "CASE WHEN `{$prefix}status` = " . 0 . " THEN 'Inactive'
                //      WHEN `{$prefix}status` = " . 1 . " THEN 'Active' END as `{$prefix}status`"
                // );
                $header[$key]->hydrahon = true;
                // $header[$key]->index->original = $original;
                $header[$key]->value = "CASE WHEN `{$prefix}status` = " . Order::STATUS_PENDING . " THEN 'Pending' 
				WHEN `{$prefix}status` = " . Order::STATUS_CONFIRMED . " THEN 'Confirmed' 
				WHEN `{$prefix}status` = " . Order::STATUS_PENDINGPAYMENT . " THEN 'Pending Payment' 
				WHEN `{$prefix}status` = " . Order::STATUS_PENDINGCANCEL . " THEN 'Pending Cancel' 
				WHEN `{$prefix}status` = " . Order::STATUS_CANCELLED . " THEN 'Reversal' 
				WHEN `{$prefix}status` = " . Order::STATUS_COMPLETED . " THEN 'Completed' 
				WHEN `{$prefix}status` = " . Order::STATUS_EXPIRED . " THEN 'Expired' END as `{$prefix}status`";
            }
        }


		//Get Lightweight Current store name
		$currentStore = $app->orderFactory()->getTableName();

        $this->app->reportingManager()->generateExportFileZip($currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, null, null, $conditions, null, 'createdon', null, $params['email']);
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
}
