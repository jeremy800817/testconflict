<?php

namespace Snap\job;

use Snap\manager\MyGtpAccountManager;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Azam <azam@silverstream.my>
* @version 1.0
* @created 26-04-2021
*/

class MyGtpProcessAccountClosureJob extends basejob
{
    public function doJob($app, $params = array())
    {
        if (isset($params['partner']) && 0 < $params['partner']) {
            $partner  = $app->partnerStore()->getById($params['partner']);

            /** @var \Snap\object\MyAccountClosure[] $accClosures */
            $accClosures = $app->myaccountclosureStore()
                ->searchView()
                ->select()
                ->where('achpartnerid', $params['partner'])
                ->where('status', \Snap\object\MyAccountClosure::STATUS_PENDING)
                ->forwardKey('accountholderid')
                ->get();

            if (empty($accClosures)) {
                return;
            }

            /** @var \Snap\object\MyAccountHolder[] $accHolders */
            $accHolders = $app->myaccountholderStore()
                ->searchTable()
                ->select()
                ->whereIn('id', array_keys($accClosures))
                ->get();

            $product  = $app->productStore()->getByField('code', 'DG-999-9');
            $provider = $app->priceproviderStore()->getForPartnerByProduct($partner, $product);

            /** @var PriceManager $priceMgr */
            $priceMgr = $app->priceManager();
            $priceStream = $priceMgr->getLatestSpotPrice($provider);

            /** @var MyGtpAccountManager $accountMgr */
            $accountMgr = $app->mygtpaccountManager();
            foreach($accHolders as $accHolder) {
                $accountMgr->processAccountClosure(
                    $partner,
                    $accHolder,
                    $accClosures[$accHolder->id],
                    $product,
                    $priceStream,
                    'MANUAL'
                );
            }
        }
    }

    public function describeOptions()
    {
        return [
            'partner' => array('required' =>  false,  'type' => 'int', 'desc' => 'Process account closures of provided partner id in argument'),
        ];
    }
}