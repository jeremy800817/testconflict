<?php

use Snap\object\Partner;
use Snap\object\MyToken;
use Snap\object\MyAccountHolder;

final class ForgotPasswordTest extends BaseTestCase
{
    protected static $partner;
    protected static $params;
    protected static $accountHolder;

    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();

        $partner = self::$app->partnerStore()->create([
            'code' => 'PARTNER001',
            'name' => 'Bank 1',
            'address' => 'Jalan Putero Tower 1',
            'postcode' => '43000',
            'state' => 'Selangor',
            'pricesourceid' => 1,
            'tradingscheduleid' => 1,
            'sapcompanysellcode1' => 'SellCode01',
            'sapcompanybuycode1' => 'BuyCode01',
            'sapcompanysellcode2' => 'SellCode02',
            'sapcompanybuycode2' => 'BuyCode02',
            'dailybuylimitxau' => 1.2,
            'dailyselllimitxau' => 1.2,
            'pricelapsetimeallowance' => 12,
            'orderingmode' => Partner::MODE_WEB,
            'autosubmitorder' => 1,
            'autocreatematchedorder' => 1,
            'orderconfirmallowance' => 1,
            'ordercancelallowance' => 1,
            'apikey' => 1234,
            'createdon' => new \DateTime('now'),
            'modifiedon' => new \DateTime('now'),
            'type' => Partner::TYPE_CUSTOMER,
            'status' => Partner::STATUS_ACTIVE,
            'orderingmode' => Partner::MODE_API
        ]);

        self::$partner = self::$app->partnerStore()->save($partner);

        $version = '1.0';
        $myFaker = self::getFaker('ms_MY');
        $usFaker = self::getFaker('en_US');

        $safeEmail            = $usFaker->safeEmail;
        $password             = $usFaker->password;
        $newPassword          = $usFaker->password;
        $myKadNo              = $myFaker->myKadNumber;
        $occupationCategoryId = 1;
        $jobTitle             = $usFaker->jobTitle;
        $merchantCode         = $partner->code;
        $preferredLang        = MyAccountHolder::LANG_BM;
        $fullName             = $myFaker->firstName . $myFaker->lastName;
        $referralBranchCode   = 'CODE1000';
        $phoneNumber          = '+' . $myFaker->mobileNumber(true, false);

        self::$params = [
            [
                'version' => $version,
                'merchant_id' => $merchantCode,
                'action' => 'forgot_password',
                'email' => $safeEmail,
            ], [
                'version' => $version,
                'merchant_id' => $merchantCode,
                'action' => 'reset_password',
                'email' => $safeEmail,
                'password' => $newPassword,
                'confirm_password' => $newPassword
            ],
        ];


        $accountHolder = self::$app->myaccountHolderStore()->create([
            'email' => $safeEmail,
            'fullname'  => $fullName,
            'mykadno' => $myKadNo,
            'phoneno' => $phoneNumber,
            'occupation' => $jobTitle,
            'occupationcategoryid' => $occupationCategoryId,
            'preferredlang' => $preferredLang,
            'referralbranchcode' => $referralBranchCode,
            'password' => $password,
            'partnerid' => $partner->id,
            'status' => MyAccountHolder::STATUS_ACTIVE
        ]);

        self::$accountHolder = self::$app->myaccountHolderStore()->save($accountHolder);
    }

    // public function testRequestForgotPassword()
    // {
    //     self::$app->apiManager()->processMyGtpRequest(self::$params[0]);

    //     $token = self::$app->mytokenStore()
    //         ->searchTable()
    //         ->select()
    //         ->where('type', '=', MyToken::TYPE_PASSWORD_RESET)
    //         ->where('accountholderid', '=', self::$accountHolder->id)
    //         ->one();

    //     $this->assertNotNull($token->id);
    //     $output = json_decode($this->getActualOutput());
    //     $this->setOutputCallback(function () {
    //     });
    //     $this->assertTrue($output->success);

    //     return $token;
    // }

    // /** @depends testRequestForgotPassword */
    // public function testResetPassword($token)
    // {
    //     self::$app->apiManager()->processMyGtpRequest(array_merge(self::$params[1], ['code' => $token->token]));

    //     $output = json_decode($this->getActualOutput());
    //     $this->setOutputCallback(function () {
    //     });
    //     $this->assertTrue($output->success);

    //     return $token;
    // }

    // /**
    //  * @depends testResetPassword
    //  */
    // public function testResetPasswordUsingInvalidCode($token)
    // {
    //     self::$app->apiManager()->processMyGtpRequest(array_merge(self::$params[1], ['code' => $token->token]));
    //     $output = json_decode($this->getActualOutput());
    //     $this->setOutputCallback(function () {
    //     });
    //     $this->assertFalse($output->success);
    // }
}
