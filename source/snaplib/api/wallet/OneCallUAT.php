<?php

namespace Snap\api\wallet;


class OneCallUAT extends OneCall
{
    // UAT endpoints
    protected const ENDPOINT_SYSTEM_LOGIN      = 'https://test.onecall.my/api/Home/LoginApi/LoginExternal';
    protected const ENDPOINT_CUSTOMER_BALANCE  = 'https://test.onecall.my/api/Home/ExternalApi/CustomerBalance';
    protected const ENDPOINT_CUSTOMER_DEBIT    = 'https://test.onecall.my/api/Home/ExternalApi/CustomerDebit';

    protected function __construct($app)
    {
        parent::__construct($app);

    }
}


?>