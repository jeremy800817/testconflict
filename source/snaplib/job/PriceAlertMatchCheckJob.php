<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;

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
class PriceAlertMatchCheckJob extends basejob
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
        echo "====== Price Alert Matching Job\n";
        echo "======\n";
        echo "============================================\n";

        $aliveTime = isset($params['alivetime']) ? $params['alivetime'] : 60;
        
        if (isset($params['partner']) && 0 < $params['partner']) {
            $partner  = $app->partnerStore()->getById($params['partner']);
            $product  = $app->productStore()->getByField('code', 'DG-999-9');
            $provider = $app->priceproviderStore()->getForPartnerByProduct($partner, $product);
            $app->mygtpPriceAlertManager()->processReceivedPriceStreamData($partner, $provider, $aliveTime);
        }

    }

    function describeOptions()
    {
        return [
            'partner' => array('required' =>  false,  'type' => 'int', 'desc' => 'Start price alert matching for the provided partner id in argument'),
        ];
    }
}
