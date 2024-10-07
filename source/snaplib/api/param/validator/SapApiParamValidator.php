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
 * SAP api specific validator
  *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param.validator
*/
class SapApiParamValidator extends ApiParamValidator {
    Use TLogging;

    //Template Method to implement test.
    protected function testPartnerSapCode($conditions, $key, $value, $originalRequestParams)
    {
        $partner = $this->app->partnerStore()->searchTable()
                        ->select()
                        ->where('sapcompanysellcode1', $value)
                        ->orWhere('sapcompanysellcode2', $value)
                        ->orWhere('sapcompanybuycode1', $value)
                        ->orWhere('sapcompanybuycode2', $value)
                        ->one();
        if(0 == $partner->id || 0 == $partner->status || 
            (\Snap\object\Partner::MODE_API != $partner->orderingmode && \Snap\object\Partner::MODE_BOTH != $partner->orderingmode)) {
            throw \Snap\api\exception\ApiParamPartnerInvalid::fromTransaction($this, ['param' => $key, 'value' => $value]);
        }
        $this->secretDigestKey = $partner->apikey;
        return true;
    }

    protected function testProductSapCode($conditions, $key, $value, $originalRequestParams)
    {
        $product = $this->app->productStore()->getByField('sapitemcode', $value);
        if(0 == $product->id || 0 == $product->status) {
            throw \Snap\api\exception\ApiParamProductInvaliid::fromTransaction($this, ['param' => $key, 'value' => $value]);
        }
        return true;
    }

    protected function testBranchSapCode($conditions, $key, $value, $originalRequestParams)
    {
        //$branch = $this->app->partnerbranchmapStore()->getByField('sapcode', $value);

        $branch = $this->app->partnerStore()->getRelatedStore('branches');
        $getBranch = $branch->getByField('sapcode', $value);
        if(0 == $getBranch->id || 0 == $getBranch->status) {
            throw \Snap\api\exception\ApiParamBranchInvaliid::fromTransaction($this, ['param' => $key, 'value' => $value]);
        }
        return true;
    }

    protected function testCurrencyCode($conditions, $key, $value, $originalRequestParams)
    {
        $currency = $this->app->tagStore()->getCurrencyByCode($value);
        if(0 == $currency->id || 0 == $currency->status) {
            throw \Snap\api\exception\ApiParamCurrencyInvaliid::fromTransaction($this, ['param' => $key, 'value' => $value]);
        }
        return true;
    }

    protected function testSignature($conditions, $key, $value, $originalRequestParams) {

        if('required' == $conditions[0]) {
            $this->digestBlock[$key] = $value;
        } elseif('ifnotempty' == $conditions[0] && strlen($value)) {
            $this->digestBlock[$key] = $value;
        } elseif('optional' == $conditions[0] && !empty($value) && 0 < strlen($value)) {
            $this->digestBlock[$key] = $value;
        }
        return true;
    }

    protected function testValidateSignature($conditions, $key, $value, $originalRequestParams) {
        $paramArray = [];
        foreach($this->digestBlock as $digestKey => $digestValue) {
            $paramArray[] = $digestKey."=".$digestValue;
        }
        $validationString = join('&', $paramArray) . "&key=" . $this->secretDigestKey;
        $generatedDigest = hash('sha256', $validationString );
        $this->logDebug("[ApiDigest] Request digest = $value; Generated digest = $generatedDigest;  Raw string = $validationString.\n");
        if(strtoupper(trim($value)) != strtoupper(trim($generatedDigest))) {
            throw \Snap\api\exception\ApiParamDigestMismtach::fromTransaction($this, ['param' => $key]);
        }
        return true;
    }

    /**
     *   In the example below, parameter `code` requires `option` to be either 'customer' or 'vendor'
     * 
     *   $this->registerParameter('', 'option', 'required;string|max=8', '', '');
     *   $this->registerParameter('', 'code', 'dependsOn=option|option=customer|option=vendor', '', '');
     */
    protected function testRequiredBy($conditions, $key, $value, $originalRequestParams) {
        if (isset($originalRequestParams[$key])) {
            try {
                foreach ($conditions as $condition) {
                    [$dependedParam, $dependValue] = explode("=", $condition);
                    if ( $dependValue == $originalRequestParams[$dependedParam]) {
                        $this->testRequired($conditions, $key, $value, $originalRequestParams);
                    }
                }
            } catch (\Snap\api\exception\ApiParamRequired $e) {
                return false;
            }
        }

        return true;
    }

    
    protected function testBoolean($conditions, $key, $value, $originalRequestParams) {
        if (! is_bool($value)) {
            throw \Snap\api\exception\ApiParamNotBoolean::fromTransaction($this, [
                'param' => $key
            ]);
        }

        return true;
    }

}
?>
