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
Use Snap\object\Order;
Use Snap\object\Buyback;
/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @version 1.0
 * @package  snap.job
 */
class CheckSapTransactionBatchJob extends basejob {
    // CAUTION - this job will `RUN` `ONCE` on `7.30am` daily check previous day
    // php source/snaplib/cli.php -f source/snaplib/job/CheckSapTransactionBatchJob.php -c source/snapapp/config.ini -p ""
    public function doJob($app, $params = array()) {

        $mibPartnerId = $app->getConfig()->{'gtp.mib.partner.id'};
        // ensure config has value;
        if (empty($mibPartnerId)){
            $this->log("Unable to expire, could not get mib/etc partnerId", SNAP_LOG_ERROR);
            exit;
        }

        $posBuybackWest = $app->getConfig()->{'gtp.pos3.partner.id'};
        $posBuybackEast = $app->getConfig()->{'gtp.pos4.partner.id'};

        // future include partners insert here [$mibPartnerId, ..]
        $partnerIds = [$mibPartnerId, $posBuybackWest, $posBuybackEast];

        
        // $now = new \DateTime();
        // $now = $now->modify('-1 days');
        // Orders
        $pendingOrders = $app->orderStore()->searchTable()->select('id')
            ->where('partnerid', 'IN', $partnerIds)
            ->andWhere('status', [Order::STATUS_PENDING])
            ->count();
        if ($pendingOrders){
            // 2nd attempt
            if (!$params['checkonly']){
                $app->spotOrderManager()->processNewOrders($partnerIds);
            }else{
                // 8.20am daily
                // email notification to gtp.support@silverstream.my
                $message = 'Has total ('.$pendingOrders.') Order(s) did not push to SAP. Please check with SAP immediately.';
                $this->sendNotification($app, $message);
            }
        }
        // Buybacks
        $pendingBuybacks = $app->buybackStore()->searchTable()->select('id')
            ->where('partnerid', 'IN', $partnerIds)
            ->andWhere('status', [Buyback::STATUS_PENDING])
            ->count();
        if ($pendingBuybacks){
            // 2nd attempt
            if (!$params['checkonly']){
                $app->buybackManager()->processNewBuybacks($partnerIds);
            }else{
                // 8.20am daily
                // email notification to gtp.support@silverstream.my
                $message = 'Has total ('.$pendingBuybacks.') Buyback(s) did not push to SAP. Please check with SAP immediately.';
                $this->sendNotification($app, $message);
            }
        }

        // new add in 08-AUG-2022 -- start
        $checkOtherPartnersPendingSAP = $app->orderStore()->searchTable()->select('id')
            ->where('partnerid', 'NOT IN', $partnerIds)
            ->andWhere('status', [Order::STATUS_PENDING])
            ->count();
        if ($checkOtherPartnersPendingSAP){
            // 2nd attempt
            $checkOtherPartnersPendingSAP = $app->orderStore()->searchView()->select('partnername')
                ->where('partnerid', 'NOT IN', $partnerIds)
                ->andWhere('status', [Order::STATUS_PENDING])
                ->groupBy('partnerid')
                ->execute();
            $partnerName = [];
            foreach ($checkOtherPartnersPendingSAP as $checkOtherPartnersPendingSAP_single){
                $partnerName[] = $checkOtherPartnersPendingSAP_single->partnername;
            }
            // 8.20am daily
            // email notification to gtp.support@silverstream.my
            $message = implode(',', $partnerName).' Has total ('.$checkOtherPartnersPendingSAP.') Order(s) did not push to SAP. Please check with SAP immediately.';
            $this->sendNotification($app, $message);
        }
        // new add in 08-AUG-2022 -- end
    }
    
    function describeOptions() {

    }
    
    private function sendNotification($app, $message){
        $mailer = $app->getMailer();
        $receiver = 'gtp.support@silverstream.my,rinston@silverstream.my';
        $mailer->addAddress($receiver);

        $mailer->Subject = 'Pending Transaction(s) - SAP Batch Job';
        
        $mailer->Body    = $message;

        $mailer->send();

        return true;
    }
}