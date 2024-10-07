<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

namespace Snap\manager;

use Snap\TLogging;
use Snap\TObservable;
use Snap\IObservable;
use Snap\object\MyLedger;
use Snap\object\MyAccountHolder;
use Snap\object\MyDailyStorageFee;
use Snap\object\MyMonthlyStorageFee;
use Snap\object\MyPartnerSetting;
use Snap\object\Partner;
use Snap\object\PriceStream;
use \Snap\object\OtcManagementFee;
use \Snap\object\MyPaymentDetail;
use \Snap\object\MyGtpEventConfig;

class MyGtpStorageManager implements IObservable
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Sum up and charge monthly storage fee for each account holder belongs to partner
     *
     * @param Partner $partner
     * @param MyAccountHolder[]|MyAccountHolder $accHolderToCharge
     * @param PriceStream $priceStream
     * @param \DateTime $date
     * @param integer $feeDecimal
     * @param bool $useChargeDate
     * @return array|null
     */
    public function chargeMonthlyFee($partner, $accHolderToCharge, $priceStream, $chargeDate, $feeDecimal = 6, $useChargeDate = true)
    {
        if (! is_array($accHolderToCharge)) {
            $accHolderToCharge = [$accHolderToCharge];
        }

        $chargeDate = \Snap\Common::convertUTCToUserDatetime($chargeDate);
        $chargeMonthStart = new \DateTime($chargeDate->format('Y-m-01 00:00:00'), $this->app->getUserTimezone());
        $chargeMonthEnd   = clone $chargeMonthStart;
        $chargeMonthEnd->add(\DateInterval::createFromDateString("1 month"));

        $chargeDate->setTimezone($this->app->getServerTimezone());
        $chargeMonthStart->setTimezone($this->app->getServerTimezone());
        $chargeMonthEnd->setTimezone($this->app->getServerTimezone());

        $accHolders = [];

        foreach ($accHolderToCharge as $acc) {
            $accHolders[$acc->id] = $acc;
        }

        $accHolderIds = $this->filterChargeableAccountHolderIds($accHolders, $chargeMonthStart, $chargeMonthEnd);
        if (empty($accHolderIds)) {
            $this->logDebug(__METHOD__ . "(): All account holders was previously charged for partner {$partner->code}");
            return;
        }

        $sumDailyFees = $this->getSumDailyFees($accHolderIds, $chargeMonthStart, $chargeDate);

        // Get the minimum fee
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        if (is_null($settings->minstoragecharge)) {
            $this->logDebug(__METHOD__ . "(): Min storage fee configuration was not found for partner {$partner->code}");
            return;
        }

        $msFees = null;

        $feePerAnnum = $partner->calculator()->add($settings->storagefeeperannum, $settings->adminfeeperannum);
        $adminFeeRatio = $settings->adminfeeperannum / $feePerAnnum;
        $storageFeeRatio = 1 - $adminFeeRatio;

        foreach ($sumDailyFees as $sumDailyFee) {
            // Skip for account holders not in previous month
            // if (0 < floatval($sumDailyFee['xau'])) {
                $msFees[$sumDailyFee['accountholderid']] = $this->chargeMonthlyFeeToAccountHolder(
                    $partner,
                    $accHolders[$sumDailyFee['accountholderid']],
                    $priceStream,
                    $sumDailyFee['xau'],
                    $chargeDate,
                    $settings->minstoragecharge,
                    $adminFeeRatio,
                    $storageFeeRatio,
                    $feeDecimal,
                    $useChargeDate
                );
            // }
        }

        return $msFees;
    }

    /**
     * Method to charge the account holder for the previous month storage fee
     *
     * @param Partner $partner
     * @param MyAccountHolder $accHolder
     * @param PriceStream $priceStream
     * @param float $xau
     * @param \DateTime $chargeDate
     * @param float $minStorageCharge
     * @param float $adminFeeRatio
     * @param float $storageFeeRatio
     * @param int $feeDecimal
     * @param bool $useChargeDate
     * @return MyMonthlyStorageFee
     */
    public function chargeMonthlyFeeToAccountHolder($partner, $accHolder, $priceStream, $xau, $chargeDate, $minStorageCharge, $adminFeeRatio, $storageFeeRatio, $feeDecimal = 6, $useChargeDate = true)
    {
        $now = new \DateTime('now', $this->app->getUserTimezone());
        $yesterday = new \DateTime($now->format('Y-m-d 00:00:00'), $this->app->getUserTimezone());
        $yesterday->sub(\DateInterval::createFromDateString("1 second"));
        $yesterday->setTimezone($this->app->getServerTimezone());

        $chargeDate = \Snap\Common::convertUTCToUserDatetime($chargeDate);

        try {
            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }

            $xau = number_format($xau, $feeDecimal, '.', '');

            // Amount in MYR of last month storage fee
            $amount = $partner->calculator()->multiply($xau, $priceStream->companybuyppg);

            // Compare MYR
            if ($amount < $minStorageCharge) {
                $xau = number_format($minStorageCharge / $priceStream->companybuyppg, $feeDecimal, '.', '');
            }
            
            // Avoid overcharging
            $goldBalance = $accHolder->getCurrentGoldBalance();
            if ($xau > $goldBalance) {
                $xau = $goldBalance;
            }

            // MYR as decimals specified in partner calculator
            // $price = $partner->calculator(true)->round($priceStream->companybuyppg);

            $chargeDate->setTimezone($this->app->getUserTimezone());

            // $adminXau   = $partner->calculator(false)->multiply($xau, 2 / 3);
            // $storageXau = $partner->calculator(false)->multiply($xau, 1 / 3);

            $adminXau   = bcmul($xau, $adminFeeRatio, 3);
            $storageXau = bcmul($xau, $storageFeeRatio,3);
            $xau = bcadd($adminXau, $storageXau, 3);

            $refNo = $this->generateRefNo('SF', $this->app->mymonthlystoragefeeStore());
            $msFee = $this->app->mymonthlystoragefeeStore()->create([
                'xau' => $xau,
                'price' => $priceStream->companybuyppg,
                'amount' => $partner->calculator()->multiply($xau, $priceStream->companybuyppg),
                'adminfeexau' => $adminXau,
                'storagefeexau' => $storageXau,
                'accountholderid' => $accHolder->id,
                'pricestreamid' => $priceStream->id,
                'refno' => $refNo,
                'status' => MyMonthlyStorageFee::STATUS_PENDING,
                'chargedon' => $chargeDate->format('Y-m-d H:i:s'),
            ]);

            $msFee = $this->app->mymonthlystoragefeeStore()->save($msFee);

            $this->log(__METHOD__ . "(): Creating ledger for gold storage fee ({$refNo})", SNAP_LOG_DEBUG);

            $chargeDate->setTimezone($this->app->getServerTimezone());
            $ledger = $this->app->myledgerStore()->create([
                'type' => MyLedger::TYPE_STORAGE_FEE,
                'typeid' => $msFee->id,
                'accountholderid' => $accHolder->id,
                'partnerid' => $accHolder->partnerid,
                'debit' => $xau,
                'credit' => 0.00,
                'refno' => $refNo,
                'status' => MyLedger::STATUS_ACTIVE,
                'remarks' => $chargeDate->format('F Y'),
                'transactiondate' => $useChargeDate ? $chargeDate->format('Y-m-d H:i:s') : $yesterday->format('Y-m-d H:i:s'),
            ]);

            $ledger = $this->app->myledgerStore()->save($ledger);


            $partnerLedger = $this->app->myledgerStore()->create([
                'type' => MyLedger::TYPE_STORAGE_FEE,
                'typeid' => $msFee->id,
                'accountholderid' => 0,
                'partnerid' => $accHolder->partnerid,
                'debit' => 0.00,
                'credit' => $xau,
                'refno' => $refNo,
                'status' => MyLedger::STATUS_ACTIVE,
                'remarks' => $chargeDate->format('F Y'),
                'transactiondate' => $useChargeDate ? $chargeDate->format('Y-m-d H:i:s') : $yesterday->format('Y-m-d H:i:s'),
            ]);

            $partnerLedger = $this->app->myledgerStore()->save($partnerLedger);

            $this->log(__METHOD__ . "(): Finished saving ledger for gold storage fee ({$refNo})", SNAP_LOG_DEBUG);

            // Save the ledger
            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
        } catch (\Exception $e) {

            $this->log(__METHOD__ . "(): Error charging storage fee for account holder ({$accHolder->id}). " . $e->getMessage(), SNAP_LOG_ERROR);

            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }
            throw $e;
        }

        return $msFee;
    }

    /**
     * Process monthly storage fee for the given partner date
     *
     * @param Partner $partner
     * @param \DateTime $date
     * @param bool $useChargeDate
     * @return void
     */
    public function processMonthyStorageFeeForPartner(Partner $partner, $date, $useChargeDate = true, $latestPriceDate = false)
    {
        $date->setTimezone($this->app->getUserTimezone());
        $chargeDate = new \DateTime($date->format('Y-m-01 00:00:00'), $this->app->getUserTimezone());
        $chargeDate->sub(\DateInterval::createFromDateString("1 second"));

        $product  = $this->app->productStore()->getByField('code', 'DG-999-9');
        $provider = $this->app->priceproviderStore()->getForPartnerByProduct($partner, $product);

        if ($latestPriceDate) {
            $now = new \DateTime('now', $this->app->getUserTimezone());
            $priceDate = new \DateTime($now->format('Y-m-01 08:30:00'), $this->app->getUserTimezone());        
        } else {            
            $priceDate = new \DateTime($chargeDate->format('Y-m-d 08:30:00'), $this->app->getUserTimezone());        
        }
        $priceDate->setTimezone($this->app->getServerTimezone());

        $priceStream = $this->app->priceStreamStore()->searchTable()
                            ->select()
                            ->where('providerid', $provider->id)
                            ->where('pricesourceon', '>=', $priceDate->format('Y-m-d H:i:s'))
                            ->orderBy('pricesourceon', 'ASC')
                            ->one();

        if (! $priceStream) {
            $message = "Price data date from ({$chargeDate->format('Y-m-d H:i:s')}) onward not available for partner ({$partner->code})";
            $this->log(__METHOD__ . "(): {$message}", SNAP_LOG_ERROR);
            throw new \Exception($message);            
        }

        $accHolders = $this->app->myaccountholderStore()
            ->searchTable()
            ->select(['id', 'partnerid'])
            ->where('status', \Snap\object\MyAccountHolder::STATUS_ACTIVE)
            ->where('investmentmade', \Snap\object\MyAccountHolder::INVESTMENT_MADE)
            ->where('partnerid', $partner->id)
            ->execute();

        if (empty($accHolders)) {
            $this->logDebug(__METHOD__ . "(): No account holders found for partner {$partner->code}");
            return;
        }

        // Reset back to server timezone
        $chargeDate->setTimezone($this->app->getServerTimezone());        
        $this->chargeMonthlyFee($partner, $accHolders, $priceStream, $chargeDate, 6, $useChargeDate);
    }

    /**
     * Get the sum of daily storage fee for each account holder provided
     *
     * @param array $accHolderIds
     * @param \DateTime $start
     * @param \DateTime $end
     * @return array
     */
    public function getSumDailyFees($accHolderIds, $start, $end)
    {
        $sums = $this->app->mydailystoragefeeStore()
            ->searchTable(false)
            ->select()
            ->addField('accountholderid', 'accountholderid')
            ->addFieldSum('xau', 'xau')
            ->where('calculatedon', '>=', $start->format('Y-m-d H:i:s'))
            ->where('calculatedon', '<=', $end->format('Y-m-d H:i:s'))
            ->where('status', MyDailyStorageFee::STATUS_ACTIVE)
            ->whereIn('accountholderid', $accHolderIds)
            ->groupBy('accountholderid')
            ->execute();

        return $sums;
    }

    /**
     * Method to calculate partner account holders storage fee
     *
     * @param Partner $partner
     * @param \DateTime $calculateDate
     * @param integer $feeDecimal
     * @return void
     */
    public function calculateDailyStorageFee($partner, $accHoldersToCalculate, $calculateDate, $feeDecimal = 6)
    {
        if (! is_array($accHoldersToCalculate)) {
            $accHoldersToCalculate = [$accHoldersToCalculate];
        }

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        if (is_null($settings->storagefeeperannum) || is_null($settings->adminfeeperannum)) {
            $this->log(__METHOD__ . "(): No active daily storage fee found for partner {$partner->code}", SNAP_LOG_ERROR);
            return;
        }        

        $accHolders = [];

        foreach ($accHoldersToCalculate as $acc) {
            $accHolders[$acc->id] = $acc;
        }

        $accHolderIds = $this->filterCalculatableAccountHolderIds($accHolders, $calculateDate);

        if (empty($accHolderIds)) {
            $this->logDebug(__METHOD__ . "(): All account holders was previously calculated for partner {$partner->code}");
            return;
        }

        foreach ($accHolderIds as $id) {
            $this->calculateDailyFeeForAccountHolder($accHolders[$id], $settings->storagefeeperannum, $settings->adminfeeperannum, $calculateDate, $feeDecimal);
        }
    }

    public function totalCountDaiyFees(MyAccountHolder $accHolder, $calculateDate)
    {
        $calculateDate = \Snap\Common::convertUTCToUserDatetime($calculateDate);
        $calculateDateStart = new \DateTime($calculateDate->format('Y-m-01 00:00:00'), $this->app->getUserTimezone());
        $calculateDateEnd   = clone $calculateDateStart;
        $calculateDateEnd->add(\DateInterval::createFromDateString("1 month"));

        $calculateDate->setTimezone($this->app->getServerTimezone());
        $calculateDateStart->setTimezone($this->app->getServerTimezone());
        $calculateDateEnd->setTimezone($this->app->getServerTimezone());

        return $count = $this->app->mydailystoragefeeStore()
            ->searchTable(false)
            ->select()
            ->where('calculatedon', '>=', $calculateDateStart->format('Y-m-d H:i:s'))
            ->where('calculatedon', '<=', $calculateDateEnd->format('Y-m-d H:i:s'))
            ->where('status', MyDailyStorageFee::STATUS_ACTIVE)
            ->where('accountholderid', [$accHolder->id])
            ->groupBy('accountholderid')
            ->count();
    }

    /**
     * Method to calculate daily fee for the accoutn holder
     *
     * @param  MyAccountHolder $accHolder
     * @param  float           $storageFeePerAnnum
     * @param  float           $adminFeePerAnnum
     * @param  \DateTime       $date
     * @param  integer         $feeDecimal
     * @return void
     */
    protected function calculateDailyFeeForAccountHolder($accHolder, $storageFeePerAnnum, $adminFeePerAnnum, $date, $feeDecimal = 6)
    {
        $date = new \DateTime($date->format('Y-m-d H:i:s'), $this->app->getServerTimezone());

        try {
            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }

            $goldBalance = $accHolder->getCurrentGoldBalance($date);
            
            $adminFeeXau   = $adminFeePerAnnum / 100 / 365 * $goldBalance;
            $storageFeeXau = $storageFeePerAnnum / 100 / 365 * $goldBalance;
            $xau           = $adminFeeXau + $storageFeeXau;
            
            $adminFeeXau   = number_format($adminFeeXau, $feeDecimal, '.', '');
            $storageFeeXau = number_format($storageFeeXau, $feeDecimal, '.', '');
            $xau           = number_format($xau, $feeDecimal, '.', '');

            $exists = $this->app->mydailystoragefeeStore()
                      ->searchTable()
                      ->select()
                      ->where('accountholderid', $accHolder->id)
                      ->where('calculatedon', '>=', $date->format('Y-m-d H:i:s'))
                      ->where('calculatedon', '<=', $date->format('Y-m-d H:i:s'))
                      ->where('status', MyDailyStorageFee::STATUS_ACTIVE)
                      ->exists();

            if ($exists) {
                $message = "Daily storage fee already exists for account holder ({$accHolder->id}) for date ({$date->format('Y-m-d H:i:s')})";
                $this->log(__METHOD__ . "(): " . $message, SNAP_LOG_ERROR);
                throw new \Exception($message);
            }
            
            $date->setTimezone($this->app->getUserTimezone());
            $dsFee = $this->app->mydailystoragefeeStore()->create([
                'xau' => $xau,
                'adminfeexau' => $adminFeeXau,
                'storagefeexau' => $storageFeeXau,
                'balancexau' => $goldBalance,
                'accountholderid' => $accHolder->id,
                'status' => MyDailyStorageFee::STATUS_ACTIVE,
                'calculatedon' => $date->format('Y-m-d H:i:s'),
            ]);

            $this->app->mydailystoragefeeStore()->save($dsFee);

            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Error calculating storage fee for account holder ({$accHolder->id}). " . $e->getMessage(), SNAP_LOG_ERROR);

            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Submit storage fee to sap
     *
     * @param Partner     $partner
     * @param \DateTime   $dateStart
     * @param \DateTime   $dateEnd
     * @param PriceStream $priceStream
     * 
     * @return bool
     */
    public function submitAdminAndStorageFeeToSAP($partner, $dateStart, $dateEnd, $priceStream)
    {
        try {
            $totalMonthlyStorageFee = $this->app->mymonthlystoragefeeStore()->searchView(false)
                                            ->select()
                                            ->addFieldSum('storagefeexau', 'storagefeexau')
                                            ->addFieldSum('adminfeexau', 'adminfeexau')
                                            ->where('partnerid', $partner->id)
                                            ->where('chargedon', '>=', $dateStart->format('Y-m-d H:i:s'))
                                            ->where('chargedon', '<=', $dateEnd->format('Y-m-d H:i:s'))
                                            ->andWhere('status', MyMonthlyStorageFee::STATUS_PENDING)
                                            ->one();

            $getlist = $this->app->mymonthlystoragefeeStore()->searchView()
                        ->select()
                        ->where('partnerid', $partner->id)
                        ->where('chargedon', '>=', $dateStart->format('Y-m-d H:i:s'))
                        ->where('chargedon', '<=', $dateEnd->format('Y-m-d H:i:s'))
                        ->andWhere('status', MyMonthlyStorageFee::STATUS_PENDING)
                        ->execute();

                                            
            if ($totalMonthlyStorageFee) {
                $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
                $fee = $this->app->mymonthlystoragefeeStore()->create([
                    'chargedon' => $dateEnd,
                    'refno'     => $partner->sapcompanysellcode1 . $partner->id . $dateEnd->format('Ym'),
                    'price'     => $priceStream->companybuyppg
                ]);

                $storagefeeresp = $this->submitStorageFeeToSAP($partner, $fee, $totalMonthlyStorageFee['storagefeexau']);
                $adminfeeresp = $this->submitAdminFeeToSAP($partner, $fee, $totalMonthlyStorageFee['adminfeexau']);

                if($storagefeeresp && $adminfeeresp){ //success send to SAP
                    foreach($getlist as $aList){
                        $aList->status = MyMonthlyStorageFee::STATUS_ACTIVE;
                        $this->app->mymonthlystoragefeeStore()->save($aList);
                    }
                    $this->log("Successfully change status in mymonthlystoragefee table.", SNAP_LOG_DEBUG);
                }
            }
            
        } catch (\Exception $e) {                    
            $this->log("Error while submitting storage fee to SAP ({$fee->refno}): ". $e->getMessage(), SNAP_LOG_ERROR);

            return false;
        }

    }

    /**
     * Submit request to manually change status from INACTIVE to ACTIVE after successfully send to SAP
     *
     * @param Partner     $partner
     * @param \DateTime   $dateStart
     * @param \DateTime   $dateEnd
     * 
     * @return bool
     */
    public function checkTransactionToManuallyChangeStatus($partner, $dateStart, $dateEnd){
        try {
            $getlist = $this->app->mymonthlystoragefeeStore()->searchView()
                        ->select()
                        ->where('partnerid', $partner->id)
                        ->where('chargedon', '>=', $dateStart->format('Y-m-d H:i:s'))
                        ->where('chargedon', '<=', $dateEnd->format('Y-m-d H:i:s'))
                        ->andWhere('status', MyMonthlyStorageFee::STATUS_PENDING)
                        ->execute();
            foreach($getlist as $aList){
                $aList->status = MyMonthlyStorageFee::STATUS_ACTIVE;
                $this->app->mymonthlystoragefeeStore()->save($aList);
            }
            $this->log("Successfully change status in mymonthlystoragefee table.", SNAP_LOG_DEBUG);
        } catch (\Exception $e) {                    
        $this->log("Error while manually change status: ". $e->getMessage(), SNAP_LOG_ERROR);

        return false;
        }
    }

    /**
     * Reset the daily storage fee for account holders of a partner
     *
     * @param Partner $partner
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @return void
     */
    public function recalculateDailyStorageFeeForPartner(Partner $partner, $dateFrom = null, $dateTo = null, $hardDelete = false)
    {
        $dsfHdl = $this->app->mydailystoragefeeStore()
                            ->searchView()
                            ->select()
                            ->where('partnerid', $partner->id)
                            ->where('status', MyDailyStorageFee::STATUS_ACTIVE)
                            ->orderBy('id', 'ASC');
                            
                            
        
        if ($dateFrom) {
            $dsfHdl = $dsfHdl->where('calculatedon', '>=', $dateFrom->format('Y-m-d H:i:s'));
        }

        if ($dateTo) {
            $dsfHdl = $dsfHdl->where('calculatedon', '<=', $dateTo->format('Y-m-d H:i:s'));
        }
        
        /** @var MyDailyStorageFee[] $dsFees */
        $dsFees = $dsfHdl->execute();
        $accHolders = [];

        /** @var MyPartnerSetting $settings */
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        foreach($dsFees as $fee) {

            if (! isset($accHolders[$fee->accountholderid])) {
                $accHolders[$fee->accountholderid] = $this->app->myaccountholderStore()->getById($fee->accountholderid);
            }

            $accHolder = $accHolders[$fee->accountholderid];
            $date = clone $fee->calculatedon;
            
            // Remove previous or inactivate
            if ($hardDelete) {
                $this->app->mydailystoragefeeStore()->delete($fee);
            } else {
                $fee->status = MyDailyStorageFee::STATUS_INACTIVE;
                $this->app->mydailystoragefeeStore()->save($fee);
            }
            
            $date->setTimezone($this->app->getServerTimezone());
            $this->calculateDailyFeeForAccountHolder($accHolder, $settings->storagefeeperannum, $settings->adminfeeperannum, $date);
        }
        
    }

    /**
     * Submit admin fee to sap
     *
     * @param Partner $partner
     * @param MyMonthlyStorageFee $fee
     * @param float $xau
     * @return bool
     */
    protected function submitAdminFeeToSAP($partner, $fee, $xau)
    {
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        $requestParams['itemCode'] = 'DG-999-9-' . $settings->sapdgcode;

        $this->log(__METHOD__ . "({$fee->refno}) init submitAdminFeeToSAP START.", SNAP_LOG_DEBUG);
        $sapSucceed = $this->submitFeeChargeToSAP($partner, $fee, $xau, 'admin_fee', $requestParams);
        $this->log(__METHOD__ . "({$fee->refno}) init submitAdminFeeToSAP END.", SNAP_LOG_DEBUG);

        if (!$sapSucceed) {
            $this->log(__METHOD__ . "():SAP Submission Failed for partner {$partner->code}, accountholder {$fee->accountholderid}", SNAP_LOG_ERROR);
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'SAP Fee Submission failed.']);
        }

        return $sapSucceed;
    }


    /**
     * Submit storage fee to sap
     *
     * @param Partner $partner
     * @param MyMonthlyStorageFee $fee
     * @param float $xau
     * @return bool
     */
    protected function submitStorageFeeToSAP($partner, $fee, $xau)
    {
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        $requestParams['itemCode'] = 'DG-999-9-' . $settings->sapdgcode;

        $this->log(__METHOD__ . "({$fee->refno}) init submitStorageFeeToSAP START.", SNAP_LOG_DEBUG);
        $sapSucceed = $this->submitFeeChargeToSAP($partner, $fee, $xau, 'storage_fee', $requestParams);
        $this->log(__METHOD__ . "({$fee->refno}) init submitStorageFeeToSAP END.", SNAP_LOG_DEBUG);

        if (!$sapSucceed) {
            $this->log(__METHOD__ . "():SAP Submission Failed for partner {$partner->code}, accountholder {$fee->accountholderid}", SNAP_LOG_ERROR);
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'SAP Fee Submission failed.']);
        }

        return $sapSucceed;
    }

    /**
     * Submit admin fee to SAP
     *
     * @param Partner $partner
     * @param MyMonthlyStorageFee $fee
     * @param float $xau
     * @return bool
     */
    protected function submitFeeChargeToSAP($partner, $fee, $xau, $type, $requestParams = [])
    {
        $apiManager = $this->app->apiManager();
        $version    = $requestParams['version'] = '1.0my';

        $this->log(__METHOD__ . "({$fee->refno}) init sapFeeCharge START.", SNAP_LOG_DEBUG);
        $sap_return = $apiManager->sapFeeCharge($partner, $version, $fee, $xau, $type, $requestParams);
        $this->log(__METHOD__ . "({$fee->refno}) init sapFeeCharge END.", SNAP_LOG_DEBUG);

        $sapSucceed = 'Y' == $sap_return[0]['success'];

        return $sapSucceed;
    }

    /**
     * Get the unique per account holder per month
     *
     * @param string      $prefix    The prefix for the reference number
     * @param dbdatastore $store     The object store
     */
    public function generateRefNo($prefix, $store, $format = 'ym')
    {
        /** @var \Snap\ICacher $cacher */
        $cacher = $this->app->getCacher();
        $now = new \DateTime('now', $this->app->getServerTimezone());
        $cacheKey = "ref:{$prefix}_" . $now->format($format);

        $cacher->waitForLock($cacheKey . "_lock", 1, 60, 60);
        $next = $cacher->increment($cacheKey, 1, 86400);    // keep key for 1 day
        if (1 == $next) {

            $totalCount = $store->searchTable()->select()->where('createdon', '>=', $now->format('Y-m-01 00:00:00'))->count();

            if (0 < $totalCount) {
                $next = $totalCount + 1;
                $cacher->set($cacheKey, $next, 86400);
            } else {
                $this->log(__METHOD__ . "(): New reference sequence for ($cacheKey)");
            }
        }
        $cacher->unlock($cacheKey . "_lock");

        $now->setTimezone($this->app->getUserTimezone());
        $refNo = strtoupper(sprintf("%s%s%07d", $prefix, $now->format($format), $next));
        $this->log(__METHOD__ . "(): Generated refno $refNo");

        return $refNo;
    }

    /**
     * Filter and get the chargeable account holder ids
     *
     * @param  MyAccountHolder[] $accHolders
     * @param  \DateTime $chargeMonthStart
     * @param  \DateTime $chargeMonthEnd
     * @return array
     */
    protected function filterChargeableAccountHolderIds($accHolders, $chargeMonthStart, $chargeMonthEnd)
    {
        $accHolderIds = array_keys($accHolders);

        $chargedAccHolders = $this->app->mymonthlystoragefeeStore()
            ->searchTable()
            ->select()
            ->whereIn('accountholderid', $accHolderIds)
            ->andWhere('chargedon', '>=', $chargeMonthStart->format('Y-m-d H:i:s'))
            ->andWhere('chargedon', '<', $chargeMonthEnd->format('Y-m-d H:i:s'))
            ->execute();

        $prevChargedIds = array_map(function ($accHolder) use ($chargeMonthEnd) {
            $this->log(__METHOD__ . "(): Skipping as account holder ({$accHolder->id}) was previously charged for storage fee month end ({$chargeMonthEnd->format('Y-m-d H:i:s')})", SNAP_LOG_DEBUG);
            return $accHolder->accountholderid;
        }, $chargedAccHolders);

        if (!is_null($prevChargedIds)) {
            $accHolderIds = array_diff($accHolderIds, $prevChargedIds);
        }

        return $accHolderIds;
    }

    /**
     * Filter and get calculatable account holder ids
     *
     * @param  MyAccountHolder[] $accHolders
     * @param  \DateTime $date
     * @return array
     */
    protected function filterCalculatableAccountHolderIds($accHolders, $date)
    {
        $accHolderIds = array_keys($accHolders);

        $day = clone $date;
        $day->setTimezone($this->app->getUserTimezone());
        $day->setTime(0,0,0);
        $day->setTimezone($this->app->getServerTimezone());


        $calculatedAccHolders = $this->app->mydailystoragefeeStore()
            ->searchTable()
            ->select('accountholderid')
            ->whereIn('accountholderid', $accHolderIds)
            ->where('status', MyDailyStorageFee::STATUS_ACTIVE)
            ->where('calculatedon', '>=', $day->format('Y-m-d H:i:s'))
            ->get();

        $prevCalculatedIds = array_map(function ($accHolder) use ($date) {
            $this->log(__METHOD__ . "(): Skipping as account holder ({$accHolder->id}) was previously calculated for storage fee ({$date->format('Y-m-d H:i:s')})", SNAP_LOG_DEBUG);
            return $accHolder->accountholderid;
        }, $calculatedAccHolders);

        if (!is_null($prevCalculatedIds)) {
            $accHolderIds = array_diff($accHolderIds, $prevCalculatedIds);
        }

        return $accHolderIds;
    }

    public function createStorageAdmTrxFromDb($partner, $transactions, $extname)
    {
        $this->log("[Transfer DB Process] Start Create new mymonthlystoragefee from other db", SNAP_LOG_DEBUG);
        foreach($transactions as $key => $aTransaction){
            /*check if transaction exist*/
            $refnoTrx = $key."_".$partner->code;
            $storageadmtrx  = $aTransaction['storageadmin'];
            $ledgertrans    = $aTransaction['ledger'];
            $storageadm = $this->app->mymonthlystoragefeeStore()->getByField('refno',$refnoTrx); 

            if(!$storageadm){
                $storageadm = $this->app->mymonthlystoragefeeStore()
                    ->create([
                        'xau'               => $storageadmtrx['xau'],
                        'price'             => $storageadmtrx['price'],
                        'amount'            => $storageadmtrx['amount'],
                        'accountholderid'   => 0,
                        'pricestreamid'     => 0,
                        'storagefeexau'     => $storageadmtrx['storagefeexau'],
                        'adminfeexau'       => $storageadmtrx['adminfeexau'],
                        'refno'             => $refnoTrx,
                        'status'            => $storageadmtrx['status'],
                        'chargedon'         => $this->app->spotorderManager()->formatDateTimeWhenTransfer($storageadmtrx['chargedon'])
                    ]);

                $this->log("[Transfer DB Process] Create new mymonthlystoragefee ".$storageadm->refno." from other db", SNAP_LOG_DEBUG);
            } else {
                if($storageadm->status != $storageadmtrx['status']) {
                    $storageadm->status = $storageadmtrx['status'];
                    $this->log("[Transfer DB Process] mymonthlystoragefee ".$refnoTrx." from other db already exist. . Change status successfully to ".$storageadmtrx['status'], SNAP_LOG_DEBUG);
                }
            }

            $saveStorageAdmin = $this->app->mymonthlystoragefeeStore()->save($storageadm);

            if($saveStorageAdmin){
                $this->logDebug("[Transfer DB Process] Creating partner ledger for monthlystoragefee $saveStorageAdmin->refno");
                /*check if ledger exist*/
                $ledgerStore = $this->app->myledgerStore();
                $ledgerTrx = $ledgerStore->getByField('refno',$saveStorageAdmin->refno); 
                if(!$ledgerTrx){
                    $partnerLedger = $ledgerStore->create([
                        'accountholderid'   => 0,
                        'partnerid'         => $partner->id,
                        'refno'             => $saveStorageAdmin->refno,
                        'typeid'            => $saveStorageAdmin->id,
                        'transactiondate'   => $this->app->spotorderManager()->formatDateTimeWhenTransfer($ledgertrans['transactiondate'],'transactiondate'),
                        'type'              => $ledgertrans['type'],
                        'credit'            => $ledgertrans['credit'],
                        'debit'             => $ledgertrans['debit'],
                        'remarks'           => $ledgertrans['remarks'],
                        'status'            => $ledgertrans['status']
                    ]);

                    $updateLedger = $ledgerStore->save($partnerLedger);
                    $this->logDebug("[Transfer DB Process] Finished saving partner ledger for monthlystoragefee $saveStorageAdmin->refno");
                } else $this->logDebug("[Transfer DB Process] Ledger for monthlystoragefee $saveStorageAdmin->refno already exist");
            }
            $this->log("[Transfer DB Process] mymonthlystoragefee ".$refnoTrx." successfully add/edit", SNAP_LOG_DEBUG);
            
        }
        return $saveStorageAdmin;
    }
	
	/**
	 * Calculate the management fee at the time of opening based on provided parameters.
	 *
	 * @param App $app The application instance.
	 * @param MyAccountHolder $myAccountHolder The account holder object.
	 * @param PriceProvider $priceProvider The price provider object.
	 * @param Partner $partner The partner object.
	 * @param DateTime $openDateTime The opening date and time.
	 * @throws \Exception If an error occurs during the calculation or transaction.
	 * @return void
	 */
	function calculateManagementFeeOpen ($myAccountHolder, $priceProvider, $partner, $openDateTime)
	{
        try {
            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }

			$currentBalance = strval($myAccountHolder->getCurrentGoldBalance());
			$balanceXauOpen = $currentBalance;

			$firstPriceOfDayDataKey = '{FirstPriceOfTheDay}:' . $priceProvider->id;
			$priceStreamCache = $this->app->getCache($firstPriceOfDayDataKey);
			$firstPriceOfDay = $this->app->priceStreamStore()->create();
			$firstPriceOfDay->fromCache($priceStreamCache);
			
			$priceOpen = $partner->calculator()->round($firstPriceOfDay->companysellppg);
			$amountOpen = $partner->calculator()->multiply($balanceXauOpen, $priceOpen);
			
			$openDateTime->setTimezone($this->app->getUserTimezone());
			$myDailyStorageFee = $this->app->mydailystoragefeeStore()->create([
				'xau' => 0,
				'balancexauopen' => $balanceXauOpen,
				'priceopen' => $priceOpen,
				'amountopen' => $amountOpen,
				'accountholderid' => $myAccountHolder->id,
				'status' => MyDailyStorageFee::STATUS_ACTIVE,
				'calculatedon' => $openDateTime->format('Y-m-d H:i:s'),
			]);

			$this->app->mydailystoragefeeStore()->save($myDailyStorageFee);

			if ($ownsTransaction) {
				$this->app->getDBHandle()->commit();
			}
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }
            throw $e;
        }
	}
	
	/**
	 * Calculate the management fee at the time of closing based on provided parameters.
	 *
	 * @param App $app The application instance.
	 * @param MyAccountHolder $myAccountHolder The account holder object.
	 * @param PriceProvider $priceProvider The price provider object.
	 * @param Partner $partner The partner object.
	 * @param DateTime $openDateTime The opening date and time.
	 * @param DateTime $closeDateTime The closing date and time.
	 * @return void
	 */	
	function calculateManagementFeeClose ($myAccountHolder, $priceProvider, $partner, $openDateTime, $closeDateTime)
	{
		$myDailyStorageFee = $this->app->mydailystoragefeeStore()
							->searchTable()
							->select()
							->where('accountholderid', $myAccountHolder->id)
							->where('calculatedon', $openDateTime->format('Y-m-d H:i:s'))
							->where('status', MyDailyStorageFee::STATUS_ACTIVE)
							->one();
		
		if ($myDailyStorageFee) {
			$currentBalance = strval($myAccountHolder->getCurrentGoldBalance());
			$balanceXauClose = $currentBalance;
			$balanceXauAvg = $partner->calculator(false)->divide(($myDailyStorageFee->balancexauopen + $balanceXauClose), 2);

			$lastPriceOfDayDataKey = '{LastPriceOfTheDay}:' . $priceProvider->id;
			$priceStreamCache = $this->app->getCache($lastPriceOfDayDataKey);
			$lastPriceOfDay = $this->app->priceStreamStore()->create();
			$lastPriceOfDay->fromCache($priceStreamCache);
			$priceClose = $partner->calculator()->round($lastPriceOfDay->companysellppg);
			
			$amountClose = $partner->calculator()->multiply($balanceXauClose, $priceClose);
			$amountAvg = $partner->calculator()->divide(($myDailyStorageFee->amountopen + $amountClose), 2);
			$accruedDailyFee = (0 < $balanceXauClose) ? $this->getAccruedDailyFee($balanceXauAvg, $amountAvg, $partner) : 0;
			
			$myDailyStorageFee->balancexauclose = $balanceXauClose;
			$myDailyStorageFee->balancexauavg = $balanceXauAvg;
			$myDailyStorageFee->priceclose = $priceClose;
			$myDailyStorageFee->amountclose = $amountClose;
			$myDailyStorageFee->amountavg = $amountAvg;
			$myDailyStorageFee->accrueddailyfee = $accruedDailyFee;
			$myDailyStorageFee->xau = 0;
			
			$closeDateTime->setTimezone($this->app->getUserTimezone());
			$myDailyStorageFee->calculatedon = $closeDateTime->format('Y-m-d H:i:s');

			$this->app->mydailystoragefeeStore()->save($myDailyStorageFee);
		}
	}
	
	/**
	 * Calculate the accrued daily fee based on provided parameters.
	 *
	 * @param App $app The application instance.
	 * @param float $balanceXauAvg The average gold balance.
	 * @param float $amountAvg The average amount.
	 * @param Partner $partner The partner object.
	 * @return float The calculated accrued daily fee.
	 */
	function getAccruedDailyFee ($balanceXauAvg, $amountAvg, $partner)
	{
		$accruedDailyFee = 0;
		$otcManagementFee = $this->app->otcmanagementfeeStore()
							->searchTable()
							->select()
							->where('avgdailygoldbalancegramfrom', '<=', $balanceXauAvg)
							->where('avgdailygoldbalancegramto', '>=', $balanceXauAvg)
							->where('status', OtcManagementFee::STATUS_ACTIVE)
							->one();
		
		if ($otcManagementFee) {
			$feePercent = $otcManagementFee->feepercent / 100;
			$feeAmount = $amountAvg * $feePercent;
			$accruedDailyFee = $partner->calculator()->divide($feeAmount, 360);
		}
		
		return $accruedDailyFee;
	}
	
	/**
	 * Calculate and record the monthly management fee for account holders.
	 *
	 * @param DateTime $startDateTime The start date and time for the fee calculation.
	 * @param DateTime $closeDateTime The closing date and time for the fee calculation.
	 *
	 * @return void
	 */
	function calculateMonthlyManagementFee ($startDateTime, $closeDateTime)
	{
		$columnPrefix = $this->app->mydailystoragefeeStore()->getColumnPrefix();
		$handle = $this->app->mydailystoragefeeStore()->searchTable(false);
		$query = $handle
				->select(['accountholderid'])
				->addField($handle->raw("SUM({$columnPrefix}accrueddailyfee)"), "{$columnPrefix}accruedmonthlyfee")
				->where('calculatedon', '>=', $startDateTime->format('Y-m-d H:i:s'))
				->where('calculatedon', '<=', $closeDateTime->format('Y-m-d H:i:s'))
				->groupBy('accountholderid');
		$accruedMonthlyFees = $query->execute();	
		
		if ($accruedMonthlyFees) {
			$closeDateTime->setTimezone($this->app->getUserTimezone());
			foreach ($accruedMonthlyFees as $accruedMonthlyFee) {
				$myMonthlyStorageFee = $this->app->mymonthlystoragefeeStore()->create([
					'xau' => '0',
					'price' => '0',
					'amount' => '0',
					'adminfeexau' => '0',
					'storagefeexau' => '0',
					'accountholderid' => $accruedMonthlyFee[$columnPrefix.'accountholderid'],
					'accruedmonthlyfee' => $accruedMonthlyFee[$columnPrefix.'accruedmonthlyfee'],
					'pricestreamid' => '0',
					'status' => MyMonthlyStorageFee::STATUS_ACTIVE,
					'chargedon' => $closeDateTime->format('Y-m-d H:i:s')
				]);

				$this->app->mymonthlystoragefeeStore()->save($myMonthlyStorageFee);
			}
		}
	}
	
	/**
	 * Calculate quarterly management fee and create corresponding monthly storage fees and payments.
	 *
	 * @param MyDailyStorageFee $myDailyStorageFee The daily storage fee instance.
	 * @param DateTime $startDateTime The start date and time for calculating the quarterly fee.
	 * @param DateTime $closeDateTime The close date and time for calculating the quarterly fee.
	 * @param DateTime $chargeDateTime The date and time when the fee is charged.
	 * @param float $priceClose The closing price used for calculation.
	 * @param Partner $partner The partner instance for fee calculation.
	 *
	 * @return void
	 */
	function calculateQuarterlyManagementFee ($myDailyStorageFee, $startDateTime, $closeDateTime, $chargeDateTime, $priceClose, $partner)
	{
		$records = $this->app->mydailystoragefeeStore()->searchTable(false)
				->select(['accountholderid'])
				->addFieldSum('xau', 'quarterlyxau')
				->addFieldSum('accrueddailyfee', 'quarterlyfee')
				->where('accountholderid', $myDailyStorageFee->accountholderid)
				//->where('calculatedon', '>=', $startDateTime->format('Y-m-d H:i:s'))
				//->where('calculatedon', '<=', $closeDateTime->format('Y-m-d H:i:s'))
				->where('accrueddailyfee', '>', 0)
				->where('status', MyDailyStorageFee::STATUS_ACTIVE)
				->execute();
				
		if ($records) {
			foreach ($records as $record) {
				$accountHolderId = $record['dsf_accountholderid'];
				$xau = $partner->calculator(false)->divide($record['quarterlyfee'], $priceClose);
				$quarterlyFee = $record['quarterlyfee'];
				$chargedonDateTime = clone $closeDateTime;
				$chargedonDateTime->setTimezone($this->app->getUserTimezone());
				$refNo = $this->generateRefNo('SF', $this->app->mymonthlystoragefeeStore());
				$myMonthlyStorageFee = $this->app->mymonthlystoragefeeStore()->create([
					'xau' => $xau,
					'price' => $priceClose,
					'amount' => '0',
					'adminfeexau' => '0',
					'storagefeexau' => '0',
					'accountholderid' => $accountHolderId,
					'refno' => $refNo,
					'accruedmonthlyfee' => $quarterlyFee,
					'pricestreamid' => '0',
					'status' => MyMonthlyStorageFee::STATUS_ACTIVE,
					'chargedon' => $chargeDateTime->format('Y-m-d H:i:s')
				]);

				$msFee = $this->app->mymonthlystoragefeeStore()->save($myMonthlyStorageFee);

				if ($msFee) {
					$dbHandle = $this->app->getDBHandle();
					$store = $this->app->mydailystoragefeeStore();
					$prefix = $store->getColumnPrefix();
					$condition = sprintf("{$prefix}status = %d AND {$prefix}accountholderid = %d", MyDailyStorageFee::STATUS_ACTIVE, $accountHolderId);
					$fields = sprintf("{$prefix}status = '%d'", MyDailyStorageFee::STATUS_INACTIVE);
					$tableName = $store->getTableName();
					$result = $dbHandle->query("UPDATE {$tableName} SET {$fields} WHERE {$condition}");
					if (1 <= $result->rowCount()) {
						$accountHolder = $this->app->myaccountholderStore()->getById($accountHolderId);
						$now = new \DateTime('now', $this->app->getUserTimezone());
						
						$payment = $this->app->mypaymentdetailStore()
									->create([
										'amount' => $msFee->accruedmonthlyfee,
										'customerfee' => 0.00,
										'accountholderid' => $accountHolder->id,
										'paymentrefno' => $this->generateRefNo("P", $this->app->mypaymentdetailStore()),
										'sourcerefno'  => $msFee->refno,
										'status'       => MyPaymentDetail::STATUS_PENDING_PAYMENT,
										'productdesc'  => "GDI Debit Management Fee",
										'transactiondate' => $now,
										'verifiedamount' => 0.00,
										'priceuuid' => 0,
										'token' => $accountHolder->accountnumber,
									]);

						$payment = $this->app->mypaymentdetailStore()->save($payment);
						
						if ($payment) {
							$apiJob = $this->app->apijobStore()
											->create([
												'refobjectid' => $msFee->id,
												'accountholderid' => $accountHolder->id,
												'apiclass' => $this->app->getConfig()->{'otc.casa.api'},
												'apimethod' => 'initializeTransaction'
											]);
											
							$this->app->apijobStore()->save($apiJob);
						}
					}
				}
			}
		}
	}
	
	/**
	 * Send storage fee notification to account holders
	 *
	 * @param mixed $accountHolder The account holder object.
	 * @param DateTime $startDateTime The start date and time of the date range.
	 * @param DateTime $closeDateTime The end date and time of the date range.
	 *
	 * @throws Exception If an error occurs during notification.
	 */	
	function storageFeeNotification ($accountHolder, $notificationEvent, $amount = 0, $weight = 0)
	{	
		$now = new \DateTime('now', $this->app->getUserTimezone());
		$observation = new \Snap\IObservation(
			$accountHolder,
			\Snap\IObservation::ACTION_NONE,
			$accountHolder->status,
			[
				'event' => $notificationEvent,
				'projectbase' => $this->app->getConfig()->{'projectBase'},
				'name' => $accountHolder->fullname,
                'accountnumber' => $accountHolder->accountnumber,
				'casaenddigit' => substr($accountHolder->accountnumber, -4),
				'receiver' => $accountHolder->email,
				'datetime' => $now->format('Y-m-d H:i:s'),
				'amount' => number_format($amount,2),
				'weight' => $weight
			]
		);

		$this->notify($observation);
	}
	
	/**
	 * Get the total outstanding management fee for a given account holder.
	 *
	 * @param int $accountHolderId The ID of the account holder.
	 *
	 * @return float The total outstanding management fee amount.
	 */
	function getOutStandingManagementFee ($accountHolderId)
	{
		$outStandingStorageFee = $this->app->mymonthlystoragefeeStore()
						->searchTable(false)
						->select()
						->addFieldSum('amount', 'amount')
						->where('accountholderid', $accountHolderId)
						->whereIn('status', [MyMonthlyStorageFee::STATUS_ACTIVE, MyMonthlyStorageFee::STATUS_FAILED])
						->one();
						
		return $outStandingStorageFee['amount'] ?? 0;
	}
}
