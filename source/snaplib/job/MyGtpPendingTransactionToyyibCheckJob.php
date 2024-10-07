<?php

namespace Snap\job;

use Snap\api\payout\BasePayout;
use Snap\api\wallet\BaseWallet;
use Snap\App;
use Snap\InputException;
use Snap\object\MyGoldTransaction;
use Snap\object\MyDisbursement;
use Snap\object\MyPaymentDetail;
use Snap\object\MyToken;
use Snap\object\Order;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Nurdianah <dianah@silverstream.my>
* @version 1.0
* @created 20-May-2022
*/

class MyGtpPendingTransactionToyyibCheckJob extends basejob
{
    public function doJob($app, $params = array())
    {
        //Params partnerids = partner id
        //params checklist = use when manually want to see Pending Transaction
        //params refno = use to only get certain transaction.separate by comma
        $now = new \DateTime();
        $now->setTimezone($app->getUserTimezone());
        $partnerIds = explode(',', $params['partnerids']);
        $checkingtransactionlist = $params['checklist'];

        if(isset($params['refno'])) {
            $refno = $params['refno'];
            $refno = explode(',', $refno);
            $addCondition = true;
        }

        $goldTxStore = $app->mygoldtransactionStore();

        $pendingTransactions = $goldTxStore->searchView()->select()
                                        ->where('ordpartnerid', 'in', $partnerIds)
                                        //->andWhere('refno', 'GT202205191700003')
                                        //->andWhere('ordbuyerid', 744)
                                        ->andWhere('status', MyGoldTransaction::STATUS_PENDING_PAYMENT);
        if($addCondition){
            $pendingTransactions->andWhere('refno' ,'in',$refno);
        }
        $pendingTransactions = $pendingTransactions->execute();

        $partnerSettings = $app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'in', $partnerIds)->forwardKey('partnerid')->get();

        if($checkingtransactionlist){
            foreach ($pendingTransactions as $tx) {
                $transactionrefno[] = $tx->refno;
            }

            $plainData = json_encode($transactionrefno);
            $this->log("---Transaction with Pending status in mygoldtransaction ".$plainData, SNAP_LOG_INFO);
            echo "Below is list of pending mygoldtransaction: \n";
            print_r($plainData);
        } else {
            foreach ($pendingTransactions as $tx) {
                try {
                    $this->log("---Start checking pending transaction refno ".$tx->refno, SNAP_LOG_INFO);
                    $order = $tx->getOrder();
                    // Updated by Cheok on 2021-05-18 to cater for status flow change for sell transactions

                    if (MyGoldTransaction::SETTLEMENT_METHOD_WALLET === $tx->settlementmethod) {
                        $walletName = basename(str_replace('\\', '/', $partnerSettings[$tx->ordpartnerid]->partnerpaymentprovider));
                        if (Order::TYPE_COMPANYBUY ==  $order->type) {
                            $className = "\\Snap\\api\\payout\\{$walletName}Payout";
                            $this->log("---Trigger wallet ".$className." for mygoldtransaction ".$tx->refno, SNAP_LOG_INFO);
                            $walletUse = BasePayout::getInstance($className);

                            $payment = $app->mydisbursementStore()->searchTable()->select()->where('transactionrefno', $tx->refno)->one();
                            $response = $walletUse->getPayoutStatus($payment);
                        } else {
                            $className = "\\Snap\\api\\wallet\\{$walletName}";
                            $this->log("---Trigger wallet ".$className." for mygoldtransaction ".$tx->refno, SNAP_LOG_INFO);
                            $walletUse = BaseWallet::getInstance($className);

                            $payment = $app->mypaymentdetailStore()->searchTable()->select()->where('sourcerefno', $tx->refno)->one();
                            $response = $walletUse->getPaymentStatus($payment);
                        }
                    }

                    //example response
                    //failed
                    //Array
                    //    (
                    //        [status] => failed
                    //        [message] => Parameter transaction_ref_no cannot be empty
                    //        [data] =>
                    //    )

                    //Array success
                    //(
                    //    [status] => success
                    //    [message] => Transaction is valid
                    //    [data] => Array
                    //        (
                    //            [transaction_ref_no] => PAY567469202232595550
                    //            [transaction_amount] => 5.27
                    //            [transaction_date] => 2022-03-25 09:55:49
                    //            [transaction_status] => SUCCESS
                    //            [ace_wallet] => 10022457
                    //            [ace_current_wallet_balance] => 15.81
                    //        )
                    //)
                    $app->getDBHandle()->beginTransaction();
                    if('success' != strtolower($response['status'])){
                        $this->log("---Start proceed change status of transaction refno ".$tx->refno, SNAP_LOG_INFO);
                        $tx->status = MyGoldTransaction::STATUS_FAILED;
                        $tx->failedon = $now;
                        $order->status = Order::STATUS_EXPIRED;
                        $order->cancelon = $now;
                        $order->remarks .= "Status change from Pending to Expired by job checking";
                        $tx = $goldTxStore->save($tx);
                        $order = $app->orderStore()->save($order);

                        if (Order::TYPE_COMPANYBUY ==  $order->type) {
                            $payment->status = MyDisbursement::STATUS_CANCELLED;
                            $payment->cancelledon = $now;
                            $payment->gatewaystatus = strtoupper($response['status']);
                            $payment = $app->mydisbursementStore()->save($payment);
                        } else {
                            $payment->status = MyPaymentDetail::STATUS_CANCELLED;
                            $payment->failedon = $now;
                            $payment->remarks .= "Status change from Pending Payment to Cancelled by job checking";
                            $payment->gatewaystatus = strtoupper($response['status']);
                            $payment = $app->mypaymentdetailStore()->save($payment);
                        } 
                    }

                    $app->getDBHandle()->commit(); 
                } catch (\Throwable $e) {
                    $this->log($e->getMessage());
                    if ($app->getDBHandle()->inTransaction()) {
                        $app->getDbHandle()->rollback();
                    }
                }
            }
        }
        

        $this->log("Finished checking for pending transactions", SNAP_LOG_INFO);
    }

    public function describeOptions()
    {
        return [
            'partnerids' => array('required' => true,  'type' => 'string', 'desc' => 'Comma separated list of partner ids'),
        ];
    }
}