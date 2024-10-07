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


class RedOneUAT extends RedOne
{
    // Production endpoints
    protected const ENDPOINT_PAYMENT = "";
    protected const ENDPOINT_CHECK_PAYMENT = "";
    protected const ACTION_BUY = "buy";
    
    protected $merchantId = null;
    protected $callbackUrl = null;

    protected function __construct($app)
    {
        parent::__construct($app);

        $this->directurl = $app->getConfig()->{'mygtp.redone.buy.directurl'};
        $this->indirecturl = $app->getConfig()->{'mygtp.redone.buy.indirecturl'};
        $this->secretkey = $app->getConfig()->{'mygtp.redone.secretkey'};
    }
}

?>