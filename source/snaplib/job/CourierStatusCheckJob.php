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

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @version 1.0
 * @package  snap.job
 */
class CourierStatusCheckJob extends basejob {
    // 30min interval
    // php source/snaplib/cli.php -f source/snaplib/job/ProcessSapTransactionBatchJob.php -c source/snapapp/config.ini -p ""
    // php source/snaplib/cli.php -f source/snaplib/job/ProcessSapTransactionBatchJob.php -c source/snapapp/config.ini -p "single=true&orderno="
    public function doJob($app, $params = array()) {
        $app->logisticManager()->courierStatusCrawler();
    }
    
    function describeOptions() {

    }
    
}