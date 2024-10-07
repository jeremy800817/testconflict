<?php

use Snap\api\mygtp\MyGtpApiProcessor1_0;
use Snap\object\MyPriceAlert;

class MyGtpApiProcessor1_0Test extends BaseTestCase {
    private $processor = null;
    public function setUp()
    {
        $this->processor = \Mockery::mock(MyGtpApiProcessor1_0::class);
        $this->processor->shouldAllowMockingProtectedMethods()->makePartial();
    }

    function testPriceAlertResponseTypes()
    {
        $priceAlert = self::$app->mypricealertStore()->create([
            'type'  => MyPriceAlert::TYPE_BUY,
            'amount'    => '111.11',
            'accountholderid'   => 1,
            'priceproviderid'   => 1,
            'status'            => MyPriceAlert::STATUS_ACTIVE
        ]);
        $formatted = $this->processor->formatPriceAlertList(self::$app, $priceAlert);

        $response = $formatted[0];
        $this->assertNotNull($response);

        $this->assertTrue(is_int($response['id']));
        $this->assertTrue(is_float($response['price']));
    }
}
