<?php
/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 26-Apr-2021
*/

namespace Snap\api\payout;

use Snap\api\exception\GeneralException;
use Snap\object\MyAccountHolder;
use Snap\object\MyDisbursement;

class CompanyManualPayout extends BasePayout 
{

    public function handleResponse($params, $body)
    {
        // Manual Payout should not reach here
        throw GeneralException::fromTransaction([], [
            'message'   => "Not implemented"
        ]);
    }

    /**
     * Send a payout request to provider
     * 
     * @param MyAccountHolder $accountHolder 
     * @param MyDisbursement $disbursement 
     * @return bool 
     */
    public function createPayout($accountHolder, $disbursement)
    {
        // Since this is manual payout, there is no external service to call 
        // Thus, we only update disbursement requestedon
        // The disbursement status & details should be updated through BO
        $now = new \DateTime('now', $this->app->getUserTimezone());
        $disbursementStore = $this->app->mydisbursementStore();

        $disbursement->requestedon = $now;
        $disbursement = $disbursementStore->save($disbursement);
        
        return true;
    }

    /**
     * Manually reads payout status from provider's server
     * 
     * @param MyDisbursement $disbursement 
     * @return exit 
     * @throws GeneralException 
     */
    public function getPayoutStatus($disbursement)
    {
        // Manual Payout should not reach here
        throw GeneralException::fromTransaction([], [
            'message'   => "Not implemented"
        ]);
    }
    
}
?>