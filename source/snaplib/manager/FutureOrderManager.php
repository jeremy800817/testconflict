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
use ReflectionClass;

class FutureOrderManager extends CommonOrdersManager implements IObservable, IObserver
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;

    const newPriceStreamQueue = '{FutureOrderQueue}:newPriceStream';
    const matchedOrderQueue = '{FutureOrderQueue}:matchedOrder';

    protected $app = null;

    // public function __construct($app, $reflectionClass)
    public function __construct($app)
    {
        $this->app = $app;
        // $this->reflectionClass = new ReflectionClass('CommonOrdersManager');
    }
    
    /**
     * Listen to the following events and update future order as appropriate:
     * 1)  Received new price data - Check for matching
     * 2)  
     *
     * @param  IObservable  $changed The initiating object
     * @param  IObservation $state   Change information
     * @return void
     */
    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        if($changed instanceof \Snap\manager\PriceManager && 
            $state->isNewAction() && 
            $state->target instanceof \Snap\object\PriceStream) {
            $this->log(__CLASS__." received notification on new price stream.  Adding to queue for matching", SNAP_LOG_DEBUG);
            $this->app->queueManager()->add(self::newPriceStreamQueue, $state->target->toCache());
        //Added by Devon on 2020/04/21 to implement observation for new orders created from the matched reference 
        } else if($changed instanceof \Snap\manager\SpotOrderManager && $state->isNewAction()) {
            //Register and ensure the future order can not be used anymore.
            if($state->otherParams['orderQueueObject'] instanceof \Snap\object\OrderQueue) {
                $spotOrder = $state->target;
                $orderQueue = $state->otherParams['orderQueueObject'];
                $orderQueue->orderid = $spotOrder->id;
                $orderQueue->status = OrderQueue::STATUS_FULLFILLED;
                $this->app->orderQueueStore()->save($orderQueue);
            }
        }
        //End add 2020/04/21
    }

    /**
     * Creates a future order for the system do matching against
     * 
     * @param  partner  $partner               The partner that is associated with this order
     * @param  string   $apiVersion            API version used to create the booking
     * @param  string   $refid                 Merchant side reference ID
     * @param  Enum     $trxType               Order transaction type (buy / sell)
     * @param  product  $product               The product in which the booking is made
     * @param  Enum     $orderType             Order made using weight or amount
     * @param  float    $orderValue            The order data either in weight or price
     * @param  float    $expectedMatchingPrice The price to match upon
     * @param  datetime $goodTillDate          The expiry date for this future order.  (Included date for matching)
     * @param  string   $notifyUrl             Url to confirm that the matching order is confirmed.
     * @param  string   $matchNotifyUrl        Url to inform the merchant when the transaction is matched.
     * @param  string   $reference             The reference information from merchant
     * @param  datetime $timeStamp             The timestamp
     * @return futureOrder
     */
    public function createFutureOrder($partner, $apiVersion, $futureRefid, $trxType, $product, $orderType, $orderValue, 
                     $expectedMatchingPrice, $goodTillDate, $notifyUrl, $matchNotifyUrl, $reference, $timeStamp, $effectiveOn = null)
    {
        // $product = $order->product;
        // $params = $order->params;

        $lockKey = '{SpotOrderBooking}:' . $partner->code;
        $cacher = $this->app->getCacher();
        $cacher->waitForLock($lockKey, 1, 60, 60);
        $this->app->getDbHandle()->beginTransaction();

        try{

            $now = new \DateTime();
            if(! $partner->status) {
                $this->log("Error in order $futureRefid hit error because partner {$partner->code} is not active", SNAP_LOG_ERROR);
                throw \Snap\api\exception\PartnerNotActiveException::fromTransaction($partner);
            }

            // START - trading schedule
            // if(! $this->reflectionClass->canTradingProceedNow($partner)) {
            if(! $this->canTradingProceedNow($partner)) {
                $this->log("Error in order queue $futureRefid for partner {$partner->code} can not proceed as it is disallowed by trading schedule", SNAP_LOG_ERROR);
                throw \Snap\api\exception\TradingHourOutOfBounds::fromTransaction($partner);
            }
            // END

            $companyBuy = (Order::TYPE_COMPANYBUY == $trxType ? true : false);
            //Ensure merchant can do booking for particular product
            if( ($companyBuy && ! $partner->canSell($product)) ||
                (! $companyBuy && ! $partner->canBuy($product))) {
                $this->log("Error in order $futureRefid for partner {$partner->code} unable to proceed due to lack of transaction permission", SNAP_LOG_ERROR);
                throw \Snap\api\exception\PartnerUnableToTransactionProduct::fromTransaction($partner, ['productCode' => $product->code]);
            }
            
            // transaction type
            if(! in_array($trxType, [Order::TYPE_COMPANYBUY, Order::TYPE_COMPANYSELL])) {
                $this->log("Error in order queue $futureRefid hit error because transaction type unknown $trxType", SNAP_LOG_ERROR);
                throw \Snap\api\exception\OrderTransactionUnrecognised::fromTransaction($partner, ['trxType' => $trxType]);
            }

            // check future ref id on order and futureorder, ensure is valid on both table
            $count = $this->app->orderQueueStore()->searchTable()
                        ->select(['id'])
                        ->where('partnerid', $partner->id)
                        ->andWhere('partnerrefid', $futureRefid)
                        ->count();
            if($count) {
                throw \Snap\api\exception\OrderDuplicatedException::fromTransaction($partner, ['partnerrefid' => $futureRefid, 'field' => 'refid']);
            }
            
            $transType = $trxType;
            //Ensure daily weight limit adherence (for spo buy only)
            $weight = ('weight' == strtolower($orderType)) ? $orderValue : $partner->calculator(false)->divide($orderValue, $expectedMatchingPrice);

            // order amount must denoted by product denomination
            if (!$product->denominationOrderChecking($weight)){
                throw \Snap\api\exception\OrderDenominationException::fromTransaction($partner, ['productDenomination' => $product->weight]);
            }

            //future order will SHARE limit with spot order, we will need to check daily limit settings.
            $productBuyLimit = $partner->getProductDailyBuyLimit($product);
            $productSellLimit = $partner->getProductDailySellLimit($product);
            if ( ($companyBuy && $productBuyLimit > 0) || (!$companyBuy && $productSellLimit > 0)) {
                $todayUsage = $this->getTotalTransactionWeight($partner, $product, $trxType);
                $productLimit = ($companyBuy ? $productBuyLimit : $productSellLimit);
                if ($productLimit < 0 || (($weight + $todayUsage) > $productLimit)){ 
                    // 0 limit = zero limit/no limit, -1 stop all trans
                    $this->log("Error in order $futureRefid for partner {$partner->code} unable to proceed due to exceeding product limit $productLimit.  Current order weight = $weight, ordered weight = $todayUsage", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\OrderTransactionLimitExceeded::fromTransaction($partner, [ 'product' => $product->code]);
                }
             }
             //Ensure transaction quantity within set limit
            if($partner->getProductClickMin($product, $companyBuy) > $weight || $partner->getProductClickMax($product, $companyBuy) < $weight) {
                $this->log("Error in order $futureRefid for partner {$partner->code} unable to proceed due to exceeding product click limit weight = $weight, click min = " . $partner->getProductClickMin($product, $companyBuy) . ", click max = ". $partner->getProductClickMax($product, $companyBuy), SNAP_LOG_ERROR);
                throw \Snap\api\exception\OrderTransactionLimitExceeded::fromTransaction($partner, [ 'product' => $product->code]);                
            }


            $orderTotalAmount = $partner->calculator()->multiply($weight, $expectedMatchingPrice);

            $byWeight = false;
            if ('weight'  == strtolower($orderType)){
                $byWeight = true;
            }
            // $queue_type = $transType; // PENDING -> notExist
            // if ($queue_type == OrderQueue::TYPE_DAY){
            //     $expire_on = strtotime(date("Y-m-d 23:59:59"));;
            // }
            // if ($queue_type == OrderQueue::TYPE_GOODTILLDATE){
            //     $expire_on = $goodTillDate;
            // }
            // if ($queue_type == OrderQueue::TYPE_GOODTILLCANCEL){
            //     $expire_on = null;
            // }
            if (empty($goodTillDate)){
                $now = new \DateTime();
                $now = \Snap\common::convertUTCToUserDatetime($now);
                $endAt = new \DateTime($now->format('Y-m-d 23:59:59'));
                // $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);
                // $expireon = $endAt->format("Y-m-d H:i:s");
                $expireon = $endAt;
                // user time, when sql will be utc coz its obj, str will not convert to utc 
            }else{
                $expireon = new \DateTime($goodTillDate);
            }
            
            $match_notify_url = ($matchNotifyUrl) ? $matchNotifyUrl : null;
        
            $newOrder = $this->app->orderQueueStore()->create([
                'partnerid' => $partner->id,
                'buyerid' => null, 
                'partnerrefid' => $futureRefid,
                'orderqueueno' => $this->generateOrderNo($cacher, $partner, $product, $companyBuy, $futureRefid, true),
                'salespersonid' => $partner->salespersonid,
                'apiversion' => $apiVersion,
                'ordertype' => $trxType,
                // 'queuetype' => $queue_type,  // PENDING, get on partner config, api not provided
                'productid' => $product->id,
                'effectiveon' => $effectiveOn,
                'expireon' => $expireon,
                'pricetarget' => $expectedMatchingPrice,
                'byweight' => $byWeight,
                'xau' => $weight, 
                'amount' => $orderTotalAmount,
                'remarks' => $reference,
                'notifyurl' => ($notifyUrl) ? $notifyUrl : null,
                'notifymatchurl' => $match_notify_url,
                'status' => OrderQueue::STATUS_PENDING,
            ]);
            $saveQueueOrder = $this->app->orderQueueStore()->save($newOrder);
            $observation = new \Snap\IObservation(
                $saveQueueOrder, 
                \Snap\IObservation::ACTION_NEW, 
                Order::STATUS_PENDING, 
                ['expectedmatchingpriceObject' => $expectedMatchingPrice]);
            $this->notify($observation);
            //Cleaning up
            $this->setTodayRemainingLimit($saveQueueOrder->partnerid, $saveQueueOrder->productid, $saveQueueOrder->ordertype, $weight);
            //Added by Devon on 2020/05/27 to notify matching strategy that a new future ordered has just been added. 
            $matchingStrategy = $this->app->priceProviderStore()
                                        ->getByField('pricesourceid', $partner->pricesourceid)
                                        ->orderMatchingStrategy();
            if($matchingStrategy) {
                $matchingStrategy->onNewFutureOrderReceived($this->app, $saveQueueOrder);
            }
            //End add 2020/05/27
            $this->app->getDbHandle()->commit();
            $cacher->unlock($lockKey);
            $this->log("Order Queue $futureRefid from merchant {$partner->code} has been successfully created.  ID = {$saveQueueOrder->id}", SNAP_LOG_DEBUG);

        }catch(\Exception $e){
            $cacher->unlock($lockKey);
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }

        return $saveQueueOrder;
    }

    /**
     * This method is to notify or confirm that the future order has been received and queued properly.
     * 
     * @param  futureOrder $futureOrder The future order to confirm
     */
    public function confirmCreateFutureOrder($futureOrder){

        $state = $this->getStateMachine($futureOrder);
        if (!$state->can(OrderQueue::STATUS_ACTIVE)){
            $this->log("confirmCreateFutureOrder({$futureOrder->orderno}):  Unable to proceed to confirm due to status", SNAP_LOG_ERROR);
            throw \Snap\api\exception\OrderInvalidAction::fromTransaction($futureOrder, ['action' => 'confirmation ']);
        }

        try{
            $this->app->getDbHandle()->beginTransaction();

            $futureOrder = $this->app->orderQueueStore()->getById($futureOrder->id);
            $oldStatus = $futureOrder->status;

            $futureOrder->status = OrderQueue::STATUS_ACTIVE;
            $updateOrderQueue = $this->app->orderQueueStore()->save($futureOrder);

            $observation = new \Snap\IObservation($updateOrderQueue, \Snap\IObservation::ACTION_CONFIRM, $oldStatus, [ ]);
            $this->notify($observation);
            $this->app->getDbHandle()->commit();
            $this->log(__METHOD__."({$futureOrder->orderno}) has been completed successfully.", SNAP_LOG_DEBUG);
        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }

        return $updateOrderQueue;
    }

    /**
     * This method is called when the future order has been matched.  It will also notify merchant of the event.
     * 
     * @param  futureOrder $futureOrder
     * @param  priceStream $priceStream
     */
    public function onFutureOrderMatched($futureOrder, $priceStream)
    {
        try {
            $this->app->getDbHandle()->beginTransaction();    
            $now = common::convertUTCToUserDatetime(new \DateTime());   
            $ifSIT  = $this->app->getConfig()->{'snap.environtment.development'};  
            $env    = ($ifSIT ? 'SIT' : 'LIVE');   
            $latestFutureOrder = $this->app->orderQueueStore()->getById($futureOrder->id);
            $state = $this->getStateMachine($latestFutureOrder);
            if($state->can(OrderQueue::STATUS_MATCHED)) {
                $partner = $this->app->partnerStore()->getById($latestFutureOrder->partnerid);
                if($partner->autocreatematchedorder) {
                    $latestFutureOrder->matchon = $now->format('Y-m-d H:i:s');
                    $latestFutureOrder->matchpriceid = $priceStream->id;
                    $latestFutureOrder->status = orderQueue::STATUS_MATCHED;
                    $this->app->orderQueueStore()->save($latestFutureOrder, ['matchon', 'matchpriceid', 'status']);
                    $lockedInPrice = Order::TYPE_COMPANYBUY == $latestFutureOrder->ordertype ? $priceStream->companybuyppg : $priceStream->companysellppg;
                    /*send email to notify match. to check match fo by GTP/others*/
                    //$emailSubject = "#####".$env."#####Future Order Matching Hit! - Non-MIB"; 
                    $emailSubject = "Future Order Matching Hit! - Non-MIB";  
                    $bodyEmail = "There are match future order for non-MBB.\n";
                    $bodyEmail .= "Future Order id : ".$latestFutureOrder->id." with future order no : ".$latestFutureOrder->orderqueueno."\n";
                    $bodyEmail .= "Pricestream id match is : ".$priceStream->id." with value of RM ".$lockedInPrice."\n\n";
                    /**/
                    //Automatically create a matched order.  Assumed no API calls.
                    $spotOrder = $this->app->spotOrderManager()->bookOrder(
                                        $partner, 
                                        'MANUAL', 
                                        $latestFutureOrder->partnerrefid, 
                                        $latestFutureOrder->ordertype, 
                                        $this->app->productStore()->getById($latestFutureOrder->productid), 
                                        0, 
                                        $latestFutureOrder->partnerrefid, 
                                        'weight', 
                                        $latestFutureOrder->xau,
                                        $latestFutureOrder->pricetarget, 
                                        $partner->calculator()->multiply($latestFutureOrder->xau, $latestFutureOrder->pricetarget), 
                                        '', 
                                        'Future Order Conversion for '.$latestFutureOrder->orderqueueno, 
                                        new \DateTime()
                                    );
                    $bodyEmail .= "Success create new order from future order matching.\n\n";
                    $this->app->getDbHandle()->commit();

                    // observation
                    $observation = new \Snap\IObservation(
                        $spotOrder, 
                        \Snap\IObservation::ACTION_NEW, 
                        Order::STATUS_PENDING, 
                        []);
                    $this->notify($observation);
                    $this->log("FUTURE_ORDER - Order object observation sent $refid from merchant {$partner->code} ", SNAP_LOG_DEBUG);
                } else {
                    $counter = 1;
                    $lockedInPrice = Order::TYPE_COMPANYBUY == $latestFutureOrder->ordertype ? $priceStream->companybuyppg : $priceStream->companysellppg;
                    $latestFutureOrder->matchon = $now->format('Y-m-d H:i:s');
                    $latestFutureOrder->matchpriceid = $priceStream->id;
                    $latestFutureOrder->status = orderQueue::STATUS_MATCHED; 
                    $sendMatchFO = $this->app->apiManager()->notifyMerchantMatchOrder($partner, $latestFutureOrder,$priceStream->id);

                    /*send email to notify match. to check match fo*/
                    $bodyEmail = "There are match future order for MBB.\n";
                    $bodyEmail .= "Future Order id : ".$latestFutureOrder->id." with future order no : ".$latestFutureOrder->orderqueueno."\n";
                    $bodyEmail .= "Pricestream id match is : ".$priceStream->id." with value of RM ".$lockedInPrice."\n\n";
                    /**/

                    if(0 == strlen($latestFutureOrder->notifyurl) || $sendMatchFO){
                        $this->log("OrderQueue id ".$latestFutureOrder->id." match with pricestream id ".$priceStream->id, SNAP_LOG_DEBUG); 
                        //$emailSubject = "#####".$env."#####Future Order Matching Hit!"; 
                        $emailSubject = "Future Order Matching Hit!"; 
                        $bodyEmail .= "Update futureorder record in progress.\n\n";
                        $bodyEmail .= "Success in save match future order.\n";
                    } else {
                        $this->log("OrderQueue id ".$latestFutureOrder->id." get error response.", SNAP_LOG_DEBUG);
                        while($counter <= 3){
                            $this->log("OrderQueue id ".$latestFutureOrder->id." ".$counter." attempts.", SNAP_LOG_DEBUG);
                            $attemptAgain = $this->app->apiManager()->notifyMerchantMatchOrder($partner, $latestFutureOrder,$priceStream->id);
                            if(!$attemptAgain) {
                                if($counter == 3){
                                    $this->log("OrderQueue id ".$latestFutureOrder->id." already trigger 3 attempts. Send out email error notification.", SNAP_LOG_DEBUG);
                                    //$emailSubject = "#####".$env."#####Future Order Matching Hit - Error notification! ";
                                    $emailSubject = "Future Order Matching Hit - Error notification! ";
                                    $bodyEmail .= "Match future order notification to MBB failed. Please contact support team for assistance. \n\n";
                                    $latestFutureOrder->status = orderQueue::STATUS_ACTIVE;
                                }
                            }
                            else {
                                $this->log("OrderQueue id ".$latestFutureOrder->id." get success response after ".$counter." attempts.", SNAP_LOG_DEBUG);
                                //$emailSubject = "#####".$env."#####Future Order Matching Hit!"; 
                                $emailSubject = "Future Order Matching Hit!";  
                                $bodyEmail .= "Update futureorder record in progress after ".$counter." attempts.\n\n";
                                $bodyEmail .= "Success in save match future order.\n";
                                break;
                            }
                            $counter++;
                        }
                    }
                    $this->app->orderQueueStore()->save($latestFutureOrder, ['matchon', 'matchpriceid','status']);
                    $this->app->getDbHandle()->commit();
                }
                $emailConfig = 'matchfo';
                $sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject,$emailConfig);
            } 
        } catch(\Exception $e) {
            $this->app->getDbHandle()->rollback();
            $this->log("Thrown exception @ ".__FILE__.":".__LINE__." while attempting to match future order {$futureOrder->orderqueueno} ({$futureOrder->id}) with message " . $e->getMessage() . ".  Exception type: " . get_class($e), SNAP_LOG_ERROR);
        }
    }

    /**
     * This method is used to cancel future order that has been previously confirmed.
     * 
     * @param  partner  $partner      The partner that is associated with this order
     * @param  string   $apiVersion   API version used to create the booking
     * @param  string   $refid        Merchant side reference ID
     * @param  string   $futureordId  Future Order ID
     * @param  string   $notifyUrl    Url to confirm that the matching order is confirmed.
     * @param  string   $reference    The reference information from merchant
     * @param  datetime $timeStamp    the timestamp
     * @return futureOrder  The future order that has been cancelled.
     */
    public function cancelFutureOrder($partner, $apiVersion = null, $refid = null, $futureordId = null, $notifyUrl = null, $reference = null, $timeStamp = null)
    {
        if ($refid){
            $futureOrder = $this->app->orderQueueStore()->searchTable()->select()
                ->where("partnerrefid", $refid)
                ->andWhere("partnerid", $partner->id)
                ->andWhere("status", 'in', [OrderQueue::STATUS_ACTIVE,OrderQueue::STATUS_PENDING])
                ->one();
        }
        if ($futureordId){
            $futureOrder = $this->app->orderQueueStore()->searchTable()->select()
                ->where("id", $futureordId)
                ->andWhere("partnerid", $partner->id)
                ->andWhere("status", 'in', [OrderQueue::STATUS_ACTIVE,OrderQueue::STATUS_PENDING])
                ->one();
        }
        if (!$futureOrder){
            $this->log("cancelFutureOrder({$refid}):  Unable to proceed to confirm due to not exist", SNAP_LOG_ERROR);
            throw \Snap\api\exception\OrderInvalidAction::fromTransaction('', ['action' => 'confirmation ']);
        }
        $state = $this->getStateMachine($futureOrder);
        if (!$state->can(OrderQueue::STATUS_PENDINGCANCEL)){
            $this->log("cancelFutureOrder({$futureOrder->orderqueueno}):  Unable to proceed to confirm due to not exist", SNAP_LOG_ERROR);
            throw \Snap\api\exception\OrderInvalidAction::fromTransaction($futureOrder, ['action' => 'confirmation ']);
        }

        try{
            $this->app->getDbHandle()->beginTransaction();
            $now = common::convertUTCToUserDatetime(new \DateTime());

            $oldStatus = $futureOrder->status;
            $futureOrder->status = OrderQueue::STATUS_PENDINGCANCEL;
            $futureOrder->cancelon = $now->format('Y-m-d H:i:s');
            $futureOrder->cancelby = defined('SNAPAPP_DBACTION_USERID') ? SNAPAPP_DBACTION_USERID : $this->app->getUsersession()->getUser()->id;
            
            $update = $this->app->orderQueueStore()->save($futureOrder);

            $observation = new \Snap\IObservation($update, \Snap\IObservation::ACTION_CANCEL, $oldStatus, []);
            $this->notify($observation);

            $this->app->getDbHandle()->commit();
        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }

        return $update;
    }

    /**
     * This method is used to notify about cancelled future is successful.
     * 
    * @param  futureOrder $futureOrder
     */
    public function confirmCanceledFutureOrder($futureOrder)
    {
        
        
        $state = $this->getStateMachine($futureOrder);
        if (!$state->can(OrderQueue::STATUS_CANCELLED)){
            $this->log("cancelFutureOrder({$futureOrder->orderqueueno}):  Unable to proceed to confirm due to not exist", SNAP_LOG_ERROR);
            throw \Snap\api\exception\OrderInvalidAction::fromTransaction($futureOrder, ['action' => 'confirmation ']);
        }

        $now = common::convertUTCToUserDatetime(new \DateTime());
        try{
            $this->app->getDbHandle()->beginTransaction();

            $oldStatus = $futureOrder->status;
            $futureOrder->status = OrderQueue::STATUS_CANCELLED;
            $futureOrder->cancelon = $now->format('Y-m-d H:i:s');
            $futureOrder->cancelby = defined('SNAPAPP_DBACTION_USERID') ? SNAPAPP_DBACTION_USERID : $this->app->getUsersession()->getUser()->id;

            $update = $this->app->orderQueueStore()->save($futureOrder);

            $this->setTodayRemainingLimit($update->partnerid, $update->productid, $update->ordertype, $update->xau, true);
            $observation = new \Snap\IObservation($update, \Snap\IObservation::ACTION_CANCEL, $oldStatus, []);
            $this->notify($observation);

            $this->app->getDbHandle()->commit();
        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
        
        return $update;
    }

    /**
     * Runs the background job to see if the new pricestream able to match any future orders.
     * 
     * @param  priceStream $priceStream The newly acquired price data
     */
    public function onReceivedNewPriceStreamData($priceStream, $provider = null)
    {
        if(! $provider) { 
            $provider = $this->app->priceProviderStore()->getById($priceStream->providerid);
        }
        $partners = $this->app->partnerStore()->searchTable()->select(['id'])->where('pricesourceid', $provider->pricesourceid)->execute();
        foreach ($partners as $partner){
            $partner_ids[] = $partner->id;
        }
        
        $this->log(__METHOD__."() - Notifying new data received in {$provider->name}({$provider->id}) with pricestream id {$priceStream->id}", SNAP_LOG_DEBUG);
        $matchingStrategy = $provider->orderMatchingStrategy();
        if(! $matchingStrategy) {
            if(0 != strlen($provider->futureorderstrategy)) {
                $this->log("The strategy class {$provider->futureorderstrategy} set in price provider {$provider->id} does not exists", SNAP_LOG_ERROR);
            } else {
                $this->log("The unable to find $matchingStrategy class for processing", SNAP_LOG_ERROR);
            }
            return;
        }
        //IF there is price stream data, assume is new price received.  Otherwise assume it is triggered on schedule
        if(null == $priceStream) {
            $continue = $matchingStrategy->canMatchOnTrigger($this->app, $provider);
        } else {
            $continue = $matchingStrategy->canMatchNewPrice($this->app, $provider, $priceStream);
        }
        if(!$continue) {
            return;
        }
        list($matchForBuy, $matchForSell, $matchPriceStream) = $matchingStrategy->getMatchFutureOrderConfig();
        if( ! $matchForBuy && ! $matchForSell) {
            return;  //No need to match the price stream.
        }
        $queueStore = $this->app->orderQueueStore();

        // bottom line to check if any incoming rotten price
        if ($matchPriceStream->companybuyppg <= 20 || $matchPriceStream->companysellppg <= 20){
            return;
        }

        $queueRes = $queueStore->searchView()->select()->where(function($q) Use ($matchPriceStream) {
                                // $q->where('pricesourceid', $matchPriceStream->pricesourceid)
                                  $q
                                  ->andWhere('effectiveon', '<=', \Snap\common::convertUserDatetimeToUTC($matchPriceStream->createdon)->format('Y-m-d H:i:s'))
                                  ->andWhere('expireon', '>=', \Snap\common::convertUserDatetimeToUTC($matchPriceStream->createdon)->format('Y-m-d H:i:s'))
                                  ->andWhere('status', OrderQueue::STATUS_ACTIVE);
                            });
        $queueRes->andWhere(function($q) Use($matchForBuy, $matchForSell, $matchPriceStream) {
            if($matchForBuy) {
                $q->orWhere(function($q2) Use ($matchPriceStream) {
                    $q2->where('pricetarget', '<=', $matchPriceStream->companybuyppg)
                       ->andWhere('ordertype', \Snap\object\Order::TYPE_COMPANYBUY);
                });
            }
            if($matchForSell) {
                $q->orWhere(function($q2) Use ($matchPriceStream) {
                    $q2->where('pricetarget', '>=', $matchPriceStream->companysellppg)
                       ->andWhere('ordertype', \Snap\object\Order::TYPE_COMPANYSELL);
                });
            }
        });
        $queueRes->andWhere('partnerid','IN', $partner_ids);
        $matches = $queueRes->execute();

        if(count($matches)) {
            $this->log(__METHOD__ ."():  Matched " . count($matches) . " from priceStream {$matchPriceStream->id}, buy: {$matchPriceStream->companybuyppg}, sell: {$matchPriceStream->companysellppg}", SNAP_LOG_DEBUG);
            foreach($matches as $aMatch) {
                $this->app->queueManager()->add(self::matchedOrderQueue, json_encode([ 'futureOrder' => $aMatch->toCache(), 'priceStream' => $matchPriceStream->toCache()]));
            }
        }
    }

    /**
     * This is the second background job to process the matched order to do notification and other tasks.
     */
    public function processReceivedPriceStreamData($aliveTime = 60)
    {
        $this->log("Executing processing of new price data for matching", SNAP_LOG_DEBUG);
        $startTime = time();
        $queueManager = $this->app->queueManager();
        while( $aliveTime >= (time() - $startTime)) {
            if($queueManager->count(self::newPriceStreamQueue)) {
                $priceStreamData = $queueManager->pop(self::newPriceStreamQueue);
                $priceStream = $this->app->priceStreamStore()->create();
                $priceStream->fromCache($priceStreamData);
                $this->onReceivedNewPriceStreamData($priceStream);
                usleep(500);
            } else {
                usleep(2500);
            }
        }
    }

    /**
     * This is the second background job to process the matched order to do notification and other tasks.
     */
    public function processPriceMatchedFutureOrder($aliveTime = 60)
    {
        $this->log("Executing notification of matched price process", SNAP_LOG_DEBUG);
        $startTime = time();
        $queueManager = $this->app->queueManager();
        while( $aliveTime >= (time() - $startTime)) {
            if($queueManager->count(self::matchedOrderQueue)) {
                $matchedInfo = json_decode($queueManager->pop(self::matchedOrderQueue), true);
                $priceStream = $this->app->priceStreamStore()->create();
                $orderQueue = $this->app->orderQueueStore()->create();
                $priceStream->fromCache($matchedInfo['priceStream']);
                $orderQueue->fromCache($matchedInfo['futureOrder']);
                $this->log("Processing matched future order {$orderQueue->orderqueueno} (id: {$orderQueue->id})", SNAP_LOG_DEBUG);
                $this->onFutureOrderMatched($orderQueue, $priceStream);
                usleep(500);
            } else {
                usleep(2500);
            }
        }
    }

    /**
     * This method will search through all active future orders and expire those that already past its date
     */
    public function expireOverdueOrders() {
        // cronjob on 00:00:00
        // run on 02-01-2020 00:00:00;
        $total_xau = 0;
        $now = new \DateTime();
        $expiredQueues = $this->app->orderQueueStore()->searchTable()->select()
            ->where('expireon', '<=', $now->format('Y-m-d H:i:s'))
            ->andWhere('status', orderQueue::STATUS_ACTIVE)
            ->execute();
        if ($expiredQueues){
            foreach($expiredQueues as $expired){
                $expired->status = OrderQueue::STATUS_EXPIRED;
                // $total_amount = $expired->amount;
                $this->app->orderQueueStore()->save($expired);
                $this->setTodayRemainingLimit($expired->partnerid, $expired->productid, $expired->ordertype, $expired->xau, true);
            }
        }
        
    }


    

    /**
     * This method will return the order state machine to manage the different states of the order process.
     * 
     * @return Finite/StateMachine/StateMachine 
     */
    public function getStateMachine($order) {
        $stateMachine = new \Finite\StateMachine\StateMachine;
        $config       = [
            'property_path' => 'status',
            'states' => [
                OrderQueue::STATUS_PENDING      => [ 'type' => 'initial', properties => []],
                OrderQueue::STATUS_ACTIVE       => [ 'type' => 'normal', properties => []],
                OrderQueue::STATUS_PENDINGCANCEL=> [ 'type' => 'normal', properties => []],
                OrderQueue::STATUS_CANCELLED    => [ 'type' => 'normal', properties => []],
                OrderQueue::STATUS_MATCHED      =>  [ 'type' => 'normal', properties => []],
                OrderQueue::STATUS_FULLFILLED   => [ 'type' => 'final', properties => []],
            ],
            'transitions' => [
                OrderQueue::STATUS_ACTIVE       => [ 'from' => [ OrderQueue::STATUS_ACTIVE, OrderQueue::STATUS_PENDING ], 'to' => OrderQueue::STATUS_ACTIVE ],
                OrderQueue::STATUS_PENDINGCANCEL=> [ 'from' => [ OrderQueue::STATUS_PENDING, OrderQueue::STATUS_ACTIVE], 'to' => OrderQueue::STATUS_PENDINGCANCEL ],
                OrderQueue::STATUS_CANCELLED    => [ 'from' => [ OrderQueue::STATUS_PENDINGCANCEL ], 'to' => OrderQueue::STATUS_CANCELLED ],
                OrderQueue::STATUS_MATCHED      => [ 'from' => [ OrderQueue::STATUS_ACTIVE ], 'to' => OrderQueue::STATUS_MATCHED ],
                OrderQueue::STATUS_FULLFILLED   => [ 'from' => [ OrderQueue::STATUS_MATCHED ], 'to' => OrderQueue::STATUS_FULLFILLED ],
            ]
        ];
        $loader = new \Finite\Loader\ArrayLoader($config);
        $loader->load($stateMachine);
        $stateMachine->setStateAccessor(new \Finite\State\Accessor\PropertyPathStateAccessor($config['property_path']));
        $stateMachine->setObject($order);
        $stateMachine->initialize();
        return $stateMachine;
    }
}
?>