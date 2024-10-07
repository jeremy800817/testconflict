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
Use Snap\object\MyLedger;
/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @version 1.0
 * @package  snap.job
 */
class MyGtpSpecialOrderManualPushToSap extends basejob {
    
    // php /usr/local/nginx/html/gtp/source/snaplib/cli.php -f /usr/local/nginx/html/gtp/source/snaplib/job/MyGtpSpecialOrderManualPushToSap.php -c /usr/local/nginx/html/gtp/source/snapapp/redone.ini -p "orderno=A2210250008D&partnerid=3416684"
    public function doJob($app, $params = array()) {

        $orderno    = $params['orderno'];
        $partnerid  = $params['partnerid'];

        //$return        = $app->orderStore()->getByField('orderno',$orderno);

        $return     = $app->orderStore()->searchTable()->select()
                        ->where('partnerid', $partnerid)
                        ->andWhere('orderno',$orderno)
                        ->one();

        if ($return){
            $spotordermanager = $app->spotorderManager();
            $returns = $spotordermanager->confirmBookOrder($return);
        }else{
            $this->log("Invalid Order No", SNAP_LOG_ERROR);
            echo "No order found.";
            return;
        }

        $finalAcePrice  = number_format($returns->price,3);
        $weight         = number_format($returns->xau,3);
        $totalEstValue  = number_format($returns->amount,2);
        $orderFee       = number_format($returns->fee,3);

        $finalAcePrice  = $finalAcePrice + $orderFee;
        $finalAcePrice  = number_format($finalAcePrice,2);

        if ($returns){
            $this->logDebug("Creating partner ledger for special order $returns->partnerrefno");
            $partnerLedger = $app->myledgerStore()->create([
                'accountholderid'   => 0,
                'partnerid'         => $returns->partnerid,
                'refno'             => $returns->partnerrefid,
                'transactiondate'   => $returns->createdon->format('Y-m-d H:i:s'),
                'remarks'           => 'Special Spot Order',
                'status'            => \Snap\object\MyLedger::STATUS_ACTIVE
            ]);
            
            // Set debit/credit depending if it is a buy or sell
            if (\Snap\object\Order::TYPE_COMPANYBUY == $returns->type) {
                $partnerLedger->type   = \Snap\object\MyLedger::TYPE_PROMO;
                $partnerLedger->credit = $returns->xau;
                $partnerLedger->debit  = 0.00;
            } else {
                $partnerLedger->type   = \Snap\object\MyLedger::TYPE_PROMO;
                $partnerLedger->debit  = $returns->xau;
                $partnerLedger->credit = 0.00;
            }
            $partnerLedger = $app->myledgerStore()->save($partnerLedger);
        }

    }
    
    function describeOptions() {

    }
    
}