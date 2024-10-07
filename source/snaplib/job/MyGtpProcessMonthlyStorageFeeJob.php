<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;
use Snap\manager\MyGtpStorageManager;

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
class MyGtpProcessMonthlyStorageFeeJob extends basejob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array())
    {
        echo "============================================\n";
        echo "======\n";
        echo "====== Job to process given missing monthly storage charges for partner\n";
        echo "======\n";
        echo "============================================\n";

        $year = $params['year'];
        $month = sprintf('%02d',$params['month']);
        $day = '01';

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "{$year}-{$month}-{$day} 00:00:00", $app->getUserTimezone());
        $dateStart = clone $date;
        $date->add(\DateInterval::createFromDateString("1 month"));
        $dateEnd = clone $date;
        $dateEnd->sub(\DateInterval::createFromDateString("1 second"));
        
        $date->setTimezone($app->getServerTimezone());
        $dateStart->setTimezone($app->getServerTimezone());
        $dateEnd->setTimezone($app->getServerTimezone());

        if (! $date) {
            echo 'Invalid year or month given';
            return;
        }

        $partner  = $app->partnerStore()->getByField('code', $params['partnercode']);
        if (! $partner) {
            echo 'Invalid year or month given';
            return;
        }

        $recalculate = $params['recalculate'] ?? 1;

        /** @var MyGtpStorageManager $storageMgr */
        $storageMgr = $app->mygtpstorageManager();
        if ($recalculate) {
            $storageMgr->recalculateDailyStorageFeeForPartner($partner, $dateStart, $dateEnd);
        }

        $useChargeDate = 0 == $params['usechargedate'] ? false : true;
        $latestpricedate = isset($params['latestpricedate']) && 0 == $params['latestpricedate'] ? false : true;
        $storageMgr->processMonthyStorageFeeForPartner($partner, $date, $useChargeDate, $latestpricedate);
    }

    function describeOptions()
    {
        return [
            'partnercode' => array('required' =>  true,  'type' => 'int', 'desc' => 'Partner id of account holders'),
            'month' => array('required' =>  true,  'type' => 'int', 'desc' => 'The month to charge'),
            'year' => array('required' =>  true,  'type' => 'int', 'desc' => 'The year to charge'),
            'recalculate' => array('required' =>  false,  'type' => 'int', 'desc' => 'Whether to recalculate daily storage for the month. Default to true'),
            'usechargedate' => array('required' =>  true,  'type' => 'int', 'desc' => 'Use charge date as transaction date. Set false if require date to be current for transaction date'),
            'latestpricedate' => array('required' => false,  'type' => 'int', 'desc' => 'Set to 1 to select latest pricestream >= 8:30 AM'),
        ];
    }
}
