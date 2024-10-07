<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Dianah <dianah@silverstream.my>
* @version 1.0
* @created 16-Aug-2022
*/

namespace Snap\api\wallet;

use Snap\api\exception\MyGtpWalletGatewayPaymentNotFound;
use Snap\object\MyAccountHolder;
use Snap\object\MyPaymentDetail;


class WavPayUAT extends WavPay
{
    // Production endpoints
    protected const ENDPOINT_PAYMENT = "https://mapi-dev.wavpay.net/api/merchant_web/payment_initial";
    protected const ENDPOINT_CHECK_PAYMENT = "https://mapi-dev.wavpay.net/api/merchant_web/query";
    protected const ACTION_BUY = "buy";
    
    protected $merchantId = null;
    protected $callbackUrl = null;

    protected function __construct($app)
    {
        parent::__construct($app);        
    }
}

?>