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
class ProcessDailyTransactionsReportJob extends basejob {

    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        //option 1 (default - 24hrs transactions) : "partnerids=3&store=mygoldtransaction&modulename=dailytrn&email=1&emailto=diyaz88@gmail.com"
        //option 1 (default - have range time for transactions. add starttime / endtime parameter) : "partnerids=3&store=mygoldtransaction&modulename=dailytrn&email=1&emailto=diyaz88@gmail.com&endtime=18:00:00"
        //option 2 (manual - date parameter use GMT. without starttime / endtime, time range will be 12am - 11:59:59pm of yesterday transactions) : "partnerids=3&store=mygoldtransaction&modulename=dailytrn&email=1&emailto=diyaz88@gmail.com&date=2022-05-07 18:00:00"
        //option 2 (manual - date parameter use GMT. with starttime / endtime, time range depends on parameter on the same day transaction) : "partnerids=3&store=mygoldtransaction&modulename=dailytrn&email=1&emailto=diyaz88@gmail.com&date=2022-05-07 18:00:00&endtime=18:00:00"
        //modulename list:
        //1.dailytrn,2.register,3.conversion,4.dailylogin
        if(!defined('SNAPAPP_DBACTION_USERID')) define('SNAPAPP_DBACTION_USERID', 2);

        if(isset($params['partnerids'])) $partnerid = $params['partnerids'];
        $partner        = $app->partnerStore()->getById($partnerid);
        if($partner->sapcompanysellcode1 != '') $pCode = $partner->sapcompanysellcode1;

        //Take note: This parameter is use if partner request to generate file for certain time range.
        //Example: easigold request to generate 2 times. 08:30am to 02:00pm & 02:01pm to 11:59pm 
        //If parameter not exist, will use default time which is 00:00:00 till 23:59:59
        if(isset($params['starttime'])) $starttime = $params['starttime'];
        else $starttime = "00:00:00";
        if(isset($params['endtime'])) $endtime = $params['endtime'];
        else $endtime = "23:59:59";

        //Important - if using current date, prepare data previous date. Default cronjob without date parameter. Currently run at 12:15am everyday.
        if(!isset($params['date'])){
            $now                = new \DateTime('now', $app->getUserTimezone());
            $nowDate            = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone()); 
            $genDate            = $nowDate->format('Y-m-d H:i:s'); //GMT
            if(isset($params['endtime'])) {
                $currDate   = date('Y-m-d', strtotime($genDate));  //GMT
                $addExtName = "_".str_replace(":","",$params['endtime']);
            }
            else $currDate = date('Y-m-d', strtotime('-1 days', strtotime($genDate)));  //GMT
            $startDate          = $currDate." ".$starttime;
            $endDate            = $currDate." ".$endtime;
            $currDateStrtotime  = strtotime($genDate);
            $reportdate         = date('Ymd',$currDateStrtotime);
        } else {
            $genDate            = $params['date']; //GMT
            if(isset($params['endtime'])) {
                $currDate = date('Y-m-d', strtotime($genDate));  //GMT
                $addExtName = "_".str_replace(":","",$params['endtime']);
            }
            else $currDate = date('Y-m-d', strtotime('-1 days', strtotime($genDate)));  //GMT
            $startDate          = $currDate." ".$starttime;
            $endDate            = $currDate." ".$endtime;
            $currDateStrtotime  = strtotime($genDate);
            $reportdate         = date('Ymd',$currDateStrtotime);
        }

        if(isset($params['email'])) $emailout = $params['email'];
        if(isset($params['emailto'])) $emailto = $params['emailto'];

        //if(isset($params['firstprocess'])) $firstprocess = $params['firstprocess'];
        if(isset($params['store'])) $currentStore = $params['store']; //store name. example mygoldtransaction/myconversion
        if(isset($params['modulename'])) $modulename = $params['modulename']; // name added in excel 

        $reportpath          = $app->getConfig()->{'mygtp.acereport.dailytransaction'}; //change based on partner
        $getReportingManager = $app->reportingManager();

        /*add this parameter if need to customize partner title for report name. Currently use for some KTP partner because one main partner can have many sub partners*/
        if(isset($params['customizepartner'])) $reportname = $params['customizepartner']."_".strtoupper($modulename)."_".$reportdate.$addExtName;
        else $reportname = $pCode."_".strtoupper($modulename)."_".$reportdate.$addExtName;
        

        $getReportingManager->generatePartnersTransactionReportByAuto($partner,$currentStore, $startDate, $endDate, $modulename, $reportpath, $reportname, $emailout, $emailto);
    }

    function describeOptions() {
    }

}

?>