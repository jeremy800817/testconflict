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
class ProcessSapTransactionCoreBatchJob extends basejob {
    
    // php source/snaplib/cli.php -f source/snaplib/job/ProcessSapTransactionBatchJob.php -c source/snapapp/config.ini -p ""
    // php source/snaplib/cli.php -f source/snaplib/job/ProcessSapTransactionBatchJob.php -c source/snapapp/config.ini -p "single=true&orderno="
    public function doJob($app, $params = array()) {

        if ($params['single']){
            $params['orderno'];
            $order = $app->orderStore()->getByField('orderno', $params['orderno']);
            if ($order){
                $app->spotOrderManager()->postOrderToSAP($order);
            }else{
                $this->log("Invalid Order No", SNAP_LOG_ERROR);
                echo "No order found.";
            }
            exit;
        }

        $mibPartnerId = $app->getConfig()->{'gtp.mib.partner.id'};
        $posBuybackWest = $app->getConfig()->{'gtp.pos3.partner.id'};
        $posBuybackEast = $app->getConfig()->{'gtp.pos4.partner.id'};
        $gogoldPartnerId = $app->getConfig()->{'gtp.go.partner.id'};

        // future include partners insert here [$mibPartnerId, ..]
        $excludePartnerIds_old = [$mibPartnerId, $posBuybackWest, $posBuybackEast, $gogoldPartnerId];


        $allNonCorePartners = $app->partnerStore()->searchTable()->select()->where('corepartner', 0)->execute();
        $excludePartnerIds = [];
        foreach ($allNonCorePartners as $unset_key => $nonCorePartner){
            array_push($excludePartnerIds, $nonCorePartner->id);
            // check caching issue
            if (in_array($nonCorePartner->id, $excludePartnerIds_old)){
                unset($excludePartnerIds[$unset_key]);
            }
        }

        // params start and end must be UTC string
        $app->spotOrderManager()->processNewOrdersGtpCoreUser($excludePartnerIds, $params['start'], $params['end']);

    }
    
    function describeOptions() {

    }
    
}