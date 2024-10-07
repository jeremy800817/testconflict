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
use Snap\object\ApiLogs;
use Snap\api\ApiPAram;
use Snap\api\ApiParamValidator;
use Snap\api\ApiParamConveter;
use Snap\api\ApiParamExtractor;

/**
 * This class implements GTP api protocol as specified for POS project (v1.0p)
 *
 * @author Dianah <dianah@silverstream.my>
 * @version 1.0
 * @package  snap.api.param
 */
class GtpApiParam1_0p extends GtpApiParam
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
        $this->registerParameter('queryPriceResponse', 'timestamp', '', '', 'fromObject|priceValidation|createdon');
        $this->registerParameter('queryPriceResponse', 'error', '', '', '');
        $signatureBuildingOptions = 'version=required|action_requested=required|product_requested=required|status=required|' .
            'price_request_id=required|product_price=required|refinery_fee=required|premium_fee=required|' .
            'total_price=required|timestamp=required';
        $this->registerParameter('queryPriceResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end Price Query*/

        /*buyback*/
        $this->registerParameter('buyback', 'version', 'required;string|max=5;signature|required', '', '');
        $this->registerParameter('buyback', 'merchant_id', 'required;partnerCode;signature|required', 'toPartner', '');
        $this->registerParameter('buyback', 'action', 'contains|buyback;signature|required', '', '');
        $this->registerParameter('buyback', 'reference_no', 'required;string|max=12;signature|required', '', '');
        $this->registerParameter('buyback', 'branch_id', 'required;string|max=12;signature|required', '', '');
        $this->registerParameter('buyback', 'branch_name', 'required;string|max=50;signature|required', '', '');
        $this->registerParameter('buyback', 'product', 'required;productCode;signature|required', 'toProduct', '');
        $this->registerParameter('buyback', 'price_request_id', 'required;string|max=14;signature|required', '', '');
        $this->registerParameter('buyback', 'item', 'signature|optional', '', '');
        $this->registerParameter('buyback', 'buyback_xau', 'required;numeric|<=100001;signature|required', '', '');
        $this->registerParameter('buyback', 'buyback_goldprice', 'required;numeric|<=1000000;signature|required', '', '');
        $this->registerParameter('buyback', 'buyback_total_price', 'required;numeric|<=1000000;signature|required', '', '');
        $this->registerParameter('buyback', 'reference', 'string|max=64;signature|ifnotempty', '', '');
        $this->registerParameter('buyback', 'timestamp', 'datetime;signature|ifnotempty;', '', '');
        $this->registerParameter('buyback', 'digest', 'validateSignature', '', '');

        $this->registerParameter('buybackResponse', 'version', '', '', 'fromRequest|version');
        $this->registerParameter('buybackResponse', 'action_requested', '', '', 'fromRequest|action');
        $this->registerParameter('buybackResponse', 'status', '', '', 'constant|1');
        $this->registerParameter('buybackResponse', 'reference_no', '', '', 'fromRequest|reference_no');
        $this->registerParameter('buybackResponse', 'receipt_no', '', '', 'fromObject|buyback|buybackno');
        $this->registerParameter('buybackResponse', 'total_gram', '', '', 'fromObject|buyback|totalweight');
        $this->registerParameter('buybackResponse', 'total_price', '', '', 'toConvertPrice|buyback|totalamount');
        $this->registerParameter('buybackResponse', 'error', '', '', '');
        $this->registerParameter('buybackResponse', 'timestamp', '', '', 'fromObject|buyback|createdon');

        $signatureBuildingOptions = 'version=required|action_requested=required|status=required|reference_no=required|' .
            'receipt_no=required|total_gram=required|total_price=required|' .
            'timestamp=required';
        $this->registerParameter('buybackResponse', 'digest', '', '', 'makeGtpSignature|' . $signatureBuildingOptions);
        /*end buyback*/
    } 
}
