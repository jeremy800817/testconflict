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
class ProcessBursaSapTransactionBatchJob extends basejob {
    
    // php source/snaplib/cli.php -f source/snaplib/job/ProcessSapTransactionBatchJob.php -c source/snapapp/config.ini -p ""
    // php source/snaplib/cli.php -f source/snaplib/job/ProcessSapTransactionBatchJob.php -c source/snapapp/config.ini -p "single=true&orderno="
    public function doJob($app, $params = array()) {

        if ($params['single']){
            $params['orderno'];
            $order = $app->orderStore()->getByField('orderno', $params['orderno']);
            if ($order){
                $app->spotOrderManager()->confirmBookOrder($order);
            }else{
                $this->log("Invalid Order No", SNAP_LOG_ERROR);
                echo "No order found.";
            }
            exit;
        }

        $mibPartnerId = $app->getConfig()->{'gtp.bursa.partner.id'};
        // ensure config has value;
        if (empty($mibPartnerId)){
            $this->log("Unable to expire, could not get mib/etc partnerId", SNAP_LOG_ERROR);
            exit;
        }

        // future include partners insert here [$mibPartnerId, ..]
        $partnerIds = [$mibPartnerId];
        // $partnerIds = [$mibPartnerId, $posBuybackWest, $posBuybackEast, $koponasBuybackWest, $koponasBuybackEast, $sahabatBuybackWest, $sahabatBuybackEast, $tekunBuybackWest, $tekunBuybackEast];

        // params start and end must be UTC string

        //$app->buybackManager()->processNewBuybacks($partnerIds, $params['start'], $params['end']);

        //$app->redemptionManager()->processReservedRedemptions($mibPartnerId, $params['start'], $params['end']);

        $app->spotOrderManager()->processNewOrders($partnerIds, $params['start'], $params['end']);

    }
    
    function describeOptions() {

    }
    
}