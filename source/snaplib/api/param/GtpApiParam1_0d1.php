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
class GtpApiParam1_0d1 extends GtpApiParam
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
        
    }
}
