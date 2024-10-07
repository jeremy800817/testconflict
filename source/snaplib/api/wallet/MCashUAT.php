<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Azam <azam@silverstream.my>
* @version 1.0
* @created 20-Oct-2021
*/

namespace Snap\api\wallet;

use Snap\api\exception\MyGtpWalletGatewayPaymentNotFound;
use Snap\object\MyAccountHolder;
use Snap\object\MyPaymentDetail;


class MCashUAT extends MCash
{
    // Production endpoints
    protected const ENDPOINT_PAYMENT = "https://newbackend.mcash.my/cross/hfive/paymentv01";
    protected const ENDPOINT_CHECK_PAYMENT = "https://newbackend.mcash.my/cross/hfive/transaction/status";
    protected const ACTION_BUY = "buy";
    
    protected $merchantId = null;
    protected $callbackUrl = null;

    protected function __construct($app)
    {
        parent::__construct($app);        
    }
}

?>