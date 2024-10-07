<?php

namespace Snap\api\payout;

class WavPayUATPayout extends WavPayPayout 
{
    protected const ENDPOINT_PAYMENT = "https://mapi-dev.Wavpay.net/api/user/merchantuserid/user-credit-transaction";
    //protected const ENDPOINT_CHECK_PAYMENT = "https://newbackend.mcash.my/cross/hfive/transaction/status";
    protected const ACTION_SELL = "sell";

    protected function __construct($app)
    {
        parent::__construct($app);
    }
}
?>