<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Exception;
use Snap\manager\MyGtpStorageManager;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author   Azam <azam@silverstream.my>
 * @version  1.0my
 * @package  snap.job
 */
class MyGtpProcessSapStorageFeeJob extends basejob
{
    public function doJob($app, $params = array())
    {
        $partnerIds = explode(',', $params['partnerids']);
        if(isset($params['month']) || isset($params['year'])) { //if manually, put month and year.
            $now = new \DateTime('now', $app->getUserTimezone());
            $dateEnd = new \DateTime($now->format($params['year'].'-'.$params['month'].'-01 00:00:00'), $app->getUserTimezone());
        } else {
            $dateEnd = new \DateTime('last day of last month', $app->getUserTimezone());
        }        
        $dateEnd   = new \DateTime($dateEnd->format('Y-m-t 23:59:59'), $app->getUserTimezone());
        $dateStart = new \DateTime($dateEnd->format('Y-m-01 00:00:00'), $app->getUserTimezone());

        // Latest price date at 8:30 AM +8
        if (isset($params['latestpricedate']) && 0 < $params['latestpricedate']) {
            $priceDate = new \DateTime('now', $app->getUserTimezone());;
        } else {
            $priceDate = clone $dateEnd;
            $priceDate->modify('+1 day');
        }

        $dateEnd->setTimezone($app->getServerTimezone());
        $dateStart->setTimezone($app->getServerTimezone());
        $priceDate->setTimezone($app->getServerTimezone());
        
        /** @var MyGtpStorageManager $storageMgr */
        $storageMgr = $app->mygtpstorageManager();

        foreach ($partnerIds as $partnerId) {
            $partner = $app->partnerStore()->getById($partnerId);
            $product  = $app->productStore()->getByField('code', 'DG-999-9');
            $provider = $app->priceproviderStore()->getForPartnerByProduct($partner, $product);
            $priceStream = $app->priceStreamStore()->searchTable()
                               ->select()
                               ->where('providerid', $provider->id)
                               ->where('pricesourceon', '>=', $priceDate->format('Y-m-d 00:30:00'))
                               ->where('pricesourceon', '<=', $priceDate->format('Y-m-d 00:30:59'))
                               ->orderBy('pricesourceon', 'ASC')
                               ->one();

            try {
                $this->log("Submitting pending admin and storage fee to SAP for {$partner->code} month {$dateStart->format('Y-m-d')}", SNAP_LOG_INFO);

                if (! $priceStream) {
                    if(isset($params['pricestreamid'])){
                        $priceStream = $app->priceStreamStore()->getById($params['pricestreamid']);
                    } else throw new Exception("Could not get price stream");
                }

                $storageMgr->submitAdminAndStorageFeeToSAP($partner, $dateStart, $dateEnd, $priceStream);                                    
            } catch (\Exception $e) {                    
                $this->log("Error while submitting storage fee for partner {$partner->code} month ($dateStart->format('Y-m-d')): {$e->getMessage()}", SNAP_LOG_ERROR);
            }
        }

        $this->log("Finished sending admin and storage fee to SAP", SNAP_LOG_INFO);
    }

    public function describeOptions()
    {
        return [
            'partnerids' => array('required' => true,  'type' => 'string', 'desc' => 'Comma separated list of partner ids'),
            'month' => array('required' => false,  'type' => 'int', 'desc' => 'The month to of the storage fee to submit to SAP'),
            'latestpricedate' => array('required' => false,  'type' => 'int', 'desc' => 'Set to 1 to select latest pricestream >= 8:30 AM'),
        ];
    }
}