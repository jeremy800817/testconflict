<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\param\validator;

Use Snap\TLogging;
Use Snap\InputException;
Use Snap\IEntity;
Use Snap\api\param\validator\ApiParamValidator;

/**
 * GTP api specific validator
  *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param.validator
*/
class GtpApiParamValidator extends ApiParamValidator {
    Use TLogging;

    //Template Method to implement test.
    protected function testPartnerCode($conditions, $key, $value, $originalRequestParams)
    {
        try {
            $partner = $this->app->partnerStore()->getByField('code', $value);
            if(0 == $partner->id || 0 == $partner->status || 
                (\Snap\object\Partner::MODE_API != $partner->orderingmode && \Snap\object\Partner::MODE_BOTH != $partner->orderingmode)) {
                throw \Snap\api\exception\ApiParamPartnerInvalid::fromTransaction($this, ['param' => $key, 'value' => $value]);
            }
            $this->secretDigestKey = $partner->apikey;
            return true;
        } catch(\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Caught Exception: '.$e->getmessage()]);
        }
    }

    protected function testProductCode($conditions, $key, $value, $originalRequestParams)
    {
        try {
            $product = $this->app->productStore()->getByField('code', $value);
            if(0 == $product->id || 0 == $product->status) {
                throw \Snap\api\exception\ApiParamProductInvaliid::fromTransaction($this, ['param' => $key, 'value' => $value]);
            }
            return true;
        } catch(\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Caught Exception: '.$e->getmessage()]);
        }
    }

    protected function testCurrencyCode($conditions, $key, $value, $originalRequestParams)
    {
        try {
            $currency = $this->app->tagStore()->getCurrencyByCode($value);
            if(0 == $currency->id || 0 == $currency->status) {
                throw \Snap\api\exception\ApiParamCurrencyInvaliid::fromTransaction($this, ['param' => $key, 'value' => $value]);
            }
            return true;
        } catch(\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Caught Exception: '.$e->getmessage()]);
        }
        
    }

    protected function testSignature($conditions, $key, $value, $originalRequestParams) {

        if('required' == $conditions[0]) {
            $this->digestBlock[$key] = $value;
        } elseif('ifnotempty' == $conditions[0] && strlen($value)) {
            $this->digestBlock[$key] = $value;
        // } elseif('optional' == $conditions[0] && !empty($value) && 0 < strlen($value)) {
        } elseif('optional' == $conditions[0] || isset($originalRequestParams[$key])) {
            $this->digestBlock[$key] = $value;
        }
        return true;
    }

    protected function testValidateSignature($conditions, $key, $value, $originalRequestParams) {
        $paramArray = [];
        foreach($this->digestBlock as $digestKey => $digestValue) {
            $paramArray[] = $digestKey."=".$digestValue;
        }
        $validationString = join('&', $paramArray) . "&key=".$this->secretDigestKey;
        $generatedDigest = hash('sha256', $validationString );
        $this->logDebug("[ApiDigest] Request digest = $value; Generated digest = $generatedDigest;  Raw string = $validationString.\n");
        if(strtoupper(trim($value)) != strtoupper(trim($generatedDigest))) {
            $this->log("[ApiDigest] Keys do not match and throw error", SNAP_LOG_ERROR);
            throw \Snap\api\exception\ApiParamDigestMismtach::fromTransaction($this, ['param' => $key]);
        }
        return true;
    }


}
?>
