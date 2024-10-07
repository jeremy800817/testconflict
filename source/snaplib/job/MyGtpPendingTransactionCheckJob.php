<?php

namespace Snap\job;

use Snap\api\payout\BasePayout;
use Snap\api\wallet\BaseWallet;
use Snap\App;
use Snap\InputException;
use Snap\object\MyDisbursement;
use Snap\object\MyGoldTransaction;
use Snap\object\MyLedger;
use Snap\object\MyPaymentDetail;
use Snap\object\MyToken;
use Snap\object\Order;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 08-Jan-2021
*/

class MyGtpPendingTransactionCheckJob extends basejob
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
        // $cutOffTime = new \DateTime('10 minutes ago');

        // 19/11/2021 As requested by Ryan that the total timeout would be 5 minutes + 20 sec (Enter Pin)
        // But 20 sec is not part of the backend so we put 5 minutes timeout
        if($app->getConfig()->{'otc.job.diffserver'} == '1'){
            $cutOffTime = new \DateTime('3 minutes ago');
        }
        else{
            $cutOffTime = new \DateTime('5 minutes ago');
        }
        // $cutOffTime = new \DateTime('5 minutes ago');

        $cutOffTimeFormatted = $cutOffTime->format('Y-m-d H:i:s');
        $this->log("Checking for pending transactions before ". $cutOffTimeFormatted, SNAP_LOG_INFO);

        $pendingTransactions = $goldTxStore->searchView()->select()
                                        ->where('createdon', '<=', $cutOffTimeFormatted)
                                        ->andWhere('ordpartnerid', 'in', $partnerIds)
                                        ->andWhere('status', 'IN', [MyGoldTransaction::STATUS_PENDING_PAYMENT, MyGoldTransaction::STATUS_PENDING_APPROVAL])
                                        ->execute();
        
        $partnerSettings = $app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'in', $partnerIds)->forwardKey('partnerid')->get();

        foreach ($pendingTransactions as $tx) {
            try {
                $order = $tx->getOrder();
                // Updated by Cheok on 2021-05-18 to cater for status flow change for sell transactions

                // Skip if its a customer sell, pending status for sell order is valid forever until disbursed
                // if (Order::TYPE_COMPANYBUY ==  $order->type) {

                //     // For wallet handle using its own class
                //     if (MyGoldTransaction::SETTLEMENT_METHOD_WALLET === $tx->settlementmethod) {
                //             $walletName = basename(str_replace('\\', '/', $partnerSettings[$tx->ordpartnerid]->partnerpaymentprovider));
                //             $className = "\\Snap\\api\\payout\\{$walletName}Payout";
                //             $payout = BasePayout::getInstance($className);

                //             $disbursement = $app->mydisbursementStore()->searchTable()->select()->where('transactionrefno', $tx->refno)->one();
                //             $payout->handleExpiredTransaction($disbursement);
                //     }
                //     continue;
                // }
                // End update by Cheok
                // if($app->getConfig()->{'otc.job.diffserver'} == '1'){
                    
                // }
                // else{
                //     $payment = $app->mypaymentdetailStore()->getByField('sourcerefno', $tx->refno);
                //     $cacheKey = '{PendingPaymentTx}:' . $payment->id;
                //     $cacher = $app->getCacher();
                //     $current = $cacher->get($cacheKey);
                //     // Only expire those whose already tried for 3rd times to get payment status
                    
                //     if (3 >= $current) {
                //         continue;
                //     }
                // }
                

                $app->getDBHandle()->beginTransaction();

                $tx->status = MyGoldTransaction::STATUS_FAILED;
                $order->status = Order::STATUS_EXPIRED;

                if (Order::TYPE_COMPANYBUY ==  $order->type) {
                    $disbursement = $app->mydisbursementStore()->getByField('transactionrefno', $tx->refno);
                    $disbursement->status = MyDisbursement::STATUS_CANCELLED;
                    $disbursement = $app->mydisbursementStore()->save($disbursement);
                }else{
                    $paymentDetail = $app->mypaymentdetailStore()->getByField('sourcerefno', $tx->refno);
                    $paymentDetail->status = MyPaymentDetail::STATUS_FAILED;
                    $paymentDetail = $app->mypaymentdetailStore()->save($paymentDetail);
                }

                $myledger = $app->myledgerStore()->searchTable()->select()
                    ->where('refno', $tx->refno)
                    ->execute();

                foreach($myledger as $statementRecord){
                    $statementRecord->status = MyLedger::STATUS_INACTIVE;
                    $app->myledgerStore()->save($statementRecord);
                }

                $tx = $goldTxStore->save($tx);
                $order = $app->orderStore()->save($order);

                

                // As Ryan requested to Expire Order > 5 minutes even success payment
                // if (MyPaymentDetail::STATUS_PENDING_PAYMENT == $payment->status) {
                    // if($app->getConfig()->{'otc.job.diffserver'} == '1'){
                    
                    // }
                    // else{
                    //     $payment->status = MyPaymentDetail::STATUS_CANCELLED;
                    //     $payment = $app->mypaymentdetailStore()->save($payment);
                    // }
                // } else {
                //     $this->log(__CLASS__.": Transaction {$tx->refno} is pending but payment status is not pending!", SNAP_LOG_ERROR);
                //     throw new \Snap\InputException(__CLASS__.": Unable to expire gold transaction {$tx->refno}", InputException::GENERAL_ERROR);
                // }
                $app->getDBHandle()->commit();

                /*SEND TO GTP DB order. Check if need to transfer order to another db*/ 
                // $transferDb = $app->getConfig()->{'mygtp.db.ordertransfer'}; // transfer if 1
                // if($transferDb) $sendTrans = $app->mygtpTransactionManager()->sendTransactionBetweenDb($tx,$order,'failed');
                // else $this->log("[Transfer DB Process] Transaction with refno ".$tx->refno." no need to trasnfer to other db.", SNAP_LOG_DEBUG);
                /**/


            } catch (\Throwable $e) {
                $this->log($e->getMessage());
                if ($app->getDBHandle()->inTransaction()) {
                    $app->getDbHandle()->rollback();
                }
                $message = $e->getMessage();
                // $this->addErrorLog($message);
            }

        }

        $this->log("Finished checking for pending transactions before $cutOffTimeFormatted", SNAP_LOG_INFO);
    }

    public function describeOptions()
    {
        return [
            'partnerids' => array('required' => true,  'type' => 'string', 'desc' => 'Comma separated list of partner ids'),
        ];
    }
}