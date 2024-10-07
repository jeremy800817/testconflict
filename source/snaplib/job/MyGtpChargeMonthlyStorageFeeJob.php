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
class MyGtpChargeMonthlyStorageFeeJob extends basejob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array())
    {
        $now = new \DateTime('now', $app->getUserTimezone());
        $chargeDate = new \DateTime($now->format('Y-m-01 00:00:00'), $app->getUserTimezone());        
        $chargeDate->sub(\DateInterval::createFromDateString("1 second"));
        $priceDate = new \DateTime($now->format('Y-m-01 08:30:00'), $app->getUserTimezone());        
        $priceDate->setTimezone($app->getServerTimezone());

        if (isset($params['partner']) && 0 < $params['partner']) {
            $partner  = $app->partnerStore()->getById($params['partner']);
            $product  = $app->productStore()->getByField('code', 'DG-999-9');
            $provider = $app->priceproviderStore()->getForPartnerByProduct($partner, $product);

            $priceStream = $app->priceStreamStore()->searchTable()
                               ->select()
                               ->where('providerid', $provider->id)
                               ->where('pricesourceon', '>=', $priceDate->format('Y-m-d H:i:s'))
                               ->orderBy('pricesourceon', 'ASC')
                               ->one();

            if (! $priceStream) {
                $this->log(__METHOD__ . "(): Price data date from ({$chargeDate->format('Y-m-d H:i:s')}) onward not available for partner ({$partner->code})", SNAP_LOG_ERROR);
                return;
            }
            // Reset back to server timezone
            $chargeDate->setTimezone($app->getServerTimezone());
            
            /** @var MyGtpStorageManager $storageMgr */
            $storageMgr = $app->mygtpstorageManager();

            if (isset($params['accountholderids']) && 0 < strlen($params['accountholderids'])) {
                $accHolderIds = explode(",", $params['accountholderids']);
                $accHolders = $app->myaccountholderStore()
                ->searchTable()
                ->select(['id', 'partnerid'])
                ->where('status', \Snap\object\MyAccountHolder::STATUS_ACTIVE)
                ->where('investmentmade', \Snap\object\MyAccountHolder::INVESTMENT_MADE)
                ->where('partnerid', $partner->id)
                ->whereIn('id', $accHolderIds)
                ->execute();
                
                $storageMgr->chargeMonthlyFee($partner, $accHolders, $priceStream, $chargeDate);                

            } else {
                $limit = 1000;
                $count = $app->myaccountholderStore()
                    ->searchTable()
                    ->select()
                    ->where('status', \Snap\object\MyAccountHolder::STATUS_ACTIVE)
                    ->where('investmentmade', \Snap\object\MyAccountHolder::INVESTMENT_MADE)
                    ->where('partnerid', $partner->id)
                    ->count();

                $totalPages = ceil($count / $limit);

                // Chunk query
                for($page = 0; $page < $totalPages; $page++) {
                    $accHolders = $app->myaccountholderStore()
                        ->searchTable()
                        ->select(['id', 'partnerid'])
                        ->where('status', \Snap\object\MyAccountHolder::STATUS_ACTIVE)
                        ->where('investmentmade', \Snap\object\MyAccountHolder::INVESTMENT_MADE)
                        ->where('partnerid', $partner->id)
                        ->page($page, $limit)
                        ->get();  
                    
                    if (empty($accHolders)) {
                        $this->logDebug(__METHOD__ . "(): No account holders found for partner {$partner->code}");
                        continue;
                    }
        
                    $storageMgr->chargeMonthlyFee($partner, $accHolders, $priceStream, $chargeDate);
                }
            }

            
        }
    }

    function describeOptions()
    {
        return [
            'partner' => array('required' =>  true,  'type' => 'int', 'desc' => 'Calculate and charge monthly storage fee for provided partner id in argument'),
            'accountholderids' => array('required' =>  false,  'type' => 'string', 'desc' => 'Comma separted accountholder ids to run'),
        ];
    }
}
