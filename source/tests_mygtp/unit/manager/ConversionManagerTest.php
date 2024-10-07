<?php

use Snap\manager\MyGtpConversionManager;
use Snap\manager\RedemptionManager;
use Snap\object\MyAccountHolder;
use Snap\object\MyConversion;
use Snap\object\MyFee;
use Snap\object\Redemption;

class ConversionManagerTest extends AuthenticatedTestCase
{

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$app->eventmessageStore();

        $faker = self::getFaker();
        $tmpAccHolder = self::$accountHolder;

        $tmpAccHolder->addAddress($faker->address, "", $faker->city, $faker->postcode, $faker->state);
        $tmpAccHolder = self::$app->myaccountholderStore()->getById($tmpAccHolder->id);

        $accountMgr = self::$app->mygtpaccountManager();
        $accountMgr->editPincode($tmpAccHolder, "123456");

        $tmpAccHolder->amlastatus = MyAccountHolder::AMLA_PASSED;
        $tmpAccHolder->kycstatus = MyAccountHolder::KYC_PASSED;
        $tmpAccHolder = self::$app->myaccountholderStore()->save($tmpAccHolder);
        self::$accountHolder = self::$app->myaccountholderStore()->getById($tmpAccHolder->id);
    }

    public function testGenerateDataForRedemption()
    {
        $product = self::$app->productStore()->getByField('code', 'GS-999-9-1g');
        $convMgr = \Mockery::mock(MyGtpConversionManager::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();
        $data = $convMgr->generateRedemptionData(self::$accountHolder, self::$accountHolder->getPartner(), $product, 1);

        $this->assertNotNull($data);
    }

    /**
     * @expectedException Snap\api\exception\RedemptionError
     */
    public function testConfirmPaidConversion()
    {
        $conversion = self::$app->myconversionStore()->create([
            'status'    => MyConversion::STATUS_PAYMENT_PAID
        ]);

        $convMgr = \Mockery::mock(MyGtpConversionManager::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();
        $convMgr->shouldReceive("waitForConfirmConversionLock")->andReturns();
        $convMgr->shouldReceive("releaseConfirmConversionLock")->andReturns();
        $convMgr->shouldReceive("sendConversionFeeToSAP")->andReturns();
        $convMgr->doConfirmConversion($conversion);
    }

    public function testConversionSplitted()
    {
        $convMgr = \Mockery::mock(MyGtpConversionManager::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();
        $convMgr->shouldReceive("validateConversionRequest")->andReturns();
        $convMgr->shouldReceive("createPaymentForConversion")->andReturn(1);
        $convMgr->shouldReceive("notify")->andReturns();

        $redemption = new \stdClass();
        $redemption->id = 1;
        $redemptionMgr = \Mockery::mock(RedemptionManager::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();
        $redemptionMgr->shouldReceive("createRedemption")->andReturn($redemption);
        $redemptionMgr->shouldReceive("confirmRedemption")->andReturnUsing(function($rdm, $partner, $bId, $details) {
            $rdm->status = Redemption::STATUS_CONFIRMED;
            return $rdm;
        });

        $paymentMode = MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX;
        $conversions = $convMgr->doConversion(self::$accountHolder, self::$accountHolder->getPartner(), self::createDummyProduct(false, 25), 10, "1.0my", $paymentMode, 'CODE','',$redemptionMgr);
        $this->assertTrue(count($conversions) > 1);
    }
}

?>