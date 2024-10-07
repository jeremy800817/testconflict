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
class ProcessGTPCoreMonthlyReportJob extends basejob {

    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        if(!defined('SNAPAPP_DBACTION_USERID')) define('SNAPAPP_DBACTION_USERID', 2);

        if(isset($params['email'])) $emailout = $params['email'];
        if(isset($params['store'])) $currentStore = $params['store']; //store name. example mygoldtransaction/myconversion
        if(isset($params['cat'])) $category = $params['cat'];
        if(isset($params['modulename'])) $modulename = $params['modulename']; // name added in excel 
        if(isset($params['emaillist'])) $emaillist = $params['emaillist']; // name added in excel 

        $reportpath             = $app->getConfig()->{'mygtp.acereport.dailytransaction'}; //change based on partner
        $getReportingManager    = $app->reportingManager();

        $now                    = new \DateTime('now', $app->getUserTimezone());
        $nowDate                = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone()); 
        $genDate                = $nowDate->format('Y-m-d H:i:s'); //GMT
        
        /* add parameter 'date' when want to manually get transaction for which month
        * example : 'date=2022-03-01 00:00:00'. This example will get previous month of transaction which is February 2022
        */
        if(isset($params['date'])) $currentdate = strtotime($params['date']); 
        else $currentdate = strtotime($genDate);

        $startDate      = date("Ym01 00:00:00", strtotime("first day of previous month",$currentdate)); //start date for previous month of current month
        $endDate        = date("Ymj 23:59:59", strtotime("last day of previous month",$currentdate)); //start date for previous month of current month

        $startDateReport    = date("Ym01", strtotime("first day of previous month",$currentdate)); //start date for previous month of current month
        $endDateReport      = date("Ymj", strtotime("last day of previous month",$currentdate)); //start date for previous month of current month

        $reportname = strtoupper($modulename)."(".$startDateReport."-".$endDateReport.")";

        $getReportingManager->generateCoreReportByAuto($currentStore, $category, $startDate, $endDate, $modulename, $reportpath, $reportname, $emailout, $emaillist);
    }

    function describeOptions() {
    }

}

?>