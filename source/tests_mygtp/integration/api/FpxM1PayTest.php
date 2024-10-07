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
        parent::__construct($app);
    }
    
    public function getAuthToken() {return parent::getAuthToken();}
    public function getMerchantId() {return $this->merchantId;}
}

class M1PayTest extends AuthenticatedTestCase
{
    static $fpx = null;
    static $randomFaker = null;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$fpx = new TestM1Pay(self::$app);
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
            "productDescription" => "Test product",
            'transactionAmount' => self::$randomFaker->randomFloat(2, 1, 5),
            "merchantOrderNo"  => self::$randomFaker->swiftBicNumber,
            "transactionCurrency" => "MYR",
            "merchantId" => self::$fpx->getMerchantId(),
        ];
        return $data;
    }

    public function testGetAuthToken()
    {
        $authToken = self::$fpx->getAuthToken();

        $this->assertNotEmpty($authToken);
    }

    public function testCreateTransaction()
    {
        $data = $this->sampleTransactionData();
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

        $data = self::$fpx->initializeTransaction(self::$accountHolder, $paymentDetail, $data['productDescription']);
        $paymentDetail = self::$app->mypaymentdetailStore()->getById($paymentDetail->id);

        $this->assertNotNull($data['redirect']);
        $this->assertEquals($paymentDetail->gatewaystatus, 'REQUEST');
        return ['location' =>$data['redirect'], 'detail' => $paymentDetail];
    }

    /**
     * @depends testCreateTransaction
     */
    public function testGetTransactionStatus($arr)
    {
        $paymentDetail = $arr['detail'];
        $paymentDetail = self::$app->mypaymentdetailStore()->getById($paymentDetail->id);

        $response = self::$fpx->getPaymentStatus($paymentDetail);
        $this->assertNotNull($response);
        $this->assertArrayHasKey('transactionStatus', $response); 
    }



}