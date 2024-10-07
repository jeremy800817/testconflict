<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

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
class MyGtpCalculateDailyStorageFeeJob extends basejob
{
    public function doJob($app, $params = array())
    {
        $now = new \DateTime();

        if (isset($params['partner']) && 0 < $params['partner']) {
            $partner       = $app->partnerStore()->getById($params['partner']);

            $accHoldersToCalculate = $app->myaccountholderStore()
                ->searchTable()
                ->select(['id', 'partnerid'])
                ->where('status', \Snap\object\MyAccountHolder::STATUS_ACTIVE)
                ->where('investmentmade', \Snap\object\MyAccountHolder::INVESTMENT_MADE)
                ->where('partnerid', $partner->id)
                ->get();

            $previousDay = \Snap\Common::convertUTCToUserDatetime($now);
            $previousDay->setTime(23, 59, 59);
            $previousDay->setTimezone($app->getServerTimezone());
            $previousDay->modify('-1 day');

            /** @var MyGtpStorageManager $storageMgr */
            $storageMgr = $app->mygtpstorageManager();
            $storageMgr->calculateDailyStorageFee($partner, $accHoldersToCalculate, $previousDay);
        }
    }

    function describeOptions()
    {
        return [
            'partner' => array('required' =>  false,  'type' => 'int', 'desc' => 'Calculate daily storage fee for account holders of provided partner id in argument'),
        ];
    }
}
