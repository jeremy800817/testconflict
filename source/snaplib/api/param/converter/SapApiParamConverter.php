<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\param\converter;

Use Snap\InputException;
Use Snap\IEntity;
Use Snap\api\param\converter\ApiParamConverter;

/**
 * Actual implementation of ApiParamConverter that does conversion for GTP.  This class will just store
 * the required methods to do conversion.  Actual logic and integration is done in parent class.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param.converter
*/
class SapApiParamConverter extends ApiParamConverter {

    protected function convertToProduct($conditions, $key, $value, $originalParams) {
        $product = $this->app->productStore()->getByField('sapitemcode', $value);
        return [ $key => $product, 'product' => $product];
    }

    protected function convertToPartner($conditions, $key, $value, $originalParams)
    {
        $partner = $this->app->partnerStore()->searchTable()
                        ->select()
                        ->where('sapcompanysellcode1', '=', $value)
                        ->orWhere('sapcompanysellcode2', '=', $value)
                        ->orWhere('sapcompanybuycode1', '=', $value)
                        ->orWhere('sapcompanybuycode2', '=', $value)
                        ->one();
         if(!$partner || 0 == $partner->id) {
            throw \Snap\api\exception\PartnerCodeNotAvailable::fromTransaction($this, ['partnercode' => $value]);
        }
        return [$key => $partner, 'partner' => $partner];
    }

    protected function convertToBranch($conditions, $key, $value, $originalParams) {
        $branch = $this->app->partnerStore()->getRelatedStore('branches');
        $getBranch = $branch->getByField('sapcode', $value);
        return [ $key => $product, 'branch' => $getBranch];
    }

    protected function convertToGoldBar($conditions, $key, $value, $originalParams) {
        //$key == goldbar, $value == array of vaultitem
        if(0 != count($value)){
            foreach ($value as $aValue){
                $result = array('serial_no'=>$aValue['serialno']);
            }
        } /*else {
            throw \Snap\api\exception\PartnerCodeNotAvailable::fromTransaction($this, ['partnercode' => $value]);
        }*/
        
        return json_encode(array('goldbar' => $result));

    }
}
?>