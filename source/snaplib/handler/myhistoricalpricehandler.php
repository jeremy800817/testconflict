<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\Partner;
Use \Snap\object\VaultItem;
Use \Snap\object\MyLedger;
Use \Snap\object\MyGoldTransaction;
Use \Snap\object\Order;
Use \Snap\object\MyAccountHolder;
use Snap\sqlrecorder;
use Snap\object\MyHistoricalPrice;
use Snap\object\OtcPricingModel;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class myhistoricalpricehandler extends CompositeHandler
{

	function __construct(App $app)
	{
		// parent::__construct('/root/bmmb;/root/go;/root/one;/root/onecall;/root/air;/root/mcash;/root/ktp;/root/kopetro;/root/kopttr;/root/pkbaffi;/root/bumira;', 'pricealert');

		$this->mapActionToRights('getPriceHistory', '/root/bsn/analytics;/root/alrajhi/analytics;/root/posarrahnu/analytics;');
        $this->mapActionToRights('getvaluedashboard', '/all/access');
        $this->mapActionToRights('VaultDataAPI', '/all/access');
        $this->mapActionToRights('exportExcel', '/all/access');

		$this->app = $app;

        $currentStore = $app->myhistoricalpriceStore();
		$this->addChild(new ext6gridhandler($this, $currentStore, 1));
	}

	 // Register accounts
     /*
     * Params 
     * 1) page_size
     * date_from
     * date_to
     * partnerid
     * 
     * */
     public function getPriceHistory($app, $params)
     {
         try {
 
            if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
            }
            else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
            }
            else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
            }

            $partner = $this->app->partnerStore()->getById($partnerId);
            $product  = $this->app->productStore()->getByField('code', 'DG-999-9');
            $provider = $app->priceProviderStore()->getForPartnerByProduct($partner, $product);

            $dateFrom = strtotime($params['date_from']);
            $dateTo = strtotime($params['date_to']);
            $startDate = date('Y-m-d', $dateFrom) . ' 00:00:00';
            $endDate = date('Y-m-d', $dateTo) . ' 23:59:59';
            
            $startDate = \Snap\Common::convertUserDatetimeToUTC(new \DateTime($startDate))->format('Y-m-d H:i:s');
            $endDate = \Snap\Common::convertUserDatetimeToUTC(new \DateTime($endDate))->format('Y-m-d H:i:s');
            // $conditionRecorder = new sqlRecorder();
            // $conditionRecorder->where('priceproviderid', $provider->id);
            // $conditionRecorder->where('priceon', '>=', $startDate);
            // $conditionRecorder->where('priceon', '<', $endDate);
            // $conditionRecorder->where('status', MyHistoricalPrice::STATUS_ACTIVE);

            $priceHistories = $this->app->myhistoricalpriceStore()->searchTable()->select()
                                ->where('priceproviderid', $provider->id)
                                // temporarily use one with data
                                // ->where('priceproviderid', 50)
                                ->where('priceon', '>=', $startDate)
                                ->where('priceon', '<', $endDate)
                                ->where('status', MyHistoricalPrice::STATUS_ACTIVE)
                                ->orderBy('priceon', 'asc')
                                ->execute();
                                // ['open', 'close', 'high', 'low', 'priceproviderid', 'priceon']
            // $listings = $this->getListing($app->myhistoricalpriceStore(), $params['page_number'], $params['page_size'], true, ['open', 'close', 'high', 'low', 'priceproviderid', 'priceon'], ['priceon' => 'DESC'], 0, $conditionRecorder);
            foreach( $priceHistories as $priceHistory) {
                $toReturn[]= array( 
                    'open_sell' => floatval($priceHistory->open), 
                    'close_sell' => floatval($priceHistory->close), 
                    'high_sell' => floatval($priceHistory->high), 
                    'min_sell' => floatval($priceHistory->low),
    
                    'priceproviderid' => $priceHistory->priceproviderid, 
                   
                    'date' => $priceHistory->priceon->format('Y-m-d'),
                 
                );
            
            }
             
             echo json_encode(['success' => true, 'records' => $toReturn]);
         } catch (\Exception $e) {
             echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
         }
     }

    public function getvaluedashboard($app, $params){

        try{
            if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
                $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();

                if(isset($params['filter']) && $params['filter'] == 'true'){
                    $filterPartners = $this->app->partnerStore()->searchTable()->select()
                        ->where('group','=', $partnerId)
                        ->andWhere('state', '=', $params['state'])
                        ->execute();

                    if(!$filterPartners){
                        throw new \Exception ('Branch not found in selected state !');
                    }

                    $filterGroupPartnerIds = array();
                    foreach ($filterPartners as $partner){
                        array_push($filterGroupPartnerIds,$partner->id);
                    }
                }

                $currentuserId = $app->getUserSession()->getUserId();
                $currentuser = $app->userStore()->getById($currentuserId);
                $currentpartnerId = $currentuser->partnerid;

                // if($currentuser->state){
                //     $partners = array();
                
                //     $partnerArr = $this->app->partnerStore()->searchView(true,1)->select()
                //         ->where('state', $currentuser->state)
                //         ->andWhere('group', $partnerId)
                //         ->execute();
                    
                //     $partners = $partnerArr;
                // }
                
                // Do checking
                // $partnerId = $this->getPartnerIdForBranch();
                $groupPartnerIds = array();
                foreach ($partners as $partner){
                    array_push($groupPartnerIds,$partner->id);
                }
            }
            else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
                $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
                // Do checking
                // $partnerId = $this->getPartnerIdForBranch();
                $groupPartnerIds = array();
                foreach ($partners as $partner){
                    array_push($groupPartnerIds,$partner->id);
                }
            }
            else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
                $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
                // Do checking
                // $partnerId = $this->getPartnerIdForBranch();
                $groupPartnerIds = array();
                foreach ($partners as $partner){
                    array_push($groupPartnerIds,$partner->id);
                }
            }

            $partner= $this->app->partnerStore()->getById($partnerId);
            $product  = $this->app->productStore()->getByField('code', 'DG-999-9');
            $provider = $app->priceProviderStore()->getForPartnerByProduct($partner, $product);
            
            $partnercode = $partner->code;  
            $responseData = $this->VaultDataAPI($partnercode);

            $vaultAmount = $responseData->vaultamount;
            // $totalCustomerHolding = $responseData->totalcustomerholding;
            // $totalBalance = $responseData->totalbalance;

            // print_r($groupPartnerIds);
            // exit;

            

            if($this->app->getConfig()->{'projectBase'} == 'BSN'){
                $bmmbxau = 0;
            }else{
                
                $vaultLocation = $this->app->vaultlocationStore()->searchView()->select()->where('partnerid', $partner->id)->one();
                $items = $this->app->vaultitemStore()->searchView()->select()->where('partnerid', $partner->id)->andWhere('vaultlocationid', $vaultLocation->id)->execute();
                $bmmbxau = $ordxau = 0;
        
                foreach ($items as $item){
                    $bmmbxau = $item->weight + $bmmbxau;
                    $bmmbxau = $partner->calculator(false)->round($bmmbxau);
                }
            }

            $balance = $this->getPartnerInventoryBalance($partner, $groupPartnerIds);
            $customerHolding = $bmmbxau - $balance;
            $customerHolding = $partner->calculator(false)->round($customerHolding);

            $totalCustomerHolding = $customerHolding;

            $totalBalance = (float) str_replace(',', '', $vaultAmount) - (float) str_replace(',', '', $totalCustomerHolding);

            if($currentpartnerId == 0){

                if(isset($params['filter']) && $params['filter'] == 'true'){
                    $groupPartnerIds = $filterGroupPartnerIds;
                }
                
                $totalaccountholder = $this->app->myaccountholderStore()->searchView()->select()
                                ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
                                ->whereIn('partnerid', $groupPartnerIds)
                                ->count('id'); 

                $totalbuygold = $this->app->mygoldtransactionStore()->searchView()->select()
                                ->where('ordtype', 'CompanyBuy')
                                ->andwhere('status' , MyGoldTransaction::STATUS_CONFIRMED)
                                ->whereIn('ordpartnerid', $groupPartnerIds)
                                ->sum('ordamount');

                $totalsellgold = $this->app->mygoldtransactionStore()->searchView()->select()
                                ->where('ordtype', 'CompanySell')
                                ->andwhere('status' , MyGoldTransaction::STATUS_CONFIRMED)
                                ->whereIn('ordpartnerid', $groupPartnerIds)
                                ->sum('ordamount'); 

            }else{
                $totalaccountholder = $this->app->myaccountholderStore()->searchView()->select()
                                ->where('partnerid', $partnerId)
                                ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
                                ->count('id'); 

                $totalbuygold = $this->app->mygoldtransactionStore()->searchView()->select()
                                ->where('ordtype', 'CompanyBuy')
                                ->andWhere('ordpartnerid', $partnerId)
                                ->andwhere('status' , MyGoldTransaction::STATUS_CONFIRMED)
                                ->sum('ordamount');

                $totalsellgold = $this->app->mygoldtransactionStore()->searchView()->select()
                                ->where('ordtype', 'CompanySell')
                                ->andWhere('ordpartnerid', $partnerId)
                                ->andwhere('status' , MyGoldTransaction::STATUS_CONFIRMED)
                                ->sum('ordamount'); 
            }

            $tier = $this->app->otcpricingmodelStore()->searchTable()
                    ->select()
                    ->where('type', 'AMOUNT')
                    ->andwhere('priceproviderid', $provider->id)
                    ->andWhere('status', OtcPricingModel::STATUS_ACTIVE)
                    ->orderby('id', 'desc')
                    ->limit(3)
                    ->get();
            
            $tierdata = [];
            foreach ($tier as $index => $value) {
                $data = [
                    'sellmarginpercent' => $value->sellmarginpercent,
                    'buymarginpercent' => $value->buymarginpercent,
                    'sellmarginamount' => $value->sellmarginamount,
                    'buymarginamount' => $value->buymarginamount,
                ];
                $tierdata[] = $data;
            }

			
            $data = [
                'totalAccountHolder' => $totalaccountholder,
                // 'totalBuyGold' => $totalbuygold,
                // 'totalSellGold' => $totalsellgold,   
                'totalBuyGold' => $totalbuygold ?? 0,
                'totalSellGold' => $totalsellgold ?? 0,   
                'vault' => $vaultAmount,
                'totalcustomerholding' => $totalCustomerHolding,
                'balance' => $totalBalance,
                'tier' => $tierdata,
            ];

            // Return the data as a JSON response
            echo json_encode(['success' => true, 'data' => $data]);

        }catch (\Exception $e) {
             echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
         }
    }

    public function VaultDataAPI($partnercode){

        try{
            $postdata = [
                "version" => "1.0my",
		        "merchant_id" =>$partnercode,
		        "projectbase" => $this->app->getConfig()->{'projectBase'},
                "action" => "vault_store_inventory"
            ];
        
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
            ];
			
			$proxyUrl = $this->app->getConfig()->{'gtp.server.proxyurl'};
			if ($proxyUrl) {
				$options['proxy'] = $proxyUrl;
			}
        
            $client         = new \GuzzleHttp\Client($options);
            $url            = $this->app->getConfig()->{'mygtp.api.url'};
            // $url            = "http://localhost:8081/gtp/source/snapapp/mygtp.php";
            $response       = $client->post($url, ['json' => $postdata]);

            return json_decode($response->getBody()->getContents());
        }
        catch(\Throwable $e){
            echo $e->getMessage();
            exit;
        }
    }

    public function getPartnerInventoryBalance(Partner $partner,  $groupPartnerIds)
    {


        if ($partner->id <= 0) {
            throw GeneralException::fromTransaction([], [
                'message' => "Partner does not have ID"
            ]);
        }

        $currentuserId = $this->app->getUserSession()->getUserId();
        $currentuser = $this->app->userStore()->getById($currentuserId);
        $currentpartnerId = $currentuser->partnerid;

        

        $ledgerStore = $this->app->myledgerStore();
        $ledgerHdl = $ledgerStore->searchTable(false);
        $p = $ledgerStore->getColumnPrefix();

        if($currentpartnerId == 0){
            $sum = $ledgerHdl
                ->select([$ledgerHdl->raw("SUM({$p}credit) - SUM({$p}debit) AS sum")])
                ->where('accountholderid', 0)
                ->andWhere('partnerid', 'IN',  $groupPartnerIds)
                //->andWhere('partnerid',  $partner->id)
                ->andWhere('status', MyLedger::STATUS_ACTIVE)
                ->one()['sum'];
        }else{
            $sum = $ledgerHdl
                ->select([$ledgerHdl->raw("SUM({$p}credit) - SUM({$p}debit) AS sum")])
                ->where('accountholderid', 0)
                // ->andWhere('partnerid', 'IN',  $groupPartnerIds)
                ->andWhere('partnerid',  $partner->id)
                ->andWhere('status', MyLedger::STATUS_ACTIVE)
                ->one()['sum'];
        }
        

        return floatval($sum);
    }

    function exportExcel($app, $params){

        if(isset($params['partner']) && $params['partner'] == 'BSN'){
            $partnerId = $app->getConfig()->{'otc.bsn.partner.id'};
            $partner = $app->partnerStore()->getById($partnerId);
            $priceProvider = $app->priceproviderStore()->getByField('pricesourceid', $partner->pricesourceid);
        }

        
        $dateStart = $params['dateStart'];
        $dateEnd = $params['dateEnd'];
		
        $conditions = function ($q) use ($priceProvider, $dateStart, $dateEnd) {
            
            if ($priceProvider) {
                $q->where('priceproviderid', $priceProvider->id);
            }
            if ($dateStart) {
                $q->where('createdon', '>=', $dateStart);
            } 
    
            if ($dateEnd) {
                $q->where('createdon', '<=', $dateEnd);
            }
        };

        $store = $app->myhistoricalpriceStore();
        // $prefix = $store->getColumnPrefix();

        // $kyc = new Expression("CASE WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_FAILED . " THEN 'Failed'" .
        //     "WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_INCOMPLETE . " THEN 'Incomplete'" .
        //     "WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PASSED . " THEN 'Passed'" .
        //     "WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PENDING . " THEN 'Pending'" .
        //     "ELSE 'Pending' END as `{$prefix}kycstatus`");
        // $kyc->original = 'kycstatus';

        $header = [
            (object) ['text' => 'ID', 'index' => 'id'],
            (object) ['text' => 'Open Price', 'index' => 'open', 'decimal' => 2],
            (object) ['text' => 'High Price', 'index' => 'high', 'decimal' => 2],
            (object) ['text' => 'Low Price', 'index' => 'low', 'decimal' => 2],
            (object) ['text' => 'Close Price', 'index' => 'close', 'decimal' => 2],
            (object) ['text' => 'Status', 'index' => 'status'],
            (object) ['text' => 'Price On', 'index' => 'createdon'],
        ];


        /** @var \Snap\manager\ReportingManager $reportingManager */
        $reportingManager = $app->reportingManager();
        return $reportingManager->generateMyGtpReport($store, $header, 'Historical_Price', null, $conditions, null, 2, false, false);
    }
}
