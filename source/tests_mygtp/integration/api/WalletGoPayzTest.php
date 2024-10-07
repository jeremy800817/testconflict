<?php

use Snap\api\wallet\GoPayz;

class TestGoPayz extends GoPayz
{
    // UAT endpoints
    protected const ENDPOINT_CHECK_PAYMENT = "https://dev.finexusgroup.com:4445/standalone/partnerservice/checkPayment";

    public function __construct($app)
    {
        parent::__construct($app);
    }
    
    public function getMerchantId() {return $this->merchantId;}
}

class GoPayzTest extends AuthenticatedTestCase
{
    static $wallet = null;
    static $randomFaker = null;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$wallet = new TestGoPayz(self::$app);
        self::$randomFaker = \Faker\Factory::create('en_US');
        self::$randomFaker->seed();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$app->persistTestStore('mypaymentdetail');
    }

    private function sampleTransactionData()
    {
        $faker = self::$randomFaker;
        $data = [
            "refReqNo" => "R20210624090137",
            'transactionAmount' => self::$randomFaker->randomFloat(2, 1, 5),
            "channel" => self::$wallet->getMerchantId(),
        ];
        return $data;
    }

    public function testCreateTransaction()
    {
        $data = $this->sampleTransactionData();
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

        $data = self::$wallet->initializeTransaction(self::$accountHolder, $paymentDetail, $data['productDescription']);
        $paymentDetail = self::$app->mypaymentdetailStore()->getById($paymentDetail->id);

        $this->assertTrue($data);
        $this->assertEquals($paymentDetail->gatewaystatus, 'PENDING');
        return ['detail' => $paymentDetail];
    }

    /**
     * @depends testCreateTransaction
     */
    public function testGetTransactionStatus($arr)
    {
        $paymentDetail = $arr['detail'];
        $paymentDetail = self::$app->mypaymentdetailStore()->getById($paymentDetail->id);

        $response = self::$wallet->getPaymentStatus($paymentDetail);
        $this->assertNotNull($response);
        $this->assertArrayHasKey('status', $response); 
    }



}