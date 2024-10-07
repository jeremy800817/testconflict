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
class FutureOrderPriceMatchCheckJob extends basejob {
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        echo "============================================\n";
        echo "======\n";
        echo "====== New Price Stream Data Checking Job\n";
        echo "======\n";
        echo "============================================\n";
        $aliveTime = isset($params['alivetime']) ? $params['alivetime'] : 60;
        if('loop' == strtolower($params['mode'])) {
            $app->futureOrderManager()->processReceivedPriceStreamData($aliveTime);
        } else {
            $this->parent->printInfo();
        }
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
        return [
            'mode' => array('required' =>  true,  'type' => 'string', 'desc' => 'Must be loop'),
            'priceproviderid' => array('required' =>  false,  'type' => 'int', 'desc' => 'The price provider id to trigger if in SINGLE mode'),
        ];
    }
}
?>