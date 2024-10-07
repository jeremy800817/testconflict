<?php

namespace Snap\job;

use Snap\object\MyGoldTransaction;
use Snap\object\MyPaymentDetail;
use Snap\object\Order;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Azam <azam@silverstream.my>
* @version 1.0
* @created 17-Sept-2021
*/

class MyGtpResetFailedTransactionJob extends basejob
{
    public function doJob($app, $params = array())
    {
        $refNo = explode(',', $params['refno']);
        
        $this->log("Begin reset failed transactions ". $params['refno'], SNAP_LOG_INFO);

        /** @var MyGoldTransaction[] $pendingTransactions */
        $pendingTransactions = $app->mygoldtransactionStore()->searchView()->select()                                        
                                        ->whereIn('refno', $refNo)                                        
                                        ->execute();
        
        foreach ($pendingTransactions as $tx) {
            try {

                $this->log("Begin reset failed transaction with refno ". $tx->refno, SNAP_LOG_INFO);

                if (MyGoldTransaction::STATUS_FAILED != $tx->status) {
                    throw new \Exception("GoldTransaction {$tx->refno} status is not Failed");
                }
                
                $order = $tx->getOrder();
                if (Order::STATUS_EXPIRED != $order->status) {
                    throw new \Exception("Order {$order->orderno} status is not Expired");
                }

                /** @var MyPaymentDetail $payment */
                $payment = $app->mypaymentdetailStore()->getByField('sourcerefno', $tx->refno);
                if (MyPaymentDetail::STATUS_CANCELLED != $payment->status) {
                    throw new \Exception("PaymentDetail {$payment->paymentrefno} status is not Cancelled");
                }

                $app->getDBHandle()->beginTransaction();

                $tx->status      = MyGoldTransaction::STATUS_PENDING_PAYMENT;
                $order->status   = Order::STATUS_PENDING;
                $payment->status = MyPaymentDetail::STATUS_PENDING_PAYMENT;

                $tx      = $app->mygoldtransactionStore()->save($tx);
                $order   = $app->orderStore()->save($order);
                $payment = $app->mypaymentdetailStore()->save($payment);

                $app->getDBHandle()->commit();
                $this->log("Finished reset failed transaction with refno ". $tx->refno, SNAP_LOG_INFO);
            } catch (\Exception $e) {
                $this->log($e->getMessage());
                $app->getDBHandle()->rollBack();
            }

        }

        $this->log("Finished resetting failed transactions {$params['refno']}", SNAP_LOG_INFO);
    }

    public function describeOptions()
    {
        return [
            'refno' => array('required' => true,  'type' => 'string', 'desc' => 'Comma separated list of transactionrefno'),
        ];
    }
}