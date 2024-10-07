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
class ProcessFtpJob extends basejob {

	/**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        //C:\laragon\bin\php\php-7.2.11-Win32-VC15-x64\php ../cli.php -f TestParseFileJob.php -c ../../snapapp/config.ini -p "debug=1&filename=PHYRTN.TXT&formatcode=phyrtn"

        if(!defined('SNAPAPP_DBACTION_USERID')) define('SNAPAPP_DBACTION_USERID', 2);
        $arguments = $params;

        if(isset($arguments['partner'])) $partnerCode = $arguments['partner'];
        else $partnerCode = 'MBISMY@SIT';
        $partner = $app->partnerStore()->getByField('code', $partnerCode);

        //$currentdate = date("Y-m-d h:i:s");
        //$currentdate = strtotime("2020-09-26 04:00:00"); //for testing purpose. PHYREP file. Every 5th and 20th
        if(isset($arguments['date'])) $currentdate = strtotime($arguments['date']."+8 hours");
        else $currentdate = strtotime("now +8 hours");

        if(isset($arguments['version'])) $version = $arguments['version'];
        else $version = '1.0m';

        if(isset($arguments['server'])) $server = $arguments['server'];
        else $server = null;

        $filename = $arguments['filename'];
        //$mbbFormatCode = $arguments['formatcode'];
        if(isset($arguments['type']) && $arguments['type'] == 'input'){
            $apiManager = $app->apiManager();
            $apiManager->ftpMbb($currentdate,$filename,$partner,$version,$server);
        }
        if(isset($arguments['type']) && $arguments['type'] == 'output'){
            $getManager = $app->ftpprocessorManager();
            if(isset($filename) && $filename == 'phyrep'){
                $getManager->getReplenishmentFtp($currentdate,$filename,$partner,$server);
            } else if(isset($filename) && $filename == 'dailygpprice'){
                $getManager->getDailyGPPriceFtp($currentdate,$filename,$partner,$server);
            } else if(isset($filename) && $filename == 'phycourier'){
                $getManager->getCourierFtp($currentdate,$filename,$partner,$server);
            } else if(isset($filename) && $filename == 'spcdelv'){
                $getManager->getSpDeliveryAppointmentFtp($currentdate,$filename,$partner,$server);
            }
        }
    }

    function describeOptions() {
    }

}

?>