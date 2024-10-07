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

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author Nurdianah Kamarudin <dianah@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class ProcessUnreserveMinted extends basejob {

    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        
        if(!defined('SNAPAPP_DBACTION_USERID')) define('SNAPAPP_DBACTION_USERID', 2);

        if(isset($params['partnerid'])) $partnerid = $params['partnerid'];
        if(isset($params['redemptionno'])) $redemptionno = $params['redemptionno'];

        $chunkRedemptionNo = explode (",", $redemptionno); 
        $getRedemptionManager = $app->redemptionManager();

        foreach($chunkRedemptionNo as $aRedemptionNo){
            $getRedemptionManager->processUnreservedMinted($partnerid,$aRedemptionNo);
        }
    }

    function describeOptions() {
    }

}

?>