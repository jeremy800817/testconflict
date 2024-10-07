<?php

use Mockery\LegacyMockInterface;
use Snap\manager\MyGtpAccountManager;
use Snap\manager\MyGtpStorageManager;
use Snap\object\MyAccountHolder;
use Snap\object\MyDailyStorageFee;
use Snap\object\MyFee;
use Snap\object\MyLedger;
use Snap\object\MyMonthlyStorageFee;
use Snap\object\MyPartnerSetting;
use Snap\object\Partner;

class StorageManagerTest extends BaseTestCase
{
    protected static $partner;
    protected static $newPriceStream;
    protected static $product;
    protected static $feePerDay;
    protected static $storageFeePerDay;
    protected static $adminFeePerDay;
    protected static $minFee;
    protected static $settings;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        // Tempory fix for event trigger view requiring eventmessage table
        self::$app->eventMessageStore();
        self::$app->mydailystoragefeeStore();
        parent::setupBeforeClass();

        self::$product = self::$app->productStore()->getByField('code', 'DG-999-9');
        $provider = self::$app->priceProviderStore()->searchTable()->select()->where('productcategoryid', self::$product->id)->one();
        $partner = self::createDummyPartner();

        $partner->pricesourceid = $provider->pricesourceid;
        $partner->calculatormode = Partner::CALC_BMMB;
        $partner = self::$partner = self::$app->partnerStore()->save($partner);

        $settings = self::$app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        $settings->storagefeeperannum = 0.5;
        $settings->adminfeeperannum = 1;
        $settings->minstoragecharge = 1;
        $settings = self::$app->mypartnersettingStore()->save($settings);

        self::$feePerDay = ($settings->storagefeeperannum + $settings->adminfeeperannum) / 365 / 100;
        self::$storageFeePerDay = $settings->storagefeeperannum / 365 / 100;
        self::$adminFeePerDay = $settings->adminfeeperannum / 365 / 100;
        self::$minFee = $settings->minstoragecharge;
        

        $newPriceStream = self::$app->priceStreamStore()->create([
            'pricesourceid' => $provider->pricesourceid,
            'providerid' => $provider->id,
            'providerpriceid' => 123,
            'pricesourceon' => new \Datetime,
            'currencyid' => 1,
            'companybuyppg' => 179,
            'companysellppg' => 200,
            'uuid' => 'abcderf11111',
            'createdon' => new \Datetime,
            'modifiedon' => new \Datetime,
            'status' => 1
        ]);

        self::$newPriceStream = self::$app->priceStreamStore()->save($newPriceStream);

    }

    public function testGenerateRefNo()
    {
        $store = self::$app->mymonthlystoragefeeStore();
        $refno = self::$app->mygtpstorageManager()->generateRefNo("TSTSF", $store);

        $this->assertNotNull($refno);
        $this->assertStringStartsWith("TSTSF", $refno);
    }

    public function testCalculateDailyStorageFee()
    {
        $partner   = self::$partner;
        /** @var MyAccountHolder $accHolder */
        $accHolder = self::createDummyAccountHolder($partner);
        $accHolder->investmentmade = true;
        $accHolder = self::$app->myaccountholderStore()->save($accHolder);

        $today = new \DateTime('midnight today', self::$app->getUserTimezone());
        $today->setTimezone(self::$app->getServerTimezone());

        $yesterday = new \DateTime('midnight yesterday', self::$app->getUserTimezone());
        $yesterday->setTimezone(self::$app->getServerTimezone());

        $this->createLedger($accHolder->id, $accHolder->partnerid, MyLedger::TYPE_BUY_FPX, 70, false, $yesterday);
        $this->createLedger($accHolder->id, $accHolder->partnerid, MyLedger::TYPE_BUY_FPX, 25, false, new \DateTime($today->format('Y-m-d H:i:s')));


        /** @var MyGtpAccountManager $accountMgr */
        $accountMgr = self::$app->mygtpaccountManager();
        $previousUncharged = $accountMgr->getAccountHolderUnchargedStorageFees($accHolder);

        /** @var MyGtpStorageManager $storageMgr */
        $storageMgr = self::$app->mygtpstorageManager();

        $yesterday = new \DateTime('now', self::$app->getUserTimezone());
        $yesterday->setTime(23, 59, 59);
        $yesterday->modify("-1 day");
        $yesterday->setTimezone(self::$app->getServerTimezone());

        $accHoldersToCalculate = self::$app->myaccountholderStore()
            ->searchTable()
            ->select(['id', 'partnerid'])
            ->where('status', MyAccountHolder::STATUS_ACTIVE)
            ->where('investmentmade', MyAccountHolder::INVESTMENT_MADE)
            ->where('partnerid', $partner->id)
            ->get();

        $storageMgr->calculateDailyStorageFee($partner, $accHoldersToCalculate, $yesterday);

        $latestUncharged = $accountMgr->getAccountHolderUnchargedStorageFees($accHolder);
        $currentGoldBalance = $accHolder->getCurrentGoldBalance($yesterday);

        /** @var MyDailyStorageFee $dsFee */
        $dsFee = self::$app->mydailystoragefeeStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', $accHolder->id)
            ->where('status', MyDailyStorageFee::STATUS_ACTIVE)
            ->one();
        
        $calculated = number_format(($currentGoldBalance - $previousUncharged) * self::$feePerDay, '6', '.', '');
        $calculatedStorage = number_format(($currentGoldBalance - $previousUncharged) * self::$storageFeePerDay, '6', '.', '');
        $calculatedAdmin = number_format(($currentGoldBalance - $previousUncharged) * self::$adminFeePerDay, '6', '.', '');

        $this->assertEquals(0, $previousUncharged);
        $this->assertNotNull($dsFee);
        $this->assertEquals($dsFee->xau, $calculated);
        $this->assertEquals($dsFee->storagefeexau, $calculatedStorage);
        $this->assertEquals($dsFee->adminfeexau, $calculatedAdmin);
        $this->assertEquals($dsFee->xau, $dsFee->adminfeexau + $dsFee->storagefeexau);
        $this->assertEquals($dsFee->xau, $latestUncharged);
        $this->assertNotEquals(0, $latestUncharged);

        return $accHolder;
    }

    /**
     * @depends testCalculateDailyStorageFee
     */
    public function testChargeStorageFee($accHolder)
    {
        $initialGoldBalance = $accHolder->getCurrentGoldBalance();
        $chargeDate = new \DateTime('last day of last month', self::$app->getServerTimezone());
        $chargeDate->setTime(15,59,59);
        $dailyStorageFee = self::$app->mydailystoragefeeStore()->create([
            'xau' => 0.5,
            'storagefeexau' => 0.166666,
            'adminfeexau' => 0.333333,
            'balancexau' => $initialGoldBalance,
            'accountholderid' => $accHolder->id,
            'status' => MyDailyStorageFee::STATUS_ACTIVE,
            'calculatedon' => $chargeDate,
        ]);

        self::$app->mydailystoragefeeStore()->save($dailyStorageFee);

        $now = new \DateTime('now');
        $now->setTimezone(self::$app->getUserTimezone());
        $lastMonthEnd = new \DateTime($now->format('Y-m-01 00:00:00'), self::$app->getUserTimezone());
        $lastMonthStart = clone $lastMonthEnd;
        $lastMonthStart->sub(\DateInterval::createFromDateString("1 month"));

        $lastMonthEnd->setTimezone(self::$app->getServerTimezone());
        $lastMonthStart->setTimezone(self::$app->getServerTimezone());

        /** @var MyGtpStorageManager/mixed $storageMgr */
        $storageMgr = \Mockery::mock(MyGtpStorageManager::class, [self::$app]);
        $storageMgr->shouldAllowMockingProtectedMethods()->makePartial();
        // $storageMgr->shouldReceive('submitFeeChargeToSAP')->andReturn([['success' => 'Y']]);

        $sumDailyFees = $storageMgr->getSumDailyFees([$accHolder->id], $lastMonthStart, $lastMonthEnd);
        $sumCalculatedFee = $sumDailyFees[0]['xau'];

        $partner = $accHolder->getPartner();

        $accHolders = self::$app->myaccountholderStore()
                ->searchTable()
                ->select(['id', 'partnerid'])
                ->where('status', \Snap\object\MyAccountHolder::STATUS_ACTIVE)
                ->where('investmentmade', \Snap\object\MyAccountHolder::INVESTMENT_MADE)
                ->where('partnerid', self::$partner->id)
                ->execute();

        $initialPartnerBalance = self::$app->mygtppartnerManager()->getPartnerInventoryBalance($partner);

        $storageMgr->chargeMonthlyFee($partner, $accHolders, self::$newPriceStream, $chargeDate);
        // Check charge is credited back to partner
        $latestPartnerBalance = self::$app->mygtppartnerManager()->getPartnerInventoryBalance($partner);
        $this->assertEquals($latestPartnerBalance, self::$partner->calculator()->add($initialPartnerBalance, $sumCalculatedFee));

        $latestGoldBalance = $accHolder->getCurrentGoldBalance();

        $this->assertNotEquals(0, $sumCalculatedFee);
        $this->assertNotEquals($initialGoldBalance, $latestGoldBalance);
        $this->assertEquals(self::$partner->calculator()->minus($initialGoldBalance, $sumCalculatedFee), $latestGoldBalance);

        $storageMgr->chargeMonthlyFee($partner, $accHolders, self::$newPriceStream, $chargeDate);



        $latestGoldBalance2 = $accHolder->getCurrentGoldBalance();
        $this->assertEquals($latestGoldBalance, $latestGoldBalance2);
    }

    public function testWholeMonthCalculateAndCharge()
    {

        $xauBalance = [
            // 27                    // 20                  // 25                   // 10                   // 10  
            [27, '0.001110', false], [7, '0.000822', true], [5, '0.001027', false], [15, '0.000411', true], [0, '0.000411', null],
            // 10                  // 10                  // 10                  // 5                   // 5            
            [0, '0.000411', null], [0, '0.000411', null], [0, '0.000411', null], [5, '0.000205', true], [0, '0.000205', null],
            // 5                   // 5                   // 15                    // 15                  // 15
            [5, '0.000205', null], [5, '0.000205', null], [10, '0.000616', false], [0, '0.000616', null], [0, '0.000616', null],
            // 15                  // 15                  // 20                   // 10                   // 10
            [0, '0.000616', null], [0, '0.000616', null], [5, '0.000822', false], [10, '0.000411', true], [0, '0.000411', null],
            // 10                  // 2                   // 2                   // 11                   // 3 
            [0, '0.000411', null], [8, '0.000082', true], [0, '0.000082', null], [9, '0.000452', false], [8, '0.000123', true],
            // 3                   // 1                   // 1
            [0, '0.000123', null], [2, '0.000041', true], [0, '0.000041', null]
        ];

        $partner   = self::$partner;
        $accHolder = self::createDummyAccountHolder($partner);
        $accHolder->investmentmade = true;
        $accHolder = self::$app->myaccountholderStore()->save($accHolder);

        $date = new \DateTime('2021-02-01 00:00:00', self::$app->getUserTimezone());
        $date->setTimezone(self::$app->getServerTimezone());

        /** @var MyGtpStorageManager/mixed $storageMgr */
        $storageMgr = \Mockery::mock(MyGtpStorageManager::class, [self::$app]);
        $storageMgr->shouldAllowMockingProtectedMethods()->makePartial();
        // $storageMgr->shouldReceive('submitFeeChargeToSAP')->andReturn([['success' => 'Y']]);

        $accHoldersToCalculate = self::$app->myaccountholderStore()
            ->searchTable()
            ->select(['id', 'partnerid'])
            ->where('status', MyAccountHolder::STATUS_ACTIVE)
            ->where('investmentmade', MyAccountHolder::INVESTMENT_MADE)
            ->where('partnerid', $partner->id)
            ->get();
            
        $dsfStore = self::$app->mydailystoragefeeStore();

        // Run the next day
        $date->modify("+1 day");
        foreach ($xauBalance as $balance) {

            $previousDay = \Snap\Common::convertUTCToUserDatetime($date);
            $previousDay->setTime(23, 59, 59);
            $previousDay->setTimezone(self::$app->getServerTimezone());
            $previousDay->modify('-1 day');

            if ($balance[2] !== null) {
                $ledger = $this->createLedger($accHolder->id,$accHolder->partnerid, MyLedger::TYPE_BUY_FPX, $balance[0], $balance[2], $previousDay);
            }
            $storageMgr->calculateDailyStorageFee($partner, $accHoldersToCalculate, $previousDay);

            $dailyStorageFee = $dsfStore->searchTable()
                ->select(['xau', 'adminfeexau', 'storagefeexau'])
                ->where('calculatedon', '>=', $previousDay->format('Y-m-d H:i:s'))
                ->where('calculatedon', '<=', $previousDay->format('Y-m-d H:i:s'))
                ->where('accountholderid', $accHolder->id)
                ->execute();

            $this->assertCount(1, $dailyStorageFee);
            $this->assertEquals($balance[1], $dailyStorageFee[0]->xau);
            $this->assertEquals($dailyStorageFee[0]->xau, $dailyStorageFee[0]->adminfeexau + $dailyStorageFee[0]->storagefeexau);            

            $date->modify("+1 day");
        }

        $lastMonthStart  = new \DateTime('2021-02-01 00:00:00', self::$app->getUserTimezone());
        $chargeDate      = new \DateTime('2021-02-28 23:59:59', self::$app->getUserTimezone());
        $lastMonthEnd    = new \DateTime('2021-03-01 00:00:00', self::$app->getUserTimezone());
        $currentMonthEnd = new \DateTime('2021-04-01 00:00:00', self::$app->getUserTimezone());
        $lastMonthStart->setTimezone(self::$app->getServerTimezone());
        $lastMonthEnd->setTimezone(self::$app->getServerTimezone());
        $currentMonthEnd->setTimezone(self::$app->getServerTimezone());
        $chargeDate->setTimezone(self::$app->getServerTimezone());

        $prevDay = new \DateTime('2021-02-28 23:59:59', self::$app->getUserTimezone());
        $prevDay->setTimezone(self::$app->getServerTimezone());

        $ledger = $this->createLedger($accHolder->id,$accHolder->partnerid, MyLedger::TYPE_BUY_FPX, $xauBalance[27][0], false, $prevDay);

        $initialPartnerBalance = self::$app->mygtppartnerManager()->getPartnerInventoryBalance($partner);

        $initialGoldBalance = $accHolder->getCurrentGoldBalance($lastMonthEnd);

        $sumDailyFees = $storageMgr->getSumDailyFees([$accHolder->id], $lastMonthStart, $lastMonthEnd);
        $sumCalculatedFee = $sumDailyFees[0]['xau'];
        $partner = $accHolder->getPartner();

        $accHolders = self::$app->myaccountholderStore()
                ->searchTable()
                ->select(['id', 'partnerid'])
                ->where('status', \Snap\object\MyAccountHolder::STATUS_ACTIVE)
                ->where('investmentmade', \Snap\object\MyAccountHolder::INVESTMENT_MADE)
                ->where('partnerid', self::$partner->id)
                ->execute();

        $storageMgr->chargeMonthlyFee($partner, $accHolders, self::$newPriceStream, $chargeDate);
        $chargedFee = self::$app->mymonthlystoragefeeStore()->searchTable()->select()
            ->where('accountholderid', $accHolder->id)
            ->where('chargedon', '>=', $lastMonthStart->format('Y-m-d H:i:s'))
            ->where('chargedon', '<', $lastMonthEnd->format('Y-m-d H:i:s'))
            ->one();

        // Check calculated fee
        $this->assertNotNull($sumCalculatedFee);
        $this->assertNotEquals(0, $sumCalculatedFee);

        // Check charged fee
        $this->assertNotNull($chargedFee);

        // Check charged fee is same as the rounded up sum daily fee
        $this->assertEquals($partner->calculator(false)->round($sumCalculatedFee), $chargedFee->xau);

        // Check charge is credited back to partner
        $latestPartnerBalance = self::$app->mygtppartnerManager()->getPartnerInventoryBalance($partner);
        $this->assertEquals($latestPartnerBalance, $initialPartnerBalance + $chargedFee->xau);

        // Check gold balance is deducted
        $latestGoldBalance =  $accHolder->getCurrentGoldBalance($chargeDate);
        $this->assertNotEquals($initialGoldBalance, $latestGoldBalance);
        $this->assertEquals(self::$partner->calculator(false)->minus($initialGoldBalance, $sumCalculatedFee), $latestGoldBalance);

        // Check double charge
        $storageMgr->chargeMonthlyFee($partner, $accHolders, self::$newPriceStream, $chargeDate);
        $latestGoldBalance2 = $accHolder->getCurrentGoldBalance($chargeDate);
        $this->assertEquals($latestGoldBalance, $latestGoldBalance2);

        return $accHolder;
    }

    /**
     * @depends testWholeMonthCalculateAndCharge
     */
    public function testCompleteMontlyStorageFee($accHolder)
    {

        $partner = $accHolder->getPartner();
        
        /** @var MyMonthlyStorageFee[] $pendingMonthlyStorageFees */
        $pendingMonthlyStorageFees = self::$app->mymonthlystoragefeeStore()->searchView()->select()
        ->where('partnerid', $partner->id)
        ->andWhere('status', MyMonthlyStorageFee::STATUS_PENDING)
        ->execute();
                
        /** @var MyGtpStorageManager/mixed $storageMgr */
        $storageMgr = \Mockery::mock(MyGtpStorageManager::class, [self::$app]);
        $storageMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $storageMgr->shouldReceive('submitFeeChargeToSAP')->times(count($pendingMonthlyStorageFees)*2)->andReturn([['success' => 'Y']]);

        foreach ($pendingMonthlyStorageFees as $msf) {
            $result = $storageMgr->submitAdminAndStorageFeeToSAP($partner, $msf);            
        }
    }

    /**
     * @depends testWholeMonthCalculateAndCharge
     */
    public function testRecalcDailyStorage($accHolder)
    {
        $partner = $accHolder->getPartner();
        /** @var MyGtpStorageManager|LegacyMockInterface $storageMgr */
        $storageMgr = \Mockery::mock(MyGtpStorageManager::class, [self::$app]);
        $storageMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $startDate  = new \DateTime('2021-02-01 00:00:00', self::$app->getUserTimezone());
        $endDate    = new \DateTime('2021-02-28 23:59:59', self::$app->getUserTimezone());
        $startDate->setTimezone(self::$app->getServerTimezone());
        $endDate->setTimezone(self::$app->getServerTimezone());

        /** @var MyDailyStorageFee[] $prevDailyStorageFees */
        $prevDailyStorageFees = self::$app->mydailystoragefeeStore()
                                          ->searchView()
                                          ->select()
                                          ->where('calculatedon', '>=', $startDate->format('Y-m-d H:i:s'))
                                          ->where('calculatedon', '<=', $endDate->format('Y-m-d H:i:s'))
                                          ->where('partnerid', $partner->id)
                                          ->execute();
        
        $settings = self::$app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        $settings->storagefeeperannum = 1;
        $settings->adminfeeperannum = 0;
        /** @var MyPartnerSetting $settings */
        $settings = self::$app->mypartnersettingStore()->save($settings);
        
        $ledgers = self::$app->myledgerStore()->searchTable()->select()
        ->where('partnerid', $partner->id)        
        ->where('transactiondate', '>=', $endDate->format('Y-m-d H:i:s'))
        ->where('transactiondate', '<=', $endDate->format('Y-m-d H:i:s'))
        ->where('type', MyLedger::TYPE_STORAGE_FEE)
        ->where('status', MyLedger::STATUS_ACTIVE)
        ->execute();

        foreach ($ledgers as $ledger) {
            self::$app->myledgerStore()->delete($ledger);
        }

        $storageMgr->recalculateDailyStorageFeeForPartner($partner, $startDate, $endDate);


        foreach ($prevDailyStorageFees as $prevFee) {
            /** @var MyDailyStorageFee $dsFee */

            $date = clone $prevFee->calculatedon;
            $date->setTimezone(self::$app->getServerTimezone());
            $newFee = self::$app->mydailystoragefeeStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', $prevFee->accountholderid)
            ->where('calculatedon', '>=', $date->format('Y-m-d H:i:s'))
            ->where('calculatedon', '<=', $date->format('Y-m-d H:i:s'))
            ->where('status', MyDailyStorageFee::STATUS_ACTIVE)
            ->one();

            $this->assertNotEquals($prevFee->xau, $newFee->xau);
            $this->assertEquals($prevFee->balancexau, $newFee->balancexau);
            $this->assertEquals($prevFee->xau, number_format(self::$feePerDay * $prevFee->balancexau, 6, '.', ''));
            $this->assertEquals($newFee->xau, number_format(($settings->adminfeeperannum + $settings->storagefeeperannum) / 365 / 100 * $prevFee->balancexau, 6, '.', ''));
        }

        return $accHolder;
    }

    /** @depends testRecalcDailyStorage */
    public function testProcessMonthlyStorageFee($accHolder)
    {
        $partner = $accHolder->getPartner();
        $date  = new \DateTime('2021-02-01 00:00:00', self::$app->getUserTimezone());
        $lastMonthStart = clone $date;
        $lastMonthStart->setTimezone(self::$app->getServerTimezone());
        $date->add(\DateInterval::createFromDateString('1 month'));
        $date->setTimezone(self::$app->getServerTimezone());
        $lastMonthEnd = clone $date;
        $lastMonthEnd->setTimezone(self::$app->getServerTimezone());
        $chargeDate = clone $date;
        $chargeDate->sub(\DateInterval::createFromDateString('1 second'));

        $ledgers = self::$app->myledgerStore()->searchTable()->select()
            ->where('partnerid', $partner->id)
            ->where('transactiondate', '>=', $chargeDate->format('Y-m-d H:i:s'))
            ->where('transactiondate', '<=', $chargeDate->format('Y-m-d H:i:s'))
            ->where('type', MyLedger::TYPE_STORAGE_FEE)
            ->where('status', MyLedger::STATUS_ACTIVE)
            ->execute();

        $prevMonthlyStorageFees = self::$app->mymonthlystoragefeeStore()
            ->searchView()
            ->select()
            ->where('chargedon', '>=', $chargeDate->format('Y-m-d H:i:s'))
            ->where('chargedon', '<=', $chargeDate->format('Y-m-d H:i:s'))
            ->where('partnerid', $partner->id)
            ->execute();

        foreach($prevMonthlyStorageFees as $prevFee) {
            self::$app->mymonthlystoragefeeStore()->delete($prevFee);
        }

        foreach ($ledgers as $ledger) {
            self::$app->myledgerStore()->delete($ledger);
        }

        /** @var MyGtpStorageManager|LegacyMockInterface $storageMgr */
        $storageMgr = \Mockery::mock(MyGtpStorageManager::class, [self::$app]);
        $storageMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $storageMgr->processMonthyStorageFeeForPartner($partner, $date);

        $prevMonthlyStorageFees = self::$app->mymonthlystoragefeeStore()
            ->searchView()
            ->select()
            ->where('chargedon', '>=', $chargeDate->format('Y-m-d H:i:s'))
            ->where('chargedon', '<=', $chargeDate->format('Y-m-d H:i:s'))
            ->where('partnerid', $partner->id)
            ->execute();

        foreach($prevMonthlyStorageFees as $prevFee) {
            self::$app->mymonthlystoragefeeStore()->delete($prevFee);
        }

        foreach ($ledgers as $ledger) {
            self::$app->myledgerStore()->delete($ledger);
        }

        $product  = self::$app->productStore()->getByField('code', 'DG-999-9');
        $provider = self::$app->priceproviderStore()->getForPartnerByProduct($partner, $product);

        $priceStream = self::$app->priceStreamStore()->searchTable()
                            ->select()
                            ->where('providerid', $provider->id)
                            ->where('pricesourceon', '>=', $chargeDate->format('Y-m-d H:i:s'))
                            ->orderBy('pricesourceon', 'ASC')
                            ->one();

        $accHolders = self::$app->myaccountholderStore()
            ->searchTable()
            ->select(['id', 'partnerid'])
            ->where('status', \Snap\object\MyAccountHolder::STATUS_ACTIVE)
            ->where('investmentmade', \Snap\object\MyAccountHolder::INVESTMENT_MADE)
            ->where('partnerid', $partner->id)
            ->execute();
        
        $storageMgr->chargeMonthlyFee($partner, $accHolders, $priceStream, $chargeDate);

        foreach ($prevMonthlyStorageFees as $prevFee) {
            $chargeDate = clone $prevFee->chargedon;
            $chargeDate->setTimezone(self::$app->getServerTimezone());

            $newFee = self::$app->mymonthlystoragefeeStore()
                ->searchTable()
                ->select()
                ->where('accountholderid', $prevFee->accountholderid)
                ->where('chargedon', '>=', $chargeDate->format('Y-m-d H:i:s'))
                ->where('chargedon', '<=', $chargeDate->format('Y-m-d H:i:s'))
                ->one();

                $ledger = self::$app->myledgerStore()->searchTable()->select()
                ->where('refno', $newFee->refno)
                ->where('status', MyLedger::STATUS_ACTIVE)
                ->where('type', MyLedger::TYPE_STORAGE_FEE)
                ->one();
    

            $this->assertEquals($prevFee->xau, $newFee->xau);
            $this->assertEquals($prevFee->adminfeexau, $newFee->adminfeexau);
            $this->assertEquals($prevFee->storagefeexau, $newFee->storagefeexau);
            $this->assertNotEquals($prevFee->id, $newFee->id);
            $this->assertEquals($ledger->remarks, $chargeDate->format('F Y'));
        }
    }

    public function testMinStorageFee()
    {
        $xauBalance = [
            [1, '0.000041'], [1, '0.000041'], [1, '0.000041'], [1, '0.000041'], [1, '0.000041'],
            [1, '0.000041'], [1, '0.000041'], [1, '0.000041'], [1, '0.000041'], [1, '0.000041'],
            [1, '0.000041'], [1, '0.000041'], [1, '0.000041'], [1, '0.000041'], [1, '0.000041'],
            [1, '0.000041'], [1, '0.000041'], [1, '0.000041'], [1, '0.000041'], [1, '0.000041'],
            [1, '0.000041'], [1, '0.000041'], [1, '0.000041'], [1, '0.000041'], [1, '0.000041'],
            [1, '0.000041'], [1, '0.000041'], [1, '0.000041']
        ];


        $partner   = self::$partner;
        $accHolder = self::createDummyAccountHolder($partner);
        $accHolder->investmentmade = true;
        $accHolder = self::$app->myaccountholderStore()->save($accHolder);

        $settings = self::$app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        $settings->storagefeeperannum = 0.5;
        $settings->adminfeeperannum = 1;
        /** @var MyPartnerSetting $settings */
        $settings = self::$app->mypartnersettingStore()->save($settings);

        $date = new \DateTime('2021-02-01 00:00:00', self::$app->getUserTimezone());
        $date->setTimezone(self::$app->getServerTimezone());

        // Stub submitFeeChargeToSAP
        /** @var MyGtpStorageManager/mixed $storageMgr */
        $storageMgr = \Mockery::mock(MyGtpStorageManager::class, [self::$app]);
        $storageMgr->shouldAllowMockingProtectedMethods()->makePartial();
        // $storageMgr->shouldReceive('submitFeeChargeToSAP')->andReturn([['success' => 'Y']]);

        $dsfStore = self::$app->mydailystoragefeeStore();

        $accHoldersToCalculate = self::$app->myaccountholderStore()
            ->searchTable()
            ->select(['id', 'partnerid'])
            ->where('status', MyAccountHolder::STATUS_ACTIVE)
            ->where('investmentmade', MyAccountHolder::INVESTMENT_MADE)
            ->where('partnerid', $partner->id)
            ->get();

        // Run the next day
        $date->modify("+1 day");
        foreach ($xauBalance as $balance) {

            $previousDay = \Snap\Common::convertUTCToUserDatetime($date);
            $previousDay->setTime(23, 59, 59);
            $previousDay->setTimezone(self::$app->getServerTimezone());
            $previousDay->modify('-1 day');

            $ledger = $this->createLedger($accHolder->id,$accHolder->partnerid, MyLedger::TYPE_BUY_FPX, $balance[0], false, $previousDay);
            $storageMgr->calculateDailyStorageFee($partner, $accHoldersToCalculate, $previousDay);

            $xau = $dsfStore->searchTable()
                ->select([])
                ->where('calculatedon', '>=', $previousDay->format('Y-m-d H:i:s'))
                ->where('calculatedon', '<=', $previousDay->format('Y-m-d H:i:s'))
                ->where('accountholderid', $accHolder->id)
                ->execute();

            $this->assertCount(1, $xau);
            $this->assertEquals($balance[1], $xau[0]->xau);

            // Cleanup to simulate balance
            self::$app->myledgerStore()->delete($ledger);

            $date->modify("+1 day");
        }

        $lastMonthStart  = new \DateTime('2021-02-01 00:00:00', self::$app->getUserTimezone());
        $lastMonthEnd    = new \DateTime('2021-03-01 00:00:00', self::$app->getUserTimezone());
        $chargeDate      = new \DateTime('2021-02-28 23:59:59', self::$app->getUserTimezone());
        $currentMonthEnd = new \DateTime('2021-04-01 00:00:00', self::$app->getUserTimezone());
        $lastMonthStart->setTimezone(self::$app->getServerTimezone());
        $lastMonthEnd->setTimezone(self::$app->getServerTimezone());
        $currentMonthEnd->setTimezone(self::$app->getServerTimezone());
        $chargeDate->setTimezone(self::$app->getServerTimezone());

        $prevDay = new \DateTime('2021-02-28 23:59:59', self::$app->getUserTimezone());
        $prevDay->setTimezone(self::$app->getServerTimezone());

        $ledger = $this->createLedger($accHolder->id,$accHolder->partnerid, MyLedger::TYPE_BUY_FPX, $xauBalance[27][0], false, $prevDay);
        $initialGoldBalance = $accHolder->getCurrentGoldBalance($lastMonthEnd);

        $sumDailyFees = $storageMgr->getSumDailyFees([$accHolder->id], $lastMonthStart, $lastMonthEnd);
        $sumCalculatedFee = $sumDailyFees[0]['xau'];
        $partner = $accHolder->getPartner();
        $initialPartnerBalance = self::$app->mygtppartnerManager()->getPartnerInventoryBalance($partner);

        $storageMgr->chargeMonthlyFee($partner, $accHolder, self::$newPriceStream, $chargeDate);


        $chargedFee = self::$app->mymonthlystoragefeeStore()->searchTable()->select()
            ->where('accountholderid', $accHolder->id)
            ->where('chargedon', '>=', $lastMonthStart->format('Y-m-d H:i:s'))
            ->where('chargedon', '<', $lastMonthEnd->format('Y-m-d H:i:s'))
            ->one();
        
        $latestPartnerBalance = self::$app->mygtppartnerManager()->getPartnerInventoryBalance($partner);
    
        // Check calculated fee
        $this->assertNotNull($sumCalculatedFee);
        $this->assertNotEquals(0, $sumCalculatedFee);

        // Check min charged fee
        $this->assertNotNull($chargedFee);
        $this->assertNotEquals(0, $chargedFee);
        
        // Check charged fee is not the same as the rounded up sum daily fee
        $this->assertNotEquals($partner->calculator(false)->round($sumCalculatedFee), $chargedFee->xau);
        $this->assertNotEquals($latestPartnerBalance, self::$partner->calculator()->add($initialPartnerBalance, $sumCalculatedFee));

        // Check charged fee is the same as the rounded up min fee
        $this->assertEquals(self::$partner->calculator(false)->round(self::$minFee / self::$newPriceStream->companybuyppg), $chargedFee->xau);
        $this->assertEquals($latestPartnerBalance, self::$partner->calculator(false)->add($initialPartnerBalance, $chargedFee->xau));

        // Check gold balance is deducted
        $latestGoldBalance =  $accHolder->getCurrentGoldBalance($chargeDate);
        $this->assertNotEquals($initialGoldBalance, $latestGoldBalance);
        $this->assertEquals(self::$partner->calculator(false)->minus($initialGoldBalance, $chargedFee->xau), $latestGoldBalance);

        // Check double charge
        $storageMgr->chargeMonthlyFee($partner, $accHolder, self::$newPriceStream, $chargeDate);
        $latestGoldBalance2 = $accHolder->getCurrentGoldBalance($chargeDate);
        $this->assertEquals($latestGoldBalance, $latestGoldBalance2);
    }

    /**
     * Create ledger for test use case
     *
     * @param  int  $accHolderId
     * @param  int  $partnerId
     * @param  stirng $ledgerType
     * @param  float $amount
     * @param  boolean $debit
     * @return MyLedger
     */
    private function createLedger($accHolderId, $partnerId, $ledgerType, $amount, $debit = false, $date = null)
    {
        $ledger = self::$app->myledgerStore()->create([
            'type'              => $ledgerType,
            'typeid'            => 1,
            'accountholderid'   => $accHolderId,
            'partnerid'         => $partnerId,
            'refno'             => mt_rand(),
            'transactiondate'   => $date ? $date->format('Y-m-d H:i:s') : new \DateTime('now'),
            'status'            => MyLedger::STATUS_ACTIVE,
        ]);

        if ($debit) {
            $ledger->debit = $amount;
            $ledger->credit = 0.00;
        } else {
            $ledger->credit = $amount;
            $ledger->debit = 0.00;
        }

        $ledger = self::$app->myledgerStore()->save($ledger);

        return $ledger;
    }
}
