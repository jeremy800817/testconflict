<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
namespace Snap\api\param\converter;

use Snap\api\param\converter\GtpApiParamConverter;

/**
 * MyGTP api specific converter
 *
 * @author Cheok <cheok@silverstream.my>
 * @version 1.0
 * @package  snap.api.param.Converter
*/
class MyGtpApiParamConverter extends GtpApiParamConverter {

    protected function convertTokenToAccountHolder($conditions, $key, $value, $originalParams) {
        try {
            $account = $this->app->mygtpauthManager()->getAccountFromAccessToken($value);

            if (!$account) {
                throw \Snap\api\exception\ApiInvalidAccessToken::fromTransaction([]);
            }

            return [ 'accountholder' => $account ];
        } catch(\Error $e) {
            http_response_code(401);
            throw \Snap\api\exception\ApiInvalidAccessToken::fromTransaction([]);
        }
    }

    /**
     * This method convert the param into the occupation category object using id
     *
     * @param  array  $conditions
     * @param  string $key
     * @param  string $value
     * @param  array  $originalParams
     * @return void
     */
    protected function convertToOccupationCategory($conditions, $key, $value, $originalParams)
    {
        try {
            $occupationCategory = $this->app->myoccupationCategoryStore()
                ->searchTable()
                ->select()
                ->where('status', '!=', \Snap\object\MyOccupationCategory::STATUS_INACTIVE)
                ->find($value);

            if (!$occupationCategory) {
                throw \Snap\api\exception\MyOccupationCategoryNotFound::fromTransaction([], ['value' => $value]);
            }
            return [$key => $occupationCategory, 'occupation_category' => $occupationCategory];
        } catch (\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Caught Exception: ' . $e->getmessage()]);
        }
    }

    
    /**
     * This method convert the param into the occupation category object using id
     *
     * @param  array  $conditions
     * @param  string $key
     * @param  string $value
     * @param  array  $originalParams
     * @return void
     */
    protected function convertToOccupationSubCategory($conditions, $key, $value, $originalParams)
    {
        try {
            
            if(empty($value)){
                return [$key => null, 'occupation_subcategory' => null];
            }

            $occupationSubCategory = $this->app->myoccupationSubCategoryStore()
                ->searchTable()
                ->select()
                ->where('status', '!=', \Snap\object\MyOccupationSubCategory::STATUS_INACTIVE)
                ->find($value);

            if (!$occupationSubCategory) {
                throw \Snap\api\exception\MyOccupationSubCategoryNotFound::fromTransaction([], ['value' => $value]);
            }
            return [$key => $occupationSubCategory, 'occupation_subcategory' => $occupationSubCategory];
        } catch (\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Caught Exception: ' . $e->getmessage()]);
        }
    }


    /**
     * This method convert the param into the bank object using id
     *
     * @param  array  $conditions
     * @param  string $key
     * @param  string $value
     * @param  array  $originalParams
     * @return void
     */
    protected function convertToBank($conditions, $key, $value, $originalParams)
    {
        try {
            $bank = $this->app->mybankStore()
                ->searchTable()
                ->select()
                ->where('status', '!=', \Snap\object\MyBank::STATUS_INACTIVE)
                ->find($value);

            if (!$bank) {
                throw \Snap\api\exception\MyBankNotFound::fromTransaction([], ['value' => $value]);
            }
            return [$key => $bank, 'bank' => $bank];
        } catch (\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Caught Exception: ' . $e->getmessage()]);
        }
    }

    /**
     * Converts param to date
     */
    protected function convertToDateTime($conditions, $key, $value, $originalParams)
    {
        try {
            if (!strlen($value)) {
                return [$key => null];
            }

            $dateStr = $value;
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $dateStr);

            return [$key => $date];
        } catch (\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Caught Exception: ' . $e->getmessage()]);
        }
    }

    /**
     * Converts to true/false
     */
    protected function convertToBoolean($conditions, $key, $value, $originalParams)
    {
        try {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);

            return [$key => $value];
        } catch (\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Caught Exception: ' . $e->getmessage()]);
        }
    }

     /** This method convert the param into the bank object using id
     *
     * @param  array  $conditions
     * @param  string $key
     * @param  string $value
     * @param  array  $originalParams
     * @return void
     */
    protected function convertToCloseReason($conditions, $key, $value, $originalParams)
    {
        try {
            $closeReason = $this->app->myclosereasonStore()
                ->searchTable()
                ->select()
                ->where('status', '!=', \Snap\object\MyCloseReason::STATUS_INACTIVE)
                ->find($value);

            if (!$closeReason) {
                throw \Snap\api\exception\MyCloseReasonInvalid::fromTransaction([], ['value' => $value]);
            }
            return [$key => $closeReason, 'close_reason' => $closeReason];
        } catch (\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Caught Exception: ' . $e->getmessage()]);
        }
    }

    public function convertToPriceAlert($conditions, $key, $value, $originalParams)
    {
        try {
            $priceAlert = $this->app->mypricealertStore()
                ->searchTable()
                ->select()
                ->where('status', '!=', \Snap\object\MyPriceAlert::STATUS_INACTIVE)
                ->find($value);

            if (!$priceAlert) {
                throw \Snap\api\exception\MyPriceAlertNotFound::fromTransaction([], ['value' => $value]);
            }
            return [$key => $priceAlert, 'price_alert' => $priceAlert];
        } catch (\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Caught Exception: ' . $e->getmessage()]);
        }
    }

}
?>