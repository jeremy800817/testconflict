<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Snap\api\exception\GeneralException;
use Snap\IObservable;
use Snap\IObservation;
use Snap\IObserver;
use Snap\object\MyBank;
use Snap\object\MyCloseReason;
use Snap\object\MyLedger;
use Snap\object\MyLocalizedContent;
use Snap\object\MyOccupationCategory;
use Snap\object\MyOccupationSubCategory;
use Snap\object\MyPartnerSetting;
use Snap\object\Partner;
use Snap\object\TradingSchedule;
use Snap\object\VaultItem;
use Snap\object\VaultLocation;
use Snap\TLogging;

/**
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 21-Oct-2020
 */

class MyGtpPartnerManager implements IObserver
{
    use TLogging;

    /** @var \Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }
    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        if ($changed instanceof \Snap\manager\BankVaultManager && $state->isAssignAction()) {
            // Vault transfers
            $this->handleVaultTransferInventoryTracking($state->target, $state->otherParams['previousVaultLocationId']);
        }
    }

    /**
     * Gets the configuration for the requested partner
     * @param Partner $partner      The partner
     * 
     * @return array
     */
    public function getPartnerConfig(Partner $partner, $language = null)
    {
        $app = $this->app;
        $config = [];

        // $parsvcStore = $app->partnerStore()->getRelatedStore('services');

        /** @var PartnerService[] $services */
        $services = $partner->getServices();
        $productIds = array_keys($services);
        $products = $app->productStore()->searchTable()->select()
                        ->whereIn('id', $productIds)
                        ->orderBy('weight', 'ASC')
                        ->execute();

        foreach ($products as $product) {
            $service = $services[$product->id];
            if (!$service->canBuy() && !$service->canSell() && !$service->canRedeem()) {
                continue;
            }

            if ($service->canBuy() || $service->canSell()) {
                $config['products'][$product->code]['buyclickminxau']  = floatval($service->buyclickminxau);
                $config['products'][$product->code]['buyclickmaxxau']  = floatval($service->buyclickmaxxau);
                $config['products'][$product->code]['sellclickminxau'] = floatval($service->sellclickminxau);
                $config['products'][$product->code]['sellclickmaxxau'] = floatval($service->sellclickmaxxau);

                $condition_type = $service->specialpricetype ? $service->specialpricetype : 0;
                $condition_value = $service->specialpricecondition ? $service->specialpricecondition : 0;
                $customer_buy = $service->specialpricecompanyselloffset ? $service->specialpricecompanyselloffset : 0;
                $customer_sell = $service->specialpricecompanybuyoffset ? $service->specialpricecompanybuyoffset : 0;
                $config['products'][$product->code]['condition_type'] = $condition_type;
                $config['products'][$product->code]['condition_value'] = floatval($condition_value);
                $config['products'][$product->code]['buy_offset'] = floatval($customer_buy);
                $config['products'][$product->code]['sell_offset'] = floatval($customer_sell);

            }

            if ($service->canRedeem()) {
                $config['conversion_list'][] = ['weight' => floatval($product->weight), 'code' => $product->code, 'name' => $product->name];
            }
        }
            

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        // Removed 
        // $config['dailybuylimitxau'] = $partner->dailybuylimitxau;
        // $config['dailyselllimitmxau'] = $partner->dailyselllimitxau;
        // $config['clickminxau'] = floatval($settings->minsubsequentxau);
        // $config['clickmaxxau'] = floatval($settings->maxsubsequentxau);
        $config['min_initial'] = floatval($settings->mininitialxau);
        $config['min_balance'] = floatval($settings->minbalancexau);
        // $config['min_subsequent'] = floatval($app->getConfig()->{'mygtp.app.minsubsequent'});
        $config['min_disbursement'] = floatval($settings->mindisbursement);
        $config['upcoming_maintenance_hours'] = $this->getUpcomingMaintenanceHours($partner);
        $config['can_trade_now'] = $app->spotorderManager()->canTradingProceedNow($partner);


        $languages = [
            MyLocalizedContent::LANG_BAHASA,
            MyLocalizedContent::LANG_ENGLISH,
            MyLocalizedContent::LANG_CHINESE
        ];

        if (!in_array($language, $languages)) {
            $language = MyLocalizedContent::LANG_ENGLISH;
        }

        $banks = $app->mybankStore()->searchTable()
            ->select()
            ->where('status', MyBank::STATUS_ACTIVE)
            ->execute();

        foreach ($banks as $bank) {
            $config['bank_list'][] = ['id' => intval($bank->id), 'name' => $bank->name];
        }

        /** @var MyCloseReason[] $reasons */
        $reasons = $app->myclosereasonStore()->searchTable()
            ->select()
            ->where('status', MyCloseReason::STATUS_ACTIVE)
            ->execute();

        foreach ($reasons as $reason) {
            $reason->language = $language;
            $config['close_reasons'][] = ['id' => intval($reason->id), 'reason' => $reason->reason];
        }

        /** @var MyOccupationCategory[] $occupationCats */
        $occupationCats = $app->myoccupationcategoryStore()->searchTable()
                            ->select()
                            ->where('status', MyOccupationCategory::STATUS_ACTIVE)
                            ->execute();

        foreach ($occupationCats as $occCat) {
            
                
            $occCat->language = $language;
            $checkSub = $app->myoccupationsubcategoryStore()->searchTable()->select()->where('occupationcategoryid', $occCat->id)->execute();
         
            if($checkSub){
                // check if subcat occupation id is same as maincat
                // add subcat, otherwise skip
                $array['subcategory'] = [];
                foreach ($checkSub as $sub){
                    $sub->language = $language;
                    if($sub->occupationcategoryid == $occCat->id){
                        $array['subcategory'][] = [
                                                    'id' => intval($sub->id),
                                                    'description' => $sub->name,
                        ];
                    }
                }
                   
                $config['occupation_category'][] = ['id' => intval($occCat->id), 
                                                    'description' => $occCat->name,
                                                    'subcategory' => $array['subcategory']
                                                    ];           
              
            }else {
                $config['occupation_category'][] = ['id' => intval($occCat->id), 
                                                    'description' => $occCat->name,
                                                    ];
            }
  
        }

        /** @var PartnerBranchMap[] $branches */
        $branches = $partner->getBranches();
        foreach ($branches as $branch) {
            $config['branches'][] = ['code' => $branch->code, 'name' => $branch->name];
        }

        return $config;
    }


    /**
     * Get the upcoming maintenance offhours
     *
     * @param Partner $partner
     * @return array
     */
    public function getUpcomingMaintenanceHours(Partner $partner)
    {
        $records = $this->app->tradingScheduleStore()
            ->searchTable()
            ->select()
            ->where('type', TradingSchedule::TYPE_STOP)
            ->andWhere('categoryid', $partner->tradingscheduleid)
            ->andWhere(function ($q) {
                $q->where('endat', '>=', $q->raw('NOW()'));
            })
            ->execute();

        $tradingOffHours = [];
        $now = \Snap\Common::convertUTCToUserDatetime(new \DateTime(gmdate("Y-m-d\TH:i:s\Z", time())));
        foreach ($records as $record) {
            $startAt = $record->startat;
            $endAt = $record->endat;

            // Referring to TradingSchedule::canTradeNow() object 
            if ($startAt->format('Ymd') == $now->format('Ymd')) {
                if ($startAt >= $now) {
                    array_push($tradingOffHours, [
                        'start_at' => $startAt->format('Y-m-d H:i:s'),
                        'end_at' => $endAt->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        return $tradingOffHours;
    }


    /**
     * Handle inventory tracking for partner vault transfers
     * @param VaultItem                 $item           
     * @param int                       $previousVaultId
     * @param MyGtpTransactionManager   $txMgr
     * 
     * @return MyLedger
     */
    protected function handleVaultTransferInventoryTracking($item, $previousVaultId, $txMgr = null) {
        if ($item->status == $item->getTransferring()) {
            // This is a request to transfer, not a confirmation of transfer.

            // GTP using notify() using ACTION_ASSIGN for both request & confirm, 
            // so we must use item status to identify the difference. 
            return;
        }

        // Destination Vault
        $destination = $this->app->vaultlocationStore()->getById($item->vaultlocationid);

        // Partner Vault
        $partner = $this->app->partnerStore()->getById($item->partnerid);
        $partnerVault = $this->app->vaultlocationStore()->searchTable()->select()
                             ->where('partnerid', $partner->id)
                             ->andWhere('defaultlocation', 0)
                             ->andWhere('type', VaultLocation::TYPE_END)
                             ->one();

        if ($destination->id == $partnerVault->id) {
            // Transferring in to partner vault
            $ledger = $this->addItemsToPartnerLedger($item, $destination);
        } else if ($previousVaultId == $partnerVault->id) {
            // Transferring out to partner vault
            // TODO: Check if returning will cause oversubscription of inventory
            $txMgr = $txMgr ?? $this->app->mygtptransactionManager();
            $txMgr->checkPartnerBalanceSufficient($partner, $item->weight, $this);
            $ledger = $this->deductItemsFromPartnerLedger($item, $destination);
        }

        return $ledger;
    }

    /**
     * Add to partner ledger for inventory tracking
     * 
     * @param VaultItem $item
     * @param VaultLocation $vaultLocation
     * 
     * @return MyLedger
     */
    protected function addItemsToPartnerLedger($item, $vaultLocation = null)
    {
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        if (!$vaultLocation) {
            $vaultLocation = $this->app->vaultlocationStore()->getById($item->vaultlocationid);
        }

        $ledger = $this->app->myledgerStore()->create([
            'partnerid' => $item->partnerid,
            'accountholderid' => 0,
            'type'      => MyLedger::TYPE_VAULT_IN,
            'typeid'    => $item->id,
            'debit'     => 0.00,
            'credit'    => $item->weight,
            'refno'     => '-',
            'remarks'   => "Vault transfer to ". $vaultLocation->name. " ($item->serialno)",
            'transactiondate'   => $now,
            'status'    => MyLedger::STATUS_ACTIVE
        ]);

        $ledger = $this->app->myledgerStore()->save($ledger);
        return $ledger;
    }

    /**
     * Deduct from partner ledger for inventory tracking
     * @param VaultItem $item
     * @param VaultLocation $vaultLocation
     * 
     * @return MyLedger
     */
    protected function deductItemsFromPartnerLedger($item, $vaultLocation)
    {
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        if (!$vaultLocation) {
            $vaultLocation = $this->app->vaultlocationStore()->getById($item->vaultlocationid);
        }

        $ledger = $this->app->myledgerStore()->create([
            'partnerid' => $item->partnerid,
            'accountholderid' => 0,
            'type'      => MyLedger::TYPE_VAULT_OUT,
            'typeid'    => $item->id,
            'credit'     => 0.00,
            'debit'    => $item->weight,
            'refno'     => '-',
            'remarks'   => "Vault transfer to ". $vaultLocation->name . " ($item->serialno)",
            'transactiondate'   => $now,
            'status'    => MyLedger::STATUS_ACTIVE
        ]);

        $ledger = $this->app->myledgerStore()->save($ledger);
        return $ledger;
    }

    /**
     * @param Partner $partner
     * 
     * @return float
     */
    public function getPartnerInventoryBalance(Partner $partner)
    {
        // if (empty($partner->id)) {   // Doesn't work, empty() uses isset()
        if ($partner->id <= 0) {
            throw GeneralException::fromTransaction([], [
                'message' => "Partner does not have ID"
            ]);
        }

        $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partner->id)->execute();
        $groupPartnerIds = array();
        foreach ($partners as $partners){
            array_push($groupPartnerIds,$partners->id);
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
                    ->andWhere('status', MyLedger::STATUS_ACTIVE)
                    ->one()['sum'];
        }else{
            $sum = $ledgerHdl
                    ->select([$ledgerHdl->raw("SUM({$p}credit) - SUM({$p}debit) AS sum")])
                    ->where('accountholderid', 0)
                    ->andWhere('partnerid', $partner->id)
                    ->andWhere('status', MyLedger::STATUS_ACTIVE)
                    ->one()['sum'];
        }
        
        return floatval($sum);
    }
}