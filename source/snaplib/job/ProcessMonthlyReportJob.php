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
class ProcessMonthlyReportJob extends basejob {

    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        if(!defined('SNAPAPP_DBACTION_USERID')) define('SNAPAPP_DBACTION_USERID', 2);
        $arguments = $params;
        
        if(isset($arguments['server'])) $server = "_".strtoupper($arguments['server'])."_";
        else $server = "_";
        if(isset($arguments['partnerids'])) $partnerids = $arguments['partnerids'];
        $chunkPartnerIds = explode (",", $partnerids);
        if(isset($arguments['email'])) $emailout = $arguments['email'];
        else $emailout = 0;
        if(isset($arguments['peak'])) $peak = $arguments['peak'];
        else $peak = null;
        if(isset($arguments['category'])) $category = $arguments['category']; 
        if(isset($arguments['emaillist'])) $emaillist = $arguments['emaillist'];

        $reportpath             = $app->getConfig()->{'mygtp.acereport.dailytransaction'}; //change based on partner
        $projectname            = $app->getConfig()->{'projectBase'}; 
        $getReportingManager    = $app->reportingManager();
        $now                    = new \DateTime('now', $app->getUserTimezone());
        $nowDate                = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone()); 
        $genDate                = $nowDate->format('Y-m-d H:i:s'); //GMT
        

        /* add parameter 'date' when want to manually get transaction for which month
        * example : 'date=2022-03-01 00:00:00'. This example will get previous month of transaction which is February 2022
        */
        if(isset($arguments['date'])) $currentdate = strtotime($arguments['date']); 
        else $currentdate = strtotime($genDate);

        $startDate       = date("Y-m-01 00:00:00", strtotime("first day of previous month",$currentdate));
        $endDate         = date("Y-m-j 23:59:59", strtotime("last day of previous month",$currentdate));
        $startDateExcel  = date("Ym01", strtotime("first day of previous month",$currentdate));
        $endDateExcel    = date("Ymj", strtotime("last day of previous month",$currentdate));

        foreach($chunkPartnerIds as $aPartner){
            $partner        = $app->partnerStore()->getById($aPartner);
            if(isset($params['customizepartner'])) $partnername    = strtoupper($params['customizepartner']);
            else $partnername    = strtoupper($projectname);
            if($category == 'commission'){
                if(!$peak) $modulename = '_NON_PEAK_HOUR';
                //$reportname = 'ACE'.$server.$partnername.'_BUY_SELL_COMMISSION'.$modulename.'('.$startDateExcel.'-'.$endDateExcel.')';
                //$getReportingManager->generateCommissionReportByAuto($partner, $currentdate,$startDate, $endDate, $reportpath, $emailout, $emaillist, $reportname, $peak);
                $reportname = 'ACE'.$server.$partnername.'_BUY_SELL_COMMISSION('.$startDateExcel.'-'.$endDateExcel.')';
                $getReportingManager->generateCommissionReportLatest($partner, $currentdate,$startDate, $endDate, $reportpath, $emailout, $emaillist, $reportname);
            } elseif($category == 'adminstoragefee'){
                $reportname = 'ACE'.$server.$partnername.'_MONTHLY_STORAGE_FEE('.$startDateExcel.'-'.$endDateExcel.')';
                $getReportingManager->generateAdminStorageFeeReportByAuto($partner, $currentdate,$startDate, $endDate, $reportpath, $emailout, $emaillist, $reportname);
            } elseif($category == 'adminstoragefeedaily'){
                $reportname = 'ACE'.$server.$partnername.'_DAILY_STORAGE_FEE('.$startDateExcel.'-'.$endDateExcel.')';
                $getReportingManager->generateAdminStorageFeeDailyReportByAuto($partner, $currentdate,$startDate, $endDate, $reportpath, $emailout, $emaillist, $reportname);
            }
        }
    }

    function describeOptions() {
    }

}

?>