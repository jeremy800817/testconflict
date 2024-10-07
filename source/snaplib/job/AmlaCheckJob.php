<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;
use Snap\object\MyAccountHolder;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author Cheok <cheok@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class AmlaCheckJob extends basejob
{

    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array())
    {

        /** @var \Snap\manager\MyGtpScreeningManager */
        $screeningManager = $app->mygtpscreeningManager();
        $accHolder = $app->myaccountholderStore()->getById($params['accountholderid']);
        $immediatelyblacklist = $params['immediatelyblacklist'] ?? false;

        $screeningManager->screenAccountHolder($accHolder, $immediatelyblacklist);
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
    function describeOptions()
    {
        return [
            'accountholderid' => array('required' =>  false,  'type' => 'int', 'desc' => 'The specific account holder to run amla check for. Default: 0'),
            'skiprecent' => array('required' =>  false,  'type' => 'int', 'desc' => 'Skip recently verified account holders (<1 month). Default: 0'),
            // 'futureorderid' => array('required' =>  false,  'type' => 'int', 'desc' => 'Future order ID to match in SINGLE mode'),
            // 'pricestreamid' => array('required' =>  false,  'type' => 'int', 'desc' => 'The price stream that matched in SINGLE mode')
        ];
    }
}
