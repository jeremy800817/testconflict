<?php

use GuzzleHttp\Psr7\Response;
use Snap\api\wallet\GoPayz;

class TestGoPayz extends GoPayz
{
    // UAT endpoints
    protected const ENDPOINT_CHECK_PAYMENT = "https://dev.finexusgroup.com:4445/standalone/partnerservice/checkPayment";

    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function getMerchantId() {return $this->merchantId;}

}

class GoPayzTest extends AuthenticatedTestCase
{
    static $gopayz = null;
    static $randomFaker = null;
    static $paymentDetail = null;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$gopayz = new TestGoPayz(self::$app);
        self::$randomFaker = \Faker\Factory::create('en_US');
        self::$randomFaker->seed();

        $data = self::sampleTransactionData();
        $paymentDetail = self::$app->mypaymentdetailStore()->create([
            'accountholderid' => self::$accountHolder->id,
            'amount'    => $data['transactionAmount'],
            'paymentrefno' => $data['refReqNo'],
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
            "refReqNo" => "R20210624090137",
            'transactionAmount' => self::$randomFaker->randomFloat(2, 1, 5),
            "channel" => self::$gopayz->getMerchantId(),
        ];
        return $data;
    }

    public function testGetTransactionStatus()
    {
        $gopayz = \Mockery::mock(TestGoPayz::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();

        $gopayz->shouldReceive('doSendGetTransactionStatus')
            ->andReturn((new Response(200, [], json_encode([
                'result' => [[
                    'status' => 'S',
                    'paymentID' => 'ABCDEF',
                    'reqRefNo' => 'R20210624090137'
                ]]
            ]))));

        $response = $gopayz->getPaymentStatus(null);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('paymentID', $response);
        $this->assertArrayHasKey('reqRefNo', $response);
    }


}