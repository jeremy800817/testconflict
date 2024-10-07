<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\manager;

Use \Snap\InputException;
Use \Snap\TLogging;
Use \Snap\IObserver;
Use \Snap\IObservable;
Use \Snap\IObservation;
Use \Snap\object\Order;
Use \Snap\object\OrderQueue;
Use \Snap\object\partner;
Use \Snap\object\product;
Use \Snap\object\TradingSchedule;
Use \Snap\object\PriceStream;
Use \Snap\object\PriceValidation;
Use \Snap\common;

class CommonOrdersManager
{
    Use TLogging;

    // private $app = null;

    public function __construct( $app ) {
        $this->app = $app;
    }

    public function canTradingProceedNow(Partner $partner)
    {
        $sqlHandle = $this->app->tradingScheduleStore()->searchTable()->select();
        $sqlHandle->where(function($q) Use ($partner) {
            $q->whereIn('type', [TradingSchedule::TYPE_DAILY, TradingSchedule::TYPE_WEEKDAYS, TradingSchedule::TYPE_WEEKENDS])
               ->andWhere('categoryid', $partner->tradingscheduleid);
          });
        $sqlHandle->orWhere(function($q) Use ($partner) {
            $q->where('type', TradingSchedule::TYPE_STOP)
                ->andWhere('categoryid', $partner->tradingscheduleid)
                ->andWhere('endat', '>=', $q->raw('NOW()'));
          });
        $records = $sqlHandle->execute();
        $canTrade = false;
        foreach($records as $aRecord) {
            if(TradingSchedule::TYPE_STOP == $aRecord->type) {
                if (!$aRecord->canTradeNow()){
                    return false;
                }else{
                    return true;
                }
                //If there is a stop instruction, trading will not be allowed.
                //i.e.  if multiple records are found, 1 that do not allow trading will disallow trading for all.
            } elseif(TradingSchedule::TYPE_STOP != $aRecord->type && $aRecord->canTradeNow()) {
                //Only 1 allow is enough to continue trade for DAILY, WEEKDAYS and WEEKENDS type.
                //i.e.  if multiple records are found and all but 1 allow trading, the trading will continue;
                $canTrade = true;
            }
        }
        return $canTrade;
    }

    public function getTotalTransactionWeight(Partner $partner, Product $product, $transType){
        $now = new \DateTime;
        $now = \Snap\common::convertUTCToUserDatetime($now);
        $index = '{Orders_'.$partner->id.'_'.$transType.'_'.$product->id.'_'.$now->format('Ymd').'}';

        $total_amount = $this->app->getCache($index);
        if (!$total_amount){
            $total_amount = $this->getTransactionWeightFromDB($partner->id, $product->id, $transType);
            $this->app->setCache($index, $total_amount, 86400 /* 1 day */);
        }
        return $total_amount;
    }

    public function getTransactionWeightFromDB($partnerid, $productid, $transType)
    {
        $now = new \DateTime;
        $now = \Snap\common::convertUTCToUserDatetime($now);
        $startAt = new \DateTime($now->format('Y-m-d 00:00:00'));
        $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
        $endAt = new \DateTime($now->format('Y-m-d 23:59:59'));
        $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

        // using value from VIEW , totalamount = amount+fee
        $total_spot_amount = $this->app->orderStore()->searchTable(false)->select()
            ->addFieldSum('xau', 'total_amount')
            ->where('partnerid', $partnerid)
            // ->where('productid', $productid)
            // ->andWhere('isspot', 1)
            ->andWhere('type', $transType)
            ->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
            ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
            ->first();

        $total_queue_amount = $this->app->orderQueueStore()->searchTable(false)->select()
            ->addFieldSum('xau', 'total_amount')
            ->where('partnerid', $partnerid)
            // ->where('productid', $productid)
            ->andWhere('ordertype', $transType)
            ->andWhere('expireon', '>=', $startAt->format('Y-m-d H:i:s'))
            ->first();

        $total_amount = ($total_spot_amount['total_amount'] + $total_queue_amount['total_amount']);
        return $total_amount;
    }

    public function setTodayRemainingLimit($partnerid, $productid, $transType, $orderAmount, $cancelled = false, $futureMatched = false){
        $now = new \DateTime;
        $now = \Snap\common::convertUTCToUserDatetime($now);
        // $index = '{Orders_'.$partnerid.'_'.$transType.'_'.$now->format('Ymd').'}';
        $index = '{Orders_'.$partnerid.'_'.$transType.'_'.$productid.'_'.$now->format('Ymd').'}';
        // $index = '{Orders}'->partner->product->transtype

        if ($futureMatched){
            return $this->app->getCache($index);
        }
        $total_amount = $this->app->getCacher()->increment($index, $cancelled ? -1 * $orderAmount : $orderAmount);
        if (!$total_amount){
            $total_amount = $this->getTransactionWeightFromDB($partnerid, $productid, $transType);
            $total_amount += ($cancelled ? -1 * $orderAmount : $orderAmount);
            $this->app->setCache($index, $total_amount, 86400 /* 1 day */);
        }
        return $total_amount;
    }
    
    public function generateOrderNo($cacher, $partner, $product, $companyBuy, $refid, $orderQueue = false){
        $moduleString = 'Order';
        $generateStore = $this->app->orderStore();
        $generateNoPrefix_Buy = 'B';
        $generateNoPrefix_Sell = 'A';
        $format = '%s%s%05d';
        $envPrefix = '';
        if ($companyBuy){
            $orderType = Order::TYPE_COMPANYBUY;
        }else{
            $orderType = Order::TYPE_COMPANYSELL;
        }
        
        $queueString = '';
        if ($orderQueue == true){
            $moduleString = 'OrderQueue';
            $generateStore = $this->app->orderQueueStore();
            $generateNoPrefix_Buy = 'D';
            $generateNoPrefix_Sell = 'C';
        }

        $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
        if ($developmentEnv){
            $format = '%s%s%04d%s';
            $envPrefix = 'D';
        }
        $this->log("generate_".$moduleString."No($refid, {$partner->code}) - into the method ", SNAP_LOG_DEBUG);
        $now = new \DateTime(gmdate('Y-m-d\TH:i:s'));
        $now = \Snap\common::convertUTCToUserDatetime($now);
        $generateNoKey = $moduleString.$orderType.'No:' . $now->format('Ymd');
        $nextGenerateSequence = $cacher->increment($generateNoKey, 1, 86400);
        $this->log("generate_".$moduleString."No($refid,{$partner->code}) - The date used is " . $now->format('Y-m-d H:i:s') . " and key = " . $generateNoKey, SNAP_LOG_DEBUG);
        if(! $nextGenerateSequence) {
            $this->log("generate_".$moduleString."No($refid,{$partner->code}) - the redis key not found.  Generating total orders from DB", SNAP_LOG_DEBUG);
            $utcStartOfDay = new \DateTime($now->format('Y-m-d 00:00:00'));
            $utcStartOfDay = \Snap\common::convertUserDatetimeToUTC($utcStartOfDay);
            //Can't find the key.  We will have to rebuild it.
            if (!$orderQueue){
                // order table
                $totalDayOrders = $generateStore->searchTable()->select()->where('createdon', '>=', $utcStartOfDay->format('Y-m-d H:i:s'))->andWhere('type',$orderType)->count();
            }else{
                // future order table
                $totalDayOrders = $generateStore->searchTable()->select()->where('createdon', '>=', $utcStartOfDay->format('Y-m-d H:i:s'))->andWhere('ordertype',$orderType)->count();
            }
            $this->log("generate_".$moduleString."No($refid,{$partner->code}) - total ".$moduleString." from DB = " . $totalDayOrders, SNAP_LOG_DEBUG);
            $cacher->set($generateNoKey, $totalDayOrders + 1, 86400);
            $nextGenerateSequence = $totalDayOrders + 1;
        }
        $nextGenerateSequence = strtoupper(sprintf($format, $companyBuy ? $generateNoPrefix_Buy : $generateNoPrefix_Sell, $now->format('ymd'), $nextGenerateSequence, $envPrefix));
        $this->log("generate_".$moduleString."No() - Generated sequence $nextGenerateSequence for ".$moduleString." $refid for partner {$partner->code}", SNAP_LOG_DEBUG);
        return $nextGenerateSequence;
    }

    public function xgenerateOrderNo($cacher, $partner, $product, $companyBuy, $refid, $orderQueue = false)
    {
        $queueString = '';
        $queueCap = '';
        if ($orderQueue == true){
            $queueString = 'Queue';
            $queueCap = 'F';
        }
        $prefix = $this->app->getConfig()->{'snap.generatenumber.prefix'};
        $queueCap = $prefix.$queueCap;

        $this->log("generateOrder".$queueString."No($refid, {$partner->code}) - into the method ", SNAP_LOG_DEBUG);
        $now = new \DateTime(gmdate('Y-m-d\TH:i:s'));
        $now = \Snap\common::convertUTCToUserDatetime($now);
        $orderNoKey = 'order'.$queueString.'No:' . $now->format('Ymd');
        $nextOrderSequence = $cacher->increment($orderNoKey, 1, 86400);
        $this->log("generateOrder".$queueString."No($refid,{$partner->code}) - The date used is " . $now->format('Y-m-d H:i:s') . " and key = " . $orderNoKey, SNAP_LOG_DEBUG);
        if(! $nextOrderSequence) {
            $this->log("generateOrder".$queueString."No($refid,{$partner->code}) - the redis key not found.  Generating total orders from DB", SNAP_LOG_DEBUG);
            $utcStartOfDay = new \DateTime($now->format('Y-m-d 00:00:00'));
            $utcStartOfDay = \Snap\common::convertUserDatetimeToUTC($utcStartOfDay);
            //Can't find the key.  We will have to rebuild it.
            if (!$orderQueue){
                $totalDayOrders = $this->app->orderStore()->searchTable()->select()->where('createdon', '>=', $utcStartOfDay->format('Y-m-d H:i:s'))->count();
            }else{
                $totalDayOrders = $this->app->orderQueueStore()->searchTable()->select()->where('createdon', '>=', $utcStartOfDay->format('Y-m-d H:i:s'))->count();
            }
            $this->log("generateOrder".$queueString."No($refid,{$partner->code}) - total orders".$queueString." from DB = " . $totalDayOrders, SNAP_LOG_DEBUG);
            $cacher->set($orderNoKey, $totalDayOrders + 1, 86400);
            $nextOrderSequence = $totalDayOrders + 1;
        }
        $nextOrderSequence = strtoupper(sprintf("%s%s%04d", $companyBuy ? $queueCap.'B' : $queueCap.'S', $now->format('ymd'), $nextOrderSequence));
        $this->log("generateOrder".$queueString."No() - Generated sequence $nextOrderSequence for order".$queueString." $refid for partner {$partner->code}", SNAP_LOG_DEBUG);
        return $nextOrderSequence;
    }

    public function sapPostSO($order, $product, $orgRequestParams) {
       $response = $this->app->apiManager()->sapPostSO($orgRequestParams['version'], $order, $product, $orgRequestParams);
       return $response;
    }
}
?>