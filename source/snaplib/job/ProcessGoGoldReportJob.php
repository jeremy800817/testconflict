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
class ProcessGoGoldReportJob extends basejob {

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

        if(isset($arguments['peak'])) $peak = $arguments['peak'];
        else $peak = null;

        $reportpath          = $app->getConfig()->{'mygtp.gopayzpayout.ftp.reportpath'};
        $getReportingManager = $app->reportingManager();

        if(isset($arguments['partner'])) $partnerCode = $arguments['partner'];
        $partner = $app->partnerStore()->getByField('code', $partnerCode);

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

        if($arguments['category'] == 'commission'){
            if(isset($arguments['emaillist'])) $emailList = $arguments['emaillist'];
            $reportacepath          = $app->getConfig()->{'mygtp.acereport.ftp'};
            if(!$peak) $modulename = '_NON_PEAK_HOUR';
            $reportname = 'ACE'.$server.$partner->name.'_BUY_SELL_COMMISSION'.$modulename.'('.$startDateExcel.'-'.$endDateExcel.')';
            $getReportingManager->generateCommissionReportByAuto($partner, $currentdate,$startDate, $endDate, $reportacepath, $emailout, $emailList, $reportname, $peak);
        } elseif($arguments['category'] == 'paymentrequest') {
            //$emailList  = $app->getConfig()->{'mygtp.gopayz.reportace'};  
            if(isset($arguments['emaillist'])) $emailList = $arguments['emaillist'];  
            else $emailList = null;         
            $reportname = 'PAYMENT_REQUEST';
            $getReportingManager->generateCompanyBuyReportByAuto($partner, $currentdate,$startDate, $endDate, $emailList, $reportname);
        } else {
            if($partner->sapcompanysellcode1 != '') $pCode = $partner->sapcompanysellcode1;
            if(!isset($arguments['date'])){
                $now                = new \DateTime('now', $app->getUserTimezone());
                $nowDate            = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone()); 
                $genDate            = $nowDate->format('Y-m-d H:i:s'); //GMT
                $currDate           = date('Y-m-d', strtotime('-1 days', strtotime($genDate)));  //GMT
                $startDate          = $currDate." 00:00:00";
                $endDate            = $currDate." 23:59:59";
                $currDateStrtotime  = strtotime($genDate);
                $reportdate         = date('Ymd',$currDateStrtotime);
            } else {
                $genDate            = $params['date']; //GMT
                $currDate           = date('Y-m-d', strtotime('-1 days', strtotime($genDate)));  //GMT
                $startDate          = $currDate." 00:00:00";
                $endDate            = $currDate." 23:59:59";
                $currDateStrtotime  = strtotime($genDate);
                $reportdate         = date('Ymd',$currDateStrtotime);
            }

            if(isset($arguments['store'])) $currentStore = $arguments['store']; //store name. example mygoldtransaction/myconversion
            if(isset($arguments['modulename'])) $modulename = $arguments['modulename']; // name added in excel 
            //$emailToAce          = $app->getConfig()->{'mygtp.gopayz.reportace'};
            //$emailToPartner      = $app->getConfig()->{'mygtp.gopayz.reportpartner'};
            //each partner will have different email and certain report need to send o certain. this is custom according to partner config
            //if($modulename == 'register'){
            //    $emailList = $emailToPartner;
            //} else {
            //    $emailList = $emailToAce.",".$emailToPartner;
            //}

            if(isset($arguments['emaillist'])) $emailList = $arguments['emaillist']; 
            //$getReportingManager->generateTransactionReportByAuto($partner,$currentStore, $currentdate, $modulename, $reportpath, $emailout, $emailList);

            /*add this parameter if need to customize partner title for report name. Currently use for some KTP partner because one main partner can have many sub partners*/
            if(isset($arguments['customizepartner'])) $reportname = $arguments['customizepartner']."_".$reportdate."_".strtoupper($modulename);
            else $reportname = $pCode."_".$reportdate."_".strtoupper($modulename);

            $getReportingManager->generatePartnersTransactionReportByAuto($partner,$currentStore, $startDate, $endDate, $modulename, $reportpath, $reportname, $emailout, $emailList);
        }
    }

    function describeOptions() {
    }

}

?>