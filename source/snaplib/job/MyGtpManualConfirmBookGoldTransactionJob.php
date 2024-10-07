<?php

namespace Snap\job;

use Snap\manager\MyGtpTransactionManager;
use Snap\object\MyLedger;
use Snap\object\Order;

class MyGtpManualConfirmBookGoldTransactionJob extends basejob
{
    public function doJob($app, $params = array())
    {
        $orderNo = $params['orderno'];
        $this->log("Processing to confirm transaction with order no ({$orderNo}) without submitting to SAP ", SNAP_LOG_INFO);
        $skipSAPSubmission = true;

        /** @var MyGtpTransactionManager $goldTxMgr */
        $goldTxMgr = $app->mygtptransactionManager();
        $unconfirmedTx = $app->mygoldtransactionStore()->searchView()->select()->where('ordorderno', $orderNo)->one();

        if (! $unconfirmedTx) {
            throw new \Exception("Order not found");
        }

        switch ($unconfirmedTx->ordtype) {
            case Order::TYPE_COMPANYSELL:
                $ledgerType = MyLedger::TYPE_BUY_FPX;
                break;
            case Order::TYPE_COMPANYSELL:
                $ledgerType = MyLedger::TYPE_SELL;
                break;
            default:
                throw new \Exception("Order type not support");
                break;
        }        

        $exists = $app->myledgerStore()->searchTable()->select()->where('type', $ledgerType)->where('status', MyLedger::STATUS_ACTIVE)->where('refno', $unconfirmedTx->refno)->exists();

        if ($exists) {
            throw new \Exception("Transaction ({$unconfirmedTx->refno}) already exists in ledger");
        }

        $cacher = $app->getCacher();        
        try {
            $lockKey = '{pendingOrderProcessor}:' . $unconfirmedTx->orderid;
            if ($cacher->waitForLock($lockKey, 1, 30, 0)) {
                $goldTxMgr->confirmBookGoldTransaction($unconfirmedTx, $ledgerType, null, true);
            }

            $this->log(__CLASS__. ": Successfully confirmed ({$unconfirmedTx->refno}) transaction ({$orderNo})", SNAP_LOG_INFO);
        } catch ( \Exception $e) {
            echo $e->getMessage();
            $this->log("Error while confirming gold transaction ($unconfirmedTx->refno) ({$orderNo})", SNAP_LOG_ERROR);
        } finally {
            $cacher->unlock($lockKey);
        }        
    }

    public function describeOptions()
    {
        return [
            'orderno' => array('required' => true,  'type' => 'string', 'desc' => 'Spot order no to confirm without submitting to SAP'),
        ];
    }
}