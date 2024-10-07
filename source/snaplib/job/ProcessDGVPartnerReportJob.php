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
class ProcessDGVPartnerReportJob extends basejob {

    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        //C:\laragon\bin\php\php-7.4.2-Win32-VC15-x64\php ../cli.php -f ProcessDGVPartnerReportJob.php -c ../../snapapp/config.ini -p "partner=GOPAYZ@UAT&server=localhost&filename=dailydvg&emaillist=diyaz88@gmail.com&email=1&date=2021-07-23 17:00:00"
        //partner :: partnercode
        //server :: either local / sit / live
        //filename :: category of report
        //emaillist :: list of recipient email
        //email :: if 1, send email. if 0 or dont have, do not run email
        //sharedgv :: write 1 if partner is sharedgv, write 0 if partner not sharing dgv
        //date :: only need/compulsory to add if run manually

        if(!defined('SNAPAPP_DBACTION_USERID')) define('SNAPAPP_DBACTION_USERID', 2);
        $arguments = $params;

        if(isset($arguments['partnerids'])) $partnerids = $arguments['partnerids'];

        if(isset($arguments['sharedgv'])) $sharedgv = $arguments['sharedgv'];

        if(isset($arguments['date'])) $currentdate = strtotime($arguments['date']."+8 hours");
        else $currentdate = strtotime("now +8 hours");

        if(isset($arguments['email'])) $emailout = $arguments['email'];
        else $emailout = 0;
        if(isset($arguments['emaillist'])) $emaillist = $arguments['emaillist'];

        $reportpath     = $app->getConfig()->{'mygtp.acereport.general'};
        $filename       = $arguments['filename'];
        $getManager     = $app->reportingmanager();
        //$mbbFormatCode = $arguments['formatcode'];
        if($filename == 'dailydvg'){
            $getManager->getCumulativeTrans($currentdate,$filename,$partnerids,$reportpath,$emaillist,$emailout,$sharedgv);
        }
    }

    function describeOptions() {
    }

}

?>