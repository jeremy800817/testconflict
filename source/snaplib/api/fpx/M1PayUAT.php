<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 12-Nov-2020
*/

namespace Snap\api\fpx;

use Snap\api\mygtp\MyGtpApiSender;
use Snap\App;
use Snap\IObservation;
use Snap\object\ApiLogs;
use Snap\object\MyPaymentDetail;

use function GuzzleHttp\Psr7\parse_query;

class M1PayUAT extends M1Pay
{
    // UAT endpoints
    protected const ENDPOINT_TOKEN = "https://keycloak.m1pay.com.my/auth/realms/master/protocol/openid-connect/token";
    protected const ENDPOINT_CREATE_TRANSACTION = "https://gateway-uat.m1pay.com.my/m1paywall/api/transaction";
    protected const ENDPOINT_GET_TRANSACTION_INFO = "https://gateway-uat.m1pay.com.my/m1paywall/api/m-1-pay-transactions";
}


?>