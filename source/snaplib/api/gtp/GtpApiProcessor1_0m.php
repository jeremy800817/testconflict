<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\gtp;

Use Snap\api\gtp\GtpApiProcessor;
Use Snap\api\param\ApiParam;
use Snap\api\sap\SapApiProcessor;
Use \Snap\object\Order;
Use \Snap\object\OrderQueue;
Use \Snap\object\Redemption;
Use \Snap\object\Partner;


/**
 * Specialised GTP API processor for Maybank implementation
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class GtpApiProcessor1_0m extends GtpApiProcessor
{
    /**
     * Main method to process the incoming request.  Implemented class can get relevant
     * information about the action to be taken etc from the apiParam and then call the
     * appropriate manager to execute the main business logics.
     * 
     * @param  App                      $app           App Class
     * @param  \Snap\api\param\ApiParam $apiParam      Api parameter object containing the decoded data
     * @param  array                    $decodedData   Decoded and converted data
     * @param  array                    $requestParams Original raw data from the API request
     * @return \Snap\api\param\ApiParam     The response type represented as apiParams.
     */
    public function process($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        switch($apiParam->getActionType()) {
            case 'price_acebuy':
            case 'price_acesell':
                return $this->doPriceQuery($app, $apiParam, $decodedData, $requestParams, $apiParam->getActionType());
                break;
            case 'spot_acebuy':
            case 'spot_acesell':
            case 'close_acebuy':
                return $this->doSpotOrder($app, $apiParam, $decodedData, $requestParams, $apiParam->getActionType());
                break;
            case 'future_acebuy':
            case 'future_acesell':
                return $this->doFutureOrder($app, $apiParam, $decodedData, $requestParams, $apiParam->getActionType());
                break;
            case 'goldbar_allocation':
                return $this->doGolbarAllocation($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'reverse_order':
                return $this->doReverseOrder($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'cancel_future_placement':
                return $this->doCancelFuturePlacement($app, $apiParam, $decodedData, $requestParams);
                break;
            /*case 'matchedFutureOrder':
                return $this->doMatchedFutureOrder($app, $apiParam, $decodedData, $requestParams);
                break;*/
            case 'redemption':
                return $this->doRedemption($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'reverse_redemption':
                return $this->doReverseRedemption($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'buyback':
                return $this->doBuyBack($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'reverse_buyback':
                return $this->doReverseBuyBack($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'partnerlist':
                return $this->dopartnerlist($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'itemlist':
                return $this->doitemlist($app, $apiParam, $decodedData, $requestParams);
                break;
            /*this is for testing*/
            case 'match_fo_test':
                return $this->doTestToken($app, $apiParam, $decodedData, $requestParams);
                break;
            /*this is for testing*/
            case 'query_price_stream':
                return $this->doPriceStreamQuery($app, $apiParam, $decodedData, $requestParams);
                break;

        }
        return parent::process($app, $apiParam, $decodedData, $requestParams);
    }

    /*this is for testing*/
    private function doTestToken($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        $future_ref_id = $requestParams['future_ref_id'];
        $pricestream_id = $requestParams['pricestream_id'];
        $orderQ = $app->orderQueueStore()->getByField('orderqueueno', $future_ref_id);
        $priceStream = $app->pricestreamStore()->getByField('id', $pricestream_id);
        $url = $app->getConfig()->{'gtp.mbb.token.url'};
        $clientid = $app->getConfig()->{'gtp.mbb.clientid'};
        $clientsecret = $app->getConfig()->{'gtp.mbb.clientsecret'};
        if($requestParams['test'] == 'token') {
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('', $requestParams['version']);
            $data = $sender->request('POST', $url, ['json'=>[
                        'grant_type'    => 'client_credentials',
                        'scope'    => 'orders',
                        'client_id'      => $clientid,
                        'client_secret'      => $clientsecret
                    ],'getToken']);
        }
        else {
            $orderValidation = $app->futureorderManager()->onFutureOrderMatched($orderQ, $priceStream,true);
        }
    }
    /*this is for testing*/

    public function doMatchedFutureOrder($app, Partner $partner, OrderQueue $matchOrder,$originalRequestParams)
    {
        try {
            $url = $matchOrder->notifyurl;
            //$url = 'https://staging.api.maybank.com/api/my/retail/products/v1/futureorder?ordertype=notification';
            $action = 'matchedFutureOrder';
            $decodedData['orderQueue'] = $matchOrder;
            $decodedData['partner'] = $partner;
            $decodedData = array_merge($originalRequestParams, $decodedData);

            $responseParams = $this->createOutputApiParam($action, $originalRequestParams, $decodedData);
            //$sender = \Snap\api\gtp\GtpApiSender::getInstance('Http', $responseData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('', $matchOrder->apiversion);
            $data = $sender->request('PUT', $url, ['json'=>$responseParams]);
            return $data;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function doPriceQuery($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams, $buyOrSell)
    {
        try {
            $toSell = preg_match('/sell/i', $buyOrSell);
            $version = strtolower($requestParams['version']);
            $priceValidation = $app->priceManager()->getSpotPrice($decodedData['partner'], $decodedData['product'], $requestParams['reference'], ($toSell ? false : true), $version);
            $decodedData['priceValidation'] = $priceValidation;

            $responseParams = $this->createOutputApiParam('queryPriceResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            \Snap\App::getInstance()->logDebug("doPriceQuery()_response");
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function doSpotOrder($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams, $buyOrSell)
    {
        try{
            $toSell = preg_match('/sell/i', $buyOrSell);
            $close = preg_match('/close/i', $buyOrSell);

            if($requestParams['order_type'] == 'amount') $orderValue = $requestParams['amount'];
            else $orderValue = $requestParams['weight'];
            $version = strtolower($requestParams['version']);

            $orderValidation = $app->spotorderManager()->bookOrder($decodedData['partner'], $version, $requestParams['ref_id'], ($toSell ? Order::TYPE_COMPANYSELL : Order::TYPE_COMPANYBUY), $decodedData['product'], $requestParams['price_request_id'], $requestParams['future_ref_id'], $requestParams['order_type'], $orderValue, $requestParams['total_price'], $requestParams['amount'], $notifyUrl, $requestParams['reference'], $requestParams['timestamp'],($close ? true : false));
            $decodedData['order'] = $orderValidation;

            // $confirmOrder = $app->spotorderManager()->confirmBookOrder($orderValidation);
            // $decodedData['confirmorder'] = $confirmOrder;

            $responseParams = $this->createOutputApiParam('spotOrderResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            \Snap\App::getInstance()->logDebug("doPriceQuery()_response");
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function doFutureOrder($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams, $buyOrSell)
    {
        try {
            $toSell = preg_match('/sell/i', $buyOrSell);
            if($requestParams['order_type'] == 'amount') $orderValue = $requestParams['amount'];
            else $orderValue = $requestParams['weight'];
            $version = strtolower($requestParams['version']);

            // mbb 30 min delay to active future order matching
            $effectiveOn = date('Y-m-d H:i:s', strtotime("+30 minutes"));
            $effectiveOn = new \DateTime($effectiveOn);
            $effectiveOn = \Snap\common::convertUTCToUserDatetime($effectiveOn);

            $orderQueue = $app->futureorderManager()->createFutureOrder(
                $decodedData['partner'], 
                $version, 
                $requestParams['future_ref_id'], 
                ($toSell ? OrderQueue::OTYPE_COMPANYSELL : OrderQueue::OTYPE_COMPANYBUY), 
                $decodedData['product'], 
                $requestParams['order_type'], 
                $orderValue, 
                $requestParams['expected_matching_price'], 
                $requestParams['future_order_expiry'], 
                $requestParams['success_notify_url'], 
                $matchNotifyUrl, 
                $requestParams['reference'], 
                $requestParams['timestamp'],
                $effectiveOn
            );
            $decodedData['orderQueue'] = $orderQueue;

            $confirmOrder = $app->futureorderManager()->confirmCreateFutureOrder($orderQueue);
            $decodedData['confirmOrderQueue'] = $confirmfutureorder;

            $responseParams = $this->createOutputApiParam('futureOrderResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function doGolbarAllocation($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $productforMBB = 'GS-999-9-1000g';
            $product = $app->productStore()->getByField('code', $productforMBB);
            $version = strtolower($requestParams['version']);

            $vaultItem = $app->bankvaultManager()->requestItemSerial($decodedData['partner'], $product, $requestParams['quantity'], $requestParams['reference'], $requestParams['timestamp'], $version);
            $decodedData['vaultItem'] = $vaultItem;

            $responseParams = $this->createOutputApiParam('goldAllocationResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function doReverseOrder($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $getOrderByRefId = $requestParams['ref_id'];
            $order = $app->orderStore()->getByField('partnerrefid', $getOrderByRefId);
            $version = strtolower($requestParams['version']);

            $cancelOrder = $app->spotorderManager()->cancelOrder($decodedData['partner'], $order, $notifyUrl, $requestParams['reference'], $requestParams['timestamp'], true);
            $decodedData['order'] = $cancelOrder;
            $confirmCancelOrder = $app->spotorderManager()->confirmCancelOrder($cancelOrder);
            $decodedData['confirmcancelorder'] = $confirmCancelOrder;

            $responseParams = $this->createOutputApiParam('reverseOrderResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function doCancelFuturePlacement($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $version = strtolower($requestParams['version']);

            $orderQueue = $app->futureorderManager()->cancelFutureOrder($decodedData['partner'], $version, $requestParams['ref_id'], $notifyUrl, $requestParams['reference'], $requestParams['timestamp']);
            $decodedData['orderQueue'] = $orderQueue;
            $confirmCancelFutureOrder = $app->futureorderManager()->confirmCanceledFutureOrder($orderQueue);
            $decodedData['confirmCancelOrderQueu'] = $confirmCancelFutureOrder;

            $responseParams = $this->createOutputApiParam('cancelFuturePlacementResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function doRedemption($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $getItemArray = json_decode($requestParams['item'],true);
            foreach($getItemArray as $getItem){
                $totalQuantity += $getItem['quantity'];
            }
            $deliveryDetails = json_decode($requestParams['delivery_info']);
            $scheduleDetails = json_decode($requestParams['schedule_info']);
            if($requestParams['type'] == 'pre_appointment') $type = 'Appointment';
            else if($requestParams['type'] == 'branch') $type = 'Branch';
            else if($requestParams['type'] == 'delivery') $type = 'Delivery';
            else if($requestParams['type'] == 'special_delivery') $type = 'SpecialDelivery';
            $version = strtolower($requestParams['version']);

            /*checking address*/
            if(in_array($type, [Redemption::TYPE_DELIVERY, Redemption::TYPE_SPECIALDELIVERY])){
                if(!empty($deliveryDetails)){
                    if(empty($deliveryDetails->address1)) throw \Snap\api\exception\ApiParamRequired::fromTransaction($this, ['param' => 'address1 in delivery_info']);
                    elseif(empty($deliveryDetails->address2)) throw \Snap\api\exception\ApiParamRequired::fromTransaction($this, ['param' => 'address2 in delivery_info']);
                    elseif(empty($deliveryDetails->state)) throw \Snap\api\exception\ApiParamRequired::fromTransaction($this, ['param' => 'state in delivery_info']);
                    elseif(empty($deliveryDetails->postcode)) throw \Snap\api\exception\ApiParamRequired::fromTransaction($this, ['param' => 'postcode in delivery_info']);
                } else {
                    throw \Snap\api\exception\ApiParamRequired::fromTransaction($this, ['param' => 'delivery_info']);
                }
            } 
            /*checking address end*/

            $redemptionValidation = $app->redemptionManager()->createRedemption($decodedData['partner'], $version, $requestParams['ref_id'], $requestParams['branch_id'], $type, $requestParams['redeem_gram'], $totalQuantity, $getItemArray, $deliveryDetails, $scheduleDetails, $requestParams['reference'], $requestParams['timestamp']);
            $redemptionValidation = $app->redemptionManager()->confirmRedemption($redemptionValidation, $decodedData['partner'], $requestParams['branch_id'], $deliveryDetails, $scheduleDetails);
            $decodedData['redemption'] = $redemptionValidation;
            
            $responseParams = $this->createOutputApiParam('redemptionResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function doReverseRedemption($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $version = strtolower($requestParams['version']);

            $reverseRedemption = $app->redemptionManager()->reverseRedemption($decodedData['partner'], $requestParams['ref_id']);
            $decodedData['redemption'] = $reverseRedemption;
            
            $responseParams = $this->createOutputApiParam('reverseRedemptionResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function doBuyBack($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try{
            $version = strtolower($requestParams['version']);
            $getItemArray = json_decode($requestParams['item'],true);

            $branch = $decodedData['partner']->getBranch($requestParams['branch_id']);
            if ($branch){
                $branchid = $branch->id;
            }else{
                // if mbb/operator dint update their `NEW` branch in GTP system.
                $branchid = $requestParams['branch_id'];
            }

            $buybackValidation = $app->buybackManager()->doBuyback($decodedData['partner'], $version, $requestParams['ref_id'], $branchid, $getItemArray, $requestParams['buyback_gram'], $requestParams['buyback_total_price'], $requestParams['buyback_quantity'], $requestParams['buyback_fee'], $requestParams['reference'], $requestParams['buyback_goldprice'], $requestParams['price_request_id']);
            $decodedData['buyback'] = $buybackValidation;

            $confirmBuyBack = $app->buybackManager()->confirmBuyback($buybackValidation);
            $decodedData['confirmBuyBack'] = $confirmBuyBack;
            $responseParams = $this->createOutputApiParam('buybackResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function doReverseBuyback($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $version = strtolower($requestParams['version']);

            $reverseBuyback = $app->buybackManager()->reverseBuyback($decodedData['partner'],$requestParams['ref_id']);
            $decodedData['buyback'] = $reverseBuyback;
            
            $responseParams = $this->createOutputApiParam('reverseBuybackResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function dopartnerlist($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        // try {
        //     $version = strtolower($requestParams['version']);
            
        //     $partnerlistvalidate = $app->partnerListingManager()->doPartnerListing($decodedData['partner'], $version, $requestParams['ref_id'], $requestParams['option'], $requestParams['reference']);
        //     $decodedData['partnerlist'] = $partnerlistvalidate;

        //     $confirmpartnerlist = $app->partnerListingManager()->returnpartnerlist($partnerlistvalidate);
        //     $decodedData['returnpartnerlist'] = $confirmpartnerlist;

        //     $responseParams = $this->createOutputApiParam('partnerlistResponse', $requestParams, $decodedData);
        //     $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $version);
        //     $sender->response($app, $responseParams);
        //     return $responseParams;
        // } catch (\Snap\api\exception\ApiException $e) {
        //     $this->formatException($e);
        // }

        $sapApiProcessor = SapApiProcessor::getInstance("1.0");
        return $sapApiProcessor->process($app, $apiParam, $decodedData, $requestParams);

    }
    private function doitemlist($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $version = strtolower($requestParams['version']);

            $partnerlistvalidate = $app->ItemListingManager()->doPartnerListing($decodedData['partner'], $version, $requestParams['ref_id'], $requestParams['item'], $requestParams['warehouse']);
            $decodedData['partnerlist'] = $partnerlistvalidate;

            $confirmpartnerlist = $app->ItemListingManager()->returnpartnerlist($partnerlistvalidate);
            $decodedData['returnpartnerlist'] = $confirmpartnerlist;

            $responseParams = $this->createOutputApiParam('partnerlistResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $version);
            $sender->response($app, $responseParams);
            return $responseParams;
        } catch (\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }
    
    private function doPriceStreamQuery($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $priceManager = $app->priceManager();
            
            $provider = $app->priceProviderStore()->getForPartnerByProduct($decodedData['partner'], $decodedData['product']);
            
            $latestPrice = $priceManager->getLatestSpotPrice($provider);

            if(! $provider->isPriceDataFresh($latestPrice)) {
                throw \Snap\api\exception\PriceStreamDataStale::fromTransaction($decodedData['partner']);
            }

            $decodedData['pricestream'] = $latestPrice;

            $responseParams = $this->createOutputApiParam('queryPriceStreamResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            \Snap\App::getInstance()->logDebug("doPriceQuery()_response");
            
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }
}
?>