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
use PhpRbac\Rbac;

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
class ProcessFtpEmailSendOutJob  extends basejob {
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        if(!defined('SNAPAPP_DBACTION_USERID')) define('SNAPAPP_DBACTION_USERID', 2);
        $arguments = $params;

        $apiManager = $app->apiManager();
        if(isset($arguments['date'])) $currentdate = strtotime($arguments['date']."+8 hours");
        else $currentdate = strtotime("now +8 hours");
        $dateFile           = date('dmY',$currentdate);
        if(isset($arguments['filename'])) $filename       = $arguments['filename'];
        $upcaseFilename = strtoupper($filename);
        if(isset($arguments['server'])) $server = strtoupper($arguments['server']);
        else $server = null;

        $subject        = "FTP(DAILY) MBB - ".$upcaseFilename." ".$dateFile;
        $bodyEmail      = "Please find the attached file ".$upcaseFilename." for your reference.";
        $emailConfig    = "sendto".$filename;
        $reportpath     = $app->getConfig()->{'gtp.ftp.report'};
        $pathToSave     = $reportpath.$upcaseFilename."_".$dateFile.".xlsx";
        $filename       = $upcaseFilename."_".$dateFile.".xlsx";

        $sendEmail = $apiManager->sendNotifyEmail($bodyEmail,$subject,$emailConfig,$pathToSave,$filename);
    }

    /**
     * This method is used to display options parameter for this job.
     * @return Array of associative array of parameters.
     *         E.g.[
     *            'param1' => array('required' => true, 'type' => 'int', 'desc' => 'Some description'),
     *            'param2' => array('required' => false, 'default' => 1, type' => 'string', 'desc' => 'Some description 22222'),
     *         ]
     *         -Where [required] indicates if the params is required for the job to run.  The cli will ensure this parameter is provided
     *                [type] is the expected data type of the parameter or its valid values.
     *                [default] is the default value for the field.
     *                [desc] is the description of the parameter and what it does.
     */
    function describeOptions() {
        /*return [
            'startall' => array('required' =>  false,  'type' => 'int', 'desc' => 'Goes through all the price providers and launch each one individually'),
            'keepalivecheck' => array('required' =>  false,  'type' => 'string', 'desc' => 'Add super-privilege (/root) to specified username'),
            'startprovider' => array('required' =>  false,  'type' => 'int', 'desc' => 'Start price collection for the specified price provider id in arguement'),
            'stopprovider' => array('required' =>  false,  'type' => 'int', 'desc' => 'Stops the price collection for price provider id given as argument'),
            'isrunning' => array('required' =>  false,  'type' => 'int', 'desc' => 'Checks if the price provider ID given is currently running')
        ];*/
    }
}
?>