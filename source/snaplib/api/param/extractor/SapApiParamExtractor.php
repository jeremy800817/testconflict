<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\param\extractor;

Use Snap\InputException;
Use Snap\IEntity;

/**
 * Actual method to implement extraction for GTP based API.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param.extractor
 */
class SapApiParamExtractor extends ApiParamExtractor {

    protected function extractFromRequest($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        if(!isset($originalRequesParams[$params[0]])) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => '$originalRequesParams']);
        }
        return [ $key => $originalRequesParams[$params[0]]];
    }

    protected function extractFromResult($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        if(!isset($processedData[$params[0]])) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => '$processedData']);
        }
        return [ $key => $processedData[$params[0]]];
    }

    protected function extractFromResultNull($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        if(null === $processedData[$params[0]] || 0 == strlen($processedData[$params[0]])) {
            $processedData[$params[0]] = "";
        }
        return [ $key => $processedData[$params[0]]];
    }

    protected function extractConstant($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        if('__null__' == $params[0]) {
            $params[0] = null;
        } elseif ('__empty__' == $params[0]) {
            $params[0] = '';
        }
        if('int' == $params[1] || is_int($params[0]) ) {
            $params[0] = $params[0] + 0;
        } elseif (is_float($params[0])) {
            $params[0] = floatval($params[0]);
        }
        return [ $key => $params[0]];
    }

    protected function extractFromObject($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);
        $object = $processedData[$params[0]];
        if(!$object) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => $params[0]]);
        }
        if(preg_match('/\(\)$/', $params[1])) {
            $data = call_user_func_array([$object, $params[2]], []);
        } else {
            $data = $object->{$params[1]};
            if(is_numeric($data)) {
                $data = floatval($data);
            } else if($data instanceof \datetime) {
                $data = $data->format('Y-m-d H:i:s');
            }
        }
        if(null === $data || 0 == strlen($data)) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => $params[0] . ' object with field/methods ' . $params[1]]);
        }
        return [ $key => $data ];
    }

    protected function extractFromObjectNull($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);
        $object = $processedData[$params[0]];
        if(!$object) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => $params[0]]);
        }
        if(preg_match('/\(\)$/', $params[1])) {
            $data = call_user_func_array([$object, $params[2]], []);
        } else {
            $data = $object->{$params[1]};
            if(is_numeric($data)) {
                $data = floatval($data);
            } else if($data instanceof \datetime) {
                $data = $data->format('Y-m-d H:i:s');
            }
        }
        if(null === $data || 0 == strlen($data)) {
            $data = "";
        }
        return [ $key => $data ];
    }

    protected function extractToCurrentTime($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $currentdate = date('Y-m-d H:i:s');
        return [ $key => $currentdate ];
    }

    protected function extractOrderLine($params, $key, $processedData, $originalRequestParams, $responseData)
    {
        $product = $processedData['product'];
        if (!$product) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => 'product object']);
        }

        $order = $processedData['order'];
        if (!$order) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => 'order object']);
        }

        if($order->type == 'CompanyBuy'){
            if($order->fee >= 0) $feeUpdate = -1 * $order->fee;
            else $feeUpdate = $order->fee;
            $fee = number_format(abs($order->fee),3,'.','');
            $feeArr = array('key' => 'U_REFINING_FEE' , 'value' => $fee);
        }
        else if($order->type == 'CompanySell'){
            $feeUpdate = $order->fee;
            $fee  = number_format($order->fee,3,'.','');
            $feeArr = array('key' => 'U_PREMIUM' , 'value' => $fee);
        }

        // Only use fields used in liveray 
        $line = [];
        $line['itemCode']        = $product->sapitemcode;
        // $line['itemDescription'] = $product->name;
        $line['quantity']        = number_format($order->xau,3,'.','');
        // $line['discountPercent'] = $originalRequestParams['lineDiscountPercent'];
        // $line['warehouseCode']   = 
        $line['lineTotal']       = number_format($order->amount,3,'.','');
        $line['unitPrice']       = number_format($order->price+($feeUpdate),3,'.','');
        $line['U_GOLD_PRICE']    = number_format($order->price,3,'.','');
        $line[$feeArr['key']]    = $feeArr['value'];

        return [ $key => [$line] ];
    }

    protected function extractBuybackLine($params, $key, $processedData, $originalRequestParams, $responseData)
    {
        $product = $processedData['product'];
        if (!$product) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => 'product object']);
        }

        $buyback = $processedData['buyback'];
        if (!$buyback) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => 'buyback object']);
        }

        // Only use fields used in liveray 
        $line = [];
        $line['itemCode']        = strtoupper('NS-'.$product->sapitemcode);
        // $line['itemDescription'] = $product->name;
        $line['quantity']        = number_format($buyback->totalweight,3,'.','');
        // $line['discountPercent'] = $originalRequestParams['lineDiscountPercent'];
        // $line['warehouseCode']   = 
        $line['lineTotal']       = number_format($buyback->totalamount,2,'.','');
        $line['unitPrice']       = number_format($buyback->price+($buyback->fee),2,'.','');
        $line['U_GOLD_PRICE']    = number_format($buyback->price,2,'.','');
        $line['U_REFINING_FEE']  = number_format(abs($buyback->fee),2,'.','');

        return [ $key => [$line] ];
    }

    protected function extractToPartnerRefId($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);
        $object = $processedData[$params[0]];

        $result = (string)$object->partnerrefid;
        return [$key => $result];
    }

    protected function extractToDoDocNumVerify($params, $key, $processedData, $originalRequesParams, $responseData){
        if(!isset($originalRequesParams[$params[0]])) {
            if($params[0] != 'DoDocNum'){
                throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => '$originalRequesParams']);
            } 
        }
        
        return [ $key => $originalRequesParams[$params[0]]];
    }

    protected function extractToStringRefNo($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);
        $object = $processedData[$params[0]];

        $result = (string)$object->deliveryordernumber;
        return [$key => $result];
    }
}
?>