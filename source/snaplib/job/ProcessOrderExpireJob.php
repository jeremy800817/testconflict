<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\job;

USe Snap\App;
Use Snap\ICliJob;
// Use Snap\TLogging;
Use Snap\object\Order;
/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @version 1.0
 * @package  snap.job
 */
class ProcessOrderExpireJob extends basejob {
    
    // php source/snaplib/cli.php -f source/snaplib/job/ProcessOrderExpireJob.php -c source/snapapp/config.ini -p ""
    public function doJob($app, $params = array()) {

        // $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
        $now = new \DateTime();
        $now = \Snap\common::convertUTCToUserDatetime($now);
        //$now = $now->modify('-1 days');
        $endAt = new \DateTime($now->format('Y-m-d 23:59:59'));
        $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

        $mibPartnerId = $app->getConfig()->{'gtp.mib.partner.id'};
        // ensure config has value;
        if (empty($mibPartnerId)){
            $this->log("Unable to expire, could not get mib/etc partnerId", SNAP_LOG_ERROR);
            exit;
        }
        // future exclude partners insert here [$mibPartnerId, ..]
        $excludePartners = [$mibPartnerId];

        $orders = $app->OrderStore()->searchTable()->select()
            ->where('createdon', '<=', $endAt->format('Y-m-d 00:00:00'))
            ->andWhere('partnerid', 'not in', $excludePartners)
            ->andWhere('status', 'in', [Order::STATUS_PENDING, Order::STATUS_ACTIVE, Order::STATUS_INACTIVE])
            ->execute();
        
        foreach ($orders as $order){
            $order->status = Order::STATUS_EXPIRED; // STATUS_INACTIVE ?;
            $save = $app->OrderStore()->save($order);
            if ($save){
                $this->log("Successful changed to status expired, order ID:".$save->id, SNAP_LOG_DEBUG);
            }else{
                // do not throw error, if error => email(error_Trans->id) to ace/dev, do not use sql_trans and rollback()
                $this->log("Unable to expire, order ID:".$order->id, SNAP_LOG_DEBUG);
            }
        }
    }
    
    function describeOptions() {

    }
    
}