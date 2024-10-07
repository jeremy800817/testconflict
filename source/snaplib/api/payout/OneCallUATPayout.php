<?php

namespace Snap\api\payout;

class OneCallUATPayout extends OneCallPayout
{
    protected const ENDPOINT_SYSTEM_LOGIN      = 'https://test.onecall.my/api/Home/LoginApi/LoginExternal';
    protected const ENDPOINT_CUSTOMER_CREDIT   = 'https://test.onecall.my/api/Home/ExternalApi/CustomerCredit';

    public function __construct($app)
    {
        parent::__construct($app);
    }
}
?>