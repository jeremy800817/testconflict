<?php

namespace Snap\api\wallet;

class GoPayzUATConversion extends GoPayzUAT
{
    protected function __construct($app)
    {
        parent::__construct($app);

        $this->merchantId = $app->getConfig()->{'mygtp.gopayz.conversionclientid'};
        if (! $this->merchantId) {
            throw new \Exception("GoPayz conversion client ID not provided");
        }
    }
}


?>