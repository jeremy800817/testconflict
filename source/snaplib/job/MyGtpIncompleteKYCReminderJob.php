<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;
use Snap\manager\MyGtpAccountManager;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class MyGtpIncompleteKYCReminderJob extends basejob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array())
    {

        $partnerid = $params['partnerid'];

        $partner = $app->partnerStore()->getById($partnerid);

        /** @var MyGtpAccountManager */
        $accMgr = $app->mygtpaccountManager();
        $accHolders = $accMgr->getKycReminderList($partnerid);

        foreach ($accHolders as $accHolder) {
            $accMgr->processKycReminder($accHolder, $partner);
        }
    }

    function describeOptions()
    {
        return [
            'partnerid' => array('required' => true, 'type' => 'int', 'desc' => 'Partner ID of account holders to run the incomplete EKYC reminder'),
        ];
    }
}
