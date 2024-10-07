<?php

namespace Snap\api\payout;

class MCashUATPayout extends MCashPayout 
{
    protected const ENDPOINT_PAYMENT = "https://newbackend.mcash.my/cross/hfive/paymentv01";
    protected const ENDPOINT_CHECK_PAYMENT = "https://newbackend.mcash.my/cross/hfive/transaction/status";
    protected const ACTION_SELL = "sell";

    protected function __construct($app)
    {
        parent::__construct($app);
    }
}
?>