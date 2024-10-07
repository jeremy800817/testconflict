<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Dianah <Dianah@silverstream.my>
* @version 1.0
* @created 30-Mar-2022
*/

namespace Snap\api\wallet;

class ToyyibConversion extends Toyyib
{
    // Production endpoints
    protected const ENDPOINT_PAYMENT = "https://api.tybgold.com/purchase-settlement-ace";
    protected const ENDPOINT_CHECKING = "https://api.tybgold.com/purchase-settlement-checking-ace";

    protected const STATUS_SUCCESS = 'success';
    protected const STATUS_FAILED = 'failed';
    
    protected $merchantId = null;
    protected $secretkey = null;
    protected $usertoken = null;

    protected function __construct($app)
    {
        parent::__construct($app);
        
        $this->merchantId  = $app->getConfig()->{'mygtp.toyyib.merchant'};
        $this->secretkey   = $app->getConfig()->{'mygtp.toyyib.secretkey'};

        if (! $this->merchantId) {
            throw new \Exception("Toyyib merchant ID not provided");
        }
    }
}


?>