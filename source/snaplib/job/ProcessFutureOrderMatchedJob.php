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
// Use Snap\TLogging;
Use Snap\object\OrderQueue;
Use Snap\common;
/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 * 
 * This job is to check any MBB future order with matched status for more than 1 minute. Change status to cancelled as it means, there is no Spot Order received
 *
 * @author Nurdianah <dianah@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class ProcessFutureOrderMatchedJob extends basejob {

    public function doJob($app, $params = array()) {
        $arguments = $params;
        //$arguments['checkingcount'] = 1 to check matched status transactions matchon differences
        //$arguments['procedd'] = 1 to proceed change status to cancelled
        $mibPartnerId = $app->getConfig()->{'gtp.mib.partner.id'};
        // ensure config has value;
        if (empty($mibPartnerId)){
            $this->log("Unable to change to cancelled, could not get mib/etc partnerId", SNAP_LOG_ERROR);
            exit;
        }
        // future exclude partners insert here [$mibPartnerId, ..]
        $excludePartners = [$mibPartnerId];

        $futureOrders = $app->OrderQueueStore()->searchTable()->select()
            ->where('partnerid', $mibPartnerId)
            ->andWhere('orderid', 0)
            ->andWhere('status', OrderQueue::STATUS_MATCHED)
            ->execute();

        $seconds = 60; //check one minute from matchon date
        $i=1;
        foreach ($futureOrders as $futureOrder){
            $now = common::convertUTCToUserDatetime(new \DateTime());
            $currentdatetime = strtotime("now +8 hours");
            $currentdate = date('Y-m-d H:i:s',$currentdatetime); //+8hrs
            $currentserverdatetime = strtotime("now"); //for db save
            $currentserverdate = date('Y-m-d H:i:s',$currentserverdatetime); //for db save
            $matchondate = strtotime($futureOrder->matchon->format('Y-m-d H:i:s')); //+8hrs
            $totalSecondsDiff = abs($currentdatetime-$matchondate); //use this one for seconds
            /*$totalMinutesDiff = $totalSecondsDiff/60; //710003.75
            $totalHoursDiff   = $totalSecondsDiff/60/60;//11833.39
            $totalDaysDiff    = $totalSecondsDiff/60/60/24; //493.05
            $totalMonthsDiff  = $totalSecondsDiff/60/60/24/30; //16.43
            $totalYearsDiff   = $totalSecondsDiff/60/60/24/365; //1.35*/
            
            
            if($totalSecondsDiff > $seconds){
                if(isset($arguments['checkingcount']) && $arguments['checkingcount']){
                    echo "Match=> ".$i.". OrderQueue ID => ".$futureOrder->id."\n";
                    echo "Here is datetime => ".$futureOrder->matchon->format('Y-m-d H:i:s')."\n";
                    echo "Here is strtotime => ".$matchondate."\n";
                    echo "Here is currentdatetime => ".$currentdate."\n";
                    echo "Here is currentstrtotime => ".$currentdatetime."\n";
                    echo "The differences time => ".$totalSecondsDiff."\n\n";
                }

                if(isset($arguments['proceed']) && $arguments['proceed']){
                    $futureOrder->status = OrderQueue::STATUS_CANCELLED;
                    $futureOrder->cancelon = $now->format('Y-m-d H:i:s');
                    $futureOrder->remarks = $futureOrder->remarks."-MATCHED change to CANCELLED as more than 1 minute from currentdatetime ".$currentserverdate;
                    $save = $app->OrderQueueStore()->save($futureOrder);

                    if ($save){
                    $this->log("Successful changed to status cancelled, future order ID:".$save->id.". MATCHED status more than 1 minute.", SNAP_LOG_DEBUG);
                    }else{
                        // do not throw error, if error => email(error_Trans->id) to ace/dev, do not use sql_trans and rollback()
                        $this->log("Unable to cancelled, future order ID:".$futureOrder->id, SNAP_LOG_ERROR);
                    }
                }
                $i++;
            }
        }
    }

    function describeOptions() {

    }

}