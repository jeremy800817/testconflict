<?php

namespace Snap\api\wallet;

class WavPayConversion extends WavPay
{
    protected function __construct($app)
    {
        parent::__construct($app);

        $this->merchantId       = $app->getConfig()->{'mygtp.wavpay.merchantidsetbywavpay'};
        $this->redirectUrl      = $app->getConfig()->{'mygtp.wavpay.redirectconversionurl'};
        if (! $this->merchantId) {
            throw new \Exception("WavPay conversion client ID not provided");
        }
    }
}


?>