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
class ProcessSapTransactionBatchJob extends basejob {
    
    // php source/snaplib/cli.php -f source/snaplib/job/ProcessSapTransactionBatchJob.php -c source/snapapp/config.ini -p ""
    // php source/snaplib/cli.php -f source/snaplib/job/ProcessSapTransactionBatchJob.php -c source/snapapp/config.ini -p "single=true&orderno="
    public function doJob($app, $params = array()) {

        $app->log(get_class($this) . '->' . __function__ .', start job : ' . date('Y-m-d H:i:s', time()), SNAP_LOG_DEBUG);
        
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

        $mibPartnerId = $app->getConfig()->{'gtp.mib.partner.id'};
        // ensure config has value;
        if (empty($mibPartnerId)){
            $this->log("Unable to expire, could not get mib/etc partnerId", SNAP_LOG_ERROR);
            exit;
        }

        $posBuybackWest     = $app->getConfig()->{'gtp.pos3.partner.id'};
        $posBuybackEast     = $app->getConfig()->{'gtp.pos4.partner.id'};
        $koponasBuybackWest = $app->getConfig()->{'gtp.koponaswest.partner.id'};
        $koponasBuybackEast = $app->getConfig()->{'gtp.koponaseast.partner.id'};
        $sahabatBuybackWest = $app->getConfig()->{'gtp.sahabatwest.partner.id'};
        $sahabatBuybackEast = $app->getConfig()->{'gtp.sahabateast.partner.id'};
        $tekunBuybackWest   = $app->getConfig()->{'gtp.tekunwest.partner.id'};
        $tekunBuybackEast   = $app->getConfig()->{'gtp.tekuneast.partner.id'};

        // future include partners insert here [$mibPartnerId, ..]
        // $partnerIds = [$mibPartnerId, $posBuybackWest, $posBuybackEast, $koponasBuybackWest, $koponasBuybackEast, $sahabatBuybackWest, $sahabatBuybackEast, $tekunBuybackWest, $tekunBuybackEast];
        // 13/06/2023: remove the koponas,sahabat and tekun id first. waiting for feedback as 
        // the reference id they send is more than 11 characters, sap couldnt process
        
        // 12/07/2023: added back koponas, sahabat, tekun id as they have change the reference id
        // 12/07/2023: update -> they ask to close back
        // 13/07/2023: reopen
        $partnerIds = [$mibPartnerId, $posBuybackWest, $posBuybackEast, $sahabatBuybackWest, $sahabatBuybackEast, $tekunBuybackWest, $tekunBuybackEast, $koponasBuybackWest, $koponasBuybackEast];
        // $partnerIds = [$mibPartnerId, $posBuybackWest, $posBuybackEast];

        // params start and end must be UTC string
        $app->log(get_class($this) . '->' . __function__ .', start processNewBuybacks : ' . date('Y-m-d H:i:s', time()), SNAP_LOG_DEBUG);
        $app->buybackManager()->processNewBuybacks($partnerIds, $params['start'], $params['end']);
        $app->log(get_class($this) . '->' . __function__ .', end processNewBuybacks : ' . date('Y-m-d H:i:s', time()), SNAP_LOG_DEBUG);

        //$app->log(get_class($this) . '->' . __function__ .', start processReservedRedemptions : ' . date('Y-m-d H:i:s', time()), SNAP_LOG_DEBUG);
        //$app->redemptionManager()->processReservedRedemptions($mibPartnerId, $params['start'], $params['end']);
        //$app->log(get_class($this) . '->' . __function__ .', end processReservedRedemptions : ' . date('Y-m-d H:i:s', time()), SNAP_LOG_DEBUG);
        
        $app->log(get_class($this) . '->' . __function__ .', start processNewOrders : ' . date('Y-m-d H:i:s', time()), SNAP_LOG_DEBUG);
        $app->spotOrderManager()->processNewOrders($partnerIds, $params['start'], $params['end']);
        $app->log(get_class($this) . '->' . __function__ .', end processNewOrders : ' . date('Y-m-d H:i:s', time()), SNAP_LOG_DEBUG);
        
        $app->log(get_class($this) . '->' . __function__ .', end job : ' . date('Y-m-d H:i:s', time()), SNAP_LOG_DEBUG);

    }
    
    function describeOptions() {

    }
    
}