<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\api\param;

use Snap\InputException;
use Snap\IEntity;
use Snap\object\Partner;
use Snap\object\Order;
use Snap\object\OrderQueue;
use Snap\object\ApiLogs;
use Snap\api\ApiPAram;
use Snap\api\ApiParamValidator;
use Snap\api\ApiParamConveter;
use Snap\api\ApiParamExtractor;

/**
 * This class implements GTP api protocol as specified for Maybank project (v1.0m)
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param
 */
class GtpApiParam1_0m extends GtpApiParam
{
    /**
     * Definitions of all the actions that can be done by the API
     */
    protected function __construct()
    {
        parent::__construct();

        /*Price Query*/
        foreach (['price_acebuy', 'price_acesell'] as $apiAction) {
            $this->registerParameter($apiAction, 'version', 'required;string|max=5;signature|required', '', '');
            $this->registerParameter($apiAction, 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
            $this->registerParameter($apiAction, 'action', 'contains|price_acebuy|price_acesell;signature|required', '', '');
            $this->registerParameter($apiAction, 'product', 'required;productCode;signature|required', 'toProduct', '');
            $this->registerParameter($apiAction, 'currency', 'required;currencyCode;signature|required', 'toCurrency', '');
            $this->registerParameter($apiAction, 'reference', 'string|max=64;signature|ifnotempty', '', '');
            $this->registerParameter($apiAction, 'timestamp', 'datetime;signature|ifnotempty;', '', '');
            $this->registerParameter($apiAction, 'digest', 'validateSignature', '', '');
        }

        $this->registerParameter('queryPriceResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('queryPriceResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('queryPriceResponse', 'product_requested', '', '', 'fromRequest|product');
        $this->registerParameter('queryPriceResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('queryPriceResponse', 'price_request_id', '', '', 'fromObject|priceValidation|uuid');
        $this->registerParameter('queryPriceResponse', 'total_price', '', '', 'toConvertPrice|priceValidation|price');
        //$this->registerParameter('queryPriceResponse', 'total_price', '', '', 'fromObject|priceValidation|price');
        $this->registerParameter('queryPriceResponse', 'timestamp', '', '', 'fromObject|priceValidation|createdon');
        $this->registerParameter('queryPriceResponse', 'error', '', '', '');
        $signatureBuildingOptions = 'version=required|action_requested=required|product_requested=required|status=required|' .
            'price_request_id=required|product_price=required|refinery_fee=required|premium_fee=required|' .
            'total_price=required|timestamp=required';
        $this->registerParameter('queryPriceResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end Price Query*/

        /*gold allocation*/
        $this->registerParameter('goldbar_allocation', 'version', 'required;string|max=5;signature|required', '', '');
        $this->registerParameter('goldbar_allocation', 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
        $this->registerParameter('goldbar_allocation', 'action', 'contains|goldbar_allocation;signature|required', '', '');
        $this->registerParameter('goldbar_allocation', 'ref_id', 'required;string|max=12;signature|required', '', '');
        $this->registerParameter('goldbar_allocation', 'quantity', 'required;numeric|<=100;signature|required', '', '');
        $this->registerParameter('goldbar_allocation', 'reference', 'string|max=64;signature|ifnotempty', '', '');
        $this->registerParameter('goldbar_allocation', 'timestamp', 'datetime;signature|ifnotempty;', '', '');
        $this->registerParameter('goldbar_allocation', 'digest', 'validateSignature', '', '');

        $this->registerParameter('goldAllocationResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('goldAllocationResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('goldAllocationResponse', 'ref_id', '', '', 'fromRequest|ref_id');
        $this->registerParameter('goldAllocationResponse', 'reply_id', '', '', 'fromObject|apiLogs|id');
        $this->registerParameter('goldAllocationResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('goldAllocationResponse', 'quantity', '', '', 'fromRequest|quantity');
        $this->registerParameter('goldAllocationResponse', 'goldbar', '', '', 'toGoldBar|vaultItem');
        $this->registerParameter('goldAllocationResponse', 'timestamp', '', '', 'toCurrentTime');
        $this->registerParameter('goldAllocationResponse', 'error', '', '', '');
        $signatureBuildingOptions = 'version=required|action_requested=required|ref_id=required|status=required|' .
            'reply_id=required|quantity=required|goldbar=required|timestamp=required';
        $this->registerParameter('goldAllocationResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end gold allocation*/

        /*spot order*/
        foreach (['spot_acebuy', 'spot_acesell', 'close_acebuy'] as $apiAction) {
            $this->registerParameter($apiAction, 'version', 'required;string|max=5;signature|required', '', '');
            $this->registerParameter($apiAction, 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
            $this->registerParameter($apiAction, 'action', 'contains|spot_acebuy|spot_acesell|close_acebuy;signature|required', '', '');
            $this->registerParameter($apiAction, 'ref_id', 'required;string|max=12;signature|required', '', '');
            $this->registerParameter($apiAction, 'price_request_id', 'string|max=14;signature|optional', '', '');
            $this->registerParameter($apiAction, 'future_ref_id', 'string|max=12;signature|optional', '', '');
            $this->registerParameter($apiAction, 'total_price', 'required;numeric|<=1000000;signature|required', '', '');
            $this->registerParameter($apiAction, 'product', 'required;productCode;signature|required', 'toProduct', '');
            $this->registerParameter($apiAction, 'order_type', 'contains|weight|amount;signature|required', '', '');
            $weightLimit = ($apiAction == 'close_acebuy') ? '' : '|<=100000';
            $amountLimit = ($apiAction == 'close_acebuy') ? '' : '|<=10000000000';
            $this->registerParameter($apiAction, 'weight', 'required;numeric'.$weightLimit.';signature|required', '', '');
            $this->registerParameter($apiAction, 'amount', 'required;numeric'.$amountLimit.';signature|required', '', '');
            $this->registerParameter($apiAction, 'reference', 'string|max=64;signature|ifnotempty', '', '');
            $this->registerParameter($apiAction, 'timestamp', 'datetime;signature|ifnotempty', '', '');
            $this->registerParameter($apiAction, 'digest', 'validateSignature', '', '');
        }

        $this->registerParameter('spotOrderResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('spotOrderResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('spotOrderResponse', 'product_requested', '', '', 'fromRequest|product');
        $this->registerParameter('spotOrderResponse', 'confirmation_price', '', '', 'toConvertPrice|order|bookingprice');
        $this->registerParameter('spotOrderResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('spotOrderResponse', 'order_id', '', '', 'fromObject|order|orderno');
        $this->registerParameter('spotOrderResponse', 'weight', '', '', 'toConvertXau|order|xau');
        $this->registerParameter('spotOrderResponse', 'amount', '', '', 'toConvertPrice|order|amount');
        $this->registerParameter('spotOrderResponse', 'timestamp', '', '', 'fromObject|order|createdon');
        $this->registerParameter('spotOrderResponse', 'error', '', '', '');
        $signatureBuildingOptions = 'version=required|action_requested=required|product_requested=required|confirmation_price=required' .
            '|status=required|order_id=required|weight=required|amount=required|timestamp=required';
        $this->registerParameter('spotOrderResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end spot order*/

        /*Reverse order*/
        $this->registerParameter('reverse_order', 'version', 'required;string|max=5;signature|required', '', '');
        $this->registerParameter('reverse_order', 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
        $this->registerParameter('reverse_order', 'action', 'contains|reverse_order;signature|required', '', '');
        $this->registerParameter('reverse_order', 'ref_id', 'required;string|max=12;signature|required', '', '');
        $this->registerParameter('reverse_order', 'reference', 'string|max=64;signature|ifnotempty', '', '');
        $this->registerParameter('reverse_order', 'timestamp', 'datetime;signature|ifnotempty;', '', '');
        $this->registerParameter('reverse_order', 'digest', 'validateSignature', '', '');

        $this->registerParameter('reverseOrderResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('reverseOrderResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('reverseOrderResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('reverseOrderResponse', 'ref_id', '', '', 'fromRequest|ref_id');
        $this->registerParameter('reverseOrderResponse', 'order_id', '', '', 'fromObject|order|orderno');
        $this->registerParameter('reverseOrderResponse', 'reversal_price', '', '', 'toConvertPrice|order|price');
        $this->registerParameter('reverseOrderResponse', 'amount', '', '', 'toConvertPrice|order|amount');
        $this->registerParameter('reverseOrderResponse', 'cancelled_price_id', '', '', 'fromObject|order|cancelpricestreamid');
        $this->registerParameter('reverseOrderResponse', 'cancelled_total_price', '', '', 'toConvertPrice|order|cancelprice');
        $this->registerParameter('reverseOrderResponse', 'timestamp', '', '', 'fromObject|order|cancelon');
        $this->registerParameter('reverseOrderResponse', 'error', '', '', '');
        $signatureBuildingOptions = 'version=required|action_requested=required|ref_id=required|status=required|' .
            'order_id=required|reversal_price=required|amount=required|cancelled_price_id=required|' .
            'cancelled_total_price=required|timestamp=required';
        $this->registerParameter('reverseOrderResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end Reverse order*/

        /*future order*/
        foreach (['future_acebuy', 'future_acesell'] as $apiAction) {
            $this->registerParameter($apiAction, 'version', 'required;string|max=5;signature|required', '', '');
            $this->registerParameter($apiAction, 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
            $this->registerParameter($apiAction, 'action', 'contains|future_acebuy|future_acesell;signature|required', '', '');
            $this->registerParameter($apiAction, 'future_ref_id', 'required;string|max=12;signature|required', '', '');
            $this->registerParameter($apiAction, 'expected_matching_price', 'required;numeric|<=1000000;signature|required', '', '');
            $this->registerParameter($apiAction, 'product', 'required;productCode;signature|required', 'toProduct', '');
            $this->registerParameter($apiAction, 'order_type', 'contains|weight|amount;signature|required', '', '');
            $this->registerParameter($apiAction, 'weight', 'required;numeric|<=100000;signature|required', '', '');
            $this->registerParameter($apiAction, 'amount', 'required;numeric|<=1000000;signature|required', '', '');
            $this->registerParameter($apiAction, 'future_order_expiry', 'required;datetime;signature|required', '', '');
            $this->registerParameter($apiAction, 'success_notify_url', 'required;string|max=256;signature|required', '', '');
            $this->registerParameter($apiAction, 'reference', 'string|max=64;signature|ifnotempty', '', '');
            $this->registerParameter($apiAction, 'timestamp', 'datetime;signature|ifnotempty;', '', '');
            $this->registerParameter($apiAction, 'digest', 'validateSignature', '', '');
        }

        $this->registerParameter('futureOrderResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('futureOrderResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('futureOrderResponse', 'product_requested', '', '', 'fromRequest|product');
        $this->registerParameter('futureOrderResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('futureOrderResponse', 'future_ref_id', '', '', 'fromRequest|future_ref_id');
        $this->registerParameter('futureOrderResponse', 'future_order_id', '', '', 'fromObject|orderQueue|orderqueueno');
        $this->registerParameter('futureOrderResponse', 'expected_matching_price', '', '', 'fromObject|orderQueue|pricetarget');
        $this->registerParameter('futureOrderResponse', 'future_order_expiry', '', '', 'fromRequest|future_order_expiry');
        $this->registerParameter('futureOrderResponse', 'success_notify_url', '', '', 'fromObject|orderQueue|notifyurl');
        $this->registerParameter('futureOrderResponse', 'timestamp', '', '', 'fromObject|orderQueue|createdon');
        $this->registerParameter('futureOrderResponse', 'error', '', '', '');
        $signatureBuildingOptions = 'version=required|action_requested=required|product_requested=required|status=required|' .
            'future_ref_id=required|future_order_id=required|expected_matching_price=required|future_order_expiry=required|' .
            'success_notify_url=required|timestamp=required';
        $this->registerParameter('futureOrderResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end future order*/

        /*Cancel future order*/
        $this->registerParameter('cancel_future_placement', 'version', 'required;string|max=5;signature|required', '', '');
        $this->registerParameter('cancel_future_placement', 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
        $this->registerParameter('cancel_future_placement', 'action', 'contains|cancel_future_placement;signature|required', '', '');
        $this->registerParameter('cancel_future_placement', 'ref_id', 'required;string|max=12;signature|required', '', '');
        $this->registerParameter('cancel_future_placement', 'reference', 'string|max=64;signature|ifnotempty', '', '');
        $this->registerParameter('cancel_future_placement', 'timestamp', 'datetime;signature|ifnotempty;', '', '');
        $this->registerParameter('cancel_future_placement', 'digest', 'validateSignature', '', '');

        $this->registerParameter('cancelFuturePlacementResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('cancelFuturePlacementResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('cancelFuturePlacementResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('cancelFuturePlacementResponse', 'ref_id', '', '', 'fromRequest|ref_id');
        $this->registerParameter('cancelFuturePlacementResponse', 'timestamp', '', '', 'fromObject|orderQueue|cancelon');
        $this->registerParameter('cancelFuturePlacementResponse', 'error', '', '', '');
        $signatureBuildingOptions = 'version=required|action_requested=required|ref_id=required|status=required|' .
            'timestamp=required';
        $this->registerParameter('cancelFuturePlacementResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end Cancel future order*/

        /*matched future order notification*/

        /*this is for testing*/
        $this->registerParameter('match_fo_test', 'version', 'required;string|max=5;signature|required', '', '');
        $this->registerParameter('match_fo_test', 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
        $this->registerParameter('match_fo_test', 'action', 'contains|match_fo_test;signature|required', '', '');
        $this->registerParameter('match_fo_test', 'future_ref_id', 'signature|required', '', '');
        $this->registerParameter('match_fo_test', 'pricestream_id', 'signature|required', '', '');
        $this->registerParameter('match_fo_test', 'test', 'signature|required', '', '');
        /*this is for testing*/

        $this->registerParameter('matchedFutureOrder', 'version', '', '', 'fromObject|orderQueue|apiversion');
        $this->registerParameter('matchedFutureOrder', 'future_ref_id', '', '', 'fromObject|orderQueue|partnerrefid');
        $this->registerParameter('matchedFutureOrder', 'price_id', '', '', 'fromObject|orderQueue|matchpriceid');
        $this->registerParameter('matchedFutureOrder', 'total_price', '', '', 'toConvertPriceFO|orderQueue|pricetarget');
        $this->registerParameter('matchedFutureOrder', 'future_order_flag', '', '', 'constant|Y');
        $this->registerParameter('matchedFutureOrder', 'fo_trans_type', '', '', 'constant|M');
        $this->registerParameter('matchedFutureOrder', 'tran_date', '', '', 'toCurrentTimeFO');
        $this->registerParameter('matchedFutureOrder', 'status', '', '', 'constant|1');  
        $this->registerParameter('matchedFutureOrder', 'msg', '', '', 'toCheckMessageNull|orderQueue|remarks');          

        /*$this->registerParameter('matchedFutureOrderResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('matchedFutureOrderResponse', 'future_ref_id', '', '', 'fromObject|orderQueue|partnerrefid');
        $this->registerParameter('matchedFutureOrderResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('matchedFutureOrderResponse', 'msg', '', '', '');*/
        /*end matched future order notification*/

        /*redemption*/
        $this->registerParameter('redemption', 'version', 'required;string|max=5;signature|required', '', '');
        $this->registerParameter('redemption', 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
        $this->registerParameter('redemption', 'action', 'contains|redemption;signature|required', '', '');
        $this->registerParameter('redemption', 'ref_id', 'required;string|max=12;signature|required', '', '');
        $this->registerParameter('redemption', 'type', 'contains|branch|delivery|special_delivery|pre_appointment;signature|required', '', '');
        $this->registerParameter('redemption', 'redeem_gram', 'required;numeric|<=100001;signature|required', '', '');
        $this->registerParameter('redemption', 'branch_id', 'string|max=12;signature|optional', '', '');
        $this->registerParameter('redemption', 'item', 'signature|optional', '', '');
        $this->registerParameter('redemption', 'delivery_info', 'signature|optional;', '', '');
        $this->registerParameter('redemption', 'schedule_info', 'signature|optional;', '', '');
        $this->registerParameter('redemption', 'reference', 'string|max=64;signature|ifnotempty', '', '');
        $this->registerParameter('redemption', 'timestamp', 'datetime;signature|ifnotempty;', '', '');
        $this->registerParameter('redemption', 'digest', 'validateSignature', '', '');

        $this->registerParameter('redemptionResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('redemptionResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('redemptionResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('redemptionResponse', 'ref_id', '', '', 'fromRequest|ref_id');
        $this->registerParameter('redemptionResponse', 'redemption_id', '', '', 'fromObject|redemption|redemptionno');
        $this->registerParameter('redemptionResponse', 'redemption_type', '', '', 'fromRequest|type');
        $this->registerParameter('redemptionResponse', 'redemption_gram', '', '', 'toConvertXau|redemption|totalweight');
        $this->registerParameter('redemptionResponse', 'redemption_branch_id', '', '', 'fromObject|redemption|branchid');
        $this->registerParameter('redemptionResponse', 'redemption_item', '', '', 'toRedemptionItems|redemption');
        $this->registerParameter('redemptionResponse', 'error', '', '', '');
        $this->registerParameter('redemptionResponse', 'timestamp', '', '', 'fromObject|redemption|createdon');

        $signatureBuildingOptions = 'version=required|action_requested=required|ref_id=required|status=required|' .
            'redemption_id=required|redemption_type=required|redemption_gram=required|redemption_branch_id=required|' .
            'redemption_item=required|timestamp=required';
        $this->registerParameter('redemptionResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end redemption*/

        /*redemption reversal*/
        $this->registerParameter('reverse_redemption', 'version', 'required;string|max=5;signature|required', '', '');
        $this->registerParameter('reverse_redemption', 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
        $this->registerParameter('reverse_redemption', 'action', 'contains|reverse_redemption;signature|required', '', '');
        $this->registerParameter('reverse_redemption', 'ref_id', 'required;string|max=12;signature|required', '', '');
        $this->registerParameter('reverse_redemption', 'reference', 'string|max=64;signature|ifnotempty', '', '');
        $this->registerParameter('reverse_redemption', 'timestamp', 'datetime;signature|ifnotempty;', '', '');
        $this->registerParameter('reverse_redemption', 'digest', 'validateSignature', '', '');            

        $this->registerParameter('reverseRedemptionResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('reverseRedemptionResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('reverseRedemptionResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('reverseRedemptionResponse', 'ref_id', '', '', 'fromRequest|ref_id');
        $this->registerParameter('reverseRedemptionResponse', 'timestamp', '', '', 'toCurrentTime');
        $this->registerParameter('reverseRedemptionResponse', 'error', '', '', '');
        $signatureBuildingOptions = 'version=required|action_requested=required|ref_id=required|status=required|' .
                                    'timestamp=required';
        $this->registerParameter('reverseRedemptionResponse', 'digest', '', '', 'makeGtpSignature|'.$signatureBuildingOptions);
        /*end redemption reversal*/

        /*buyback*/
        $this->registerParameter('buyback', 'version', 'required;string|max=5;signature|required', '', '');
        $this->registerParameter('buyback', 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
        $this->registerParameter('buyback', 'action', 'contains|buyback;signature|required', '', '');
        $this->registerParameter('buyback', 'ref_id', 'required;string|max=12;signature|required', '', '');
        $this->registerParameter('buyback', 'branch_id', 'required;string|max=12;signature|required', '', '');
        $this->registerParameter('buyback', 'price_request_id', 'required;string|max=14;signature|required', '', '');
        $this->registerParameter('buyback', 'buyback_gram', 'required;numeric|<=100001;signature|required', '', '');
        $this->registerParameter('buyback', 'buyback_goldprice', 'required;numeric|<=1000000;signature|required', '', '');
        $this->registerParameter('buyback', 'buyback_fee', 'required;numeric|<1000000;signature|required', '', '');
        $this->registerParameter('buyback', 'buyback_total_price', 'required;numeric|<=1000000;signature|required', '', '');
        $this->registerParameter('buyback', 'buyback_quantity', 'required;numeric|<=100;signature|required', '', '');
        $this->registerParameter('buyback', 'item', 'signature|optional', '', '');
        $this->registerParameter('buyback', 'reference', 'string|max=64;signature|ifnotempty', '', '');
        $this->registerParameter('buyback', 'timestamp', 'datetime;signature|ifnotempty;', '', '');
        $this->registerParameter('buyback', 'digest', 'validateSignature', '', '');

        $this->registerParameter('buybackResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('buybackResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('buybackResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('buybackResponse', 'ref_id', '', '', 'fromRequest|ref_id');
        $this->registerParameter('buybackResponse', 'buyback_id', '', '', 'fromObject|buyback|buybackno');
        $this->registerParameter('buybackResponse', 'total_gram', '', '', 'fromObject|buyback|totalweight');
        $this->registerParameter('buybackResponse', 'total_quantity', '', '', 'fromObject|buyback|totalquantity');
        $this->registerParameter('buybackResponse', 'total_price', '', '', 'toConvertPrice|buyback|totalamount');
        $this->registerParameter('buybackResponse', 'error', '', '', '');
        $this->registerParameter('buybackResponse', 'timestamp', '', '', 'fromObject|buyback|createdon');

        $signatureBuildingOptions = 'version=required|action_requested=required|ref_id=required|status=required|' .
            'buyback_id=required|total_gram=required|total_quantity=required|total_price=required|' .
            'timestamp=required';
        $this->registerParameter('buybackResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end buyback*/

        /*buyback reversal*/
        $this->registerParameter('reverse_buyback', 'version', 'required;string|max=5;signature|required', '', '');
        $this->registerParameter('reverse_buyback', 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
        $this->registerParameter('reverse_buyback', 'action', 'contains|reverse_buyback;signature|required', '', '');
        $this->registerParameter('reverse_buyback', 'ref_id', 'required;string|max=12;signature|required', '', '');
        $this->registerParameter('reverse_buyback', 'reference', 'string|max=64;signature|ifnotempty', '', '');
        $this->registerParameter('reverse_buyback', 'timestamp', 'datetime;signature|ifnotempty;', '', '');
        $this->registerParameter('reverse_buyback', 'digest', 'validateSignature', '', '');            

        $this->registerParameter('reverseBuybackResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('reverseBuybackResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('reverseBuybackResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('reverseBuybackResponse', 'ref_id', '', '', 'fromRequest|ref_id');
        $this->registerParameter('reverseBuybackResponse', 'timestamp', '', '', 'toCurrentTime');
        $this->registerParameter('reverseBuybackResponse', 'error', '', '', '');
        $signatureBuildingOptions = 'version=required|action_requested=required|ref_id=required|status=required|' .
                                    'timestamp=required';
        $this->registerParameter('reverseBuybackResponse', 'digest', '', '', 'makeGtpSignature|'.$signatureBuildingOptions);
        /*end buyback reversal*/

        /*businesspartnerlist*/
        $this->registerParameter('itemlist', 'version', 'required;string|max=5;signature|required', '', '');
        $this->registerParameter('itemlist', 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
        $this->registerParameter('itemlist', 'action', 'contains|partnerlist;signature|required', '', '');
        $this->registerParameter('itemlist', 'ref_id', 'required;string|max=12;signature|required', '', '');
        $this->registerParameter('itemlist', 'item', 'signature|optional', '', '');
        $this->registerParameter('itemlist', 'warehouse', 'signature|optional', '', '');
        $this->registerParameter('itemlist', 'reference', 'string|max=64;signature|ifnotempty', '', '');
        $this->registerParameter('itemlist', 'timestamp', 'datetime;signature|ifnotempty;', '', '');
        $this->registerParameter('itemlist', 'digest', 'validateSignature', '', '');

        $this->registerParameter('itemlistResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('itemlistResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('itemlistResponse', 'ref_id', '', '', 'fromRequest|ref_id');
        $this->registerParameter('itemlistResponse', 'itemCode', '', '', 'fromObject|partnerbranchmap|pbm_sapcode');
        $this->registerParameter('itemlistResponse', 'itemName', '', '', 'fromObject|partnerbranchmap|pbm_name');
        $this->registerParameter('itemlistResponse', 'frgnName', '', '', 'constant|__null__');
        $this->registerParameter('itemlistResponse', 'whsCode', '', '', 'constant|__null__');
        $this->registerParameter('itemlistResponse', 'onHand', '', '', 'constant|__null__');
        $this->registerParameter('itemlistResponse', 'isCommited', '', '', 'constant|__null__');
        $this->registerParameter('itemlistResponse', 'onOrder', '', '', 'constant|__null__');
        $this->registerParameter('itemlistResponse', 'minStock', '', '', 'constant|__null__');
        $this->registerParameter('itemlistResponse', 'maxStock', '', '', 'constant|__null__');
        $this->registerParameter('itemlistResponse', 'minOrder', '', '', 'constant|__null__');
        $this->registerParameter('itemlistResponse', 'avgPrice', '', '', 'constant|__null__');
        $this->registerParameter('itemlistResponse', 'locked', '', '', 'constant|__null__');
        $this->registerParameter('itemlistResponse', 'createDate', '', '', 'constant|__null__');
        $this->registerParameter('itemlistResponse', 'updateDate', '', '', 'constant|__null__');

        $signatureBuildingOptions = 'version=required|action_requested=required|ref_id=required|' .
            'CardCode=required|CardName=required|';
        $this->registerParameter('partnerlistResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end businesspartnerlist*/
        
        /*Query Price Stream*/
        $this->registerParameter('query_price_stream', 'version', 'required;string|max=5;signature|required', '', '');
        $this->registerParameter('query_price_stream', 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
        $this->registerParameter('query_price_stream', 'action', 'contains|query_price_stream;signature|required', '', '');
        $this->registerParameter('query_price_stream', 'product', 'required;productCode;signature|required', 'toProduct', '');
        $this->registerParameter('query_price_stream', 'currency', 'required;currencyCode;signature|required', 'toCurrency', '');
        $this->registerParameter('query_price_stream', 'reference', 'string|max=64;signature|ifnotempty', '', '');
        $this->registerParameter('query_price_stream', 'timestamp', 'datetime;signature|ifnotempty;', '', '');
        $this->registerParameter('query_price_stream', 'digest', 'validateSignature', '', '');
        
        $this->registerParameter('queryPriceStreamResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('queryPriceStreamResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('queryPriceStreamResponse', 'product_requested', '', '', 'fromRequest|product');
        $this->registerParameter('queryPriceStreamResponse', 'timestamp', '', '', 'fromObject|pricestream|createdon');
        $this->registerParameter('queryPriceStreamResponse', 'error', '', '', '');
        $this->registerParameter('queryPriceStreamResponse', 'providerid', '', '', 'fromObject|pricestream|providerid');
        $this->registerParameter('queryPriceStreamResponse', 'providerpriceid', '', '', 'fromObject|pricestream|providerpriceid');
        $this->registerParameter('queryPriceStreamResponse', 'uuid', '', '', 'fromObject|pricestream|uuid');
        $this->registerParameter('queryPriceStreamResponse', 'currencyid', '', '', 'fromObject|pricestream|currencyid');
        $this->registerParameter('queryPriceStreamResponse', 'companybuyppg', '', '', 'fromObject|pricestream|companybuyppg');
        $this->registerParameter('queryPriceStreamResponse', 'companysellppg', '', '', 'fromObject|pricestream|companysellppg');
        $this->registerParameter('queryPriceStreamResponse', 'rawfxusdbuy', '', '', 'fromObject|pricestream|rawfxusdbuy');
        $this->registerParameter('queryPriceStreamResponse', 'rawfxusdsell', '', '', 'fromObject|pricestream|rawfxusdsell');
        $this->registerParameter('queryPriceStreamResponse', 'rawfxsource', '', '', 'fromObject|pricestream|rawfxsource');
        $this->registerParameter('queryPriceStreamResponse', 'pricesourceid', '', '', 'fromObject|pricestream|pricesourceid');
        $this->registerParameter('queryPriceStreamResponse', 'pricesourceon', '', '', 'fromObject|pricestream|pricesourceon');
        $this->registerParameter('queryPriceStreamResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('queryPriceStreamResponse', 'id', '', '', 'fromObject|pricestream|id');
        
        $signatureBuildingOptions = 'version=required|action_requested=required|product_requested=required|timestamp=required|' .
            'providerid=required|providerpriceid=required|uuid=required|currencyid=required|companybuyppg=required|companysellppg=required|' .
            'rawfxusdbuy=required|rawfxusdsell=required|rawfxsource=required|pricesourceid=required|pricesourceon=required|status=required';
        $this->registerParameter('queryPriceStreamResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end Query Price Stream*/
    }
}
