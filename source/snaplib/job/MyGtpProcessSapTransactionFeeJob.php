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
use Snap\object\Order;
use Snap\object\MyGoldTransaction;
use Snap\object\Redemption;
use Snap\object\MyConversion;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author Nurdianah Kamarudin <dianah@silverstream.my>
 * @version 1.0my
 * @package  snap.job
 */
class MyGtpProcessSapTransactionFeeJob extends basejob
{
    public function doJob($app, $params = array())
    {
        /*check partner id at each environment/server*/
        $partnerIds = explode(',', $params['partnerids']);
        $fpxcharge = $params['fpxcharge'];

        $goldTxStore        = $app->mygoldtransactionStore();
        $conversionTxStore  = $app->myconversionStore();
        /*report generate at 2am*/
        if(isset($params['date'])) $currentdate = strtotime($params['date']."+8 hours");
        else $currentdate = strtotime("now +8 hours");

        $date               = date('Y-m-d h:i:s',$currentdate);
        $createDateStart    = date_create($date);
        $modifyDateStart    = date_modify($createDateStart,"-2 day");
        $startDate          = date_format($modifyDateStart,"Y-m-d 16:00:00");
        $createDateEnd      = date_create($date);
        $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
        $endDate            = date_format($modifyDateEnd,"Y-m-d 15:59:59");

        $apiManager = $app->apiManager();
        $version    = $requestParams['version'] = '1.0my';
        foreach($partnerIds as $aPartnerId){
            $settings = $app->mypartnersettingStore()->getByField('partnerid', $aPartnerId);
            $partner  = $app->partnerStore()->getByField('id', $aPartnerId);
            $requestParams['itemCode'] = 'DG-999-9-' . $settings->sapdgcode;
            $requestParams['fpxcharge'] = $fpxcharge;

            $dailyGoldTransaction = $goldTxStore->searchView()->select()
                                ->where('createdon', '>=', $startDate)
                                ->andwhere('createdon', '<=', $endDate)
                                ->andWhere('ordpartnerid', $aPartnerId)
                                //->andWhere('ordbuyerid', 321)
                                ->andWhere('status','IN', [MyGoldTransaction::STATUS_CONFIRMED,MyGoldTransaction::STATUS_PAID]) //order status = 1
                                ->andWhere('settlementmethod', MyGoldTransaction::SETTLEMENT_METHOD_FPX) //order status = 1
                                ->groupBy('id')
                                ->execute();
            if(count($dailyGoldTransaction) > 0) $sendToApiManager = $apiManager->sapFeeCharge($partner, $version, $dailyGoldTransaction, 1, 'processing_fee', $requestParams, 'order'); //onlysend when have transaction

            $dailyConversionTransaction = $conversionTxStore->searchView()->select()
                                ->where('createdon', '>=', $startDate)
                                ->andwhere('createdon', '<=', $endDate)
                                ->andWhere('rdmpartnerid', $aPartnerId)
                                //->andWhere('accountholderid', 321)
                                ->andWhere('status', MyConversion::STATUS_PAYMENT_PAID)
                                ->andWhere('logisticfeepaymentmode', MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX) //order status = 1
                                ->groupBy('id')
                                //->andWhere('rdmstatus', Redemption::STATUS_CONFIRMED) 
                                ->execute();
            if(count($dailyConversionTransaction) > 0) $sendToApiManager = $apiManager->sapFeeCharge($partner, $version, $dailyConversionTransaction, 1, 'processing_fee', $requestParams, 'conversion'); //onlysend when have transaction
        }

        $this->log("Finished send transaction fee to SAP for date {$startDate}", SNAP_LOG_INFO);
    }

    public function describeOptions()
    {
        return [
            'partnerids' => array('required' => true,  'type' => 'string', 'desc' => 'Comma separated list of partner ids'),
        ];
    }
}