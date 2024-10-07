<?php

use Snap\api\fpx\M1Pay;

class TestM1Pay extends M1Pay
{
    // UAT endpoints
    protected const ENDPOINT_TOKEN = "https://keycloak.m1pay.com.my/auth/realms/master/protocol/openid-connect/token";
    protected const ENDPOINT_CREATE_TRANSACTION = "https://gateway-uat.m1pay.com.my/m1paywall/api/transaction";
    protected const ENDPOINT_GET_TRANSACTION_INFO = "https://gateway-uat.m1pay.com.my/m1paywall/api/m-1-pay-transactions";

    public function __construct($app)
    {
        $this->app = $app;
    }
    
    // public function verifySignedData($encoded, $plain) {return parent::verifySignedData($encoded, $plain);}
    // public function formatData($arr) {return parent::formatData($arr);}
    public function getMerchantId() {return $this->merchantId;}

}

class M1PayTest extends AuthenticatedTestCase
{
    static $fpx = null;
    static $randomFaker = null;
    static $paymentDetail = null;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$fpx = new TestM1Pay(self::$app);
        self::$randomFaker = \Faker\Factory::create('en_US');
        self::$randomFaker->seed();

        $data = self::sampleTransactionData();
        $paymentDetail = self::$app->mypaymentdetailStore()->create([
            'amount'    => $data['transactionAmount'],
            'paymentrefno' => $data['merchantOrderNo'],
            'sourcerefno'  => self::$randomFaker->swiftBicNumber,   // Get random swiftbic number
            'gatewayfee'    => '0.00',
            'customerfee'   => '0.00',
            'transactiondate' => (new \DateTime())->format('Y-m-d H:i:s'),
            'status'    => 1,
        ]);
        $paymentDetail = self::$app->mypaymentdetailStore()->save($paymentDetail);
        self::$paymentDetail = $paymentDetail;
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        // self::$app->persistTestStore('mypaymentdetail');
    }

    private static function sampleTransactionData()
    {
        $data = [
            "productDescription" => "Test product",
            'transactionAmount' => "1.01",
            "merchantOrderNo"  => self::$randomFaker->swiftBicNumber,
            "transactionCurrency" => "MYR",
            "merchantId" => self::$fpx->getMerchantId(),
        ];
        return $data;
    }

    public function testCreateTransaction()
    {
        $fpx = \Mockery::mock(TestM1Pay::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();

        $fpx->shouldReceive('signData')
            ->andReturn('ABCDEF');

        $fpx->shouldReceive('getAuthToken')
            ->andReturn("1234");

        $fpx->shouldReceive('doSendCreateTransaction')
            ->andReturn(new \GuzzleHttp\Psr7\Response(200, [], "http://www.test.com/?transactionId=1111&signedData=11"));


        $data = $fpx->initializeTransaction(self::$accountHolder, self::$paymentDetail, '');
        $this->assertArrayHasKey('redirect', $data);
        $this->assertNotNull($data['redirect']);
    }


}