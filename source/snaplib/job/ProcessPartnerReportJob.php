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
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class ProcessPartnerReportJob extends basejob {

    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        if(!defined('SNAPAPP_DBACTION_USERID')) define('SNAPAPP_DBACTION_USERID', 2);
        $arguments = $params;

        if(isset($arguments['server'])) $server = strtoupper($arguments['server'])."_";
        else $server = null;

        $reportpath          = $app->getConfig()->{'mygtp.acereport.general'};
        $getReportingManager = $app->reportingManager();

        if(isset($arguments['partner'])) $partnerCode = $arguments['partner'];
        $partner = $app->partnerStore()->getByField('code', $partnerCode);

        /*add date when generate manually. date format yyyy-mm-dd hh:ii:ss*/
        if(isset($arguments['date'])) $currentdate = strtotime($arguments['date']."+8 hours");
        else $currentdate = strtotime("now +8 hours");

        /*$startDate       = date('Y-m-01 00:00:00',$currentdate);
        $endDate         = date('Y-m-t 23:59:59',$currentdate);*/
        $startDate       = date("Y-n-j 00:00:00", strtotime("first day of previous month",$currentdate));
        $endDate         = date("Y-n-j 23:59:59", strtotime("last day of previous month",$currentdate));

        /*$startDateExcel  = date('Y-m-01',$currentdate);
        $endDateExcel    = date('Y-m-t',$currentdate);*/
        $startDateExcel  = date("Ym01", strtotime("first day of previous month",$currentdate));
        $endDateExcel    = date("Ymj", strtotime("last day of previous month",$currentdate));

        if(isset($arguments['email'])) $emailout = $arguments['email'];
        else $emailout = 0;

        if(isset($arguments['emailist'])) $emailist = $arguments['emailist'];
        if(isset($arguments['store'])) $currentStore = $arguments['store'];
        if(isset($arguments['modulename'])) $modulename = $arguments['modulename'];

        $getReportingManager->generateTransactionReportByAuto($partner,$currentStore, $currentdate, $modulename, $reportpath, $emailout, $emailist);
    }

    function describeOptions() {
    }

}

?>