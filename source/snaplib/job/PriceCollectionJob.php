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
class PriceCollectionJob  extends basejob {
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        $continue = false;
        foreach( $this->describeOptions() as $anOption => $desc) {
            if( isset($params[$anOption])) {
                $continue = true;
                break;
            }
        }
        if(!$continue && !isset($params['_start_collector_real_'])) {
            $this->parent->printInfo();
            return;
        }

        echo "============================================\n";
        echo "======\n";
        echo "====== Price Collection Job\n";
        echo "======\n";
        echo "============================================\n";
        if(isset($params['startall'])) {
            $this->startAllJobs($app);
        } elseif(isset($params['startprovider']) && 0 < $params['startprovider']) {
            $priceProvider = $app->priceProviderStore()->getById($params['startprovider']);
            $this->launchAJob($app, $priceProvider);
        } elseif(isset($params['stopprovider']) && 0 < $params['stopprovider']) {
            $priceProvider = $app->priceProviderStore()->getById($params['stopprovider']);
            $app->priceManager()->stopPriceCollector($priceProvider);
        } elseif(isset($params['isrunning']) && 0 < $params['isrunning']) {
            $priceProvider = $app->priceProviderStore()->getById($params['isrunning']);
            $running = $app->priceManager()->isPriceCollectorRunning($priceProvider);
            echo "The provider {$priceProvider->name} is " . ($running?'active and running': 'NOT running') . "\n";
        } elseif(isset($params['keepalivecheck'])) {
            $allPriceProvider = $app->priceProviderStore()->searchTable()->select()->where('status', 1)->execute();
            foreach($allPriceProvider as $aProvider) {
                if($app->priceManager()->isPriceCollectorRunning($aProvider)) {
                   echo "Price collector for {$aProvider->name} is running....\n" ;
                } else {
                   echo "Price collector for {$aProvider->name} is NOT running....attempting to restart\n" ;
                   $this->launchAJob($app, $aProvider);
                }
            }            
        } elseif(isset($params['_start_collector_real_'])  && 0 < $params['_start_collector_real_']) {
            $priceProvider = $app->priceProviderStore()->getById($params['_start_collector_real_']);
            $app->priceManager()->startPriceCollector($priceProvider);
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
            'startall' => array('required' =>  false,  'type' => 'int', 'desc' => 'Goes through all the price providers and launch each one individually'),
            'keepalivecheck' => array('required' =>  false,  'type' => 'string', 'desc' => 'Add super-privilege (/root) to specified username'),
            'startprovider' => array('required' =>  false,  'type' => 'int', 'desc' => 'Start price collection for the specified price provider id in arguement'),
            'stopprovider' => array('required' =>  false,  'type' => 'int', 'desc' => 'Stops the price collection for price provider id given as argument'),
            'isrunning' => array('required' =>  false,  'type' => 'int', 'desc' => 'Checks if the price provider ID given is currently running')
        ];
    }

    private function startAllJobs($app) {
        $allPriceProvider = $app->priceProviderStore()->searchTable()->select()->where('status', 1)->execute();
        foreach($allPriceProvider as $aProvider) {
            $this->launchAJob($app, $aProvider);
        }
    }

    private function launchAJob($app, $provider) {
        $app->startCLIJob(__FILE__, [ '_start_collector_real_' => $provider->id]);
    }
}
?>