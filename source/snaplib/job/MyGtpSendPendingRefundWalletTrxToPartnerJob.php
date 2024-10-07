<?php

namespace Snap\job;

use Snap\manager\MyGtpTransactionManager;
use Snap\object\MyGoldTransaction;
use Snap\object\Order;
use Snap\api\wallet\BaseWallet;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2023
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Rinston Oliver <rinston@silverstream.my>
* @version 1.0
* @created 15-Mac-2023
*/

class MyGtpSendPendingRefundWalletTrxToPartnerJob extends basejob
{
    public function doJob($app, $params = array())
    {
        $this->log("Starting cron job for Send Pending Refund Wallet trx...", SNAP_LOG_DEBUG);
        $partner = explode(',', $params['partner']);

        $cutOffTimeStart = new \DateTime("10 minutes ago");
        $cutOffTimeStartFormatted = $cutOffTimeStart->format('Y-m-d H:i:s');
        $this->log("Start date: {$cutOffTimeStartFormatted}", SNAP_LOG_DEBUG);
        echo $cutOffTimeStartFormatted."\n";

        $cutOffTimeEnd = new \DateTime(date('Y-m-d')." 23:59:59");
        $cutOffTimeEndFormatted = $cutOffTimeEnd->format('Y-m-d H:i:s');
        $this->log("End date: {$cutOffTimeEndFormatted}", SNAP_LOG_DEBUG);
        echo $cutOffTimeEndFormatted."\n";

        $partnersetting = $app->mypartnersettingStore()
                               ->searchTable()
                               ->select()
                               ->where('partnerid', '=', $partner)
                               ->forwardKey('partnerid')
                               ->get();
        
        $goldtxStore = $app->mygoldtransactionStore();
        $pendingRefundGoldTx = $goldtxStore->searchView()->select()
                                ->where('ordpartnerid', '=', $partner)
                                ->andWhere('createdon', '<=', $cutOffTimeEndFormatted)
                                ->andWhere('createdon', '>=', $cutOffTimeStartFormatted)
                                ->andWhere('status', MyGoldTransaction::STATUS_PENDING_REFUND)
                                ->andWhere('ordtype', Order::TYPE_COMPANYSELL)
                                ->andWhere('settlementmethod', MyGoldTransaction::SETTLEMENT_METHOD_WALLET)
                                ->execute();
    
        $ttl = count($pendingRefundGoldTx);
        $this->log("Total record: {$ttl}", SNAP_LOG_DEBUG);
        if ($ttl > 0) {
            $sent = $this->sendTrxToPartner($app, $partnersetting, $pendingRefundGoldTx);
        }
    }

    public function sendTrxToPartner($app, $partnersetting, $pendingRefundGoldTx){
        foreach ($pendingRefundGoldTx as $tx) {
            try {
                
                $refno = $tx->refno;
                $partnerId = $tx->ordpartnerid;
                $this->log("Send {$tx->refno} to partner {$tx->ordpartnerid}", SNAP_LOG_DEBUG);

                $paymentProvider = BaseWallet::getInstance($partnersetting[$partnerId]->partnerpaymentprovider);
                if (!$paymentProvider) {
                    throw new \Exception(__METHOD__ . "(): No partner payment provider found for partner {$partnerId}");
                }
                $paymentDetail = $app->mypaymentdetailStore()->getByField('sourcerefno', $refno);
                if (!$paymentDetail) {
                    throw new \Exception(__METHOD__ . "(): No payment found for {$refno}");
                }

                

                $app->getDBHandle()->beginTransaction();
                $one = $paymentProvider->sendPendingRefundStatus($paymentDetail);
                $app->getDBHandle()->commit();
            } catch (\Throwable $e) {
                echo $e->getMessage();
                $this->log("ERROR sendTrxToPartner: ".$e->getMessage());
                if ($app->getDBHandle()->inTransaction()) {
                    $app->getDbHandle()->rollback();
                }
            }
        }
    }

    public function describeOptions()
    {
        return [
            'partner' => array('required' => true,  'type' => 'string', 'desc' => 'Partner ID'),
        ];
    }
}