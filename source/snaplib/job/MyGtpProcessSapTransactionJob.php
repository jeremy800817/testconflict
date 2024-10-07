<?php

namespace Snap\job;

use Snap\manager\MyGtpTransactionManager;
use Snap\object\MyGoldTransaction;
use Snap\object\MyPaymentDetail;
use Snap\object\Order;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 15-Jan-2021
*/

class MyGtpProcessSapTransactionJob extends basejob
{
    public function doJob($app, $params = array())
    {
        $partnerIds = explode(',', $params['partnerids']);
        $cutOffTime = new \DateTime('');
        $cutOffTimeFormatted = $cutOffTime->format('Y-m-d H:i:s');
        $this->log("Submitting confirmed transactions to SAP on ". $cutOffTimeFormatted, SNAP_LOG_INFO);

        $start = new \DateTime();
        $start->setTimezone($app->getUserTimezone());

        /** @var MyGtpTransactionManager $goldTxMgr */
        $goldTxMgr = $app->mygtptransactionManager();
        $unconfirmedTx = $app->mygoldtransactionStore()->searchView()->select()
                                        ->where('ordpartnerid', 'in', $partnerIds)
                                        ->andWhere(function ($q) {
                                            $q->where(function ($r) {
                                                $r->where('ordtype', Order::TYPE_COMPANYBUY);
                                                $r->andWhere('status', MyGoldTransaction::STATUS_PENDING_PAYMENT);
                                                $r->andWhere('ordstatus', '=', Order::STATUS_PENDING);
                                            });

                                            $q->orWhere(function ($r) {
                                                $r->where('status', MyGoldTransaction::STATUS_PAID);
                                                $r->andWhere('ordstatus', '=', Order::STATUS_PENDING);
                                            });
                                        })
                                        ->execute();

        $this->log(__CLASS__. " [{$start->format('Y-m-d H:i:s')}]:" . count($unconfirmedTx)." unconfirmed transactions. ", SNAP_LOG_INFO);
        $cacher = $app->getCacher();
        foreach ($unconfirmedTx as $tx) {
            try {
                $lockKey = '{pendingOrderProcessor}:' . $tx->orderid;
                if ($cacher->waitForLock($lockKey, 1, 30, 0)) {
                    $goldTxMgr->confirmGoldTransaction($tx);
                }
            } catch ( \Exception $e) {
                $this->log("Error while confirming gold transaction ($tx->refno)", SNAP_LOG_ERROR);
            } finally {
                $cacher->unlock($lockKey);
            }
        }

        $end = new \DateTime();
        $end->setTimezone($app->getUserTimezone());
        $this->log(__CLASS__. " [{$end->format('Y-m-d H:i:s')}]: Successfully submitted ". count($unconfirmedTx)." transactions to SAP ", SNAP_LOG_INFO);
    }

    public function describeOptions()
    {
        return [
            'partnerids' => array('required' => true,  'type' => 'string', 'desc' => 'Comma separated list of partner ids'),
        ];
    }
}