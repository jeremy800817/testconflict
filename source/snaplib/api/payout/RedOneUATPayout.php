<?php

namespace Snap\api\payout;

class RedOneUATPayout extends RedOnePayout 
{
    protected const ENDPOINT_PAYMENT = "https://login.redone.com.my/ewalletpaymentgateway/api/v1/Ewallet/Sell";
    //protected const ENDPOINT_CHECK_PAYMENT = "https://newbackend.mcash.my/cross/hfive/transaction/status";
    protected const ACTION_SELL = "sell";

    protected function __construct($app)
    {
        parent::__construct($app);
    }
}
?>