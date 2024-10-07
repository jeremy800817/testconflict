<?php

namespace Snap\api\payout;

class HopeGoldUATPayout extends HopeGoldPayout
{
    protected const ENDPOINT_PAYMENT = "https://apistg.tybgold.com/purchase-settlement-ace";
    protected const ENDPOINT_CHECKING = "https://apistg.tybgold.com/purchase-settlement-checking-ace";

    protected $merchantId = null;
    protected $callbackUrl = null;

    public function __construct($app)
    {
        parent::__construct($app);
    }
}
?>