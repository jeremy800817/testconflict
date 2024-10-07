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
use Snap\object\Partner;

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
class MyGtpDormantAccountCloseJob extends basejob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array())
    {
        $partnercode = $params['partnercode'];        
        $version = $params['apiversion'] ?? '1.0my';

        /** @var Partner $partner */
        $partner = $app->partnerStore()->getByField('code', $partnercode);
        /** @var MyGtpAccountManager $accMgr */
        $accMgr = $app->mygtpaccountManager();
        

        $this->log(__METHOD__ . "(): ---------- Processing dormant account holders ---------", SNAP_LOG_DEBUG);
        $accMgr->checkDormantAccount($partner, $version);
        $this->log(__METHOD__ . "(): ---------- Finished processing dormant account holders ---------", SNAP_LOG_DEBUG);
    }

    function describeOptions()
    {
        return [
            'partnercode' => array('required' =>  true,  'type' => 'string', 'desc' => 'Partner of dormant account holders to process'),
            'apiversion' => array('required' =>  false,  'type' => 'string', 'desc' => 'Version of the api to use. Default to (1.0my)'),
        ];
    }
}
