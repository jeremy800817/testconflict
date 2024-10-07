<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 12-Nov-2020
*/

namespace Snap\api\wallet;

use Snap\api\exception\MyGtpWalletGatewayPaymentNotFound;
use Snap\IObservation;
use Snap\object\MyPaymentDetail;


class GoPayzUAT extends GoPayz
{
    // UAT endpoints
    protected const ENDPOINT_CHECK_PAYMENT = "https://dev.finexusgroup.com:4445/standalone/partnerservice/checkPayment";
    protected const ENDPOINT_PAYMENT_PREAUTH = "https://dev.finexusgroup.com:4445/standalone/doPreAuth";
    protected const ENDPOINT_TRANSACTION_STATUS = "https://dev.finexusgroup.com:4445/standalone/partnerservice/transactionStatusEnquiry";

    protected $merchantId = null;

    protected function __construct($app)
    {
        parent::__construct($app);

        $this->merchantId = $app->getConfig()->{'mygtp.gopayz.clientid'};
        if (! $this->merchantId) {
            throw new \Exception("GoPayz client ID not provided");
        }
    }
}


?>