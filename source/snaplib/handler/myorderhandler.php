<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

use Exception;
use Snap\api\exception\GeneralException;
Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;
use Snap\object\Order;
use Snap\InputException;
use Snap\object\account;
use Snap\object\MyGoldTransaction;
use Snap\object\MyLedger;
use Snap\object\Partner;
use Snap\object\rebateConfig;
use Snap\api\exception\MyGtpPriceValidationNotValid;
Use Snap\object\PriceAdjuster;

use Snap\api\casa\BaseCasa;

// \PhpOffice\PhpWord\Settings::setPdfRendererPath('vendor/tecnickcom/tcpdf');
// \PhpOffice\PhpWord\Settings::setPdfRendererName('TCPDF');


use Spipu\Html2Pdf\Html2Pdf;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class myOrderHandler extends orderHandler {

	
	function __construct(App $app) {
			
		$this->app = $app;

		$mygoldtransactionStore = $app->mygoldtransactionfactory();

		$this->currentStore = $mygoldtransactionStore;
		//parent::$children = [];
		$this->addChild(new ext6gridhandler($this, $mygoldtransactionStore, 1 ));

		parent::__construct($app);

		$this->mapActionToRights('exportExcel', '/root/bmmb/goldtransaction/list;/root/go/goldtransaction/list;/root/one/goldtransaction/list;/root/onecall/goldtransaction/list;/root/air/goldtransaction/list;/root/mcash/goldtransaction/list;/root/toyyib/goldtransaction/list;/root/ktp/goldtransaction/list;/root/kopetro/goldtransaction/list;/root/kopttr/goldtransaction/list;/root/pkbaffi/goldtransaction/list;/root/bumira/goldtransaction/list;/root/nubex/goldtransaction/list;/root/hope/goldtransaction/list;/root/mbsb/goldtransaction/list;/root/red/goldtransaction/list;/root/kodimas/goldtransaction/list;/root/kgoldaffi/goldtransaction/list;/root/koponas/goldtransaction/list;/root/wavpay/goldtransaction/list;/root/noor/goldtransaction/list;/root/bsn/goldtransaction/list;/root/alrajhi/goldtransaction/list;/root/posarrahnu/goldtransaction/list;/root/waqaf/goldtransaction/list;/root/igold/goldtransaction/list;/root/kasih/goldtransaction/list;/root/bursa/goldtransaction/list;');
        $this->mapActionToRights('detailview', '/root/bmmb/goldtransaction/list;/root/go/goldtransaction/list;/root/one/goldtransaction/list;/root/onecall/goldtransaction/list;/root/air/goldtransaction/list;/root/mcash/goldtransaction/list;/root/toyyib/goldtransaction/list;/root/ktp/goldtransaction/list;/root/kopetro/goldtransaction/list;/root/kopttr/goldtransaction/list;/root/pkbaffi/goldtransaction/list;/root/bumira/goldtransaction/list;/root/nubex/goldtransaction/list;/root/hope/goldtransaction/list;/root/mbsb/goldtransaction/list;/root/red/goldtransaction/list;/root/kodimas/goldtransaction/list;/root/kgoldaffi/goldtransaction/list;/root/koponas/goldtransaction/list;/root/wavpay/goldtransaction/list;/root/noor/goldtransaction/list;/root/bsn/goldtransaction/list;/root/alrajhi/goldtransaction/list;/root/posarrahnu/goldtransaction/list;/root/waqaf/goldtransaction/list;/root/igold/goldtransaction/list;/root/kasih/goldtransaction/list;/root/bursa/goldtransaction/list;');
        $this->mapActionToRights('list', '/root/bmmb/goldtransaction/list;/root/go/goldtransaction/list;/root/one/goldtransaction/list;/root/onecall/goldtransaction/list;/root/air/goldtransaction/list;/root/mcash/goldtransaction/list;/root/toyyib/goldtransaction/list;/root/ktp/goldtransaction/list;/root/kopetro/goldtransaction/list;/root/kopttr/goldtransaction/list;/root/pkbaffi/goldtransaction/list;/root/bumira/goldtransaction/list;/root/nubex/goldtransaction/list;/root/hope/goldtransaction/list;/root/mbsb/goldtransaction/list;/root/red/goldtransaction/list;/root/kodimas/goldtransaction/list;/root/kgoldaffi/goldtransaction/list;/root/koponas/goldtransaction/list;/root/wavpay/goldtransaction/list;/root/noor/goldtransaction/list;/root/bsn/goldtransaction/list;/root/alrajhi/goldtransaction/list;/root/posarrahnu/goldtransaction/list;/root/waqaf/goldtransaction/list;/root/igold/goldtransaction/list;/root/kasih/goldtransaction/list;/root/bursa/goldtransaction/list;');
        $this->mapActionToRights('getMerchantList', '/root/bmmb/goldtransaction/list;/root/go/goldtransaction/list;/root/one/goldtransaction/list;/root/onecall/goldtransaction/list;/root/air/goldtransaction/list;/root/mcash/goldtransaction/list;/root/toyyib/goldtransaction/list;/root/ktp/goldtransaction/list;/root/kopetro/goldtransaction/list;/root/kopttr/goldtransaction/list;/root/pkbaffi/goldtransaction/list;/root/bumira/goldtransaction/list;/root/nubex/goldtransaction/list;/root/hope/goldtransaction/list;/root/mbsb/goldtransaction/list;/root/red/goldtransaction/list;/root/kodimas/goldtransaction/list;/root/kgoldaffi/goldtransaction/list;/root/koponas/goldtransaction/list;/root/wavpay/goldtransaction/list;/root/noor/goldtransaction/list;/root/bsn/goldtransaction/list;/root/alrajhi/goldtransaction/list;/root/posarrahnu/goldtransaction/list;/root/waqaf/goldtransaction/list;/root/igold/goldtransaction/list;/root/kasih/goldtransaction/list;/root/bursa/goldtransaction/list;');
        $this->mapActionToRights('uploadbulkpaymentresponse', '/root/bmmb/goldtransaction/list;/root/go/goldtransaction/list;/root/one/goldtransaction/list;/root/onecall/goldtransaction/list;/root/air/goldtransaction/list;/root/mcash/goldtransaction/list;/root/toyyib/goldtransaction/list;/root/ktp/goldtransaction/list;/root/kopetro/goldtransaction/list;/root/kopttr/goldtransaction/list;/root/pkbaffi/goldtransaction/list;/root/bumira/goldtransaction/list;/root/nubex/goldtransaction/list;/root/hope/goldtransaction/list;/root/mbsb/goldtransaction/list;/root/red/goldtransaction/list;/root/kgoldaffi/goldtransaction/list;/root/koponas/goldtransaction/list;/root/wavpay/goldtransaction/list;/root/noor/goldtransaction/list;/root/bsn/goldtransaction/list;/root/alrajhi/goldtransaction/list;/root/posarrahnu/goldtransaction/list;/root/waqaf/goldtransaction/list;/root/igold/goldtransaction/list;/root/kasih/goldtransaction/list;/root/bursa/goldtransaction/list;');

		$this->mapActionToRights('exportZip', '/root/bmmb/goldtransaction/list;/root/go/goldtransaction/list;/root/one/goldtransaction/list;/root/onecall/goldtransaction/list;/root/air/goldtransaction/list;/root/mcash/goldtransaction/list;/root/toyyib/goldtransaction/list;/root/ktp/goldtransaction/list;/root/kopetro/goldtransaction/list;/root/kopttr/goldtransaction/list;/root/pkbaffi/goldtransaction/list;/root/bumira/goldtransaction/list;/root/nubex/goldtransaction/list;/root/hope/goldtransaction/list;/root/mbsb/goldtransaction/list;/root/red/goldtransaction/list;/root/kodimas/goldtransaction/list;/root/kgoldaffi/goldtransaction/list;/root/koponas/goldtransaction/list;/root/wavpay/goldtransaction/list;/root/noor/goldtransaction/list;/root/bsn/goldtransaction/list;/root/alrajhi/goldtransaction/list;/root/posarrahnu/goldtransaction/list;/root/waqaf/goldtransaction/list;/root/igold/goldtransaction/list;/root/kasih/goldtransaction/list;/root/bursa/goldtransaction/list;');

		$this->mapActionToRights('getOtcOrders', '/all/access');
		
		$this->mapActionToRights('doOtcOrders', '/all/access');
		$this->mapActionToRights('doAqad', '/all/access');
		$this->mapActionToRights('checkApprovalStatus', '/all/access');
		$this->mapActionToRights('approvePendingGoldTransactions', '/all/access');
		$this->mapActionToRights('rejectPendingGoldTransactions', '/all/access');
		$this->mapActionToRights('printSpotOrderOTC', '/all/access');
		$this->mapActionToRights('getInterval', '/all/access');
		
	}

	
	/*
        This method is to get data for view details
    */
	function detailview($app, $params) {
		//$object = $app->orderfactory()->getById($params['id']);
		
		$object = $this->app->mygoldtransactionStore()->searchView()->select()
			->where('id', $params['id'])
			->one();


		$partner = $app->partnerFactory()->getById($object->ordpartnerid);
		$buyername = $app->myaccountholderFactory()->getById($object->ordbuyerid)->fullname;
		//$partnername = $app->partnerFactory()->getById($object->partnerid)->name;
		//$partnercode = $app->partnerFactory()->getById($object->partnerid)->code;
		
		//$productname = $app->productFactory()->getById($object->ordproductid)->name;

		$confirmbyname = $app->userFactory()->getById($object->confirmby)->name;

		$isSpot = "";

		if($object->ordisspot > 0) $isSpot = "Yes";
		else $isSpot = 'No';

		$byWeight = "";
		
		if($object->ordbyweight > 0) $byWeight = "Weight";
		else $byWeight = 'Amount';

		$bookingOn = $object->ordbookingon ? $object->ordbookingon->format('Y-m-d H:i:s') : '0000-00-00 00:00:00';
		//$confirmedOn = $object->confirmon ? $object->confirmon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

		if($object->completedon == '0000-00-00 00:00:00' || !$object->completedon){
			$completedOn = '0000-00-00 00:00:00';
		}else {
			$completedOn = $object->completedon->format('Y-m-d H:i:s');
		}

		if($object->failedon == '0000-00-00 00:00:00' || !$object->failedon){
			$failedOn = '0000-00-00 00:00:00';
		}else {
			$failedOn = $object->failedon->format('Y-m-d H:i:s');
		}

		if($object->dbmpdtrequestedon == '0000-00-00 00:00:00' || !$object->dbmpdtrequestedon){
			$transactionDate = '0000-00-00 00:00:00';
		}else {
			$transactionDate = $object->dbmpdtrequestedon->format('Y-m-d H:i:s');
		}

		if($object->ordconfirmon == '0000-00-00 00:00:00' || !$object->ordconfirmon){
			$confirmedOn = '0000-00-00 00:00:00';
		}else {
			$confirmedOn = $object->ordconfirmon->format('Y-m-d H:i:s');
		}

		if($object->ordcancelon == '0000-00-00 00:00:00' || !$object->ordcancelon){
			$cancelledOn = '0000-00-00 00:00:00';
		}else {
			$cancelledOn = $object->ordcancelon->format('Y-m-d H:i:s');
		}

		//$cancelledOn = $object->cancelon ? $object->cancelon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		//$reconciledOn = $object->reconciledon ? $object->reconciledon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		// Status
		if ($object->status == 0){
			$statusname = 'Pending Payment';
		}else if ($object->status == 1){
			$statusname = 'Confirmed';
		}else if ($object->status == 2){
			$statusname = 'Paid';
		}else if ($object->status == 3){
			$statusname = 'Failed';
		}else if ($object->status == 4){
			$statusname = 'Reversed';
		}else if ($object->status == 5){
			$statusname = 'Pending Refund';
		}else if ($object->status == 6){
			$statusname = 'Refunded';
		}else {
			$statusname = 'Unidentified';
		}
		
		// Set 
		if ($object->ordtype == 'CompanySell'){
			$totalestname = 'Total Customer Buy';
		}else if ($object->ordtype == 'CompanyBuy'){
			$totalestname = 'Total Customer Sell';
		}else {
			$totalestname = 'Total Value';
		}
	

		// $finalAcePrice =  $partner->calculator()->round($object->ordprice);
		// $weight = $partner->calculator(false)->round($object->ordxau);
		// $totalEstValue = $partner->calculator()->round($object->ordamount);
		// $orderFee = $partner->calculator()->round($object->ordfee);		

		$finalAcePrice = number_format($object->ordprice,2);
		$weight = number_format($object->ordxau,3);
		$totalEstValue = number_format($object->ordamount,2);
		$orderFee = number_format($object->ordfee,2);

		// $verifiedAmount = $partner->calculator()->round($object->dbmpdtverifiedamount);	
		$verifiedAmount = number_format($object->dbmpdtverifiedamount,2);

		// $bookingPrice =  $partner->calculator()->round($object->ordbookingprice);
		// $confirmPrice = $partner->calculator(false)->round($object->ordconfirmprice);
		// $cancelPrice = $partner->calculator()->round($object->ordcancelprice);
	

		// $bookingPrice = number_format($bookingPrice,2);
		// $confirmPrice = number_format($confirmPrice,2);
		// $cancelPrice = number_format($cancelPrice,2);
		// Possible additions to detailview when needed
		/*
		ordstatus
		ordpartnername
		pdtamount
		pdtsourcerefno
		pdtsigneddata
		pdtlocation
		pdtgatewayfee
		pdtcustomerfee
		pdttoken
		pdtstatus
		pdttransactiondate
		pdtsuccesson
		pdtfailedon
		pdtrefundedon
		dbmamount
		dbmbankid
		dbmbankrefno
		dbmaccountname
		dbmacebankcode
		dbmfee
		dbmstatus
		dbmtransactionrefno
		dbmdisbursedon
		dbmbankname
		*/
		
		$detailRecord['default'] = [ //"ID" => $object->id,
								    'Partner' => $object->ordpartnername,
									'Buyer' => $buyername,
                                    'Transaction Reference' => $object->refno,
									
                                    'Order No' => $object->ordorderno,
                                    'Settlement Method' => $object->settlementmethod,
                                    'Type' => $object->ordtype,
                                    'Product' => $object->ordproductname,
                                    'Is Spot' => $isSpot,
                                    'Price' => $finalAcePrice,
                                    'Xau' => $weight,
									$totalestname => $totalEstValue,
                                    'Processing Fee' => $orderFee,
                                    'Remarks' => $object->ordremarks,
									'Booked By' => $byWeight,
                                    'Booking On' => $bookingOn,
                                    'Confirm On' => $confirmedOn,
                                    'Confirm By' => $confirmbyname,

									'Cancel On' => $cancelledOn,
									'Completed On' => $completedOn,
									'Failed On' => $failedOn,

                                    'Gateway Reference No' => $object->dbmpdtgatewayrefno,
									'Disbursement Reference No' => $object->dbmpdtreferenceno,
									'Transaction Date' => $transactionDate,
                                    'Requested On' => $object->confirmprice,
                                    'Account Holder Name' => $object->dbmpdtaccountholdername,
									'Account Holder Code' => $object->dbmpdtaccountholdercode,
                                    'Verified Amount' => $verifiedAmount,
									'Bank Ref No' => $object->dbmbankrefno,
                                 
                                    //'Cancel Price' => $object->cancelprice,
                                    'Notify URL' => $object->notifyurl,
									//'Reconciled' => $isReconciled,
									//'Reconciled On' => $reconciledOn,
                                    //'Reconciled By' => $reconciledbyname,

									'Status' => $statusname,
									'Created on' => $object->createdon->format('Y-m-d H:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d H:i:s'),
									'Modified by' => $modifieduser,
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

	function onPreListing($objects, $params, $records) {

		$app = App::getInstance();

		// $lastTransactionsCutoff = $this->app->getConfig()->{'mygtp.accountclosure.lasttrasactioncutoff'} ?? '6 months';
      
        // $withTransactionIds = $this->app->myledgerStore()
        //         ->searchTable(false)
        //         ->select(['accountholderid'])
        //         ->addFieldMax('transactiondate', 'led_lasttransactiondate')                
        //         ->whereIn('accountholderid', $accHolderIds)
        //         ->whereIn('type', [MyLedger::TYPE_BUY_FPX, MyLedger::TYPE_SELL])
        //         ->where('transactiondate','<=', $dateStart->format('Y-m-d H:i:s'))
        //         ->where('transactiondate','>', $dateLastTransaction->format('Y-m-d H:i:s'))
        //         ->groupBy('accountholderid')
        //         ->forwardKey('led_accountholderid')
        //         ->get();

		
		if (preg_match('/(1|on|yes)/i', $app->getConfig()->{'otc.pricestream.adjust'})) {
			foreach($records as $key => $record) {


				$order = $app->orderStore()->getById($records[$key]['orderid']);
				
				$records[$key]['discountinfo'] = json_decode($order->discountinfo);

				$margin = $app->otcPricingModelStore()->searchTable()->select()
							->where('id',$records[$key]['discountinfo']->discountid)
							->one();


				$bankmargin = $app->otcPricingModelStore()->searchTable()->select()
                                                        ->where('name','Standard')
                                                        ->one();

				if($order->type == 'CompanySell'){
					$marginTrx = $margin->sellmarginpercent;
					$bankMargin = $bankmargin->sellmarginpercent;
				}else{
					$marginTrx = $margin->buymarginpercent;
					$bankMargin = $bankmargin->buymarginpercent;
				}

				if (0 < $order->pricestreamid) $priceStream = $this->app->pricestreamStore()->getById($order->pricestreamid);
				if (0 < $priceStream->priceadjusterid) $otcPricingModel = $this->app->otcpricingmodelStore()->getById($priceStream->priceadjusterid);
				if ($otcPricingModel) {
					//discountprice is negative in db 
					$records[$key]['originalprice'] = $order->price + $order->discountprice;
					//$records[$key]['ordamount'] = ($order->price + $order->discountprice) * $order->xau;
					$partner = $app->partnerStore()->getById($order->partnerid);
                                        $records[$key]['ordamount'] = $partner->calculator()->multiply(($order->price + $order->discountprice),$order->xau);
					$originalamount = $order->amount;
					//$records[$key]['commision'] = abs(($order->amount) - (($order->amount)/(1 + ($marginTrx /100))));
					$records[$key]['aceprice'] = number_format($order->price / (1 + ($bankMargin/100)) ,2);
					//$records[$key]['aceprice'] = (1 + ($bankMargin/100)) ;
					$records[$key]['commision'] = $partner->calculator()->multiply((($order->price + $order->discountprice) - $records[$key]['aceprice'] ),$order->xau);
					$records[$key]['pricedifference'] =  - ($order->discountprice) * $order->xau;
				}

				
				$records[$key]['discountAmount'] = $records[$key]['discountinfo'] -> discountname;

				// $createdOn = new DateTime($record['createdon']);            
				//Display the current $record variable
				// $partner = $app->partnerStore()->getById($records[$key]['ordpartnerid']);

				// $product = $app->productStore()->getByField('name', $records[$key]['ordproductname']);

				// $priceProvider = $this->app->priceProviderStore()->getForPartnerByProduct($partner, $product);
				// if (!$priceProvider) {
				// 	$this->log(__function__ . " Price provider not found for partner", SNAP_LOG_ERROR);
				// }
				// $partnerPriceAdjuster = $this->app->priceAdjusterStore()->getForPartnerByPriceProvider($priceProvider);
				// if (0 == count($partnerPriceAdjuster)) {
				// 	$this->log(__function__ . " Partner price adjuster not found for partner", SNAP_LOG_ERROR);
				// }
		
				// $partnerPriceAdjuster
				// get correct adjust price
				// foreach ($partnerPriceAdjuster as $priceAdjuster) {
				// 	if ($priceAdjuster->tier == PriceAdjuster::TIER_PEAK){
				// 		$records[$key]['originalprice'] = $this->app->priceManager()->getOriginalPrice($priceAdjuster, $records[$key]['ordamount'] , $records[$key]['ordtype']);
				// 		$records[$key]['pricedifference'] = $records[$key]['ordamount'] - $records[$key]['originalprice'];
				// 	}
				// }
				
				
				
			}
		}
		return $records;
	}

	function onPreQueryListing($params, $sqlHandle, $fields){
		$app = App::getInstance();
		//$bmmbpartnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};

		// Start Query
		if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
			$sqlHandle->andWhere('ordpartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
			$sqlHandle->andWhere('ordpartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
			$sqlHandle->andWhere('ordpartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
			$sqlHandle->andWhere('ordpartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
			$sqlHandle->andWhere('ordpartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
			$sqlHandle->andWhere('ordpartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
			$sqlHandle->andWhere('ordpartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
		//added on 13/12/2021
		}else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
			->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
			->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
        }
		// Add new permissions for OTC
		else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerIdBSN = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerIdBSN)->execute();
            // Do checking
            $partnerId = $this->getPartnerIdForBranch();

			//check user state permission
            $userId = $this->app->getUserSession()->getUserId();
            $user = $this->app->userStore()->getById($userId);
            
            if($user->state){
				$partners = array();
            
                $partnerArr = $this->app->partnerStore()->searchTable()->select()
                    ->where('state', $user->state)
                    ->andWhere('group', $partnerIdBSN)
                    ->execute();
                
				$partners = $partnerArr;
            }
			
			$dateRange = json_decode($params["daterange"]);

            if($partnerId != 0){
				if(isset($params['daterange'])){
					$sqlHandle
					->andWhere('ordpartnerid', $partnerId)
					->andwhere('createdon', '>=', $dateRange->startDate)
					->andWhere('createdon', '<=', $dateRange->endDate);
				}else{
					$sqlHandle->andWhere('ordpartnerid', $partnerId);
				}
                
            }else{
              
                $groupPartnerIds = array();
                foreach ($partners as $partner){
                    array_push($groupPartnerIds,$partner->id);
                }
				if(isset($params['daterange'])){
					$sqlHandle
					->andWhere('ordpartnerid', 'IN', $groupPartnerIds)
					->andwhere('createdon', '>=', $dateRange->startDate)
					->andWhere('createdon', '<=', $dateRange->endDate);
				}else{
					$sqlHandle->andWhere('ordpartnerid', 'IN', $groupPartnerIds);
				}
                
            }
        }
		else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
            // Do checking
            $partnerId = $this->getPartnerIdForBranch();
            if($partnerId != 0){
                $sqlHandle->andWhere('ordpartnerid', $partnerId);
            }else{
              
                $groupPartnerIds = array();
                foreach ($partners as $partner){
                    array_push($groupPartnerIds,$partner->id);
                }
                $sqlHandle->andWhere('ordpartnerid', 'IN', $groupPartnerIds);
            }
        }
		else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
            // Do checking
            $partnerId = $this->getPartnerIdForBranch();
            if($partnerId != 0){
                $sqlHandle->andWhere('ordpartnerid', $partnerId);
            }else{
              
                $groupPartnerIds = array();
                foreach ($partners as $partner){
                    array_push($groupPartnerIds,$partner->id);
                }
                $sqlHandle->andWhere('ordpartnerid', 'IN', $groupPartnerIds);
            }
        }
		// End OTC Partner
		if(isset($params['mykadno'])){
			$sqlHandle->andWhere("achmykadno", $params['mykadno'])->andWhere("achcode", $params['accountholdercode']);
		}

		// $currentuserId = $app->getUserSession()->getUserId();
        // $currentuser = $app->userStore()->getById($currentuserId);
        // $currentpartnerId = $currentuser->partnerid;

		// if ($currentpartnerId == 0){
		// 	$sign = '>';
		// }else{
		// 	$sign = '<';
		// }
		
		if (isset($params['filter'])){
			// check filter case 
			switch($params['filter']){
				case 'approval':
					// filter for approval view
					$sqlHandle->andWhere('status', MyGoldTransaction::STATUS_PENDING_APPROVAL);
					//$sqlHandle->andWhere('originalamount', '>=' , 10000);
					break;
				case 'individual':
					// filter for individual
					break;
				default:
					// execute for default
					// $sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
			}
		}
		// If filter is available add filter to settings
		
		// End

		//$sqlHandle->andWhere('ordpartnerid', $bmmbpartnerid);
		// $sqlHandle->andWhere('id' , 7347);
		// $sqlHandle->orWhere('id' , 7346);
        return array($params, $sqlHandle, $fields);
    }

	function exportExcel($app, $params){
		
		try {
			//$bmmbpartnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};
			$originalPrice = false;
			// Start Query
			if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
				$modulename = 'BMMB_ORDER';
			}else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
				$modulename = 'GO_ORDER';
			}else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
				$modulename = 'ONECENT_ORDER';
			}else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
				$modulename = 'ONECALL_ORDER';
			}else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
				$modulename = 'AIR_ORDER';
			}else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
				$modulename = 'MCASH_ORDER';
			}else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
				$modulename = 'TOYYIB_ORDER';
			}else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
				$modulename = 'NUBEX_ORDER';
			}else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
				$modulename = 'HOPE_ORDER';
			}else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
				$modulename = 'MBSB_ORDER';
			}else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
				$modulename = 'REDGOLD_ORDER';
			}else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
				$modulename = 'WAVPAYGOLD_ORDER';
			}else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
				$modulename = 'NOOR_ORDER';
			}else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
				$modulename = 'IGOLD_ORDER';
			}else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
                $modulename = 'BURSA_ORDER';
            }
			//added on 13/12/2021
			else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$modulename = 'PITIH_ORDER';
				// $partnerId = [
				//  	 //$this->app->getConfig()->{'gtp.go.partner.id'},
				//  	 //$this->app->getConfig()->{'gtp.one.partner.id'}
				//  	$params['selected']
				// ];
				$partnerId = explode(",",$params['selected']);
				//$sqlHandle->andWhere('ordpartnerid', 'IN', $ktpPartnerId_s);
			}
			else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$modulename = 'KOPETRO_ORDER';
				// $partnerId = [
				//  	 //$this->app->getConfig()->{'gtp.go.partner.id'},
				//  	 //$this->app->getConfig()->{'gtp.one.partner.id'}
				//  	$params['selected']
				// ];
				$partnerId = explode(",",$params['selected']);
				//$sqlHandle->andWhere('ordpartnerid', 'IN', $ktpPartnerId_s);
			}
			else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$modulename = 'KOPTTR_ORDER';
				// $partnerId = [
				//  	 //$this->app->getConfig()->{'gtp.go.partner.id'},
				//  	 //$this->app->getConfig()->{'gtp.one.partner.id'}
				//  	$params['selected']
				// ];
				$partnerId = explode(",",$params['selected']);
				//$sqlHandle->andWhere('ordpartnerid', 'IN', $ktpPartnerId_s);
			}
			else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
				$modulename = 'PITIHAFFI_ORDER';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
				$modulename = 'BUMIRA_ORDER';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
				$modulename = 'KGOLD_ORDER';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
				$modulename = 'KGOLDAFFI_ORDER';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
				$modulename = 'KiGA_ORDER';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
				$modulename = 'ANNURGOLD_ORDER';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
				$modulename = 'KASIHGOLD_ORDER';
				$partnerId = explode(",",$params['selected']);
			}

			// Add new permissions for OTC
			else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
				$modulename = 'BSN_ORDER';

				// Custom field with original price
				$originalPrice = true;

				// Enable custom data load
				$useDataFromList = true;
				$dateRange = json_decode($params["daterange"]);
			}
			else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
				$modulename = 'ALRAJHI_ORDER';

				// Enable custom data load
				$useDataFromList = true;
			}
			else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
				$modulename = 'POS_ORDER';

				// Enable custom data load
				$useDataFromList = true;
			}
			// End OTC Partner

			$header = json_decode($params["header"]);
			$dateRange = json_decode($params["daterange"]);
			$type = json_decode($params["type"]);
	
			$d1 = (new \DateTime($dateRange->startDate))->format('Ymd');
			$d2 = (new \DateTime($dateRange->endDate))->format('Ymd');

			// if ($d1 == $d2) {
			// 	$modulename = $modulename . '_' . $d1;
			// } else {
			// 	$modulename = $modulename . '_' . $d1 . '-' . $d2;
			// }
			$modulename = $modulename.'_ORDER';

			if (isset($params['partnercode']) && 'KTP' === $params['partnercode']){
			 	$conditions = ["ordpartnerid", "IN", $partnerId];

			}else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']){
			 	$conditions = ["ordpartnerid", "IN", $partnerId];

			}else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']){
				$conditions = ["ordpartnerid", "IN", $partnerId];
		   	}
			else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']){
				$conditions = ["ordpartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']){
				$conditions = ["ordpartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']){
				$conditions = ["ordpartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']){
				$conditions = ["ordpartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']){
				$conditions = ["ordpartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']){
				$conditions = ["ordpartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']){
				$conditions = ["ordpartnerid", "IN", $partnerId];
			}
			else{
			
				if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']){
					// Do custom settings here
					// Teller/PC/RO - Download within branch only
					// KSO/KKP/KSPP-Download within state
					// Gold Desk - Download all state 
					// Currently only have 1
					
					if ($this->app->hasPermission('/root/tier/tier1') || $this->app->hasPermission('/root/tier/tier2')){
						// view all, supposed to filer by state
						
					}else{
						// Tier 3
						$conditions = ['ordpartnerid' => $partnerId];
					}
					

				}else{
					$conditions = ['ordpartnerid' => $partnerId];
				}
		
			}

			// If use data from list
			if($useDataFromList == true){
				// grab all from list
				ob_start();
				$this->doAction($app, 'list', $params);
				$data = ob_get_contents();
				ob_end_clean();
				//$data = '{"data":"hello"}';
				// var_dump($data);
				// echo 'data='.$data . '<br/>';
				// $list = json_decode($data, true);
				$list = json_decode($data);

				// change status based on object status
				foreach ($list->records as $record) {

					// check status with constant
					switch ($record->status) {
						case MyGoldTransaction::STATUS_PENDING_PAYMENT:
							$record->status = gettext("Pending");
							break;
						case MyGoldTransaction::STATUS_PAID:
							$record->status = gettext("Paid");
							break;
						case MyGoldTransaction::STATUS_CONFIRMED:
							$record->status = gettext("Confirmed");
							break;
						case MyGoldTransaction::STATUS_REVERSED:
							$record->status = gettext("Reversed");
							break;
						case MyGoldTransaction::STATUS_FAILED:
							$record->status = gettext("Failed");
							break;
						case MyGoldTransaction::STATUS_PENDING_REFUND:
							$record->status = gettext("Pending Refund");
							break;
						case MyGoldTransaction::STATUS_REFUNDED:
							$record->status = gettext("Refunded");
							break;
						case MyGoldTransaction::STATUS_PENDING_APPROVAL:
							$record->status = gettext("Pending Approval");
							break;
						case MyGoldTransaction::STATUS_REJECTED:
							$record->status = gettext("Rejected");
							break;
						// case self::STATUS_CONFIRM_FAILED;
						//     return "Confirmation failed";
						default:
							return "";
					}
				}
				// if($list === null) {
				// 	$error = json_last_error();
				// 	$errorMessage = json_last_error_msg();
				// 	echo "JSON decoding error: {$errorMessage} (Code: {$error})";
				// }
					
				// echo'<pre>';print_r($list);echo'</pre>';

				// $php_Object = json_decode($list);
				// var_dump($php_Object);
				// call custom function

				
				$this->app->reportingManager()->generateTransactionReportWithList($list->records, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, '', $originalPrice, $this->currentStore->getTableName());
			}else{
				// dp standard functions
				//$conditions = ['ordpartnerid' => $partnerId];
				$prefix = $this->currentStore->getColumnPrefix();
				foreach ($header as $key => $column) {

					// Overwrite index value with expression
					$original = $column->index;
					if ('status' === $column->index) {
						
						//add new status
						$header[$key]->index = $this->currentStore->searchTable(false)->raw(
							"CASE WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_PENDING_PAYMENT . " THEN 'Pending Payment'
							WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_PAID . " THEN 'Paid'
							WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_FAILED . " THEN 'Failed'
							WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_REVERSED . " THEN 'Reversed'
							WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_PENDING_REFUND . " THEN 'Pending Refund'
							WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_REFUNDED . " THEN 'Refunded'
							WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_CONFIRMED . " THEN 'Confirmed'
							WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_PENDING_APPROVAL . " THEN 'Pending Approval' END as `{$prefix}status`"
						);
						$header[$key]->index->original = $original;
					}
				}

				// do check if params contain special search
				if($params['fromsearchresult']){
					// search custom fieldsphp array
					$searchResults = [
						'achmykadno' => $params['mykadno'],
						'achcode' => $params['accountholdercode'],
					];

					$this->app->reportingManager()->generateTransactionReportFromSearchResult($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, $searchResults, $originalPrice);
				}else{
					
					$this->app->reportingManager()->generateTransactionReport($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, '', $originalPrice);
				}
			}
			
		
			
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
    }
	
	function getMerchantList($app,$params){
		//for now this is dummy data. The id is taken from existing partner
		// $merchantdata = array(array('id'=>'2917155','name'=>'KTP1'),array('id'=>'2917154','name'=>'KTP2'),array('id'=>'45','name'=>'KTP3'),array('id'=>'2917159','name'=>'KTP4'));
        //$merchantdata = array(array('id'=>'2917155','name'=>'PKB'));
		if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ??  $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ??  $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
			->andWhere('group','=', $partnerId)
            ->execute();;
        }
		elseif (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
		elseif (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
		elseif (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
			->andWhere('group','=', $partnerId)
            ->execute();
        }
		elseif (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
            ->execute();
        }
		elseif (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
            ->execute();
        }
		elseif (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
            ->execute();
        }
		// $partners = $app->partnerStore()->searchTable()->select()->where('group','=', 'PKB@UAT')->execute();
        $merchantdata=array();
        foreach ($partners as $partner){
             $arr = array('id'=>$partner->id,'name'=>$partner->name,'parent'=>$partner->parent);
             array_push($merchantdata,$arr);
        }
        return json_encode(array('success' => true, 'merchantdata' => $merchantdata));
	}

	public function uploadbulkpaymentresponse($app, $params){
        try{
        	$now        = new \DateTime('now', $app->getUserTimezone());
	        $nowDate    = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone()); 
	        $curDate    = $nowDate->format('Y-m-d H:i:s'); //GMT

            $file = $_FILES['bpaymentlist'];

            /*check if extension is correct*/
            $fileName 		= $file['name'];
		    $fileSize 		= $file['size'];
		    $fileTmpName  	= $file['tmp_name'];
		    $fileType 		= $file['type'];
		    $fileExtension 	= strtolower(end(explode('.',$fileName)));

		    $extensions= array("txt","TXT");
      
		    if(in_array($fileExtension,$extensions)=== false){
		        throw new \Exception("Choose correct .txt file");
		    }
            
            $apiManager = $app->apiManager();
            $response 	= $apiManager->processMBBResponse($curDate,$file);
            //$import = $app->GoodsReceivedNoteManager()->readImportGrnExcel_POS($file, $preview);

            // Start Query
            // get partnerlist at config. for new partner, please add in this variable list at config file.
            $checklistofpartner = $app->getConfig()->{'mygtp.partnerlist.upload'};

            $partnerIdsList = explode(',', $checklistofpartner);
            foreach($partnerIdsList as $aPartnerList){
            	$separateCodeId = explode('|', $aPartnerList);
            	//[0] = partner code ui. refer at here for the code
            	//[1] = partner id at gtp db
            	//[2] = db name
            	//[3] = main link for partner. example: ktp main link is https://ktp.ace2u.com/
            	//[4] = partner id at other db
            	$partnerLists[] = $separateCodeId[0];
            	$separateCodeId[4] = explode('-', $separateCodeId[4]);
            	$getIdByCode[$separateCodeId[0]] = array($separateCodeId[1],$separateCodeId[2],$separateCodeId[3],$separateCodeId[4]);
            }

            $permissionOtherDbToUpload = $app->getConfig()->{'mygtp.otherdb.canupload'};

            $this->log("Bulk Payment - Process current partner ".$params['partnercode'], SNAP_LOG_ERROR);

            if(isset($params['partnercode']) && in_array($params['partnercode'], $partnerLists)) {
            	//[0] = partner id at gtp db
            	//[1] = db name
            	//[2] = main link for partner. example: ktp main link is https://ktp.ace2u.com/
            	//[3] = partner id at other db
            	$mainlink 		= $getIdByCode[$params['partnercode']][2]; 
            	$otherDbPartner = $getIdByCode[$params['partnercode']][3];

            	if(in_array(0,$otherDbPartner)) { //means if partner not exist in other db
            		$this->log("Bulk Payment - ".$params['partnercode']." is from GTP db", SNAP_LOG_ERROR);
            		if(!$permissionOtherDbToUpload) {
            			$this->log("Bulk Payment - Other db cannot upload in GTP ui", SNAP_LOG_ERROR);
            			$partnerId  	= $getIdByCode[$params['partnercode']][0];
            		}
            		else {
            			$this->log("Bulk Payment - Please upload at ".$mainlink, SNAP_LOG_ERROR);
            			throw new \Exception("Please upload file at ".$mainlink);
            		}
            	} else {
            		$this->log("Bulk Payment - ".$params['partnercode']." is not from GTP db", SNAP_LOG_ERROR);
            		if($permissionOtherDbToUpload) {
            			$this->log("Bulk Payment - Other db upload at their UI", SNAP_LOG_ERROR);
            			$partnerId  	= $otherDbPartner;
            		}
            		else {
            			$this->log("Bulk Payment - Please upload at ".$mainlink, SNAP_LOG_ERROR);
            			throw new \Exception("Please upload file at ".$mainlink);
            		}
            	}
            }
            else throw new \Exception("Please set partner first to continue upload file.");

			//if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
			//else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
			//else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
			//else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
			//else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
			//else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) $partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
			//else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
			//else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) $partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
			//else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
			//else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
			//else throw new \Exception("Please set partner first to continue upload file.");

            $disbursementManager = $app->mygtpdisbursementManager();
            $update = $disbursementManager->updateStatusFromMbbResponse($response,$partnerId);

            if ($update){
                $return = [
                    'success' => true
                ];
            }else{
                $return = [
                    'error' => true
                ];
            }
        }catch(\Exception $e){
            $this->log("Maybank bulk payment upload failed", SNAP_LOG_ERROR);
            $return = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return json_encode($return);
    }

	function exportZip($app, $params){
        
        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);
        // $params['summary']

		// Start Query
		if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
        }else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
        }else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
        }else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
        }else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
		}else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
		}else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
		}else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
		}else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
		}else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
        }else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
		}else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $conditions = ['ordpartnerid' => $partnerId];
            $modulename = $params["partnercode"].'_ORDER';
        }
		//KTP modules
		else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
			$modulename = 'PITIH_ORDER';
			$partnerId = explode(",",$params['selected']);
			$conditions = ["ordpartnerid", "IN", $partnerId];
		}
		else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
			$modulename = 'KOPETRO_ORDER';
			$partnerId = explode(",",$params['selected']);
			$conditions = ["ordpartnerid", "IN", $partnerId];
		}
		else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
			$modulename = 'KOPTTR_ORDER';
			$partnerId = explode(",",$params['selected']);
			$conditions = ["ordpartnerid", "IN", $partnerId];
		}
		else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
			$modulename = 'PITIHAFFI_ORDER';
			$partnerId = explode(",",$params['selected']);
			$conditions = ["ordpartnerid", "IN", $partnerId];
		}
		else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
			$modulename = 'BUMIRA_ORDER';
			$partnerId = explode(",",$params['selected']);
			$conditions = ["ordpartnerid", "IN", $partnerId];
		}
		else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
			$modulename = 'KGOLD_ORDER';
			$partnerId = explode(",",$params['selected']);
			$conditions = ["ordpartnerid", "IN", $partnerId];
		}
		else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
			$modulename = 'KGOLDAFFI_ORDER';
			$partnerId = explode(",",$params['selected']);
			$conditions = ["ordpartnerid", "IN", $partnerId];
		}
		else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
			$modulename = 'KiGA_ORDER';
			$partnerId = explode(",",$params['selected']);
			$conditions = ["ordpartnerid", "IN", $partnerId];
		}
		else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
			$modulename = 'ANNURGOLD_ORDER';
			$partnerId = explode(",",$params['selected']);
			$conditions = ["ordpartnerid", "IN", $partnerId];
		}
		else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
			$modulename = 'KASIHGOLD_ORDER';
			$partnerId = explode(",",$params['selected']);
			$conditions = ["ordpartnerid", "IN", $partnerId];
		}
		// Add new permissions for OTC
		else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
			//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
		}
		else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
			//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
			$conditions = ['ordpartnerid' => $partnerId];
			$modulename = $params["partnercode"].'_ORDER';
		}
		// End OTC Partner

        $prefix = $this->currentStore->getColumnPrefix();
        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
			if ('status' === $column->index) {
                
                $header[$key]->hydrahon = true;
                // $header[$key]->index->original = $original;
                $header[$key]->value = "CASE WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_PENDING_PAYMENT . " THEN 'Pending' 
				WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_CONFIRMED . " THEN 'Confirmed' 
				WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_PAID . " THEN 'Pending Payment' 
				WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_FAILED . " THEN 'Pending Cancel' 
				WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_REVERSED . " THEN 'Reversal' 
				WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_PENDING_REFUND . " THEN 'Completed' 
				WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_REFUNDED . " THEN 'Expired'
				WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_PENDING_APPROVAL . " THEN 'Pending Approval' END as `{$prefix}status`";
            }
        }


		//Get Lightweight Current store name
		$currentStore = $this->currentStore->getTableName();

        $this->app->reportingManager()->generateExportFileZip($currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, null, null, $conditions, null, 'createdon', null, $params['email']);
    }

	public function getOtcOrders($app, $params){

        // Get partner id from teller session for now
        // Basically the acquired partner id is by branch AKA partner 
        $userId = $app->getUserSession()->getUserId();
        $user = $app->userStore()->getById($userId);
        $partnerId = $user->partnerid;
        // Check partner id
        // If > 0 means they are limited to branch visibility
        // If 0 means they are admin and can view everything
        // if($partnerId > 0){
		// 	$mytransactions = $app->mygoldtransactionFactory()->searchView()->select()
		// 		->where("status", 1)
		// 		->andWhere("achmykadno", $params['mykadno'])
		// 		->andWhere("ordpartnerid", $partnerId)
		// 		->execute();
        // }else{
		// 	$mytransactions = $app->mygoldtransactionFactory()->searchView()->select()
		// 		->where("status", 1)
		// 		->andWhere("achmykadno", $params['mykadno'])
		// 		->execute();
        // }
		$mytransactions = $app->mygoldtransactionStore()->searchView(false, 1)->select()
		// ->where("status", 1)
		->where("achmykadno", $params['mykadno'])
		->andWhere("achcode", $params['accountholdercode'])
		->andWhere("ordpartnerid", $params['partnerid'])
		->execute();

		$prefix = 'gtr_';
		$records = [];
		foreach($mytransactions as $mytransaction){
			$mytransaction = $this->stripArrayKeyPrefix($mytransaction, $prefix);
			array_push($records, $mytransaction);
		}

		// if ($startDate) {
		// 	$startDate = \Snap\common::convertUserDatetimeToUTC($startDate);
		// 	$query->where('transactiondate', '>=', $startDate->format('Y-m-d H:i:s'));
		// }

		// if ($endDate) {
		// 	$endDate = \Snap\common::convertUserDatetimeToUTC($endDate);
		// 	$query->where('transactiondate', '<=', $endDate->format('Y-m-d H:i:s'));
		// }

		// if ($condition) {
		// 	$query->where($condition);
		// }

		// $query->where('status', MyLedger::STATUS_ACTIVE);

		// $records = $query->where('accountholderid', $accountHolder->id)->execute();

		// return $records;
        
        echo json_encode(array('success' => true, 'records'=>$records));  
        
    }

	public function doAqad($app, $params)
    {
		try{
			$userId = $app->getUserSession()->getUserId();
			$user = $app->userStore()->getById($userId);
			// Get account holder
			$accHolder = $app->myaccountHolderStore()->getById($params['accountholder_id']);
			// GET USER PARTNER ID FOR BRANCH
			if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$partnerId = $app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
				//echo $partnerId;exit;	
						
			}
			else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$partnerId = $app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};

				// For Alrajhi get casa accounts
				$input['keyword'] = $accHolder->partnercusid;
				$casaAccounts = $this->getCasaAccounts($app, $input);
				// if ($casaAccounts !== false) {
				// 	// Code to execute if getCasaAccounts() is successful
				// 	// Continue your code here
				// } else {
				// 	// Code to handle if getCasaAccounts() is not successful

				// }
				
				
			}
			else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$partnerId = $app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
			}
			// Main branch purchase by default
			$finalPartnerId = $user->partnerid != 0 ? $user->partnerid : $partnerId;
			$partner = $app->partnerStore()->getById($finalPartnerId);
			//var_dump($partner);exit;

			$isSell    = false != preg_match('/acebuy/i', $params['is_order_type_sell']);
			$orderType = $isSell ? Order::TYPE_COMPANYBUY : Order::TYPE_COMPANYSELL;			
			$productCode = $params['product_code'] ? $params['product_code'] : "DG-999-9";
			$priceRef  = $params['uuid'];
			$grams     = $partner->calculator(false)->round($params['weight']);

			$partnerData = $params['partner_data'] ? $params['partner_data'] : '';

			$product = $app->productStore()->getByField('code', $productCode);

			
			// check for note and accesstoken
			$accHolder->note = $params['note'];
			$accHolder->accesstoken = $params['access_token'] ? $params['access_token'] : null;
			
			$priceObj = $app->pricestreamStore()->getByField('uuid', $priceRef);
			if (! $priceObj) {
				$this->log(__METHOD__."(): Unable to find price stream with uuid {$priceRef}", SNAP_LOG_DEBUG);
				throw MyGtpPriceValidationNotValid::fromTransaction(null);
			}
			
			$ppg          = $isSell ? $priceObj->companybuyppg : $priceObj->companysellppg;
			$ppg          = $partner->calculator()->round($ppg);
			
			/** @var MyGtpTransactionManager $txMgr */
			$txMgr = $app->mygtptransactionManager();

			$settlementMethodMap = [
				'fpx' => MyGoldTransaction::SETTLEMENT_METHOD_FPX,
				'bank_account' => MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT,
				'wallet' => MyGoldTransaction::SETTLEMENT_METHOD_WALLET,
				'loan' => MyGoldTransaction::SETTLEMENT_METHOD_LOAN,
			];

			if (! isset($params['settlement_method'])) {
				$settlementMethod = MyGoldTransaction::SETTLEMENT_METHOD_FPX;
				if ($isSell) {
					$settlementMethod = MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT;
				}
			} else {
				$settlementMethod = $settlementMethodMap[$params['settlement_method']];
			}
			
			$campaignCode = $params['campaign_code'] ?? '';
			$memberType = $accHolder->getAdditionalData()->category;

			$txMgr->validateBookGoldTxRequest($accHolder, $partner, $product, $orderType, $priceRef, $grams,$settlementMethod,null,null,null,true);
			$breakdownArr = $txMgr->bookGoldTransactionAmountBreakdown($partner, $product, $priceObj, $isSell, $grams, $settlementMethod, $campaignCode, $memberType);

			$spPrice = (!empty($breakdownArr['discount'])||$breakdownArr['discount'] != null) ? $breakdownArr['specialprice'] : null;

			$response = [
				'data' => [
					'weight' => floatval(number_format($grams, 3, '.', '')),
					'price'  => floatval(number_format($ppg, 2, '.', '')),
					'amount' => floatval(number_format($breakdownArr['amount'], 2, '.', '')),
					'transaction_fee' => floatval(number_format($breakdownArr['transaction_fee'], 2, '.', '')),
					'total_transaction_amount' => floatval(number_format($breakdownArr['total'], 2, '.', '')),
					'special_price' => ($spPrice) ? floatval(number_format($spPrice, 2, '.', '')) : null,
					'casa_accounts' => ($casaAccounts) ? $casaAccounts : null,
				]
			];

			// $sender = \Snap\api\mygtp\MyGtpApiSender::getInstance('Json', null);
			// $sender->response($app, $response);
			echo json_encode(array('success' => true, 'record'=>$response)); 
			//return $response;
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'record' => '', 'errorMessage' => $e->getMessage()]);
		}
    }

	/*
	* Params involved
	* 1) string order_type
	* 2) string Settlement method
	* 3) int accountholder id
	*/
	// Still pending updates
	public function doOtcOrders($app, $params){
		
		try{
			// Get partner id from teller session for now
			// Basically the acquired partner id is by branch AKA partner 
			$userId = $app->getUserSession()->getUserId();
			$user = $app->userStore()->getById($userId);
			
			if(!$user || $userId == '' || $userId == null || $userId == 0){
				$this->log("User id when order: ".$user->id);
				throw new \Exception('user id not found');
			}
			// GET USER PARTNER ID FOR BRANCH
			if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$partnerId = $app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
				// For BSN need to treshold to allow approval
				$enableMetricLimit = true;

				/* Metric scaling 
				* 1) < 10,000 teller approve
				* 2) 10,000 < x < 30,000 pegawai cawagan
				* 3) > 30,000 require approval to proceed
				*/
				
			}
			else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$partnerId = $app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};

				// for Alrajhi, get branchIdent-AccTypeValue-AccIdentValue as string
				// pass as partnerdata
				
			}
			else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$partnerId = $app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
				
			}
			// Main branch purchase by default
			$finalPartnerId = $user->partnerid != 0 ? $user->partnerid : $partnerId;
		
			$partner = $app->partnerStore()->getById($finalPartnerId);
			/*get partner id & get list of partnerids that can skip confirmation. Wallet purpose*/
			$checkPartnerId = $partner->id;
			$getskipartnerids = $app->getConfig()->{'mygtp.partnerids.skipconfirmation'};
			$skipartnerids = explode(',', $getskipartnerids);
			// init settings
			
			$fromAlert = $params['from_alert'] ? $params['from_alert'] : false;
			$version = "1.0my";

			$productCode = $params['product_code'] ? $params['product_code'] : "DG-999-9";

			$partnerData = $params['partner_data'] ? $params['partner_data'] : '';

			$product = $app->productStore()->getByField('code', $productCode);

			// $orderType = $aceSell ? Order::TYPE_COMPANYSELL : Order::TYPE_COMPANYBUY;

			//$orderType = $params['is_order_type_sell'] ? Order::TYPE_COMPANYSELL : Order::TYPE_COMPANYBUY;

			if($params['is_order_type_sell']=="true"){
				$orderType = Order::TYPE_COMPANYSELL;
			}else{
				$orderType = Order::TYPE_COMPANYBUY;
			}

			// create constant array
			$settlementMethodMap = [
				'fpx' => MyGoldTransaction::SETTLEMENT_METHOD_FPX,
				'bank_account' => MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT,
				'wallet' => MyGoldTransaction::SETTLEMENT_METHOD_WALLET,
				'cash' => MyGoldTransaction::SETTLEMENT_METHOD_CASH,
				'casa' => MyGoldTransaction::SETTLEMENT_METHOD_CASA,
				'loan' => MyGoldTransaction::SETTLEMENT_METHOD_LOAN
			];

			// check, if no settlement_method, check method by partner
			if(!$params['settlement_method']){
				if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
					$settlementType = 'casa';
				}
				else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
					$settlementType = 'casa';
				}
				else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
					$settlementType = 'cash';
				}
			}else{
				$settlementType = $params['settlement_method'];
			}
			$settlementMethod = $settlementMethodMap[$settlementType];

			// Get account holder
			$accHolder = $app->myaccountHolderStore()->getById($params['accountholder_id']);
			// check for note and accesstoken
			$accHolder->note = $params['note'];
			$accHolder->accesstoken = $params['access_token'] ? $params['access_token'] : null;
			//var_dump($accHolder);exit;

			// Do checking based on metrics
			if($enableMetricLimit && $orderType == Order::TYPE_COMPANYSELL){

				//check if amount is x 
				// get gold price now
				$trxMgr = $app->mygtptransactionManager();
				$amount = $trxMgr->getCurrentAmount($partner, $params['uuid'], $params['weight'], $orderType);
				// do metric check 
				switch ($amount) {
					case 10000 >= $amount:

						// proceed to trx
						break;
					case 10000 < $amount && 30000 >= $amount:
						
						$skipTransaction = true;
						break;
					case 30000 < $amount:
		
						if(isset($params['partnercode']) && 'BSN' === $params['partnercode']){
							$finalPartnerId = 0;
						}
						$skipTransaction = true;
						break;
				}
	
				// save new status
				if($skipTransaction){
					$this->log(__METHOD__."(): Running gold TX ");
					$goldTx = $app->mygtptransactionManager()
					->bookGoldTransactionOtcPartOne($accHolder, $partner, $product,
								$params['uuid'], $orderType,number_format((float)$params['weight'],3,'.',''), $settlementMethod,
								$version, $params['pin'], $fromAlert, $params['campaign_code'], $partnerData);
					
					if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
						$goldTx->salespersoncode = $params['referralsalespersoncode'];
						$goldTx->extradata = $params['referralintroducercode'];
					}			
					// proceed to change status			
					$goldTx->status = MyGoldTransaction::STATUS_PENDING_APPROVAL;			
					$goldTx = $app->myGoldTransactionStore()->save($goldTx);
					$this->log(__METHOD__."(): Done creating gold TX ". $goldTx->id);
					// return json_encode(['success' => true, 'isawait'=> true, 'id'=> $goldTx->id]);
					// call notifcation to approve
					// return signal to load loading page while waiting approval
					$notificationManager = $app->notificationManager();

					$urlparam = urlencode($goldTx->id);

					$data = [
						'title'   => 'Title',
						'body'        => 'Transaction to be approved'.$goldTx->refno ?? null,
						'url'           => 'https://10.10.55.114/#approvalview='.$urlparam,
						'partnerid'   => $finalPartnerId,
					];

					$notificationManager->postToNotificationChannel($data);
					$this->log(__METHOD__."(): Unable to proceed past notif manager");
					return json_encode(['success' => true, 'isawait'=> true, 'id'=> $goldTx->id , 'partnerid' => $finalPartnerId]);
				}else{
					// do full trx
					$goldTx = $app->mygtptransactionManager()
					->bookGoldTransaction($accHolder, $partner, $product,
								$params['uuid'], $orderType, number_format((float)$params['weight'],3,'.',''), $settlementMethod,
								$version, $params['pin'], $fromAlert, $params['campaign_code'], $partnerData);
				}
	
			}else{
				// do full trx
				$goldTx = $app->mygtptransactionManager()
				->bookGoldTransaction($accHolder, $partner, $product,
							$params['uuid'], $orderType,number_format((float)$params['weight'],3,'.',''), $settlementMethod,
							$version, $params['pin'], $fromAlert, $params['campaign_code'], $partnerData);
				
			}
				
			
			
			if('CASH' == $settlementMethod)
			{
				// save once before entering this section
				$goldTx = $app->myGoldTransactionStore()->save($goldTx);
				$goldTx = $app->mygtptransactionManager()->confirmBookGoldTransaction($goldTx, MyLedger::TYPE_BUY_FPX);
				
			}
			// $goldTx = $app->mygtptransactionManager()
			// 	->bookGoldTransaction($accHolder, $partner, $product,
			// 				$params['uuid'], $orderType, (float)$params['weight'], $settlementMethod,
			// 				$version, $params['pin'], $fromAlert, $params['campaign_code'], $partnerData);
			// to add custom ledger functions
			
			if ( Order::TYPE_COMPANYSELL == $orderType && $goldTx) {
				// Spot Buy Transaction
				if (in_array($settlementMethod, [MyGoldTransaction::SETTLEMENT_METHOD_FPX, MyGoldTransaction::SETTLEMENT_METHOD_WALLET, MyGoldTransaction::SETTLEMENT_METHOD_CASH, MyGoldTransaction::SETTLEMENT_METHOD_CASA])) {
					$payment = $app->mypaymentdetailStore()->getByField('sourcerefno', $goldTx->refno);
					// Skip location check for now ( paywall check )
					// $location = $payment->location;

					// if (! $location) {
					// 	$this->log(__METHOD__."(): Unable to obtain paywall location from payment detail");
					// 	throw GeneralException::fromTransaction(null, [
					// 		'message'   => 'Unable to retrieve payment wall location'
					// 	]);
					// }

					if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
                        $goldTx->salespersoncode = $params['referralsalespersoncode'];
                        $goldTx->extradata = $params['referralintroducercode'];
                        $goldTx = $app->myGoldTransactionStore()->save($goldTx);
                    }

					$order = $goldTx->getOrder();
					$statusString = $goldTx->getStatusString();
					$data = [
						'id' 			  => $order->id,
						'fullname'        => $accHolder->fullname,
						'mykadno'         => $accHolder->mykadno,
						'accountnumber'   => $accHolder->accountnumber,
						'xau'   	 	  => $order->xau,
						'transactionid'   => $goldTx->refno,
						'location'        => $partner->name ?? null,
						'refno'           => $payment->paymentrefno,
						'transactionid'   => $goldTx->refno,
						'amount'          => floatval(number_format($goldTx->originalamount, 2, '.', '')),
						'transaction_fee' => floatval(number_format($order->fee, 2, '.', '')),
						'total_transaction_amount'    => floatval(number_format($order->amount, 2, '.', '')),
						'status' => $statusString
					];
				}
			} else {
				$disbursement = $app->mydisbursementStore()->getByField('transactionrefno', $goldTx->refno);

				
				/*TOYYIB wallet situation where GTP straight send to their wallet & receive status transaction*/
				//this is because transaction straight change to STATUS_PAID.It give error when trigger MyGtpTransactionManager::confirmBookGoldTransaction
				if(in_array($checkPartnerId,$skipartnerids)) {
					$this->log("[Skip confirmation for wallet] Partner id ".$checkPartnerId." wallet can skip confirmation.", SNAP_LOG_DEBUG);
					$goldTx->skipconfirm = 1;
				}
				$goldTx->skipconfirm = 1;
				// Spot Sell Transaction
				// Just confirm the sell transaction.
				$goldTx = $app->mygtptransactionManager()->confirmBookGoldTransaction($goldTx, MyLedger::TYPE_SELL);
				$order = $goldTx->getOrder();
				$statusString = $goldTx->getStatusString();
				$data = [        
					'id' 			  => $order->id,        
					'refno'           => $disbursement->refno,
					'transactionid'   => $goldTx->refno,
					'amount'          => floatval(number_format($goldTx->originalamount, 2, '.', '')),
					'transaction_fee' => floatval(number_format($order->fee, 2, '.', '')),
					'total_transaction_amount'    => floatval(number_format($order->amount, 2, '.', '')),
					'status' => $statusString
				];            

				if (MyGoldTransaction::SETTLEMENT_METHOD_WALLET == $goldTx->settlementmethod) {
					$data['location'] = $disbursement->location;
				}            
			}
			// End TRX

			// $mytransactions = $app->mygoldtransactionStore()->searchView(false, 1)->select()
			// // ->where("status", 1)
			// ->where("achmykadno", $params['mykadno'])
			// ->andWhere("achcode", $params['accountholdercode'])
			// ->andWhere("ordpartnerid", $params['partnerid'])
			// ->execute();

			// $prefix = 'gtr_';
			// $records = [];
			// foreach($mytransactions as $mytransaction){
			// 	$mytransaction = $this->stripArrayKeyPrefix($mytransaction, $prefix);
			// 	array_push($records, $mytransaction);
			// }

			echo json_encode(array('success' => true, 'record'=>$data,'id'=>$goldTx->id,'orderType'=>$orderType)); 
		}catch(\Exception $e){
			$this->log("Failed to perform OTC Order for goldtransaction ID : ". $goldTx->id, SNAP_LOG_ERROR);
			// $return = [
			// 	'error' => true,
			// 	'message' => $e->getMessage()
			// ];
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
         
        
    }

	public function checkApprovalStatus($app, $params){
		// this function is to check for records that have status::pending_approval
		
		$constStatus = MyGoldTransaction::STATUS_PENDING_APPROVAL;
		$constStatus_pending = MyGoldTransaction::STATUS_PENDING_PAYMENT;
		try{
			if($params['id']){
				// search for gold trx record
				$goldTrx = $this->app->mygoldtransactionStore()->searchView()->select()
				->where('id', $params['id'])
				->one();
				$data = [];
				// if transaction found, check status
				if($goldTrx){
					if($params['approve'] == 'yes'){
						$isPendingApproval = $constStatus == $goldTrx->status ? true : false;
					}else{
						$isPendingPayment = $constStatus_pending == $goldTrx->status ? true : false;
					}
					$isPurchaseSuccessful = false;
					
				}

				if($params['approve']){
					if(!$isPendingApproval){
						// check status if transaction is success or fail
						$statusString = $goldTrx->getStatusString();
						//do status check and match
						//all status that identify as a successful transaction
						if(in_array($goldTrx->status, [MyGoldTransaction::STATUS_PAID, MyGoldTransaction::STATUS_CONFIRMED])){
							$isPurchaseSuccessful = true;
							// call and get gold trx information
							
						//all status that identify as a unsuccessful transaction
						}else if(in_array($goldTrx->status, [MyGoldTransaction::STATUS_PENDING_PAYMENT, MyGoldTransaction::STATUS_FAILED, MyGoldTransaction::STATUS_REVERSED, MyGoldTransaction::STATUS_REJECTED])){
							$isPurchaseSuccessful = false;
						}
						$order = $goldTrx->getOrder();
						$data = [
							'id' 			  => $order->id,
							'xau'          	  => floatval(number_format($order->xau, 3, '.', '')),
							'amount'          => floatval(number_format($goldTrx->originalamount, 2, '.', '')),
							'price'			  => floatval(number_format($goldTrx->ordprice, 2, '.', '') + number_format($order->discountprice, 2, '.', '')),
							'discountprice'   => floatval(number_format(abs($order->discountprice), 2, '.', '')),
							'discountAmount'  => floatval(number_format(abs($order->discountprice), 2, '.', '') * number_format($order->xau, 3, '.', '')),
							'transaction_fee' => floatval(number_format($order->fee, 2, '.', '')),
							'total_transaction_amount'    => floatval(number_format($order->amount, 2, '.', '')),
							'status' => $statusString,
						];
					}
				}else{
					if(!$isPendingPayment){
						// check status if transaction is success or fail
						$statusString = $goldTrx->getStatusString();
						//do status check and match
						//all status that identify as a successful transaction
						if(in_array($goldTrx->status, [MyGoldTransaction::STATUS_PAID, MyGoldTransaction::STATUS_CONFIRMED])){
							$isPurchaseSuccessful = true;
							// call and get gold trx information
							
						//all status that identify as a unsuccessful transaction
						}else if(in_array($goldTrx->status, [MyGoldTransaction::STATUS_FAILED, MyGoldTransaction::STATUS_REVERSED, MyGoldTransaction::STATUS_REJECTED])){
							$isPurchaseSuccessful = false;
						}
						$order = $goldTrx->getOrder();
						$data = [
							'id' 			  => $order->id,
							'xau'          	  => floatval(number_format($order->xau, 3, '.', '')),
							'amount'          => floatval(number_format($goldTrx->amount, 2, '.', '')),
							'price'			  => floatval(number_format($goldTrx->ordprice, 2, '.', '') + number_format($order->orddiscountprice, 2, '.', '')),
							'discountprice'   => floatval(number_format($order->orddiscountprice, 2, '.', '')),
							'discountAmount'  => floatval(number_format($order->orddiscountprice, 2, '.', '') * number_format($order->xau, 3, '.', '')),
							'transaction_fee' => floatval(number_format($order->fee, 2, '.', '')),
							'total_transaction_amount'    => floatval(number_format($order->amount, 2, '.', '')),
							'status' => $statusString,
						];
					}
				}
			}else{
				throw new \Exception("Please include search parameters");
			}


			echo json_encode(['success' => true, 'ispendingapproval' => $isPendingApproval,'ispendingpayment' => $isPendingPayment,  'ispurchasesuccessful' => $isPurchaseSuccessful, 'status' => $goldTrx->status, 'statusstring' => $statusString, 'record' => $data]);
		}catch (\Exception $e){
			$this->log("Failed to validate OTC Order Approval Status for goldtransaction ID : ". $params['id'], SNAP_LOG_ERROR);
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
		
	}

	public function approvePendingGoldTransactions($app, $params){
		// this function is to approve records that have status::pending_approval
		$constStatus = MyGoldTransaction::STATUS_PENDING_APPROVAL;

		try{
			if($params['id']){
				// search for gold trx record
				$goldTrx = $this->app->mygoldtransactionStore()->searchView()->select()
				->where('id', $params['id'])
				->one();
				$data = [];
				// if transaction found, check status
				if($goldTrx){
					$isPendingApproval = $constStatus == $goldTrx->status ? true : false;
					$settlementMethod = $goldTrx->settlementmethod;
				}

				// If transaction status is pending approval, activate transaction for part 2 
				if($isPendingApproval){
					$order = $goldTrx->getOrder();
					$accHolder = $app->myaccountholderStore()->getById($order->buyerid);
					$partner = $app->partnerStore()->getById($order->partnerid);
					$product = $app->productStore()->getById($order->productid);
					// $pricestream = $app->pricestreamStore()->getById($order->bookingpricestreamid);

					// to save the details of the checker
					$userId = $app->getUserSession()->getUserId();
					$user = $app->userStore()->getById($userId);
					$checker = $user->username;
					$remarks = $params['remarks'];
					$approvalcode = $params['approvalcode'];
					$goldTrx->checker = $checker;
					$goldTrx->remarks = 'Remarks: '.$remarks.'; Approval Code:'.$approvalcode;
					//save time while checker action
					$now = new \DateTime(date('Y-m-d H:00:00'));
					$goldTrx->actionon = $now->format('Y-m-d H:00:00');

					// Set pending payment to allow transaction
					$goldTrx->status = MyGoldTransaction::STATUS_PENDING_PAYMENT;			
					$goldTrx = $app->myGoldTransactionStore()->save($goldTrx);

					$goldTx = $app->mygtptransactionManager()
					->bookGoldTransactionOtcPartTwo($accHolder, $partner, $product,
							 	$goldTrx, $order, $settlementMethod);
					

					if ( Order::TYPE_COMPANYSELL == $order->type && $goldTx) {
						// Spot Buy Transaction
						if (in_array($settlementMethod, [MyGoldTransaction::SETTLEMENT_METHOD_FPX, MyGoldTransaction::SETTLEMENT_METHOD_WALLET, MyGoldTransaction::SETTLEMENT_METHOD_CASH, MyGoldTransaction::SETTLEMENT_METHOD_CASA])) {
							$payment = $app->mypaymentdetailStore()->getByField('sourcerefno', $goldTx->refno);
							// Skip location check for now ( paywall check )
							// $location = $payment->location;
		
							// if (! $location) {
							// 	$this->log(__METHOD__."(): Unable to obtain paywall location from payment detail");
							// 	throw GeneralException::fromTransaction(null, [
							// 		'message'   => 'Unable to retrieve payment wall location'
							// 	]);
							// }

							$order = $goldTx->getOrder();
							$data = [
								'id' 			  => $order->id,
								'fullname'        => $accHolder->fullname,
								'mykadno'         => $accHolder->mykadno,
								'accountnumber'   => $accHolder->accountnumber,
								'xau'   	 	  => $order->xau,
								'transactionid'   => $goldTx->refno,
								'location'        => $partner->name ?? null,
								'refno'           => $payment->paymentrefno,
								'transactionid'   => $goldTx->refno,
								'amount'          => floatval(number_format($goldTx->originalamount, 2, '.', '')),
								'transaction_fee' => floatval(number_format($order->fee, 2, '.', '')),
								'total_transaction_amount'    => floatval(number_format($order->amount, 2, '.', ''))
							];
						}
					} else {
						$disbursement = $app->mydisbursementStore()->getByField('transactionrefno', $goldTx->refno);
		
						/*TOYYIB wallet situation where GTP straight send to their wallet & receive status transaction*/
						//this is because transaction straight change to STATUS_PAID.It give error when trigger MyGtpTransactionManager::confirmBookGoldTransaction
						$checkPartnerId = $partner->id;
						$getskipartnerids = $app->getConfig()->{'mygtp.partnerids.skipconfirmation'};
						$skipartnerids = explode(',', $getskipartnerids);
						if(in_array($checkPartnerId,$skipartnerids)) {
							$this->log("[Skip confirmation for wallet] Partner id ".$checkPartnerId." wallet can skip confirmation.", SNAP_LOG_DEBUG);
							
						}
						$goldTx->skipconfirm = 1;
						// Spot Sell Transaction
						// Just confirm the sell transaction.
						$goldTx = $app->mygtptransactionManager()->confirmBookGoldTransaction($goldTx, MyLedger::TYPE_SELL);
						$order = $goldTx->getOrder();
						$data = [    
							'id' 			  => $order->id,            
							'refno'           => $disbursement->refno,
							'transactionid'   => $goldTx->refno,
							'amount'          => floatval(number_format($goldTx->originalamount, 2, '.', '')),
							'transaction_fee' => floatval(number_format($order->fee, 2, '.', '')),
							'total_transaction_amount'    => floatval(number_format($order->amount, 2, '.', ''))
						];            
		
						if (MyGoldTransaction::SETTLEMENT_METHOD_WALLET == $goldTx->settlementmethod) {
							$data['location'] = $disbursement->location;
						}            
					}
				}
			}else{
				throw new \Exception("Please include search parameters");
			}

			echo json_encode(array('success' => true, 'record'=>$data,'orderType'=>$order->type)); 
		}catch(\Exception $e){
			$this->log("Failed to perform OTC Order for goldtransaction ID : ". $goldTx->id, SNAP_LOG_ERROR);
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
		
	}

	public function rejectPendingGoldTransactions($app, $params){
		// this function is to approve records that have status::pending_approval
		$constStatus = MyGoldTransaction::STATUS_PENDING_APPROVAL;
		try{
			if($params['id']){
				// search for gold trx record
				$goldTrx = $this->app->mygoldtransactionStore()->searchView()->select()
				->where('id', $params['id'])
				->one();
				$data = [];
				// if transaction found, check status
				if($goldTrx){
					$isPendingApproval = $constStatus == $goldTrx->status ? true : false;
					$settlementMethod = $goldTrx->settlementmethod;
				}

				// If transaction status is pending approval, activate transaction for part 2 
				if($isPendingApproval){
					$order = $goldTrx->getOrder();
					$accHolder = $app->myaccountholderStore()->getById($order->buyerid);

					// to save the details of the checker
					$userId = $app->getUserSession()->getUserId();
					$user = $app->userStore()->getById($userId);
					$checker = $user->username;
					$goldTrx->checker = $checker;
					//save time while checker action
					$now = new \DateTime(date('Y-m-d H:00:00'));
					$goldTrx->actionon = $now->format('Y-m-d H:00:00');
					//save the remarks after reject 
					$goldTrx->actionon = $params['remarks'];

					$goldTrx->status = MyGoldTransaction::STATUS_REJECTED;
					$goldTx = $app->myGoldTransactionStore()->save($goldTrx);

					$data = [
						'id' 			  => $order->id,
						'fullname'        => $accHolder->fullname,
						'mykadno'         => $accHolder->mykadno,
						'accountnumber'   => $accHolder->accountnumber,
						'xau'   	 	  => $order->xau,
						'transactionid'   => $goldTx->refno,
						'transactionid'   => $goldTx->refno,
						'amount'          => floatval(number_format($goldTx->originalamount, 2, '.', '')),
						'transaction_fee' => floatval(number_format($order->fee, 2, '.', '')),
						'total_transaction_amount'    => floatval(number_format($order->amount, 2, '.', ''))
					];
				}
			}else{
				throw new \Exception("Please include search parameters");
			}


			echo json_encode(array('success' => true, 'record'=>$data,'orderType'=>$order->type)); 
		}catch(\Exception $e){
			$this->log("Failed to reject OTC Order for goldtransaction ID : ". $goldTx->id, SNAP_LOG_ERROR);
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
		
	}

	public function printSpotOrderOTC($app, $params){

		
		// require_once '../../vendor/phpoffice/phpword/src/PhpWord/Autoloader.php';
		// \PhpOffice\PhpWord\Autoloader::register();

		
		$spotordermanager= $app->spotorderManager();
		
		// // Check if Buy Or Sell
		try{
			// do double check
			// if have data do preview
			//print_r($params['data']);exit;

			$projectBase = $app->getConfig()->{'projectBase'};
			if($projectBase != 'POSARRAHNU'){
				if(!$params['data']){
					
					if(!$params['customerid']){
						$orderPdf = $spotordermanager->printSpotOrderOTC($params['orderid']);
					}else{
						$orderPdf = $spotordermanager->printSpotOrderOTC($params['orderid'], $params['customerid']);
					}

					//print_r($orderPdf);exit;

					$userId = $app->getUserSession()->getUserId();
					$user = $app->userStore()->getById($userId);
					$teller = $user->username;

					$projectbase = $app->getConfig()->{'projectBase'};
					
					if($projectbase == 'BSN'){
						if($params['printType'] == 'printAqad') {
							$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/aqad_template.docx');
						}else{
							$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/receipt_template.docx');
						}
						$finalAcePriceTitle = strtoupper($orderPdf['details_title']);
					}else{
						$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/alrajhi_receipt_template.docx');
						$finalAcePriceTitle = $orderPdf['finalAcePriceTitle'];
					}
					

					$templateProcessor->setValues([
						'partner_name' => $orderPdf['partner_name'],
						'receipt_no' => $orderPdf['receipt_no'],
						'order_no' => $orderPdf['transactionid'],
						'date' => $orderPdf['date'],
						'full_name' => $orderPdf['fullname'],
						'finalAcePriceTitle' => $finalAcePriceTitle,
						'finalAcePriceLabel' => $orderPdf['finalAcePriceLabel'],
						'finalAcePriceTitle_BM'         => $orderPdf['finalAcePriceTitle_BM'],
						'finalAcePriceTitle_desc'       => $orderPdf['finalAcePriceTitle_desc'],
						'finalAcePriceTitle_BM_desc'    => $orderPdf['finalAcePriceTitle_BM_desc'],
						'finalAcePrice' => $orderPdf['finalAcePrice'],
						'details_title' => $orderPdf['details_title'],
						'details_BM_title' => $orderPdf['details_BM_title'],
						'details_title_2' => strtolower($orderPdf['details_title']),
						'details_BM_title_2' => strtolower($orderPdf['details_BM_title']),
						'details_desc_BM_title' => strtoupper($orderPdf['details_desc_BM_title']),
						'details_desc_BM_title_2' => $orderPdf['details_desc_BM_title'],
						'final_total' => $orderPdf['final_total'],
						'weight' => $orderPdf['xau'],
						'teller' => $teller,
						'teller_id' => $userId,
						'accountholder_code' => $orderPdf['accountholdercode'],
						'casa_bankaccount' => $orderPdf['casa_bankaccount'],
						'mykad_no'                      => $orderPdf['mykad_no'],
						'join_nok_mykadno'              => $orderPdf['nok_mykadno'],
						'nok_full_name'                 => '',
						'join_full_name'                => '',
						'status' => 'Successful',
						
					]);
					

					if($projectbase == 'BSN'){
						if($params['printType'] == 'printAqad') {
							$filename = strtoupper($orderPdf['details_title']).'_Order_Confirmation_'.$orderPdf['accountholdercode'].'.docx';
						}else{
							$filename = $orderPdf['finalAcePriceTitle'].'_Order_Receipt_'.$orderPdf['transactionid'].'.docx';
						}
					}else{
						$filename = $orderPdf['finalAcePriceTitle'].'_Order_Receipt_'.$orderPdf['transactionid'].'.docx';
					}
					

					$templateProcessor->saveAs('word/'.$filename);
					$fileUrl = 'word/'.$filename;

                                        $command = "sudo -u siteadm /usr/local/nginx/html/gtp/source/snapapp_otc/printScript.sh $filename";
                                        $output = shell_exec($command);

                                        $pattern = '/pdf\/(.*?)\.pdf/';
                                        preg_match($pattern, $output, $matches);
				        $pdfPath = $matches[1];

					echo json_encode(['success'=> true, 'url'=> 'pdf/'.$pdfPath.'.pdf']);
					//return("word/".$filename);
					
				}else{
					// do preview

					$userId = $app->getUserSession()->getUserId();
					$user = $app->userStore()->getById($userId);
					$partnerId = $user->partnerid;

					$userobj = $app->partnerStore()->getById($partnerId);
					$partnername = $userobj->name;

					$data = json_decode($params['data']);

					//print_r($data);exit;
					if($data->type == 'sell' ){
						$ordertype = Order::TYPE_COMPANYBUY;
					}else{
						$ordertype = Order::TYPE_COMPANYSELL;
					}
					
					if(Order::TYPE_COMPANYBUY == $ordertype){
						$finalAcePriceTitle = "SELL";
						$details_title = "Sale";
						$details_BM_title = "Jualan";
						$details_desc_BM_title = "Penjualan";
						$finalAcePriceLabel = "Sell Final Price (RM/g)";
						$orderFeeLabel = "Refining Fee";
					}else if (Order::TYPE_COMPANYSELL == $ordertype){
						$finalAcePriceTitle = "PURCHASE";
						$details_title = "Purchase";
						$details_BM_title = "Belian";
						$details_desc_BM_title = "Pembelian";
						$finalAcePriceLabel = "Purchase Final Price (RM/g)";
						$orderFeeLabel = "Premium Fee";
					}else{
						$finalAcePriceTitle = "-";
						$finalAcePriceLabel = "-";
						$orderFeeLabel = "-";
					}

					$datetime = str_replace("GMT 0800 (Malaysia Time)","",$data->date);
					$datetime = strtotime($datetime);
					$datetime = date('Y-m-d H:i:s', $datetime);

					$finalAcePrice = number_format(preg_replace("/[^0-9.]/", "", $data->price),2);
					$weight = number_format($data->xau,3);
					$totalbuy = number_format(preg_replace("/[^0-9.]/", "", $data->amount),2);
					$finaltotal = number_format(preg_replace("/[^0-9.]/", "", $data->finaltotal),2);

					$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/aqad_template.docx');

					$templateProcessor->setValues([
						'partner_name' => $partnername,
						'date' => $datetime,
						'full_name' => $data->fullname,
						'mykad_no'  => $data->mykadno,
						'accountholder_code'  => $data->accountholdercode,
						'casa_bankaccount'  => $data->accountnumber,
						'finalAcePriceTitle' => strtoupper($details_title),
						'finalAcePriceLabel' => $finalAcePriceLabel,
						'details_title' => $details_title,
						'details_BM_title' => $details_BM_title,
						'details_title_2' => strtolower($details_title),
						'details_BM_title_2' => strtolower($details_BM_title),
						'details_desc_BM_title' => strtoupper($details_desc_BM_title),
						'details_desc_BM_title_2' => $details_desc_BM_title,
						'finalAcePrice' => $finalAcePrice,
						'final_total' => $finaltotal,
						'weight' => $weight,
						'teller' => $data->teller,
					]);
					$filename = $finalAcePriceTitle.'_Order_Confirmation'.$data->accountholdercode.'.docx';

					$templateProcessor->saveAs('word/'.$filename);
					$fileUrl = 'word/'.$filename;
					//$pdfDir = 'pdf/';
					//$command = 'soffice --headless --convert-to pdf ' . escapeshellarg($fileUrl) .' --outdir '. escapeshellarg($pdfDir);
					
					// $output = shell_exec($command);

					// if ($output !== null) {
					// 	echo $output;
					// } else {
					// 	echo 'Conversion failed. No output received.';
					// }
					//return("word/".$filename);

					$command = "sudo -u siteadm /usr/local/nginx/html/gtp/source/snapapp_otc/printScript.sh $filename";
                                        $output = shell_exec($command);

                                        $pattern = '/pdf\/(.*?)\.pdf/';
                                        preg_match($pattern, $output, $matches);
                                        $pdfPath = $matches[1];

                                        echo json_encode(['success'=> true, 'url'=> 'pdf/'.$pdfPath.'.pdf']);
					//$orderPdf = $spotordermanager->printSpotOrderOTCPreview($data);
				}
			}else{

				if(!$params['customerid']){
					$orderPdf = $spotordermanager->printSpotOrder($params['orderid']);
				}else{
					$orderPdf = $spotordermanager->printSpotOrder($params['orderid'], $params['customerid']);
				}
				if ($orderPdf){
					$order = $app->orderStore()->getById($params['orderid']);
					$developmentEnv = $app->getConfig()->{'snap.environtment.development'};
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
				
				$orderPdf = $this->completePdf($orderPdf,$params['partnercode']);
	
	
				// ob_start();
				// include dirname(__FILE__).'/../../vendor/spipu/html2pdf/examples/res/example15.php';
				// $content = ob_get_clean();
				// $html2pdf->writeHTML($content);
				
				$html2pdf->pdf->setTitle($filename);
				$html2pdf->writeHTML($orderPdf);
	
				echo json_encode(['success' => true, 'return' => $html2pdf->output($filename.'.pdf')]);
			}
			
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
	   
       
       
        //return $html2pdf->output('FILENAME.pdf');
	}

	private function completePdf($content, $projectbase){
        $f = 1;
        
        $wrap_start = '<page backtop="10mm" backbottom="10mm" backleft="20mm" backright="20mm">
		<page_header>
			<img style="width: 100%;" src="src/resources/images/'.strtolower($projectbase).'_header.png">
		
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
		$header = '';
        $footer = '';
        

        $html = $wrap_start.$header.$footer.$content.$wrap_end;

        return $html;
	}

	private function stripArrayKeyPrefix(array $input, $prefix)
	{
		$return = array();
		foreach ($input as $key => $value) {
			if (strpos($key, $prefix) === 0)
				$key = substr($key, strlen($prefix));

			if (is_array($value))
				$value = $this->stripArrayKeyPrefix($value, $prefix); 

			$return[$key] = $value;
		}
		return $return;
	}

	// Perform check to filter records
	private function getPartnerIdForBranch() {
		$app = App::getInstance();
		$userId = $app->getUserSession()->getUserId();
		$user = $app->userStore()->getById($userId);
		if($user->partnerid > 0 ){
			$partnerid = $user->partnerid;
		}else{
			$partnerid = 0;
		}     
		return $partnerid;
	}
	// private function removePrefix(array $input, $prefix) {

	// 	$return = array();
	// 	foreach ($input as $key => $value) {
	// 		if (strpos($key, $prefix) === 0)
	// 			$key = substr($key, strlen($prefix));
	
	// 		if (is_array($value))
	// 			$value = $this->removePrefix($value); 
	
	// 		$return[$key] = $value;
	// 	}
	// 	return $return;
	// }

	//OTC function to check whether the order is expired
	public function getInterval($app, $params){
		$goldTrx = $app->mygoldtransactionStore()->searchView()->select()
			->where('id', $params['transactionId'])
			->one();

		$now = new \DateTime("now", $app->getUserTimezone());
		// echo $payment->createdon->format('Y-m-d h:i:s').' '.$goldTx->createdon->format('Y-m-d h:i:s');exit;
		$orderDate = $goldTrx->createdon->format('Y-m-d H:i:s');
		$interval = $goldTrx->createdon->diff($now);
		$totalinterval = $interval->s+($interval->i * 60)+($interval->h * 3600);
		$now = $now->format('Y-m-d H:i:s');


		if($totalinterval < 180){
			return json_encode(array('success' => true, 'interval'=> $totalinterval,'currentdatetime'=> $now,'orderdate'=> $orderDate));
		}else{
			return json_encode(array('success' => false, 'interval'=> $totalinterval,'currentdatetime'=> $now,'orderdate'=> $orderDate));
		}
	}
	
	public function getCasaAccounts($app, $params) {	
		$toReturn = array();
		// Set fields to call getcasainfo
		// $params['partyId'] = $paddedNumber = str_pad($params['searchfield'], 16, '0', STR_PAD_LEFT);

		//print_r($params);exit;
		$casa = BaseCasa::getInstance($app->getConfig()->{'otc.casa.api'});
		//print_r('test');exit;
		$data = $casa->getCasaInfo($params);
		if($data['success']){
			// do something
		
			//branchIdent-AccTypeValue-AccIdentValue 
			foreach ($data['data'] as $record) {
				$string = $record['BranchIdent'] . '-' . $record['AcctTypeValue'] . '-' . $record['AcctIdentValue'];
				$toReturn[]= array( 
					'accountnumber' =>  $record['AcctIdentValue'],
					'combination' =>  $string,
				);
			}
		}

		return $toReturn;
    }
}
