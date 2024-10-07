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
 * 20211108 - This file is alternative to grab price for all active providers at table at 8:30am everyday
 *
 * @author Dianah <dianah@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class MyGtpGetPriceStreamFirstPriceJob  extends basejob {
     
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        if(!defined('SNAPAPP_DBACTION_USERID')) define('SNAPAPP_DBACTION_USERID', 2);
        $arguments = $params;

        $getDateTimeFirst = date("Y-m-d 00:30:00");
        $getCurrentDate = date("Y-m-d H:i:s");
        $nextDateTime = date('Y-m-d 00:30:00', strtotime(' +1 day'));

        $convertCurrDate = strtotime($getCurrentDate);
        $convertTomDate = strtotime($nextDateTime);
        $diffTimes = $convertTomDate-$convertCurrDate;
        //$getProviderList = explode(',', $app->getConfig()->{'mygtp.firstpriceofday.list'});
        //$getProviderList = explode(',', $arguments['providerid']);

        $getProviderList = $app->priceproviderStore()->searchTable()->select()
                            ->where('status',1)
                            ->execute();


        foreach($getProviderList as $aProvider){
            //2) Get the last pricestream from Redis cache.
            $latestPriceDataKey = '{PriceFeedLast}:' . $aProvider->id;
            $priceStreamCache = $app->getCache($latestPriceDataKey);
            $firstPriceOfDayDataKey = '{PriceFeedFirstOfDay}:' . $aProvider->id;
            if(0 == strlen($priceStreamCache) || null == $priceStreamCache) {
                $this->log("getFirstDaySpotPrice for $aProvider->id .select from DB. ", SNAP_LOG_DEBUG);
                $firstPriceOfDay = $app->priceStreamStore()->searchTable()
                    ->select()
                    ->where('providerid', $aProvider->id)
                    ->andWhere('createdon', '>=', $getDateTimeFirst)
                    ->orderby('createdon', 'ASC')
                    ->one();
            } else {
                $this->log("Grab getLatestSpotPrice select from cache. ", SNAP_LOG_DEBUG);
                $firstPriceOfDay = $app->priceStreamStore()->create();
                $firstPriceOfDay->fromCache($priceStreamCache);
            }

            if(null != $firstPriceOfDay){
                $app->setCache($firstPriceOfDayDataKey, $firstPriceOfDay->toCache(), $diffTimes);
                $this->log("getFirstDaySpotPrice for $aProvider->id. Pricestream id is $firstPriceOfDay->id. save at redis. ", SNAP_LOG_DEBUG);
            } else {
                $this->log("getFirstDaySpotPrice for $aProvider->id is not available. ", SNAP_LOG_DEBUG);
            }
            
        }
        $this->log("Finish getFirstDaySpotPrice for all active provider listed in db . ", SNAP_LOG_DEBUG);
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