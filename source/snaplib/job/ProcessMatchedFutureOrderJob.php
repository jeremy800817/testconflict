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
class ProcessMatchedFutureOrderJob extends basejob {
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        echo "============================================\n";
        echo "======\n";
        echo "====== Matched Future Order Processing Job\n";
        echo "======\n";
        echo "============================================\n";
        $aliveTime = isset($params['alivetime']) ? $params['alivetime'] : 60;
        if('loop' == strtolower($params['mode'])) {
            $app->futureOrderManager()->processPriceMatchedFutureOrder($aliveTime);
        } else if('single' == strtolower($params['mode']) && isset($params['futureorderid']) && isset($params['pricestreamid'])) {
            $futureOrder = $app->orderQueueStore()->getById($params['futureorderid']);
            $priceStream = $app->pricestreamStore()->getById($params['pricestreamid']);
            if($futureOrder && 0 < $futureOrder->id && $priceStream && 0 < $priceStream->id) {
                $app->futureOrderManager()->onFutureOrderMatched($futureOrder, $priceStream);
            } else {
                $app->log("Unable to match the future order provided with ID {$params['futureorderid']} and price stream id {$params['pricestreamid']}", SNAP_LOG_ERROR);
            }
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
            'mode' => array('required' =>  true,  'type' => 'string', 'desc' => 'Either in LOOP mode (keep running till alivetime is up.  SINGLE mode will process only 1 matched future order'),
            'alivetime' => array('required' =>  false,  'type' => 'int', 'desc' => 'How long to run the job.  Default is 1 minute.  For unlimited time, set it to -1'),
            'futureorderid' => array('required' =>  false,  'type' => 'int', 'desc' => 'Future order ID to match in SINGLE mode'),
            'pricestreamid' => array('required' =>  false,  'type' => 'int', 'desc' => 'The price stream that matched in SINGLE mode')
        ];
    }
}
?>