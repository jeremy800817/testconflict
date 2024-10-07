<?php

namespace Snap\api\payout;

class GoPayzUATPayout extends GoPayzPayout
{
    public function __construct($app)
    {
        parent::__construct($app);

    }

    public function createPayout($accHolder, $disbursement)
    {
        parent::createPayout($accHolder, $disbursement);
        $list = scandir($this->requestpath);
        $this->logDebug(__METHOD__ . ':  Scanned Payout Request File for GoPayz: ' . json_encode($list));

        return true;
    }
}
?>