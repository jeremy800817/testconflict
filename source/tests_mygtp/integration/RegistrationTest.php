<?php

use Snap\object\MyAccountHolder;
use Snap\object\Partner;
use Snap\object\MyOccupationCategory;
use Snap\object\MyLocalizedContent;

final class RegistrationTest extends BaseTestCase
{
    protected static $partner1;
    protected static $partner2;
    protected static $params;

    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();

        $partner1 = self::$app->partnerStore()->create([
            'code' => 'CODE01',
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

        self::$partner1 = self::$app->partnerStore()->save($partner1);

        $partner2 = self::$app->partnerStore()->create([
            'code' => 'CODE02',
            'name' => 'Bank 2',
            'address' => 'Jalan Putero Tower 2',
            'postcode' => '43000',
            'state' => 'Selangor',
            'pricesourceid' => 2,
            'tradingscheduleid' => 2,
            'sapcompanysellcode1' => 'SellCode03',
            'sapcompanybuycode1' => 'BuyCode03',
            'sapcompanysellcode2' => 'SellCode05',
            'sapcompanybuycode2' => 'BuyCode05',
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
            'orderingmode' => Partner::MODE_BOTH
        ]);

        self::$partner2 = self::$app->partnerStore()->save($partner2);

        $occupationCategory = self::$app->myoccupationCategoryStore()->create([
            'category'    => 'IT',
            'description' => 'Software Engineer, Software Developer, Software Analyst, Mobile Developer',
            'status'      => MyOccupationCategory::STATUS_ACTIVE,
            'language'    => MyLocalizedContent::LANG_ENGLISH
        ]);

        $occupationCategory = self::$app->myoccupationCategoryStore()->save($occupationCategory);

        $action = 'register';
        $version = '1.0';
        $myFaker = self::getFaker('ms_MY');
        $usFaker = self::getFaker('en_US');

        $safeEmail            = $usFaker->safeEmail;
        $password             = $usFaker->password;
        $myKadNo              = $myFaker->myKadNumber;
        $occupationCategoryId = 1;
        $jobTitle             = $usFaker->jobTitle;
        $merchantCode         = $partner1->code;
        $preferredLang        = MyAccountHolder::LANG_BM;
        $fullName             = $myFaker->firstName . $myFaker->lastName;
        $referralBranchCode   = 'CODE1000';
        $phoneNumber          = '+' . $myFaker->mobileNumber(true, false);

        $safeEmail2            = $usFaker->safeEmail;
        $password2             = $usFaker->password;
        $myKadNo2              = $myFaker->myKadNumber;
        $occupationCategoryId2 = 2;
        $jobTitle2             = $usFaker->jobTitle;
        $merchantCode2         = $partner2->code;
        $preferredLang2        = MyAccountHolder::LANG_CN;
        $fullName2             = $myFaker->firstName . $myFaker->lastName;
        $phoneNumber2          = '+' . $myFaker->mobileNumber(true, false);

        self::$params = [
            [
                'version' => $version,
                'merchant_id' => $merchantCode,
                'action' => $action,
                'email' => $safeEmail,
                'full_name' => $fullName,
                'mykad_number' => $myKadNo,
                'phone_number' => $phoneNumber,
                'occupation_category_id' => $occupationCategoryId,
                'occupation' => $jobTitle,
                'preferred_lang' => $preferredLang,
                'referral_branch_code' => $referralBranchCode,
                'password' => $password,
                'confirm_password' => $password,
            ], [
                'version' => $version,
                'merchant_id' => $merchantCode2,
                'action' => $action,
                'email' => $safeEmail2,
                'full_name' => $fullName2,
                'mykad_number' => $myKadNo2,
                'phone_number' => $phoneNumber2,
                'occupation_category_id' => $occupationCategoryId2,
                'occupation' => $jobTitle2,
                'preferred_lang' => $preferredLang2,
                'password' => $password2,
                'confirm_password' => $password2
            ],
        ];
    }


    // public function testSuccessfulRegistration()
    // {
    //     // Suppress  output to console
    //     $this->setOutputCallback(function() {});

    //     // Register through api
    //     $output = self::$app->apiManager()->processMyGtpRequest(self::$params[0]);

    //     $account = self::$app->myaccountHolderStore()->searchTable()->select()->find(1);

    //     $this->assertNotNull($output);
    //     $this->assertTrue($output['success']);
    //     $this->assertNotNull($account->id);
    //     $this->assertEquals($account->email, self::$params[0]['email']);
    //     $this->assertEquals($account->firstname, self::$params[0]['first_name']);
    //     $this->assertEquals($account->middlename, self::$params[0]['middle_name']);
    //     $this->assertEquals($account->lastname, self::$params[0]['last_name']);
    //     $this->assertEquals($account->mykadno, self::$params[0]['mykad_number']);
    //     $this->assertEquals($account->phoneno, self::$params[0]['phone_number']);
    //     $this->assertEquals($account->occupation, self::$params[0]['occupation']);
    //     $this->assertEquals($account->occupationcategoryid, self::$params[0]['occupation_category_id']);
    //     $this->assertEquals($account->preferredlang, self::$params[0]['preferred_lang']);
    //     $this->assertEquals($account->referralbranchcode, self::$params[0]['referral_branch_code']);
    //     $this->assertNotNull($account->password);
    //     $this->assertNotEquals($account->password, self::$params[0]['password']);

    //     return $account->email;
    // }

    // /**
    //  * @depends             testSuccessfulRegistration
    //  */
    // public function testFailedRegistration($email)
    // {
    //     // Suppress  output to console
    //     $this->setOutputCallback(function() {});

    //     // Register through api, replace param with used email
    //     $output = self::$app->apiManager()->processMyGtpRequest(array_merge(self::$params[1], ['email' => $email]));

    //     $account = self::$app->myaccountHolderStore()->searchTable()->select()->find(1);
    //     $account2 = self::$app->myaccountHolderStore()->searchTable()->select()->find(2);

    //     $this->assertFalse($output['success']);
    //     $this->assertNotNull($account->id);
    //     $this->assertNull($account2->id);
    // }
}
