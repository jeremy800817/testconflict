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
Use Snap\object\OrderQueue;
Use Snap\object\Order;
Use Snap\object\MbbApFund;
/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @version 1.0
 * @package  snap.job
 */
class MbbAPFundRecalculationJob extends basejob {

    // php /usr/local/nginx/html/gtp/source/snaplib/cli.php -f /usr/local/nginx/html/gtp/source/snaplib/job/MbbAPFundRecalculationJob.php -c /usr/local/nginx/html/gtp/source/snapapp/config.ini -p "action=pool"
    // php /usr/local/nginx/html/gtp/source/snaplib/cli.php -f /usr/local/nginx/html/gtp/source/snaplib/job/MbbAPFundRecalculationJob.php -c /usr/local/nginx/html/gtp/source/snapapp/config.ini -p "action=poolSingle&orderno="
    // php /usr/local/nginx/html/gtp/source/snaplib/cli.php -f /usr/local/nginx/html/gtp/source/snaplib/job/MbbAPFundRecalculationJob.php -c /usr/local/nginx/html/gtp/source/snapapp/config.ini -p "action=moveconfirmtobooking&enddate=2021-03-26 00:00:00"
    public function doJob($app, $params = array()) {
        
        $mibPartnerId = $app->getConfig()->{'gtp.mib.partner.id'};
        // ensure config has value;
        if (empty($mibPartnerId)){
            $message = "Unable to process MbbAPFundRecalculationJob, could not get mib/etc partnerId";
            $this->log($message, SNAP_LOG_ERROR);
            echo $message;
            exit;
        }
        
        if ($params['action'] == 'ordercancel'){
            $this->recalculate_cancelprice($app);
        }
        if ($params['action'] == 'pool'){
            // re-create from new table
            $this->recalculatePool($app, $mibPartnerId);
        }
        if ($params['action'] == 'poolSingle'){
            // re-create from new table
            $this->recalculatePoolSingle($app, $mibPartnerId, $params['orderno']);
        }
        if ($params['action'] == 'patchp1p2p3timestamp'){
            // patch p1, p2, p3 price timestamp
            // $params['id'] => mbbanpfund->id OR "ALL"
            $this->patchp1p2p3timestamp($app, $params['id']);
        }
        if ($params['action'] == 'moveconfirmtobooking'){
            // end date format '2021-03-01 16:00:00' // utc time
            $this->moveConfirmPriceToBookingPrice($app, $mibPartnerId, $params['enddate']);
        }
        
    }

    function describeOptions() {

    }

    private function recalculate_cancelprice($app){
        $orders = $app->orderStore()->searchTable()->select()
            ->where('status', \Snap\object\Order::STATUS_CANCELLED)
            ->andWhere('cancelprice', '>', 0)->execute();

        $pricestreamStore = $app->PriceStreamStore();
        foreach ($orders as $order){
            $p3 = $pricestreamStore->getById($order->cancelpricestreamid);
            if ($order->type == Order::TYPE_COMPANYSELL){
                $order->cancelprice = $p3->companybuyppg;
            }
            if ($order->type == Order::TYPE_COMPANYBUY){
                $order->cancelprice = $p3->companysellppg;
            }
            $app->orderStore()->save($order);
        }
    }

    private function recalculatePool($app, $mbbPartnerId){
        $orders = $app->orderStore()->searchTable()->select()->where('partnerid', $mbbPartnerId)->execute();

        foreach ($orders as $order){
            if ($order->bookingpricestreamid){
                if ($order->status == \Snap\object\Order::STATUS_CONFIRMED || $order->status == \Snap\object\Order::STATUS_PENDING){
                    $type = MbbApFund::TYPE_ORDERCONFIRM;
                    $app->MbbAnPManager()->createConfirmAnP($order, $type);
                }
            }
            if ($order->cancelprice > 0 && $order->status == \Snap\object\Order::STATUS_CANCELLED){
                $type = MbbApFund::TYPE_ORDERCONFIRM;
                $app->MbbAnPManager()->createConfirmAnP($order, $type);
                $type = MbbApFund::TYPE_ORDERREVERSE;
                $app->MbbAnPManager()->createReversalAnP($order, $type);
            }
        }
    }

    private function recalculatePoolSingle($app, $mbbPartnerId, $orderNo){
    
        $order = $app->orderStore()->searchTable()->select()->where('partnerid', $mbbPartnerId)->andWhere('orderno', $orderNo)->one();

        if (!$order){
            $message = "Unable to process MbbAPFundRecalculationJob, no order found that belongs to MBB";
            $this->log($message, SNAP_LOG_ERROR);
            echo $message;
            exit;
        }
        
        if ($order->bookingprice){
            if ($order->status == \Snap\object\Order::STATUS_CONFIRMED || $order->status == \Snap\object\Order::STATUS_PENDING){
                $type = MbbApFund::TYPE_ORDERCONFIRM;
                $app->MbbAnPManager()->updateAnP($order, $type);
            }
        }
        if ($order->cancelprice > 0 && $order->status == \Snap\object\Order::STATUS_CANCELLED){
            $type = MbbApFund::TYPE_ORDERCONFIRM;
            $app->MbbAnPManager()->updateAnP($order, $type);
            $type = MbbApFund::TYPE_ORDERREVERSE;
            $app->MbbAnPManager()->updateAnP($order, $type);
        }

    }

    private function patchp1p2p3timestamp($app, $mbbanpfundId = 'ALL'){

        if ($mbbanpfundId == null){
            $mbbanpfundId = "ALL";
        }

        $app->MbbAnPManager()->patchp1p2p3timestamp($mbbanpfundId);

    }

    // 24-03-2021 - only ONCE to patch the wrong P2 price to maybank which used confirmprice, should use `bookingprice`
    private function moveConfirmPriceToBookingPrice($app, $mbbPartnerId, $endDate){

        if (!$endDate){
            echo 'invalid end date';
            exit;
        }
        
        $orders_move_bookingprice = $app->MbbAnPManager()->moveConfirmPriceToBookingPrice($mbbPartnerId, $endDate);
        
        if (!$orders_move_bookingprice){
            echo 'invalid orders_move_bookingprice';
            exit;
        }

        // recalculate entire A and P table
        // $this->recalculatePool($app, $mbbPartnerId);

    }

}
