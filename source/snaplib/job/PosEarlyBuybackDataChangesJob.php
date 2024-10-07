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
Use Snap\object\OrderQueue;
Use Snap\object\Order;
Use Snap\object\MbbApFund;
/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @version 1.0
 * @package  snap.job
 */
class PosEarlyBuybackDataChangesJob extends basejob {

    // run ONCE ONLY - on 30/03/2021 - 00:06:00;
    // php /usr/local/nginx/html/gtp/source/snaplib/cli.php -f /usr/local/nginx/html/gtp/source/snaplib/job/PosEarlyBuybackDataChangesJob.php -c /usr/local/nginx/html/gtp/source/snapapp/config.ini -p "action=init"
    // php source/snaplib/cli.php -f source/snaplib/job/PosEarlyBuybackDataChangesJob.php -c source/snapapp/config.ini -p "action=init"
    public function doJob($app, $params = array()) {
        
        
        if ($params['action'] == 'init'){
            $this->changeData($app);
        }
        
        
    }

    function describeOptions() {

    }

    private function changeData($app){
        $app->GoodsReceivedNoteManager()->updateBuybackData();
    }


}
