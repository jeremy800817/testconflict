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
Use \Snap\object\Partner;
Use \Snap\object\Product;
Use \Snap\object\TradingSchedule;
Use \Snap\object\PriceStream;
Use \Snap\object\PriceValidation;
Use \Snap\common;
use \Snap\object\AchAdditionalData;
use \Snap\object\OtcPricingModel;

/*spreadsheet/excel*/
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpotOrderManager extends CommonOrdersManager implements IObservable
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;
    
    protected $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Manually book an order
     * @param  Partner $partner    The partner that is associated with this order
     * @param  Enum    $trxType    Order transaction type (buy / sell)
     * @param  Product $product    The product in which the booking is made
     * @param  Enum    $orderType  Order made using weight or amount
     * @param  decimal $orderValue The order data either in weight or price.
     * @return Order object.
     */
    public function bookManualOrder(partner $partner, $trxType, product $product, $orderType, $orderValue)
    {
    }

    /**
     * Standard booking order interface.  This will be used by the API as well as other process to do the main
     * booking.  This method will create a new order object and then follow the ordering process going through 
     * its preferred workflow.
     * 
     * @param  partner   $partner        The partner that is associated with this order`
     * @param  String    $apiVersion     API version used to create the booking
     * @param  String    $refid          Merchant side reference ID
     * @param  Enum      $trxType        Order transaction type (buy / sell)
     * @param  product   $product        The product in which the booking is made
     * @param  Numnber   $priceRequestId The price request ID referenced for this order
     * @param  String    $futureOrderRef The future order ID that is linked to this order
     * @param  [type]    $orderType      Order made using weight or amount
     * @param  Decimal   $orderValue     The order data either in weight or price
     * @param  Decimal   $lockedinPrice  The price that is used
     * @param  Decimal   $orderTotal     Total amount due
     * @param  String    $notifyUrl      URL to notify when order is successfully executed
     * @param  String    $reference      The reference information from merchant
     * @param  Datetime  $timeStamp      Datetime when the order is created.
     * @return Order
     */
    public function bookOrder(partner $partner, $apiVersion, $refid, $trxType, product $product, $priceRequestId, $futureOrderRef, $orderType, $orderValue, $lockedinPrice, $orderTotal, $notifyUrl, $reference, $timeStamp, $unlimited = false, $campaignCode = '', $memberType = '')
    // public function bookOrder($order)
    {
        $this->log("into SpotOrderManager::bookOrder({$partner->code}, $apiVersion, $refid, $trxType, {$product->code}, $priceRequestId, $futureOrderRef, $orderType, $orderValue, $lockedinPrice, $orderTotal, $notifyUrl, $reference, $timeStamp) method", SNAP_LOG_DEBUG);

        //Serialise the requests per merchant base.  I.e. if more than 2 requests coming in for same merchant, we do it one by one.
        $lockKey = '{SpotOrderBooking}:' . $partner->code;
        $cacher = $this->app->getCacher();
        $cacher->waitForLock($lockKey, 1, 60, 60);
        $alreadyInTransaction = $this->app->getDbHandle()->inTransaction();
        if(! $alreadyInTransaction) {
            $this->app->getDbHandle()->beginTransaction();
        }
        try {
            //Initialise variables & pre-checking
            // START check spot/futuer
            $isSpot = (null == $futureOrderRef || 0 == strlen($futureOrderRef)) ? true : false;
            $companyBuy = (Order::TYPE_COMPANYBUY == $trxType ? true : false);
            $now = new \DateTime();
            if(! $partner->status) {
                $this->log("Error in order $refid hit error because partner {$partner->code} is not active", SNAP_LOG_ERROR);
                throw \Snap\api\exception\PartnerNotActiveException::fromTransaction($partner);
            }
            //Ensure ordering mode compliance
            if(('MANUAL' != $apiVersion && !in_array($partner->orderingmode, [Partner::MODE_API, Partner::MODE_BOTH])) ||
                ('MANUAL' == $apiVersion && !in_array($partner->orderingmode, [Partner::MODE_WEB, Partner::MODE_BOTH]))) {
                $this->log("Error in order $refid hit error because partner {$partner->code} does not have the required ordering mode ($apiVersion)", SNAP_LOG_ERROR);
                throw \Snap\api\exception\PartnerOrderModeMismatched::fromTransaction($partner);
            }
            //Ensure merchant can do booking for particular product
            if( ($companyBuy && ! $partner->canSell($product)) ||
                (! $companyBuy && ! $partner->canBuy($product))) {
                $this->log("Error in order $refid for partner {$partner->code} unable to proceed due to lack of transaction permission", SNAP_LOG_ERROR);
                throw \Snap\api\exception\PartnerUnableToTransactionProduct::fromTransaction($partner, ['productCode' => $product->code]);
            }
            //Ensure no duplicated refid for partner
            $count = $this->app->orderStore()
                        ->searchTable()
                        ->select(['id'])
                        ->where('partnerid', $partner->id)
                        ->andWhere('partnerrefid', $refid)
                        ->count();
            if($count) {
                throw \Snap\api\exception\OrderDuplicatedException::fromTransaction($partner, ['partnerrefid' => $refid, 'field' => 'refid']);
            }
            //Ensure transaction type valid
            if(! in_array($trxType, [Order::TYPE_COMPANYBUY, Order::TYPE_COMPANYSELL, Order::TYPE_COMPANYBUYBACK])) {
                $this->log("Error in order $refid hit error because transaction type unknown $trxType", SNAP_LOG_ERROR);
                throw \Snap\api\exception\OrderTransactionUnrecognised::fromTransaction($partner, ['trxType' => $trxType]);
            }
            //Ensure it is trading time now
            if(! $this->canTradingProceedNow($partner)) {
                $this->log("Error in order $refid for partner {$partner->code} can not proceed as it is disallowed by trading schedule", SNAP_LOG_ERROR);
                throw \Snap\api\exception\TradingHourOutOfBounds::fromTransaction($partner);
            }

            $fees = ($companyBuy ? $partner->getRefineryFee($product) : $partner->getPremiumFee($product));

            //Ensure daily weight limit adherence (for spo buy only)
            $weight = ('weight' == strtolower($orderType)) ? $orderValue : $partner->calculator(false)->divide($orderValue, $lockedinPrice + ($fees));

            // order amount must denoted by product denomination (gtp core)
            if (!$product->denominationOrderChecking($weight)){
                throw \Snap\api\exception\OrderDenominationException::fromTransaction($partner, ['productDenomination' => $product->weight]);
            }

            //Only for spot buy (not future order) we will need to check daily limit settings.
            $productBuyLimit = $partner->getProductDailyBuyLimit($product);
            $productSellLimit = $partner->getProductDailySellLimit($product);
            if ( $isSpot && (($companyBuy && $productBuyLimit > 0) || (!$companyBuy && $productSellLimit > 0))) {
                $todayUsage = $this->getTotalTransactionWeight($partner, $product, $trxType);
                $productLimit = ($companyBuy ? $productBuyLimit : $productSellLimit);
                if ($productLimit < 0 || (($weight + $todayUsage) > $productLimit)){ 
                    // 0 limit = zero limit/no limit, -1 stop all trans
                    $this->log("Error in order $refid for partner {$partner->code} unable to proceed due to exceeding product limit $productLimit.  Current order weight = $weight, ordered weight = $todayUsage", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\OrderTransactionLimitExceeded::fromTransaction($partner, [ 'product' => $product->code]);
                }
            }
            if (!$unlimited){
                //Ensure transaction quantity within set limit
                if($partner->getProductClickMin($product, $companyBuy) > $weight || $partner->getProductClickMax($product, $companyBuy) < $weight) {
                    $this->log("Error in order $refid for partner {$partner->code} unable to proceed due to exceeding product click limit weight = $weight, click min = " . $partner->getProductClickMin($product, $companyBuy) . ", click max = ". $partner->getProductClickMax($product, $companyBuy), SNAP_LOG_ERROR);
                    throw \Snap\api\exception\OrderTransactionLimitExceeded::fromTransaction($partner, [ 'product' => $product->code]);                
                }
            }else{
                $reference = 'Unlimited order - '.$reference;
            }
            //Get the price object (PriceStream / PriceValidation) and timing validity of data
            $gtpReferencePrice = 0;
            $future_order = null;
            if(! $isSpot) {
                // check future_ref_id 
                $future_order = $this->app->orderQueueStore()
                                    ->searchTable()
                                    ->select()
                                    ->where('partnerrefid', $futureOrderRef)
                                    ->andWhere('status', OrderQueue::STATUS_MATCHED)
                                    ->one();
                // if ($future_order->matchpriceid != $price_id){
                //     $this->log("Error in order $refid for partner {$partner->code} invalid future spot booking ($future_order)", SNAP_LOG_ERROR);
                //     throw \Snap\api\exception\FutureOrderIdMismatched::fromTransaction($partner, [ 'futureorderref' => $futureOrderRef]);
                // }
                if(!$future_order || 0 == $future_order->id || $future_order->partnerid != $partner->id) {
                    $this->log("Error in order $refid for partner {$partner->code} invalid future spot booking ($future_order)", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\FutureOrderIdMismatched::fromTransaction($partner, [ 'futureorderref' => $futureOrderRef]);
                }
                if(0 < $future_order->orderid) {
                    $this->log("Error in order $refid for partner {$partner->code} due to ($future_order) has been matched", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\FutureOrderIdMismatched::fromTransaction($partner, [ 'futureorderref' => $futureOrderRef]);
                }
                $price = $this->app->priceStreamStore()->getById($future_order->matchpriceid);
                $gtpReferencePrice = $future_order->pricetarget;
            } else {
                //Support for PriceStream and PriceValidation prices.  
                $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'), $this->app->getUserTimeZone());
                $store = (preg_match('/^PV.*/', $priceRequestId) ? $this->app->priceValidationStore() : $this->app->priceStreamStore());
                
                $price = $store->searchTable()->select()->where('uuid', $priceRequestId)->one();
                
                if(! $price) {
                        $this->log("Error in order $refid for partner {$partner->code} because system unable to find the corresponding price id $priceRequestId in store " . $store->getTableName(), SNAP_LOG_ERROR);
                        throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId]);                
                }
                if($price instanceof PriceValidation) { //Price validation checking
                    $gtpReferencePrice = $price->price;
                    if($price->partnerid != $partner->id) { //must be generated by same partner
                        $this->log("Error in order $refid for partner {$partner->code}({$partner->id}) due to ($future_order) has been matched priceValidation partner id {$price->partnerid} mismatched", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->validtill->format('Y-m-d H:i:s')]);
                    } else if($price->validtill <= $now) { //not expired yet.
                        $this->log("Error in order $refid for partner {$partner->code} due to priceValidation validtill = ".$price->validtill->format('Y-m-d H:i:s').", current time = ".$now->format('Y-m-d H:i:s'), SNAP_LOG_ERROR);
                        throw \Snap\api\exception\OrderPriceDataExpired::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->validtill->format('Y-m-d H:i:s')]);
                    } else if(0 < $price->orderid) { //not used yet
                        $this->log("Error in order $refid for partner {$partner->code} due to priceValidation has been utilised by another order {$price->orderid}", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->validtill->format('Y-m-d H:i:s')]);
                    }
                    
                    if( (PriceValidation::REQUEST_COMPANYBUY == $price->requestedtype && ! $companyBuy) ||
                        (PriceValidation::REQUEST_COMPANYSELL == $price->requestedtype && $companyBuy)) { //mismatch of order with price validation
                        $this->log("Error in order $refid for partner {$partner->code} due price validation requestType does not match order type", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->validtill->format('Y-m-d H:i:s')]);
                    }
                } else {  //price stream.  Have to check.
                    
                    $provider = $this->app->priceProviderStore()->getById($price->providerid);
                    
                    if($provider->pricesourceid != $partner->pricesourceid) { //same price source
                        $this->log("Error in order $refid for partner {$partner->code} due to priceStream price source is different from partner price source", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->createdon->format('Y-m-d H:i:s')]);                    
                    }
                    $priceEffectiveUntil = $price->createdon->getTimeStamp() + $partner->orderconfirmallowance;
                    if(time() > $priceEffectiveUntil) { //already expired
                        $this->log("Error in order $refid for partner {$partner->code} due to priceStream expired = ".gmdate('Y-m-d H:i:s', $priceEffectiveUntil).", current time = ".gmdate('Y-m-d H:i:s', time()), SNAP_LOG_ERROR);
                        throw \Snap\api\exception\OrderPriceDataExpired::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->createdon->format('Y-m-d H:i:s')]);
                    }
                }
            }
            
            //If it is PriceStream object, then have to get right data for price
            if(0 == $gtpReferencePrice) {
                $gtpReferencePrice = ($companyBuy ? $price->companybuyppg : $price->companysellppg);
                $gtpReferencePrice = $partner->calculator()->round($gtpReferencePrice); // CAUTION_ REF_5 all calculator mode must correct
            }
            //Ensure system price is the same as price provided by merchant
            // CAUTION_ REF_5 must round before pass in
            if($lockedinPrice != $gtpReferencePrice) {
                if ($partner->calculator()->round($lockedinPrice) != $gtpReferencePrice){
                    $this->log("Error in order $refid for partner {$partner->code} due to gtp reference price $gtpReferencePrice is not the same as API provided lockedin price $lockedinPrice", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId]);                    
                }
            }

            // ensure no weird price
            if ($gtpReferencePrice <= 20){
                $this->log("Error in order $refid for partner {$partner->code} due to gtp reference price $gtpReferencePrice is less than 20", SNAP_LOG_ERROR);
                throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId]);
            }
            
            // $order_total_amount = $orderTotal + $fees; // we need this? $orderValue included fees?
            $userId = $this->app->getUserSession()->getUser() ? $this->app->getUserSession()->getUser()->id : 0;
            $provider = $this->app->priceProviderStore()->getForPartnerByProduct($partner, $product);
            $bookingPriceObj = $this->app->priceManager()->getLatestSpotPrice($provider, $refid);
            $bookingPrice = ($companyBuy ? $bookingPriceObj->companybuyppg : $bookingPriceObj->companysellppg);
            $bookingPrice = $partner->calculator()->round($bookingPrice);
            $this->log("bookingprice $refid from merchant {$partner->code} has been successfully calculated.  value = {$bookingPrice}", SNAP_LOG_DEBUG);

            $partnerServices_product = $partner->getServiceForProduct($product);
            $specialDiscountCondition = $partnerServices_product->specialpricetype;
            if ($specialDiscountCondition != \Snap\object\PartnerService::SPECIALTYPE_NONE && $isSpot){
                $discountPriceReturn = $this->specialPriceOffsetDiscount($partner, $product, $trxType, $gtpReferencePrice, $partner->calculator()->multiply($weight, $gtpReferencePrice), $weight);
                if (!$discountPriceReturn){
                    $discountInfo = '';
                    $discountPrice = 0;
                }else{
                    $discountInfo = [
                        'order_transaction_type' => $trxType,
                        'specialprice_type' => $discountPriceReturn['specialprice_type'],
                        'specialprice_condition' => $discountPriceReturn['specialprice_condition'],
                        'specialprice_offset' => $discountPriceReturn['specialprice_offset'],
                    ];
                    $discountInfo = json_encode($discountInfo);
                    $discountPrice = $discountPriceReturn['discountprice'];
                }
            }else{
                $discountInfo = '';
                $discountPrice = 0;
            }
			
			//pricing model discount
			if ($this->app->getStore('otcpricingmodel')) {
				$discountPriceReturn = $this->otcPricingModelDiscount($price, $campaignCode, $memberType, $weight, $gtpReferencePrice, $fees, $partner, $trxType);
				if (!$discountPriceReturn){
                    $discountInfo = '';
                    $discountPrice = 0;
                }else{
                    $discountInfo = [
                        'order_transaction_type' => $trxType,
                        'discountprice' => $discountPriceReturn['discountprice'],
                        'discounttype' => $discountPriceReturn['discounttype'],
                        'discountcode' => $discountPriceReturn['discountcode'],
                        'discountname' => $discountPriceReturn['discountname'],
                        'discountid' => $discountPriceReturn['discountid']
                    ];
                    $discountInfo = json_encode($discountInfo);
                    $discountPrice = $discountPriceReturn['discountprice'];
                }
			}

            $_now = new \DateTime();
            $_now = \Snap\common::convertUTCToUserDatetime($_now);
            $newOrder = $this->app->orderStore()->create([
                'partnerid' => $partner->id,
                'buyerid' => $userId,
                'partnerrefid' => $refid,
                'orderno' => $this->generateOrderNo($cacher, $partner, $product, $companyBuy, $refid, $order),
                'pricestreamid' => ($price instanceof PriceValidation) ? $price->pricestreamid : $price->id,
                'salespersonid' => $partner->salespersonid,
                'apiversion' => $apiVersion,
                'type' => $trxType,
                'productid' => $product->id,
                'isspot' => $isSpot,
                'price' => $gtpReferencePrice,
                'byweight' => (strtolower($orderType) == 'weight') ? 1  : 0,
                'xau' => $weight,
                'amount' => $partner->calculator()->multiply($weight, $gtpReferencePrice + ($fees) + ($discountPrice)),
                'fee' => $fees,
                'remarks' => $reference,
                'bookingon' => $_now,
                'bookingprice' => $bookingPrice,
                'bookingpricestreamid' => $bookingPriceObj->id,
                'notifyurl' => $notifyUrl,
                'reconciled' => 0, 
                'status' => Order::STATUS_PENDING,
                'discountinfo' => $discountInfo, // string _ info about discount, all info (json)
                'discountprice' => $discountPrice // float _ value of discount on goldprice eg_companysell: 250 (-2), default 0
            ]);
            $this->log("Order object $refid from merchant {$partner->code} has been successfully prepared.", SNAP_LOG_DEBUG);
            $saveOrder = $this->app->orderStore()->save($newOrder);
            $this->log("Order object $refid from merchant {$partner->code} has been successfully execute SQL.  unknown return", SNAP_LOG_DEBUG);
            $observation = new \Snap\IObservation(
                                    $saveOrder, 
                                    \Snap\IObservation::ACTION_NEW, 
                                    Order::STATUS_PENDING, 
                                    ['priceObject' => $price, 'orderQueueObject' => $future_order, 'bookingPriceObject' => $bookingPriceObj]);
            $this->notify($observation);
            $this->log("Order object observation sent $refid from merchant {$partner->code} ", SNAP_LOG_DEBUG);
            //Cleaning up
            $this->setTodayRemainingLimit($saveOrder->partnerid, $saveOrder->productid, $saveOrder->type, $weight, false, !$isSpot);
            $this->log("Order $refid from merchant {$partner->code} setTodayRemainingLimit executed. ", SNAP_LOG_DEBUG);
            if(! $alreadyInTransaction) {
                $this->app->getDbHandle()->commit();
                $this->log("Order $refid from merchant {$partner->code} SQL committed ", SNAP_LOG_DEBUG);
            }
            $cacher->unlock($lockKey);
            $this->log("Order $refid from merchant {$partner->code} has been successfully created.  ID = {$saveOrder->id}", SNAP_LOG_DEBUG);
        } catch(\Exception $e) {
            $cacher->unlock($lockKey);
            if(! $alreadyInTransaction && $this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;  //rethrow the data
        }
        return $saveOrder;
    }

    /**
     * This method is used to set the order into confirmed status.  This will also sent the request internally to SAP
     * for processing
     * 
     * @param  Order $order  The order to be confirmed
     * @return Order
     */
    public function confirmBookOrder($order)
    {
        // CAUTION booking order will not valid if PRICE_REQUEST_ID is used for CORE
        // price request id is consider as booking ID in aspects;
        $this->log(__METHOD__."({$order->orderno}) initialized.", SNAP_LOG_DEBUG);
        $state = $this->getStateMachine($order);
        if (!$state->can(Order::STATUS_CONFIRMED)){
            $this->log("confirmBookOrder({$order->orderno}):  Unable to proceed to confirm due to status", SNAP_LOG_ERROR);
            throw \Snap\api\exception\OrderInvalidAction::fromTransaction($order, ['action' => 'confirmation ']);
        }
        try {
            $order = $this->app->orderStore()->getById($order->id);  //get latest copy unchanged
            $this->log(__METHOD__."({$order->orderno}) init sapBookNewOrder START.", SNAP_LOG_DEBUG);
            $sap_return = $this->app->apiManager()->sapBookNewOrder($order);
            $this->log(__METHOD__."({$order->orderno}) init sapBookNewOrder END.", SNAP_LOG_DEBUG);
            $this->app->getDbHandle()->beginTransaction();

            // SAP_API will update sap_order_no, sap_order_ref, etc..
            if ('Y' == $sap_return[0]['success']) {
                $_now = new \DateTime();
                $_now = \Snap\common::convertUTCToUserDatetime($_now);
                // $provider = $this->app->priceProviderStore()->getForPartnerByProduct($order->getPartner(), $order->getProduct());
                // $confirmPriceObj = $this->app->priceManager()->getLatestSpotPrice($provider);
                $oldStatus = $order->status;
                $order->status = Order::STATUS_CONFIRMED;
                $order->confirmon = $_now;
                // $order->confirmprice = ($order->isCompanyBuy() ? $confirmPriceObj->companybuyppg : $confirmPriceObj->companysellppg);
                $order->confirmby = defined('SNAPAPP_DBACTION_USERID') ? SNAPAPP_DBACTION_USERID : $this->app->getUsersession()->getUser()->id;;
                // $order->confirmpricestreamid = $confirmPriceObj->id;
                $updateOrder = $this->app->orderStore()->save($order);
            }else{
                $this->log("SAP Submission Failed", SNAP_LOG_ERROR);
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'SAP Submission failed: Unable to proceed order.']);
                //throw new \Exception('SAP Submission failed');
            }
            $confirmPriceObj = null;
            $observation = new \Snap\IObservation($updateOrder, \Snap\IObservation::ACTION_CONFIRM, $oldStatus, [ 'confirmPriceObject' => $confirmPriceObj ]);
            $this->notify($observation);
            $this->app->getDbHandle()->commit();
            $this->log(__METHOD__."({$order->orderno}) has been completed successfully.", SNAP_LOG_DEBUG);
        } catch(\Exception $e) {
            $this->log(__METHOD__."({$order->orderno}) encountered exception " . get_class($e) . " with message " . $e->getMessage(), SNAP_LOG_ERROR);
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;  //rethrow the exception
        }
        return $updateOrder;
    }

    /**
     * Operation to cancel the order provided.  The order must still be in processing mode for this method to run.
     * 
     * @param  Order    $order     Order object that is involved in this cancellation
     * @param  String   $notifuUrl URL to send to merchant when the cancellation is completed.
     * @param  String   $reference The reference information provided
     * @param  dateitme $timeStamp when the request is send,
     * @return Order
     */
    public function cancelOrder(Partner $partner, Order $order, $notifyUrl, $reference, $timeStamp, $forceCancel = false)
    {
        $state = $this->getStateMachine($order);
        if (!$state->can(Order::STATUS_PENDINGCANCEL)){
            $this->log(__METHOD__."({$order->orderno}) - status {$order->status} does not allow for cancel order", SNAP_LOG_ERROR);
            throw \Snap\api\exception\OrderInvalidAction::fromTransaction($order, ['action' => 'cancel ']);
        }
        if($partner->id != $order->partnerid) {
            $this->log(__METHOD__."({$order->orderno}) - partner {$partner->code} provided is not owner or order", SNAP_LOG_ERROR);
            throw \Snap\api\exception\OrderInvalidAction::fromTransaction($order, ['action' => 'cancel ']);
        }
        //Ensure appropriate permission to perform action.....needed here?
        $user = $this->app->getUsersession()->getUser();
        if(! $forceCancel && $user && $user->partnerid > 0 && ($user->partnerid != $order->partnerid || $user->id != $order->salespersonid)) {
            $this->log(__METHOD__."({$order->orderno}) - BO user partner {$partner->code} provided is not owner or order", SNAP_LOG_ERROR);
            throw \Snap\api\exception\OrderInvalidAction::fromTransaction($order, ['action' => 'cancel ']);            
        }
        //Check if has cancellation allowance expired.  0 means no expiry.
        if (! $forceCancel || $partner->ordercancelallowance > 0) {
            $expireOn = strtotime("+{$partner->ordercancelallowance} seconds");
            if(Order::STATUS_CONFIRMED == $order->status) {
                $orderDate = common::convertUserDatetimeToUTC($order->confirmon);
            } else {
                $orderDate = common::convertUserDatetimeToUTC($order->bookingon);
            }
            if($orderDate->getTimeStamp() > $expireOn) { //expired
                $this->log(__METHOD__."({$order->orderno}) - can not cancel due to cancellation allowance expired.  Order date = " . $orderDate->format('Y-m-d H:i:s').", allowance = {$partner->ordercancelallowance}s, now is " . gmdate('Y-m-d H:i:s') . " / $expireOn/ ".$orderDate->getTimeStamp(), SNAP_LOG_ERROR);
                throw \Snap\api\exception\OrderInvalidAction::fromTransaction($order);
            }
        }
        $product = $order->getProduct();
        $provider = $this->app->priceProviderStore()->getForPartnerByProduct($partner, $product);
        $cancellationPriceObj = $this->app->priceManager()->getLatestSpotPrice($provider);
        $now = common::convertUTCToUserDatetime(new \DateTime());

        try {
            $this->app->getDbHandle()->beginTransaction();
            $order = $this->app->orderStore()->getById($order->id);
            $oldStatus = $order->status;
            $order->cancelon = $now->format('Y-m-d H:i:s');
            $order->cancelby = defined('SNAPAPP_DBACTION_USERID') ? SNAPAPP_DBACTION_USERID : $this->app->getUsersession()->getUser()->id;
            $order->cancelpricestreamid = $cancellationPriceObj->id;
            $order->cancelprice = ($order->isCompanyBuy ? $cancellationPriceObj->companybuyppg : $cancellationPriceObj->companysellppg);
            $order->cancelprice = ($order->isCompanyBuy() ? $cancellationPriceObj->companysellppg : $cancellationPriceObj->companybuyppg);

            $order->status = Order::STATUS_PENDINGCANCEL;
            $this->app->orderStore()->save($order);
            $observation = new \Snap\IObservation(
                                    $order, 
                                    \Snap\IObservation::ACTION_CANCEL, 
                                    $oldStatus, 
                                    [ 'priceObject' => $cancellationPriceObj ]);
            $this->notify($observation);
            $this->app->getDbHandle()->commit();
        } catch (\Exception $e) {
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
        return $order;
    }

    /**
     * This method is used to confirm or notify that the cancel order process has completed.
     * 
     * @param  Order  $order Order object that is involved in this cancellation
     */
    public function confirmCancelOrder($order){

        $state = $this->getStateMachine($order);
        if (!$state->can(Order::STATUS_CANCELLED)){
            $this->log(__METHOD__."({$order->orderno}) - status {$order->status} does not allow for confirm cancel order", SNAP_LOG_ERROR);
            throw \Snap\api\exception\OrderInvalidAction::fromTransaction($order, ['action' => 'confirmcancel ']);
        }
        try {
            $this->app->getDbHandle()->beginTransaction();
            $order = $this->app->orderStore()->getById($order->id);
            // $sap_return = $this->app->apiManager()->sapCancelOrder($order);
            // // SAP_API will update sap_order_no, sap_order_ref, etc..
            // if ('Y' == $sap_return[0]['success']) {
            //     $oldStatus = $order->status;
            //     $order->status = Order::STATUS_CANCELLED;
            //     $updateOrder = $this->app->orderStore()->save($order);
            // }
            // else{
            //     $this->log("SAP Submission Failed", SNAP_LOG_ERROR);
            //     throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'SAP Submission failed: Unable to proceed order']);
            //     /*throw new \Exception('SAP Submission failed');*/
            // }
            $oldStatus = $order->status;
            $order->status = Order::STATUS_CANCELLED;
            $updateOrder = $this->app->orderStore()->save($order);
            $this->app->getDbHandle()->commit();
            $this->setTodayRemainingLimit($order->partnerid, $order->productid, $order->type, $order->xau, true);
            $observation = new \Snap\IObservation($updateOrder, \Snap\IObservation::ACTION_CANCEL, $oldStatus, []);
            $this->notify($observation);
            $this->log(__METHOD__."({$order->orderno}) has been cancelled successfully.", SNAP_LOG_DEBUG);  
        } catch(\Exception $e) {
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
        return $updateOrder;
    }


     /**
     * Operation to cancel the order provided.  The order must still be in processing mode for this method to run.
     * 
     * @param  Order    $item     Selected items to be cancelled
     * @return Order
     */
    public function cancelOrderGTP($item)
    {
        
        // To be modified later with updated status for expired
        $now = common::convertUTCToUserDatetime(new \DateTime());

        try {
            $this->app->getDbHandle()->beginTransaction();
            //$this->app->getDbHandle()->beginTransaction();
            $order = $this->app->orderStore()->getById($item);
            //$oldStatus = $order->status;
            $order->cancelon = $now->format('Y-m-d H:i:s');
            $order->cancelby = defined('SNAPAPP_DBACTION_USERID') ? SNAPAPP_DBACTION_USERID : $this->app->getUsersession()->getUser()->id;
            //$order->cancelpricestreamid = $cancellationPriceObj->id;
            //$order->cancelprice = ($order->isCompanyBuy ? $cancellationPriceObj->companybuyppg : $cancellationPriceObj->companysellppg);
            //$order->cancelprice = ($order->isCompanyBuy() ? $cancellationPriceObj->companysellppg : $cancellationPriceObj->companybuyppg);
            if(Order::STATUS_PENDING != $order->status ){
                $this->log(__METHOD__."({$order->orderno}) - status {$order->status} does not allow for confirm cancel order", SNAP_LOG_ERROR);
                throw \Snap\api\exception\OrderInvalidAction::fromTransaction($order, ['action' => 'confirmcancel ']);
            }
            $order->status = Order::STATUS_EXPIRED;
            
            
            $this->app->orderStore()->save($order);
            $this->app->getDbHandle()->commit();
        } catch (\Exception $e) {
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
        return $order;
    }

    /**
     * Operation to print spot order details
     * 
     * @param  Order    $orderId        Order Id of purchase 
     * @param  Order    $customerid     Customer Id input to determine Spot Order Document Type (Spot Order/ Spot Order Special)
     * @return Order
     */
    public function printSpotOrder($orderId, $customerId = null)
    {

        try {
            
            if ($this->app->getUsersession()->getUser()->type != 'Customer'){
                $order = $this->app->orderStore()->getById($orderId);
            }else{
                $partnerId = $this->app->getUsersession()->getUser()->partnerid;
                $order = $this->app->orderStore()->searchTable()->select()
                    ->where('id', $orderId)
                    ->andWhere('partnerid', $partnerId)
                    ->one();
            }
            if (!$order){
                return false;
            }

            if(Order::TYPE_COMPANYBUY == $order->type){
                $finalAcePriceTitle = "ACE - Buy Order";
                $finalAcePriceLabel = "ACE Buy Final Price (RM/g)";
                $orderFeeLabel = "Refining Fee";
            }else if (Order::TYPE_COMPANYSELL == $order->type){
                $finalAcePriceTitle = "ACE - Sell Order";
                $finalAcePriceLabel = "ACE Sell Final Price (RM/g)";
                $orderFeeLabel = "Premium Fee";
            }else{
                $finalAcePriceTitle = "-";
                $finalAcePriceLabel = "-";
                $orderFeeLabel = "-";
            }
        
            $finalAcePrice = number_format(($order->price + ($order->fee)),3);
            $weight = number_format($order->xau,3);
            $totalEstValue = number_format($order->amount,3);
            $orderFee = number_format($order->fee,3);

            // Get customer name
            $customerId = $order->partnerid;
            $userobj = $this->app->partnerStore()->getById($customerId);
            $customername = $userobj->name;

            $product = $this->app->productStore()->getById($order->productid);

            // Get salesperson name
            if ($order->salespersonid && $order->salespersonid != 0){
                $salesperson = $this->app->userStore()->getById($order->salespersonid);
                $salespersonname = $salesperson->name;
            }else{
                $salespersonname = '-';
            }

            // $returnPdf = '
            
            // <table style="width: 100%;" >
            //     <tr>
            //         <td style="width: 60%;">
            //         <div style="font-weight:bold;font-size:16px;margin-bottom: 8px">'. $finalAcePriceTitle . '</div>
            //         <span><p>Order No. 						: '. $order->orderno . '</p></span>
            //         <span><p>Customer Name 					: '. $customername . '</p></span>
            //         <span><p>Salesperson					: '. $salespersonname . '</p></span>
            //         <span><p>Product						: '. $product->name . '</p></span>
            //         <span><p>'. $finalAcePriceLabel . '     : '. $finalAcePrice . '</p></span>
            //         <span><p>Total est. Value (RM) 		    : '. $totalEstValue . '</p></span>
            //         <span><p>Weight (g)			 		    : '. $weight . '</p></span>
            //         <span><p>Date				 		    : '. $order->createdon->format('Y-m-d H:i:s') . '</p></span>
                    
            //         </td>
                    
            //     </tr>
            // </table>
            // ';

            $returnPdf = '
            <div class="container">        
                <table>
                    <tr>
                        <td colspan=4 class="form-title" align="center"><b>BSN MYGOLD ACCOUNT- I</b></td>
                        <td colspan=4 align="right">Cawangan / Branch: "Branch Name"</td>
                    </tr>
                    <tr>
                        <td colspan=9 class="grey"><b>PENGESAHAN PESANAN PEMBELIAN EMAS / GOLD PURCHASE ORDER CONFIRMATION</b></td>			
                    </tr>
                    <tr>
                        <td colspan=9><b>MAKLUMAT PELANGGAN / APPLICANT’S DETAILS</b><td>		
                    </tr>
			
	

                </table>			
            </div>
            ';
            // <span><p>'. $orderFeeLabel . '          : '. $orderFee  . '</p></span>
            
			


        
        } catch (\Exception $e) {
           
            throw $e;
        }
        return $returnPdf;
    }

    /**
     * Operation to print spot order details for OTC
     * 
     * @param  Order    $orderId        Order Id of purchase 
     * @param  Order    $customerid     Customer Id input to determine Spot Order Document Type (Spot Order/ Spot Order Special)
     * @return Order
     */
    public function printSpotOrderOTC($orderId, $customerId = null)
    {

        try {
            
            if ($this->app->getUsersession()->getUser()->type != 'Customer'){
                $order = $this->app->orderStore()->getById($orderId);
            }else{
                $partnerId = $this->app->getUsersession()->getUser()->partnerid;
                $order = $this->app->orderStore()->searchTable()->select()
                    ->where('id', $orderId)
                    ->andWhere('partnerid', $partnerId)
                    ->one();
            }
            if (!$order){
                return false;
            }

            if(Order::TYPE_COMPANYBUY == $order->type){
                $finalAcePriceTitle = "SELL";
                $finalAcePriceTitle_BM = "PENJUALAN";
                $finalAcePriceTitle_desc = "sell";
                $finalAcePriceTitle_BM_desc = "jualan";

                //for bsn
                $details_title = "Sale";
                $details_BM_title = "Jualan";
                $details_desc_BM_title = "Penjualan";

                $finalAcePriceLabel = "Sell Final Price (RM/g)";
                $orderFeeLabel = "Refining Fee";
            }else if (Order::TYPE_COMPANYSELL == $order->type){
                $finalAcePriceTitle = "PURCHASE";
                $finalAcePriceTitle_BM = "PEMBELIAN";
                $finalAcePriceTitle_desc = "purchase";
                $finalAcePriceTitle_BM_desc = "pembelian";
                //for bsn
                $details_title = "Purchase";
                $details_BM_title = "Belian";
                $details_desc_BM_title = "Pembelian";

                $finalAcePriceLabel = "Purchase Final Price (RM/g)";
                $orderFeeLabel = "Premium Fee";
            }else{
                $finalAcePriceTitle = "-";
                $finalAcePriceLabel = "-";
                $orderFeeLabel = "-";
            }
        
            $finalAcePrice = number_format(($order->price + ($order->discountprice)),2);
            $weight = number_format($order->xau,3);
            $totalEstValue = number_format($order->amount,2);
            $orderFee = number_format($order->fee,2);

            // Get customer name
            $customerId = $order->partnerid;
            $userobj = $this->app->partnerStore()->getById($customerId);
            $customername = $userobj->name;

            // Get customer info and bank account info 
            
            $goldTx = $this->app->mygoldtransactionStore()->searchView()->select()
            ->where('orderid', $order->id)
            ->one();

            if($goldTx){
                $accountholdercode = $goldTx -> achcode;
                $fullname = $goldTx -> achfullname;
                $mykadno = $goldTx -> achmykadno;
                $nok_mykadno = $goldTx -> achnokmykadno;
            }

            $accountholder = $this->app->myaccountholderStore()->searchView()->select()
            ->where('accountholdercode', $accountholdercode)
            ->one();

            $product = $this->app->productStore()->getById($order->productid);

            // Get salesperson name
            if ($order->salespersonid && $order->salespersonid != 0){
                $salesperson = $this->app->userStore()->getById($order->salespersonid);
                $salespersonname = $salesperson->name;
            }else{
                $salespersonname = '-';
            }

            $data = [
                'date'                          => $order->createdon->format('Y-m-d H:i:s'),
                'partner_name'                  => $customername,
                'fullname'                      => $fullname,
                'accountholdercode'             => $accountholdercode,
                'finalAcePriceTitle'            => $finalAcePriceTitle,
                'finalAcePriceLabel'            => $finalAcePriceLabel,
                'finalAcePrice'                 => $finalAcePrice,
                'finalAcePriceTitle_BM'         => $finalAcePriceTitle_BM,
                'finalAcePriceTitle_desc'       => $finalAcePriceTitle_desc,
                'finalAcePriceTitle_BM_desc'    => $finalAcePriceTitle_BM_desc,
                'details_title'                 => $details_title,
                'details_BM_title'              => $details_BM_title,
                'details_desc_BM_title'         => $details_desc_BM_title,
                'xau'   	 	                => number_format($order->xau,3),
                'transactionid'                 => $order->orderno,
                'teller'                        => $salespersonname,
                'final_total'                   => $totalEstValue,
                'receipt_no'                    => $goldTx->refno,
                'casa_bankaccount'              => $accountholder->accountnumber,
                'mykad_no'                      => $mykadno,
                'nok_mykadno'                   => $nok_mykadno,
            ];

            return($data);

            // $returnPdf = '
            
            // <table style="width: 100%;" >
            //     <tr>
            //         <td style="width: 60%;">
            //         <div style="font-weight:bold;font-size:16px;margin-bottom: 8px">'. $finalAcePriceTitle . '</div>
            //         <span><p>Order No. 						: '. $order->orderno . '</p></span>
            //         <span><p>Customer Name 					: '. $customername . '</p></span>
            //         <span><p>Salesperson					: '. $salespersonname . '</p></span>
            //         <span><p>Product						: '. $product->name . '</p></span>
            //         <span><p>'. $finalAcePriceLabel . '     : '. $finalAcePrice . '</p></span>
            //         <span><p>Total est. Value (RM) 		    : '. $totalEstValue . '</p></span>
            //         <span><p>Weight (g)			 		    : '. $weight . '</p></span>
            //         <span><p>Date				 		    : '. $order->createdon->format('Y-m-d H:i:s') . '</p></span>
                    
            //         </td>
                    
            //     </tr>
            // </table>
            // ';
            // $returnPdf = '
 
            //     <style>
            //         body {
            //         font-size: 16px;
            //         }

            //         table {
            //             margin: auto;
            //             border-collapse: collapse;
            //             width: 100%;
            //         }
                    
            //         td, th {
            //             text-align: left;
            //             padding: 8px;
            //             padding-left: 20px;
            //             padding-right: 20px;
            //         } 
                    
            //         .bold {
            //         font-weight: bold;
            //         }
                    
            //         .right {
            //         text-align: right;
            //         }
                    
            //         .large {
            //         font-size: 1.75em;
            //         }
                    
            //         .total {
            //         font-weight: bold;
            //         color: #fb7578;
            //         }
                    
            //         .invoice-info-container {
            //         font-size: 0.875em;
            //         }
            //         .invoice-info-container td {
            //         padding: 4px 0;
            //         }
                    
            //         .client-name {
            //         font-size: 1.5em;
            //         vertical-align: top;
            //         }
                    
            //         .line-items-container {
            //         margin: 70px 0 0 0;
            //         font-size: 0.875em;
            //         }
                    
            //         .line-items-container th {
            //         text-align: left;
            //         color: #999;
            //         border-bottom: 2px solid #ddd;
            //         padding: 10px 0 15px 0;
            //         font-size: 0.75em;
            //         text-transform: uppercase;
            //         }
                    
            //         .line-items-container th:last-child {
            //         text-align: right;
            //         }
                    
            //         .line-items-container td {
            //         padding: 15px 0;
            //         }
                    
            //         .line-items-container tbody tr:first-child td {
            //         padding-top: 25px;
            //         }
                    
            //         .line-items-container.has-bottom-border tbody tr:last-child td {
            //         padding-bottom: 25px;
            //         border-bottom: 2px solid #ddd;
            //         }
                    
            //         .line-items-container.has-bottom-border {
            //         margin-bottom: 0;
            //         }
                    
            //         .line-items-container th.heading-quantity {
            //         width: 50px;
            //         }
            //         .line-items-container th.heading-price {
            //         text-align: right;
            //         width: 100px;
            //         }
            //         .line-items-container th.heading-subtotal {
            //         width: 100px;
            //         }
                    
            //         .payment-info {
            //         width: 38%;
            //         font-size: 0.75em;
            //         line-height: 1.5;
            //         }
                    
            //         .footer {
            //         margin-top: 100px;
            //         }
                    
            //         .footer-thanks {
            //         font-size: 1.125em;
            //         }
                    
            //         .footer-thanks img {
            //         display: inline-block;
            //         position: relative;
            //         top: 1px;
            //         width: 16px;
            //         margin-right: 4px;
            //         }
                    
            //         .footer-info {
            //         float: right;
            //         margin-top: 5px;
            //         font-size: 0.75em;
            //         color: #ccc;
            //         }
                    
            //         .footer-info span {
            //         padding: 0 5px;
            //         color: black;
            //         }
                    
            //         .footer-info span:last-child {
            //         padding-right: 0;
            //         }
                    
            //         .page-container {
            //         width: 100%;
            //         text-align:center;
            //         margin-bottom:15px;
            //         font-size:20px;
            //         }

            //         .title_table{
            //             padding-right:150px;
            //         }

            //         .desc_table{
            //             padding-left:200px;
            //             text-align:right;
            //         }
            //     </style>
          
            //     <div class="page-container">
            //         <span class="page">'. $finalAcePriceTitle . ' Receipt</span>
            //     </div>

            //     <table>
            //         <tr>
            //             <td class="title_table">
            //                 Branch Name:
            //             </td>
            //             <td class="desc_table">
            //                 '.$customername.'
            //             </td>
            //         </tr>
            //         <tr>
            //             <td class="title_table">
            //                 Invoice Date: 
            //             </td>
            //             <td class="desc_table">
            //                 <strong>'.$order->createdon->format('Y-m-d H:i:s').'</strong>
            //             </td>
            //         </tr>
            //         <tr>
            //             <td class="title_table">
            //                 Invoice No: 
            //             </td>
            //             <td class="desc_table">
            //                 <strong>'. $order->orderno . '</strong>
            //             </td>
            //         </tr>
            //     </table>                
                
            //     <table class="line-items-container">
            //         <thead>
            //             <tr>
            //             <th class="heading-description title_table">Description</th>
            //             <th class="heading-price">Value</th>
            //             </tr>
            //         </thead>
            //         <tbody>
            //             <tr>
            //             <td class="title_table">Order No. </td>
            //             <td class="desc_table">'. $order->orderno . '</td>
            //             </tr>
            //             <tr>
            //             <td class="title_table">Customer Name</td>
            //             <td class="desc_table">'. $goldTx->achfullname . '</td>
            //             </tr>
            //             <tr>
            //             <td class="title_table">Salesperson</td>
            //             <td class="desc_table">'. $salespersonname . '</td>
            //             </tr>
            //             <tr>
            //             <td class="title_table">Product</td>
            //             <td class="desc_table">'. $product->name . '</td>
            //             </tr>
            //             <tr>
            //             <td class="title_table">'. $finalAcePriceLabel . '</td>
            //             <td class="desc_table">'. $finalAcePrice . '</td>
            //             </tr>
            //             <tr>
            //             <td class="title_table">Total est. Value (RM)</td>
            //             <td class="desc_table">'. $totalEstValue . '</td>
            //             </tr>
            //             <tr>
            //             <td class="title_table">Weight (g)</td>
            //             <td class="desc_table">'. $weight . '</td>
            //             </tr>
            //             <tr>
            //             <td class="title_table">Date</td>
            //             <td class="desc_table">'. $order->createdon->format('Y-m-d H:i:s') . '</td>
            //             </tr>
            //         </tbody>
            //     </table>


            //     <table class="line-items-container has-bottom-border">
            //         <thead>
            //             <tr>
            //             <th class="title_table">Payment Info</th>
            //             <th class="desc_table" style="padding-left:280px;">Total Amount</th>
            //             </tr>
            //         </thead>
            //         <tbody>
            //             <tr>
            //             <td class="payment-info title_table">
            //                 <div>
            //                 Account No: <strong>'.$accountholdercode.'</strong>
            //                 </div><br>
            //                 <div>
            //                 Name:<br> <strong>'.$fullname.'</strong>
            //                 </div>
            //             </td>
            //             <td class="large total desc_table">'. $totalEstValue . '</td>
            //             </tr>
            //         </tbody>
            //     </table>

            //     <div class="footer">
            //         <div class="footer-info">
            //             <span>example@gmail.com</span> |
            //             <span>555 444 6666</span> |
            //             <span>examplewebsite.com</span>
            //         </div>
            //     </div>
            // ';
            // <span><p>'. $orderFeeLabel . '          : '. $orderFee  . '</p></span>
            // <table class="line-items-container has-bottom-border">
            //     <thead>
            //         <tr>
            //         <th>Payment Info</th>
            //         <th>Due By</th>
            //         <th>Total Due</th>
            //         </tr>
            //     </thead>
            //     <tbody>
            //         <tr>
            //         <td class="payment-info">
            //             <div>
            //             Account No: <strong>123567744</strong>
            //             </div>
            //             <div>
            //             Routing No: <strong>120000547</strong>
            //             </div>
            //         </td>
            //         <td class="large">May 30th, 2024</td>
            //         <td class="large total">$105.00</td>
            //         </tr>
            //     </tbody>
            //     </table>
			


        
        } catch (\Exception $e) {
           
            throw $e;
        }
        //return $returnPdf;
    }

    public function printSpotOrderOTCPreview($data)
    {
        

        try {
            
            if($data->type == 'sell' ){
                $ordertype = Order::TYPE_COMPANYBUY;
            }else{
                $ordertype = Order::TYPE_COMPANYSELL;
            }
            
            if(Order::TYPE_COMPANYBUY == $ordertype){
                $finalAcePriceTitle = "SELL";
                $finalAcePriceLabel = "Sell Final Price (RM/g)";
                $orderFeeLabel = "Refining Fee";
            }else if (Order::TYPE_COMPANYSELL == $ordertype){
                $finalAcePriceTitle = "PURCHASE";
                $finalAcePriceLabel = "Buy Final Price (RM/g)";
                $orderFeeLabel = "Premium Fee";
            }else{
                $finalAcePriceTitle = "-";
                $finalAcePriceLabel = "-";
                $orderFeeLabel = "-";
            }

            
            $datetime = str_replace("GMT 0800 (Malaysia Time)","",$data->date);
            $datetime = strtotime($datetime);
            $datetime = date('Y-m-d H:i:s', $datetime);
            //print_r($data);exit;
            
            $finalAcePrice = number_format(preg_replace("/[^0-9.]/", "", $data->price),2);
            $weight = number_format($data->xau,3);
            $totalbuy = number_format(preg_replace("/[^0-9.]/", "", $data->amount),2);
            $finaltotal = number_format(preg_replace("/[^0-9.]/", "", $data->finaltotal),2);

            // Get customer name
            $customername = $data->fullname;

            $product = 'Digital Gold';

            // $returnPdf = '
 
            //     <style>
            //         body {
            //         font-size: 16px;
            //         }

            //         table {
            //             margin: auto;
            //             border-collapse: collapse;
            //             width: 100%;
            //         }
                    
            //         td, th {
            //             text-align: left;
            //             padding: 8px;
            //             padding-left: 20px;
            //             padding-right: 20px;
            //         } 
                    
            //         .bold {
            //         font-weight: bold;
            //         }
                    
            //         .right {
            //         text-align: right;
            //         }
                    
            //         .large {
            //         font-size: 1.75em;
            //         }
                    
            //         .total {
            //         font-weight: bold;
            //         color: #fb7578;
            //         }
                    
            //         .invoice-info-container {
            //         font-size: 0.875em;
            //         }
            //         .invoice-info-container td {
            //         padding: 4px 0;
            //         }
                    
            //         .client-name {
            //         font-size: 1.5em;
            //         vertical-align: top;
            //         }
                    
            //         .line-items-container {
            //         margin: 100px 0 0 0;
            //         font-size: 0.875em;
            //         }
                    
            //         .line-items-container th {
            //         text-align: left;
            //         color: #999;
            //         border-bottom: 2px solid #ddd;
            //         padding: 10px 0 15px 0;
            //         font-size: 0.75em;
            //         text-transform: uppercase;
            //         }
                    
            //         .line-items-container th:last-child {
            //         text-align: right;
            //         }
                    
            //         .line-items-container td {
            //         padding: 15px 0;
            //         }
                    
            //         .line-items-container tbody tr:first-child td {
            //         padding-top: 25px;
            //         }
                    
            //         .line-items-container.has-bottom-border tbody tr:last-child td {
            //         padding-bottom: 25px;
            //         border-bottom: 2px solid #ddd;
            //         }
                    
            //         .line-items-container.has-bottom-border {
            //         margin-bottom: 0;
            //         }
                    
            //         .line-items-container th.heading-quantity {
            //         width: 50px;
            //         }
            //         .line-items-container th.heading-price {
            //         text-align: right;
            //         width: 100px;
            //         }
            //         .line-items-container th.heading-subtotal {
            //         width: 100px;
            //         }
                    
            //         .payment-info {
            //         width: 38%;
            //         font-size: 0.75em;
            //         line-height: 1.5;
            //         }
                    
            //         .footer {
            //         margin-top: 100px;
            //         }
                    
            //         .footer-thanks {
            //         font-size: 1.125em;
            //         }
                    
            //         .footer-thanks img {
            //         display: inline-block;
            //         position: relative;
            //         top: 1px;
            //         width: 16px;
            //         margin-right: 4px;
            //         }
                    
            //         .footer-info {
            //         float: right;
            //         margin-top: 5px;
            //         font-size: 0.75em;
            //         color: #ccc;
            //         }
                    
            //         .footer-info span {
            //         padding: 0 5px;
            //         color: black;
            //         }
                    
            //         .footer-info span:last-child {
            //         padding-right: 0;
            //         }
                    
            //         .page-container {
            //         width: 100%;
            //         text-align:center;
            //         margin-bottom:15px;
            //         font-size:20px;
            //         margin-top:20px;
            //         }

            //         .title_table{
            //             padding-right:150px;
            //         }

            //         .desc_table{
            //             padding-left:200px;
            //             text-align:right;
            //         }
            //     </style>
          
            //     <div class="page-container">
            //         <span class="page">'. $finalAcePriceTitle . ' Confirmation</span>
            //     </div>

            //     <table>
            //         <tr>
            //             <td class="title_table">
            //                 Branch Name:
            //             </td>
            //             <td class="desc_table">
            //                 '.$customername.'
            //             </td>
            //         </tr>
            //         <tr>
            //             <td class="title_table">
            //                 Date: 
            //             </td>
            //             <td class="desc_table">
            //                 <strong>'.$datetime.'</strong>
            //             </td>
            //         </tr>
            //     </table>                
                
            //     <table class="line-items-container" style="margin-bottom:180px;">
            //         <thead>
            //             <tr>
            //             <th class="heading-description title_table" style="padding-left:40px;">Description</th>
            //             <th class="heading-price">Value</th>
            //             </tr>
            //         </thead>
            //         <tbody>
            //             <tr>
            //             <td class="title_table" style="padding-left:40px;">Customer Name</td>
            //             <td class="desc_table" style="padding-left:220px;">'. $customername . '</td>
            //             </tr>
            //             <tr>
            //             <td class="title_table" style="padding-left:40px;">Product</td>
            //             <td class="desc_table" style="padding-left:220px;">'. $product . '</td>
            //             </tr>
            //             <tr>
            //             <td class="title_table" style="padding-left:40px;">'. $finalAcePriceLabel . '</td>
            //             <td class="desc_table" style="padding-left:220px;">'. $finalAcePrice . '</td>
            //             </tr>
            //             <tr>
            //             <td class="title_table" style="padding-left:40px;">Weight (g)</td>
            //             <td class="desc_table" style="padding-left:220px;">'. $weight . '</td>
            //             </tr>
            //             <tr>
            //             <td class="title_table" style="padding-left:40px;">Total '.$data->type.' (RM)</td>
            //             <td class="desc_table" style="padding-left:220px;">'. $totalbuy . '</td>
            //             </tr>
            //         </tbody>
            //     </table>

            //     <hr>
                
            //     <table>
            //         <tr>
            //             <td class="title_table" style="padding-right:210px; padding-left:30px;" >
            //                 Final Total (RM): 
            //             </td>
            //             <td class="desc_table" style="padding-left:220px;">
            //                 <strong>'.$finaltotal.'</strong>
            //             </td>
            //         </tr>
            //     </table> 
                
            //     <div style="margin-top:100px;">
            //         <span>Signature: </span><br><br><br><br><br>
            //         <span>Date: </span>
            //     </div>
            // ';

        
        } catch (\Exception $e) {
           
            throw $e;
        }
        return $returnPdf;
    }

    /**
     * This is a background job executor to process all orders in certain statuses for GTP PARTNER WITH `DGV` ONLY 
     * 
     */
    public function processNewOrders($partnerIds = null, $start = null, $end = null)
    {
        if (!$partnerIds){
            return false;
        }
        $cacher = $this->app->getCacher();
        // $start $end MUST be string in UTC time
        if ($start && $end){
            $allPendingOrders = $this->app->orderStore()->searchTable()->select()
                ->where('status', Order::STATUS_PENDING)
                ->andWhere('partnerid', 'IN', $partnerIds)
                ->andWhere('createdon', '>=', $start)
                ->andWhere('createdon', '<=', $end)
                ->execute();
        }else{
            $allPendingOrders = $this->app->orderStore()->searchTable()->select()
                ->where('status', Order::STATUS_PENDING)
                ->andWhere('partnerid', 'IN', $partnerIds)
                ->execute();
        }
        foreach($allPendingOrders as $anOrder) {
            $lockKey = '{pendingOrderProcessor}:' . $anOrder->id;
            if($cacher->waitForLock($lockKey, 1, 30, 0)) {
                try {
                    $order = $this->confirmBookOrder($anOrder);
                    if(Order::STATUS_CONFIRMED == $order->status) {
                        $cacher->set('{confirmedOrder}:'.$order->id, 1, 600 /* 10 minutes */);
                    }
                } catch(\Exception $e) {
                    $this->log("Error automatic processing of pending order {$anOrder->orderno} with error " . $e->getMessage(), SNAP_LOG_ERROR);
                }
                $cacher->unlock($lockKey);
            }
        }
    }

    /**
     * This is meant to be a background job to process all the cancel requests in the background
     */
    public function processCancelRequests()
    {
        $cacher = $this->app->getCacher();
        $allPendingOrders = $this->app->orderStore()->searchTable()->select()->where('status', Order::STATUS_PENDINGCANCEL);
        foreach($allPendingOrders as $anOrder) {
            $lockKey = '{cancelOrderProcessor}:' . $anOrder->id;
            if($cacher->waitForLock($lockKey, 1, 30, 0)) {
                try {
                    $order = $this->confirmCancelOrder($order);
                    if(Order::STATUS_CANCELLED == $order->status) {
                        $cacher->set('{cancelledOrder}:'.$order->id, 1, 600 /* 10 minutes */);
                    }
                } catch(\Exception $e) {
                    $this->log("Error automatic processing of cancel order {$order->orderno} with error " . $e->getMessage(), MX_LOG_ERROR);
                }
                $cacheer->unlock($lockKey);
            }
        }
    }

    /**
     * This is a background job executor to process all orders in certain statuses for GTP CORE USER ONLY 
     * 
     */
    public function processNewOrdersGtpCoreUser($partnerIds = null, $start = null, $end = null)
    {
        if (!$partnerIds){
            return false;
        }
        $cacher = $this->app->getCacher();
        // $start $end MUST be string in UTC time
        if ($start && $end){
            $allPendingOrders = $this->app->orderStore()->searchTable()->select()
                ->where('status', Order::STATUS_PENDING)
                ->andWhere('partnerid', 'NOT IN', $partnerIds)
                ->andWhere('apiversion', 'MANUAL') // need change to partner->type but need left join 
                ->andWhere('createdon', '>=', $start)
                ->andWhere('createdon', '<=', $end)
                ->execute();
        }else{
            $allPendingOrders = $this->app->orderStore()->searchTable()->select()
                ->where('status', Order::STATUS_PENDING)
                ->andWhere('partnerid', 'NOT IN', $partnerIds)
                ->andWhere('apiversion', 'MANUAL') // need change to partner->type but need left join 
                ->execute();
        }
        foreach($allPendingOrders as $anOrder) {
            $lockKey = '{pendingOrderProcessor}:' . $anOrder->id;
            if($cacher->waitForLock($lockKey, 1, 30, 0)) {
                try {
                    $order = $this->postOrderToSAP($anOrder);
                    if(Order::STATUS_CONFIRMED == $order->status) {
                        $cacher->set('{confirmedOrder}:'.$order->id, 1, 600 /* 10 minutes */);
                    }
                } catch(\Exception $e) {
                    $this->log("Error automatic processing of pending order {$anOrder->orderno} with error " . $e->getMessage(), SNAP_LOG_ERROR);
                }
                $cacher->unlock($lockKey);
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
                Order::STATUS_PENDING         => [ 'type' => 'initial', properties => []],
                Order::STATUS_CONFIRMED       => [ 'type' => 'normal', properties => []],
                Order::STATUS_PENDINGPAYMENT  => [ 'type' => 'normal', properties => []],
                Order::STATUS_PENDINGCANCEL  =>  [ 'type' => 'normal', properties => []],
                Order::STATUS_COMPLETED       => [ 'type' => 'final', properties => []],
                Order::STATUS_CANCELLED       => [ 'type' => 'final', properties => []],
                Order::STATUS_EXPIRED       => [ 'type' => 'final', properties => []]
            ],
            'transitions' => [
                Order::STATUS_COMPLETED       => [ 'from' => [ Order::STATUS_CONFIRMED, Order::STATUS_PENDINGPAYMENT ], 'to' => Order::STATUS_COMPLETED ],
                Order::STATUS_CANCELLED       => [ 'from' => [ Order::STATUS_PENDINGPAYMENT, Order::STATUS_PENDINGCANCEL], 'to' => Order::STATUS_CANCELLED ],
                Order::STATUS_PENDINGPAYMENT  => [ 'from' => [Order::STATUS_CONFIRMED], 'to' => Order::STATUS_PENDINGPAYMENT ],
                Order::STATUS_PENDINGCANCEL  => [ 'from' => [Order::STATUS_PENDING, Order::STATUS_CONFIRMED], 'to' => Order::STATUS_PENDINGCANCEL ],
                Order::STATUS_CONFIRMED       => [ 'from' => [Order::STATUS_PENDING ], 'to' => Order::STATUS_CONFIRMED ],
                Order::STATUS_EXPIRED       => [ 'from' => [Order::STATUS_PENDING ], 'to' => Order::STATUS_EXPIRED ]
            ]
        ];
        $loader       = new \Finite\Loader\ArrayLoader($config);
        $loader->load($stateMachine);
        $stateMachine->setStateAccessor(new \Finite\State\Accessor\PropertyPathStateAccessor($config['property_path']));
        $stateMachine->setObject($order);
        $stateMachine->initialize();
        return $stateMachine;
    }


    // private function getTotalTransactionWeight(Partner $partner, Product $product, $transType){
    //     $now = new \DateTime;
    //     $now = \Snap\common::convertUTCToUserDatetime($now);
    //     $index = '{Orders_'.$partner->id.'_'.$transType.'_'.$now->format('Ymd').'}';

    //     $total_amount = $this->app->getCache($index);
    //     if (!$total_amount){
    //         $total_amount = $this->getTransactionWeightFromDB($partner->id, $product->id, $transType);
    //         $this->app->setCache($index, $total_amount, 86400 /* 1 day */);
    //     }
    //     return $total_amount;
    // }

    // private function setTodayRemainingLimit($partnerid, $productid, $transType, $orderAmount, $cancelled = false){
    //     $now = new \DateTime;
    //     $now = \Snap\common::convertUTCToUserDatetime($now);
    //     $index = '{Orders_'.$partnerid.'_'.$transType.'_'.$now->format('Ymd').'}';

    //     $total_amount = $this->app->getCacher()->increment($index, $cancelled ? -1 * $orderAmount : $orderAmount);
    //     if (!$total_amount){
    //         $total_amount = $this->getTransactionWeightFromDB($partnerid, $productid, $transType);
    //         $total_amount += ($cancelled ? -1 * $orderAmount : $orderAmount);
    //         $this->app->setCache($index, $total_amount, 86400 /* 1 day */);
    //     }
    //     return $total_amount;
    // }

    // private function getTransactionWeightFromDB($partnerid, $productid, $transType)
    // {
    //     $now = new \DateTime;
    //     $now = \Snap\common::convertUTCToUserDatetime($now);
    //     $startAt = new \DateTime($now->format('Y-m-d 00:00:00'));
    //     $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
    //     $endAt = new \DateTime($now->format('Y-m-d 23:59:59'));
    //     $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

    //     // using value from VIEW , totalamount = amount+fee
    //     $total_spot_amount = $this->app->orderStore()->searchTable(false)->select()
    //         ->addFieldSum('xau', 'total_amount')
    //         ->where('partnerid', $partnerid)
    //         ->where('productid', $productid)
    //         // ->andWhere('isspot', 1)
    //         ->andWhere('type', $transType)
    //         ->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
    //         ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
    //         ->first();

    //     $total_queue_amount = $this->app->orderQueueStore()->searchTable(false)->select()
    //         ->addFieldSum('xau', 'total_amount')
    //         ->where('partnerid', $partnerid)
    //         ->where('productid', $productid)
    //         ->andWhere('ordertype', $transType)
    //         ->andWhere('expireon', '>=', $startAt->format('Y-m-d H:i:s'))
    //         ->first();

    //     $total_amount = ($total_spot_amount['total_amount'] + $total_queue_amount['total_amount']);
    //     return $total_amount;
    // }

    // private function canTradingProceedNow(Partner $partner)
    // {
    //     $sqlHandle = $this->app->tradingScheduleStore()->searchTable()->select();
    //     $sqlHandle->where(function($q) Use ($partner) {
    //         $q->whereIn('type', [TradingSchedule::TYPE_DAILY, TradingSchedule::TYPE_WEEKDAYS, TradingSchedule::TYPE_WEEKENDS])
    //            ->andWhere('categoryid', $partner->tradingscheduleid);
    //       });
    //     $sqlHandle->orWhere(function($q) Use ($partner) {
    //         $q->where('type', TradingSchedule::TYPE_STOP)
    //             ->andWhere('categoryid', $partner->tradingscheduleid)
    //             ->andWhere('endat', '>=', $q->raw('NOW()'));
    //       });
    //     $records = $sqlHandle->execute();
    //     $canTrade = false;
    //     foreach($records as $aRecord) {
    //         if(TradingSchedule::TYPE_STOP == $aRecord->type) {
    //             if (!$aRecord->canTradeNow()){
    //                 return false;
    //             }else{
    //                 return true;
    //             }
    //             //If there is a stop instruction, trading will not be allowed.
    //             //i.e.  if multiple records are found, 1 that do not allow trading will disallow trading for all.
    //         } elseif(TradingSchedule::TYPE_STOP != $aRecord->type && $aRecord->canTradeNow()) {
    //             //Only 1 allow is enough to continue trade for DAILY, WEEKDAYS and WEEKENDS type.
    //             //i.e.  if multiple records are found and all but 1 allow trading, the trading will continue;
    //             $canTrade = true;
    //         }
    //     }
    //     return $canTrade;
    // }

    // private function generateOrderNo($cacher, $partner, $product, $companyBuy, $refid)
    // {
    //     $this->log("generateOrderNo($refid, {$partner->code}) - into the method ", SNAP_LOG_DEBUG);
    //     $now = new \DateTime(gmdate('Y-m-d\TH:i:s'));
    //     $now = \Snap\common::convertUTCToUserDatetime($now);
    //     $orderNoKey = 'orderNo:' . $now->format('Ymd');
    //     $nextOrderSequence = $cacher->increment($orderNoKey, 1, 86400);
    //     $this->log("generateOrderNo($refid,{$partner->code}) - The date used is " . $now->format('Y-m-d H:i:s') . " and key = " . $orderNoKey, SNAP_LOG_DEBUG);
    //     if(! $nextOrderSequence) {
    //         $this->log("generateOrderNo($refid,{$partner->code}) - the redis key not found.  Generating total orders from DB", SNAP_LOG_DEBUG);
    //         $utcStartOfDay = new \DateTime($now->format('Y-m-d 00:00:00'));
    //         $utcStartOfDay = \Snap\common::convertUserDatetimeToUTC($utcStartOfDay);
    //         //Can't find the key.  We will have to rebuild it.
    //         $totalDayOrders = $this->app->orderStore()->searchTable()->select()->where('createdon', '>=', $utcStartOfDay->format('Y-m-d H:i:s'))->count();
    //         $this->log("generateOrderNo($refid,{$partner->code}) - total orders from DB = " . $totalDayOrders, SNAP_LOG_DEBUG);
    //         $cacher->set($orderNoKey, $totalDayOrders + 1, 86400);
    //         $nextOrderSequence = $totalDayOrders + 1;
    //     }
    //     $nextOrderSequence = strtoupper(sprintf("%s%s%04d", $companyBuy ? 'B' : 'S', $now->format('ymd'), $nextOrderSequence));
    //     $this->log("generateOrderNo() - Generated sequence $nextOrderSequence for order $refid for partner {$partner->code}", SNAP_LOG_DEBUG);
    //     return $nextOrderSequence;
    // }

    // private function getTotalTransactionWeight($product, $transType, $partner){
    //     // buy and sell limit?
    //     $today = new \DateTime;
    //     $end_day = $today->modify("+1 day");
    //     $orders_buy_limit = '{Orders}:' . $partner->id;
    //     $orders_buy_sell = '{Orders}:' . $partner->id;

    //     $total_amount = $this->getStore()->getRelatedStore('order')->searchTable()->select()
    //         ->addFieldSum('price', 'total_amount')
    //         ->where('partnerid', $partner->id)
    //         // ->andWhere('isspot', 1)
    //         ->andWhere('type', $transType)
    //         ->andWhere('createdon', '>', $today->format('Y-m-d'))
    //         ->andWhere('createdon', '<', $end_day->format('Y-m-d'))
    //         ->first();
        
    //     $total_queue_amount = $this->app->getCache($orders);
    //     if(0 == strlen($total_queue_amount) || null == $total_queue_amount) {
    //         $total_queue_amount = $this->getStore()->getRelatedStore('order')->searchTable()->select()
    //             ->addFieldSum('price', 'total_amount')
    //             ->where('partnerid', $partner->id)
    //             // ->andWhere('isspot', 1)
    //             ->andWhere('type', $transType)
    //             ->andWhere('createdon', '>', $today->format('Y-m-d'))
    //             ->andWhere('createdon', '<', $end_day->format('Y-m-d'))
    //             ->first();
    //     } else {
    //         $total_queue_amount = $this->getStore()->create();
    //         $total_queue_amount->fromCache($total_queue_amount);
    //     }
    //     if ($transType == 'CompanyBuy'){
    //         $return['total_amount'] = $partner->getProductDailyBuyLimit($product) - $total_amount['total_amount'];
    //     }
    //     if ($transType == 'CompanySell'){
    //         $return['total_amount'] = $partner->getProductDailySellLimit($product) - $total_amount['total_amount'];
    //     }
    //     if (!$return){
    //         throw new \Exception("Invalid partner daily limit amount");
    //     }
    //     return $return;
    // }

    public function postOrderToSAP($order){
        $this->app->getDbHandle()->beginTransaction();
        $version = '1.0'; // sap version ??
        $product = $this->app->productStore()->getById($order->productid);

        $orgRequestParams['version'] = '1.0'; // sap version ??

        if ($product->code == 'DG-999-9'){
            // throw error
            return false;
        }

        if ($order->type == Order::TYPE_COMPANYBUY){
            // purchase order
            $return = $this->app->apiManager()->sapPostPO($version, $order, $product, $orgRequestParams);
        }
        if ($order->type == Order::TYPE_COMPANYSELL){
            // sale order
            $return = $this->app->apiManager()->sapPostSO($version, $order, $product, $orgRequestParams);
        }

        /*
        $return value
        This is client error =>
        stdClass Object
        (
            [actionSuccess] => 1
            [errorMessage] =>
            [actionResult] => 11374
        )
        */

        if (!$this->sapReturnVerify($return)){  
            $this->log("Order - data{$order}, sap{$return} - error on sap_response :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_ERROR);
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Unable to proceed order.']);
        }

        $_now = new \DateTime();
        $_now = \Snap\common::convertUTCToUserDatetime($_now);
        $oldStatus = $order->status;
        $order->status = Order::STATUS_CONFIRMED;
        $order->confirmon = $_now;
        $order->confirmby = defined('SNAPAPP_DBACTION_USERID') ? SNAPAPP_DBACTION_USERID : $this->app->getUsersession()->getUser()->id;
        $updateOrder = $this->app->orderStore()->save($order);
        $this->app->getDbHandle()->commit();

        return $return;
    }

    private function sapReturnVerify($sap_response){
        // very array data;
        $this->log("Order - sapReturnVerify - verify sap return on item status :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);
        if (!$sap_response){
            return false;
        }
        if (isset($sap_response->actionSuccess)){
            // sap old 1.0 (gtp_core) return format
            if ($sap_response->actionSuccess == 0 || $sap_response->actionSuccess == false){
                return false;
            }
        }else{
            // sap new (MBB) return format
            if ($sap_response && 'N' == $sap_response[0]['success']){
                return false;
            }
            foreach ($sap_response as $sap_data){
                if ('N' == $sap_data['success']){
                    return false;
                }
            }
        }
        return true;
    }

    public function specialPriceOffsetDiscount($partner, $product, $transactionType, $goldPrice = 0, $orderAmount = 0, $orderGram = 0){
        //
        // condition always is `>=` MORE THAN EQUAL 
        //
        if ($goldPrice == 0){
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Unable to proceed order. Invalid special price']);
        }
        $services = $partner->getServiceForProduct($product);
        if ($transactionType == 'CompanyBuy'){
            $offset = $services->specialpricecompanybuyoffset;
        }else if ($transactionType == 'CompanySell'){
            $offset = $services->specialpricecompanyselloffset;
        }else{
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Unable to proceed order. Invalid Special price condition.']);
        }
        $condition_type = $services->specialpricetype;
        $condition_value = $services->specialpricecondition;
        $specialprice_offset = $offset;
        
        if ($condition_type == 'AMOUNT'){
            if ($orderAmount >= $condition_value){
                // $return['discountedgoldprice'] = $goldPrice + ($specialprice_offset);
                $return['discountprice'] = $specialprice_offset; // offsetonly
            }
        }else if ($condition_type == 'GRAM'){
            if ($orderGram >= $condition_value){
                // $return['discountdiscountedgoldpriceprice'] = $goldPrice + ($specialprice_offset);
                $return['discountprice'] = $specialprice_offset; // offsetonly
            }
        }else{
            return false;
        }

        $return['specialprice_type'] = $condition_type;
        $return['specialprice_condition'] = $condition_value;
        $return['specialprice_offset'] = $specialprice_offset;
        return $return;
    
    }
	
	/**
	 * Calculates the pricing model discount based on various parameters.
	 *
	 * @param object $priceStream The price stream object.
	 * @param string $campaignCode The campaign code.
	 * @param string $memberType The member type.
	 * @param float $weight The weight.
	 * @param float $basePrice The base price.
	 * @param float $fee The fee.
	 * @param object $partner The partner object.
	 * @param string $orderType The order type.
	 * @return array The discount information.
	 */
	public function otcPricingModelDiscount ($priceStream, $campaignCode, $memberType, $weight, $basePrice, $fee, $partner, $orderType)
	{
		$otcPricingModelStore = $this->app->otcpricingmodelStore();
		$basePricingModel = $otcPricingModelStore->getById($priceStream->priceadjusterid);
		if (OtcPricingModel::TYPE_AMOUNT == $basePricingModel->type) {
			$amount = $partner->calculator()->multiply($weight, $basePrice + ($fee));
		}
		if (OtcPricingModel::TYPE_GRAM == $basePricingModel->type) {
			$amount = $weight;
		}
		
		$priceStreamPrice = (Order::TYPE_COMPANYBUY == $orderType) ? $priceStream->companybuyppg : $priceStream->companysellppg;
		
		$response = array(
			'discountprice' => 0,
			'discounttype' => $basePricingModel->type,
			'discountcode' => $basePricingModel->code,
			'discountname' => $basePricingModel->name,
			'discountid' => $basePricingModel->id
		);

		if ($campaignCode) {
			$campaignCodePricingModel = $otcPricingModelStore->searchTable()
										->select()
										->where('type', OtcPricingModel::TYPE_CODE)
										->andWhere('id', '!=', $priceStream->priceadjusterid)
										->andWhere('code', $campaignCode)
										->andWhere('status', OtcPricingModel::STATUS_ACTIVE)
										->orderby('id', 'desc')
										->one();
			if ($campaignCodePricingModel) {
				$discountAmount = $this->calculateOtcPricingModelDiscount($campaignCodePricingModel, $basePricingModel, $priceStreamPrice, $orderType, $partner);
				$response = array(
					'discountprice' => $discountAmount,
					'discounttype' => $campaignCodePricingModel->type,
					'discountcode' => $campaignCodePricingModel->code,
					'discountname' => $campaignCodePricingModel->name,
					'discountid' => $campaignCodePricingModel->id
				);
			}
		} else if (AchAdditionalData::CATEGORY_STAFF == $memberType) {
			$now = new \DateTime();
			$staffPricingModel = $otcPricingModelStore->searchTable()
										->select()
										->where('type', OtcPricingModel::TYPE_STAFF)
										->andWhere('id', '!=', $priceStream->priceadjusterid)
										->andWhere('min', '<=', $amount)
										->andWhere('max', '>=', $amount)
										->andWhere('starton', '<=', $now->format('Y-m-d H:i:s'))
										->andWhere('endon', '>=', $now->format('Y-m-d H:i:s'))
										->andWhere('status', OtcPricingModel::STATUS_ACTIVE)
										->orderby('id', 'desc')
										->one();
			if ($staffPricingModel) {
				$discountAmount = $this->calculateOtcPricingModelDiscount($staffPricingModel, $basePricingModel, $priceStreamPrice, $orderType, $partner);
				$response = array(
					'discountprice' => $discountAmount,
					'discounttype' => $staffPricingModel->type,
					'discountcode' => $staffPricingModel->code,
					'discountname' => $staffPricingModel->name,
					'discountid' => $staffPricingModel->id
				);
			}
		} else {
			$amountPricingModel = $otcPricingModelStore->searchTable()
										->select()
										->where('type', $basePricingModel->type)
										->andWhere('id', '!=', $priceStream->priceadjusterid)
										->andWhere('min', '<=', $amount)
										->andWhere('max', '>=', $amount)
										->andWhere('status', OtcPricingModel::STATUS_ACTIVE)
										->orderby('id', 'desc')
										->one();

			if ($amountPricingModel) {
				$discountAmount = $this->calculateOtcPricingModelDiscount($amountPricingModel, $basePricingModel, $priceStreamPrice, $orderType, $partner);
				$response = array(
					'discountprice' => $discountAmount,
					'discounttype' => $amountPricingModel->type,
					'discountcode' => $amountPricingModel->code,
					'discountname' => $amountPricingModel->name,
					'discountid' => $amountPricingModel->id
				);
			}
		}
		
		return $response;
	}
	
	/**
	 * Calculates the discount amount for the pricing model.
	 *
	 * @param object $newPricingModel The new pricing model.
	 * @param object $basePricingModel The base pricing model.
	 * @param float $basePrice The base price.
	 * @param string $orderType The order type.
	 * @param object $partner The partner object.
	 * @return float The discount amount.
	 */
	public function calculateOtcPricingModelDiscount ($newPricingModel, $basePricingModel, $basePrice, $orderType, $partner)
	{
		if (Order::TYPE_COMPANYSELL == $orderType) {
			$baseMarginPercent = $basePricingModel->sellmarginpercent;
			$baseMarginAmount = $basePricingModel->sellmarginamount;
			$newMarginPercent = $newPricingModel->sellmarginpercent;
			$newMarginAmount = $newPricingModel->sellmarginamount;
		}
		
		if (Order::TYPE_COMPANYBUY == $orderType) {
			$baseMarginPercent = $basePricingModel->buymarginpercent;
			$baseMarginAmount = $basePricingModel->buymarginamount;
			$newMarginPercent = $newPricingModel->buymarginpercent;
			$newMarginAmount = $newPricingModel->buymarginamount;
		}
		
		if ($baseMarginPercent) {
			$originalPrice = $basePrice / (1 + ($baseMarginPercent / 100));
		}
		if ($baseMarginAmount) {
			$originalPrice = $basePrice - $baseMarginAmount;
		}
		
		$originalPrice = round($originalPrice, 2, PHP_ROUND_HALF_UP);
		
		if ($newMarginPercent) {
			$newPrice = $originalPrice * (1 + ($newMarginPercent / 100));
		}
		if ($newMarginAmount) {
			$newPrice = $originalPrice + $newMarginAmount;
		}
		
		$newPrice = round($newPrice, 2, PHP_ROUND_HALF_UP);
		
		return $partner->calculator()->sub($newPrice, $basePrice);
	}
    
    public function registerOrdFromDb(Partner $partner,$ordTrans,$extname)
    {
        /*check if order exist*/
        $order = $this->app->orderStore()->getByField('orderno',$ordTrans['orderno']."_".$extname); 
        $refsourceno = $ordTrans['partnerrefid'];

        if(!$order){
            $order = $this->app->orderStore()->create([
                'partnerid'             => $partner->id,
                'partnerrefid'          => $ordTrans['partnerrefid']."_".$extname,
                'orderno'               => $ordTrans['orderno']."_".$extname,
                'pricestreamid'         => $ordTrans['pricestreamid'],
                'salespersonid'         => $ordTrans['salespersonid'],
                'apiversion'            => $ordTrans['apiversion'],
                'type'                  => $ordTrans['type'],
                'productid'             => $ordTrans['productid'],
                'isspot'                => $ordTrans['isspot'],
				'partnerprice'          => $ordTrans['partnerprice'],
                'price'                 => $ordTrans['price'],
                'byweight'              => $ordTrans['byweight'],
                'xau'                   => $ordTrans['xau'],
                'amount'                => $ordTrans['amount'],
                'fee'                   => $ordTrans['fee'],
                'transfee'              => $ordTrans['transfee'],
                'remarks'               => $ordTrans['remarks'],
                'bookingon'             => $this->formatDateTimeWhenTransfer($ordTrans['bookingon']),
                'confirmon'             => $this->formatDateTimeWhenTransfer($ordTrans['confirmon']),
                'cancelon'              => $this->formatDateTimeWhenTransfer($ordTrans['cancelon']),
                'bookingprice'          => $ordTrans['bookingprice'],
                'bookingpricestreamid'  => $ordTrans['bookingpricestreamid'],
                'notifyurl'             => $ordTrans['notifyurl'],
                'reconciled'            => $ordTrans['reconciled'], 
                'status'                => $ordTrans['status']
            ]);
            $this->log("[Transfer DB Process] Create new order ".$ordTrans['partnerrefid']." from other db", SNAP_LOG_DEBUG);
        } else {
            /*update status*/
            $order->status       = $ordTrans['status'];
            $order->confirmon    = $this->formatDateTimeWhenTransfer($ordTrans['confirmon']);
            $order->cancelon     = $this->formatDateTimeWhenTransfer($ordTrans['cancelon']);
            $this->log("[Transfer DB Process] Order ".$ordTrans['partnerrefid']." from other db already exist. Change status successfully to ".$ordTrans['status'], SNAP_LOG_DEBUG);
        }
        $saveorder = $this->app->orderStore()->save($order);

        if($saveorder) $this->log("[Transfer DB Process] Order ".$ordTrans['partnerrefid']." from other db successfully add/update.", SNAP_LOG_DEBUG);
        return $saveorder;
    }

    public function formatDateTimeWhenTransfer($arraydate = null,$conditions = null){
        /*date format*/
        //Array
        //(
        //    [date] => 2022-06-23 22:15:56.000000
        //    [timezone_type] => 3
        //    [timezone] => Asia/Kuala_Lumpur
        //)

        if($arraydate != null) {
            if($arraydate == '0000-00-00 00:00:00') $returnDate = $arraydate;
            else {
                if($conditions == 'transactiondate'){
                    $date       = $arraydate['date'];
                    $timezone   = $arraydate['timezone'];

                    $time = date('Y-m-d H:i:s',strtotime($date));
                    $dateTime = $time; 
                    $tz_from = $timezone;
                    $newDateTime = new \DateTime($dateTime, new \DateTimeZone($tz_from)); 
                    $newDateTime->setTimezone(new \DateTimeZone("UTC")); 
                    $returnDate = $newDateTime->format("Y-m-d H:i:s");
                } else $returnDate = $arraydate['date'];
            } 
        } else $returnDate = null;
        
        return $returnDate;
    }
}
?>
