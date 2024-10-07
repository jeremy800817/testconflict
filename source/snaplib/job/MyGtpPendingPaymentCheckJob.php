<?php

namespace Snap\job;

use Snap\api\fpx\BaseFpx;
use Snap\api\payout\BasePayout;
use Snap\api\wallet\BaseWallet;
use Snap\object\MyConversion;
use Snap\object\MyGoldTransaction;
use Snap\object\MyPaymentDetail;
use Snap\object\Order;
use Throwable;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Azam <azam@silverstream.my>
* @version 1.0
* @created 17-Sept-2021
*/

class MyGtpPendingPaymentCheckJob extends basejob
{
    public function doJob($app, $params = array())
    {
        if($app->getConfig()->{'otc.job.diffserver'} == '1'){
            $partnerid = $params['partnerids'];
            $partner = $app->partnerStore()->searchTable()->select()->where('group', $partnerid)->execute();
            $partnerIds = [];
            foreach($partner as $record){
                array_push($partnerIds, $record->id);
            }
        }
        else{
            $partnerIds = explode(',', $params['partnerids']);
        }

        $goldTxStore = $app->mygoldtransactionStore();
        $conversionStore = $app->myconversionStore();

        // Retries for minute 3, 4, 5 
        $cutOffTime = new \DateTime('3 minutes ago');
        $cutOffTimeFormatted = $cutOffTime->format('Y-m-d H:i:s');

        // Retries for minute 8, 9, 10
        $conversionCutOffTime = new \DateTime('8 minutes ago');
        $conversionCutOffTimeFormatted = $conversionCutOffTime->format('Y-m-d H:i:s');

        $this->log("Checking for pending transactions before ". $cutOffTimeFormatted, SNAP_LOG_INFO);

        /** @var MyGoldTransaction[] $pendingTransactions */
        $pendingGoldTransactions = $goldTxStore->searchView()->select()
                                        ->where('createdon', '<=', $cutOffTimeFormatted)
                                        ->andWhere('ordpartnerid', 'in', $partnerIds)
                                        ->andWhere('status', MyGoldTransaction::STATUS_PENDING_PAYMENT)
                                        ->andWhere('ordtype', Order::TYPE_COMPANYSELL)
                                        ->execute();

        /** @var MyGoldTransaction[] $pendingWalletSellTransactions */
        $pendingWalletSellTransactions = $goldTxStore->searchView()->select()
                                        ->where('createdon', '<=', $cutOffTimeFormatted)
                                        ->andWhere('ordpartnerid', 'in', $partnerIds)
                                        ->andWhere('status', MyGoldTransaction::STATUS_PENDING_PAYMENT)
                                        ->andWhere('settlementmethod', MyGoldTransaction::SETTLEMENT_METHOD_WALLET)
                                        ->andWhere('ordtype', Order::TYPE_COMPANYBUY)
                                        ->execute();



        $pendingConversions = $conversionStore->searchView()->select()
                                        ->where('createdon', '<=', $conversionCutOffTimeFormatted)
                                        ->andWhere('rdmpartnerid', 'in', $partnerIds)
                                        ->andWhere('status', MyConversion::STATUS_PAYMENT_PENDING)
                                        ->execute();

        $partnerSettings = $app->mypartnersettingStore()
                               ->searchTable()
                               ->select()
                               ->where('partnerid', 'in', $partnerIds)
                               ->forwardKey('partnerid')
                               ->get();


        if (0 < count($pendingGoldTransactions)) {
            $this->handleCompanyBuyOrder($app, $partnerSettings, $pendingGoldTransactions);
        }

        if (0 < count($pendingWalletSellTransactions)) {
            $this->handleCompanySellOrder($app, $partnerSettings, $pendingWalletSellTransactions);
        }

        if (0 < count($pendingConversions)) {
            $this->handleConversion($app, $partnerSettings, $pendingConversions);
        }

        $this->log("Finished checking for pending transactions before $cutOffTimeFormatted", SNAP_LOG_INFO);
    }

    protected function handleCompanyBuyOrder($app, $partnerSettings, $pendingTransactions)
    {
        foreach ($pendingTransactions as $tx) {
            try {

                $refno = $tx->refno;
                $partnerId = $tx->ordpartnerid;

                $this->log("Checking payment for {$refno}", SNAP_LOG_DEBUG);

                if (MyGoldTransaction::SETTLEMENT_METHOD_WALLET === $tx->settlementmethod) {
                    $paymentProvider = BaseWallet::getInstance($partnerSettings[$partnerId]->partnerpaymentprovider);
                } else {
                    $paymentProvider = BaseFpx::getInstance($partnerSettings[$partnerId]->companypaymentprovider);
                }

                $paymentDetail = $app->mypaymentdetailStore()->getByField('sourcerefno', $refno);
                if (!$paymentDetail) {
                    throw new \Exception(__METHOD__ . "(): No payment found for {$refno}");
                }

                if (!$paymentProvider) {
                    throw new \Exception(__METHOD__ . "(): No partner payment provider found for partner {$partnerId}");
                }

                $cacheKey = '{PendingPaymentTx}:' . $paymentDetail->id;
                $cacher = $app->getCacher();
                $current = $cacher->increment($cacheKey, 1, 600);    // keep key for 5 Minutes

                // If has been more than 3 retries to get status then we skip
                // The other job file will expire this transaction 
                if (3 < $current) {
                    continue;
                }

                $app->getDBHandle()->beginTransaction();
                $paymentProvider->getPaymentStatus($paymentDetail);
                $app->getDBHandle()->commit();
            } catch (\Throwable $e) {
                $this->log($e->getMessage());
                if ($app->getDBHandle()->inTransaction()) {
                    $app->getDbHandle()->rollback();
                }
            }
        }
    }

    protected function handleCompanySellOrder($app, $partnerSettings, $pendingTransactions)
    {
        foreach ($pendingTransactions as $tx) {
            try {
                if (MyGoldTransaction::SETTLEMENT_METHOD_WALLET === $tx->settlementmethod) {
    
                    $refno = $tx->refno;
                    $partnerId = $tx->ordpartnerid;
    
                    $this->log("Checking payment for {$refno}", SNAP_LOG_DEBUG);

                    $walletName = basename(str_replace('\\', '/', $partnerSettings[$partnerId]->partnerpaymentprovider));
                    $className = "\\Snap\\api\\payout\\{$walletName}Payout";
                    $paymentProvider = BasePayout::getInstance($className);

                    $disbursement = $app->mydisbursementStore()->getByField('transactionrefno', $refno);
                    if (!$disbursement) {
                        throw new \Exception(__METHOD__ . "(): No payment found for {$refno}");
                    }

                    if (!$paymentProvider) {
                        throw new \Exception(__METHOD__ . "(): No partner payment provider found for partner {$partnerId}");
                    }

                    $app->getDBHandle()->beginTransaction();
                    $paymentProvider->getPayoutStatus($disbursement);
                    $app->getDBHandle()->commit();
                }
            } catch (\Throwable $e) {
                $this->log($e->getMessage());
                if ($app->getDBHandle()->inTransaction()) {
                    $app->getDbHandle()->rollback();
                }
            }
        }
    }

    protected function handleConversion($app, $partnerSettings, $pendingTransactions)
    {
        foreach ($pendingTransactions as $tx) {
            try {

                

                // This is due to splitted conversions using 1 PaymentDetail
                $refno = $tx->getRefNo(true);
                $partnerId = $tx->rdmpartnerid;

                $this->log("Checking payment for {$refno}", SNAP_LOG_DEBUG);

                if (MyConversion::LOGISTIC_FEE_PAYMENT_MODE_WALLET === $tx->logisticfeepaymentmode) {
                    $className = $partnerSettings[$partnerId]->partnerpaymentprovider;
                    if (class_exists($className . 'Conversion')) {
                        $className = $className . 'Conversion';
                    }
                    $paymentProvider = BaseWallet::getInstance($className);
                } else {
                    $paymentProvider = BaseFpx::getInstance($partnerSettings[$partnerId]->companypaymentprovider);
                }

                $paymentDetail = $app->mypaymentdetailStore()->getByField('sourcerefno', $refno);
                if (!$paymentDetail) {
                    throw new \Exception(__METHOD__ . "(): No payment found for {$refno}");
                }

                if (!$paymentProvider) {
                    throw new \Exception(__METHOD__ . "(): No partner payment provider found for partner {$partnerId}");
                }

                $cacheKey = '{PendingPaymentTx}:' . $paymentDetail->id;
                $cacher = $app->getCacher();
                $current = $cacher->increment($cacheKey, 1, 600);    // keep key for 5 Minutes

                // If has been more than 3 retries to get status then we skip
                // The other job file will expire this transaction 
                if (3 < $current) {
                    continue;
                }
                $app->getDBHandle()->beginTransaction();
                $paymentProvider->getPaymentStatus($paymentDetail);
                $app->getDBHandle()->commit();
            } catch (\Throwable $e) {
                $this->log($e->getMessage());
                if ($app->getDBHandle()->inTransaction()) {
                    $app->getDbHandle()->rollback();
                }
            }
        }
    }

    public function describeOptions()
    {
        return [
            'partnerids' => array('required' => true,  'type' => 'string', 'desc' => 'Comma separated list of partner ids'),
        ];
    }
}