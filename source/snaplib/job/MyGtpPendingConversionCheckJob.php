<?php

namespace Snap\job;

use Snap\api\wallet\BaseWallet;
use Snap\InputException;
use Snap\object\MyConversion;
use Snap\object\MyPaymentDetail;
use Snap\object\Redemption;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 04-Feb-2021
*/

class MyGtpPendingConversionCheckJob extends basejob
{
    /** @var MyPaymentDetail[] $paymentDetails */
    private $paymentDetails = [];

    public function doJob($app, $params = array())
    {
        if($app->getConfig()->{'otc.job.diffserver'} == '1'){
            $partnerid = $params['partnerids'];
            $partner = $app->partnerStore()->searchTable()->select()->where('group', $partnerid)->execute();
            $partnerIds = [];
            foreach($partner as $record){
                array_push($partnerIds, $record->id);
            }
        }
        else{
            $partnerIds = explode(',', $params['partnerids']);
        }

        $conversionStore = $app->myconversionStore();
        $cutOffTime = new \DateTime('10 minutes ago');
        $cutOffTimeFormatted = $cutOffTime->format('Y-m-d H:i:s');
        $this->log("Checking for pending conversion before ". $cutOffTimeFormatted, SNAP_LOG_INFO);

        $pendingConversions = $conversionStore->searchView()->select()
                                        ->where('createdon', '<=', $cutOffTimeFormatted)
                                        ->andWhere('rdmpartnerid', 'in', $partnerIds)
                                        ->andWhere('status', MyConversion::STATUS_PAYMENT_PENDING)
                                        ->execute();
        
        // Process tasks
        $toExpire = [];
        foreach ($pendingConversions as $tx) {
            // This is due to splitted conversions using 1 PaymentDetail
            $refno = $tx->getRefNo(true);
            $payment = $app->mypaymentdetailStore()->getByField('sourcerefno', $refno);
            $this->paymentDetails[$payment->id] = $payment;
            if (MyPaymentDetail::STATUS_PENDING_PAYMENT == $payment->status) {

                $cacheKey = '{PendingPaymentTx}:' . $payment->id;
                $cacher = $app->getCacher();
                $current = $cacher->get($cacheKey);

                // Only expire those whose already tried for 3rd times to get payment status
                if (3 >= $current) {
                    continue;
                }

                // Expire the conversion if payment is not paid yet. 
                $toExpire[$payment->id][] = $tx;
            }
        }

        // Expire conversions first
        $this->expireConversion($app, $toExpire);

        $this->log("Finished checking for pending conversions before $cutOffTimeFormatted", SNAP_LOG_INFO);
    }

    protected function expireConversion($app, array $toExpire)
    {
        foreach ($toExpire as $pdId => $conversions) {
            $app->getDBHandle()->beginTransaction();
            try {
                $app->mygtpConversionManager()->doExpireConversion($conversions);

                // Cancel payment detail
                $payment = $this->paymentDetails[$pdId];
                $payment->status = MyPaymentDetail::STATUS_CANCELLED;
                $payment = $app->mypaymentdetailStore()->save($payment);
                $this->paymentDetails[$pdId] = $payment;

                $app->getDBHandle()->commit();
            } catch (\Throwable $e) {
                $this->log($e->getMessage());
                $app->getDBHandle()->rollBack();
            }
        }


    }

    public function describeOptions()
    {
        return [
            'partnerids' => array('required' => true,  'type' => 'string', 'desc' => 'Comma separated list of partner ids'),
        ];
    }
}