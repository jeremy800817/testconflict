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
class GtpApiParamConverter extends ApiParamConverter {

    protected function convertToProduct($conditions, $key, $value, $originalParams) {
        try {
            $product = $this->app->productStore()->getByField('code', $value);
            return [ $key => $product ];
        } catch(\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Caught Exception: '.$e->getmessage()]);
        }
    }

    protected function convertToCurrency($conditions, $key, $value, $originalParams) {
        try {
            $currency = $this->app->tagStore()->getCurrencyByCode($value);
            return [$key => $currency];
        } catch(\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Caught Exception: '.$e->getmessage()]);
        }
    }

    protected function convertToPartner($conditions, $key, $value, $originalParams)
    {
        try {
            $partner = $this->app->partnerStore()->getByField('code', $value);
            if(!$partner || $value != $partner->code) {
                throw \Snap\api\exception\PartnerCodeNotAvailable::fromTransaction($this, ['partnercode' => $value]);
            }
            return [$key => $partner, 'partner' => $partner];
        } catch(\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Caught Exception: '.$e->getmessage()]);
        }
    }

    /*protected function convertToGoldBar($conditions, $key, $value, $originalParams) {
        //$key == goldbar, $value == array of vaultitem
        if(0 != count($value)){
            foreach ($value as $aValue){
                $result = array('serial_no'=>$aValue['serialno']);
            }
        } /*else {
            throw \Snap\api\exception\PartnerCodeNotAvailable::fromTransaction($this, ['partnercode' => $value]);
        }*/
        
       /* return json_encode(array('goldbar' => $result));

    }*/
}
?>