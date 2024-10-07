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
class GtpApiParamExtractor extends ApiParamExtractor{

    protected function extractMakeGtpSignature($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        if(!isset($processedData['partner'])) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => 'partner']);
        }
        $digestGenerator = '';
        $this->apiKey = $processedData['partner']->apikey;
        foreach($params as $data) {
            list($field, $requiredOption) = explode('=', $data);
            if('required' == $requiredOption || isset($responseData[$field])) {
                if(strlen($digestGenerator)) {
                    $digestGenerator .= "&";
                }
                $digestGenerator .= "$field={$responseData[$field]}";
            }
        }
        $digestGenerator = $digestGenerator . "&key={$this->apiKey}";;
        $digest = strtoupper(hash('sha256', $digestGenerator));
        $this->log("Generated signature is $digest from raw string $digestGenerator", SNAP_LOG_ERROR);
        return [ $key => $digest ];
    }

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

    protected function extractConstant($params, $key, $processedData, $originalRequesParams, $responseData)
    {
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
            if($data instanceof \datetime) {
                $data = $data->format('Y-m-d H:i:s');
            }
        }
        if(null === $data || 0 == strlen($data)) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => $params[0] . ' object with field/methods ' . $params[1]]);
        }
        return [ $key => $data ];
    }

    protected function extractToGoldBar($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);
        $object = $processedData[$params[0]];
        foreach($object as $value){
            $result[] = array('serialno'=>$this->serialNoFormatToMbb($value->serialno));
        }
        return [$key => $result];
    }

    protected function extractToRedemptionItems($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);

        $object = $processedData;
        foreach($object as $value){
            $itemsRedemption = json_decode(($value->items),true);
            foreach($itemsRedemption as $aValue){
                $result[] = array(
                    'serialno'=>$this->serialNoFormatToMbb($aValue['serialnumber']),
                    'denomination'=>(int)$aValue['weight'],
                    'quantity'=>1,
                );
            }
        }
        return [$key => $result];
    }

    protected function extractToRedemptionItemsBursa($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);

        $object = $processedData;
        foreach($object as $value){
            $itemsRedemption = json_decode(($value->items),true);
            foreach($itemsRedemption as $aValue){
                $result[] = array(
                    'serialno'=>$this->serialNoFormatToMbb($aValue['serialnumber']),
                    'denomination'=>$aValue['weight'],
                    'quantity'=>1,
                );
            }
        }
        return [$key => $result];
    }

    protected function extractToCurrentTime($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $currentdate = date('Y-m-d H:i:s');
        return [ $key => $currentdate ];
    }

    protected function extractToCurrentTimeFO($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $currentdate = date('dmY');
        return [ $key => $currentdate ];
    }

    protected function extractToCheckMessageNull($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $object = $processedData[$params[0]];
        if(!$object) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => $params[0]]);
        }
        if(preg_match('/\(\)$/', $params[1])) {
            $data = call_user_func_array([$object, $params[2]], []);
        } else {
            $data = $object->{$params[1]};
            if($data instanceof \datetime) {
                $data = $data->format('Y-m-d H:i:s');
            }
        }
        if(null === $data || 0 == strlen($data)) {
            $data = '';
        }
        return [ $key => $data ];
    }

    protected function extractToConvertPrice($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);
        $object = $processedData[$params[0]];
        if(!$object) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => $params[0]]);
        }
        $fieldTarget = $params[1];
        $convertNumber = number_format($object->$fieldTarget,2,'.','');
        return [ $key => $convertNumber];
    }

    protected function extractToConvertPriceFO($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);
        $object = $processedData[$params[0]];
        if(!$object) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => $params[0]]);
        }
        $fieldTarget = $params[1];
        $convertNumber = number_format($object->$fieldTarget,2,'.','');
        $spCaseNumber = preg_replace('/[^0-9]/', '', $convertNumber); //MBB received only hex. need to remove '.'
        return [ $key => $spCaseNumber];
    }

    protected function extractToConvertXau($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);
        $object = $processedData[$params[0]];
        if(!$object) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => $params[0]]);
        }
        $fieldTarget = $params[1];
        $convertNumber = number_format($object->$fieldTarget,3,'.','');
        return [ $key => $convertNumber];
    }

    protected function extractToConvertXauBursa($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);
        $object = $processedData[$params[0]];
        if(!$object) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => $params[0]]);
        }
        $fieldTarget = $params[1];
        $convertNumber = number_format($object->$fieldTarget,6,'.','');
        return [ $key => $convertNumber];
    }

    protected function extractToConvertPartner($params, $key, $processedData, $originalRequesParams, $responseData)
    {
        $this->log("In " . __METHOD__ . "with $key.... and data " . json_encode(array_keys($processedData)), SNAP_LOG_ERROR);
        $object = $processedData[$params[0]];
        if(!$object) {
            throw \Snap\api\exception\ApiExtractorDataNotFound::fromTransaction($this, ['param' => $params[0]]);
        }
        $partner = $object->getPartner();
        foreach($partner as $aPartner){
            $partnercode = $aPartner->code;
        }

        return [ $key => $partnercode];
    }
}
?>