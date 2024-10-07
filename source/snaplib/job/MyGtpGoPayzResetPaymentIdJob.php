<?php

namespace Snap\job;

use Snap\api\payout\BasePayout;
use Snap\api\wallet\BaseWallet;
use Snap\api\wallet\GoPayz;
use Snap\object\MyGoldTransaction;
use Snap\object\Order;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Azam <azam@silverstream.my>
* @version 1.0
* @created 27-Aug-2021
*/

class MyGtpGoPayzResetPaymentIdJob extends basejob
{
    /** @var MyPaymentDetail[] $paymentDetails */
    private $paymentDetails = [];

    public function doJob($app, $params = array())
    {
        if (! isset($params['partnercode']) || 0 == strlen($params['partnercode'])) {
            $this->logDebug(__METHOD__ . '(): No partner code was given');
            return;
        }

        $partnercode = $params['partnercode'];            
        $partner = $app->partnerStore()->getByField('code', $partnercode);
        $settings = $app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        $walletName = basename(str_replace('\\', '/', $settings->partnerpaymentprovider));
        
        $this->logDebug(__METHOD__ . ": ------------ Begin Resetting Gatewayrefno ---------------");
        
        $className = "\\Snap\\api\\wallet\\{$walletName}";

        /** @var GoPayz $wallet */
        $wallet = BaseWallet::getInstance($className);

        /** @var MyGoldTransaction[] $pendingPaymentGoldTx  */
        $pendingPaymentGoldTx = $app->mygoldtransactionStore()->searchView()
                                        ->select('refno')
                                        ->where('ordpartnerid', $partner->id)
                                        ->whereIn('status', [MyGoldTransaction::STATUS_CONFIRMED, MyGoldTransaction::STATUS_PAID])
                                        ->andWhere('ordtype', Order::TYPE_COMPANYSELL)
                                        ->whereIn('ordstatus', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED])
                                        ->andWhere('settlementmethod', MyGoldTransaction::SETTLEMENT_METHOD_WALLET)                                                                                
                                        ->execute();

        try {
            $startedTransaction = $app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $app->getDBHandle()->beginTransaction();
            }

            foreach ($pendingPaymentGoldTx as $goldTx) {
            
                $paymentDetail = $app->mypaymentdetailStore()->getByField('sourcerefno', $goldTx->refno);
                $wallet->resetPaymentId($paymentDetail);
                
            }

            if ($ownsTransaction) {
                $app->getDBHandle()->commit();
            }

        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $app->getDBHandle()->rollBack();
            }
            
            $this->log(__METHOD__ . "(): Error while resetting gatewayrefno for payment ({$paymentDetail->paymentrefno})", SNAP_LOG_ERROR);
            throw $e;
        }
        
        $this->logDebug(__METHOD__ . ": ------------ Finished Resetting Gatewayrefno ---------------");
    }

    public function describeOptions()
    {
        return [
            'partnercode' => ['required' => true, 'type' => 'string', 'desc' => "Partner code to re-apply payment id from checkPayment API."],
        ];
    }
}