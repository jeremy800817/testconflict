<?php

use Snap\App;
use Snap\IObservation;
use Snap\manager\MyGtpAccountManager;
use Snap\manager\MyGtpPushNotificationManager;
use Snap\manager\MyGtpStorageManager;
use Snap\manager\MyGtpTransactionManager;
use Snap\object\EventMessage;
use Snap\object\MyAccountClosure;
use Snap\object\MyAccountHolder;
use Snap\object\MyDisbursement;
use Snap\object\MyLedger;
use Snap\object\MyLocalizedContent;
use Snap\object\MyPartnerApi;
use Snap\object\MyPushNotification;
use Snap\object\TradingSchedule;
use Snap\object\MyToken;
use Snap\object\MyGtpEventConfig;
use Snap\util\ekyc\Innov8tifProvider;
use Snap\object\MyOccupationCategory;
use Snap\object\MyOccupationSubCategory;
Use Snap\Object\SnapObject;

require MOCKS_DIR."TestKycProvider.php";

final class AccountManagerTest extends BaseTestCase
{
    protected static $partner1;
    protected static $partner2;
    protected static $occupationCategory1;
    protected static $occupationCategory2;
    protected static $occupationSubCategory1;
    protected static $occupationSubCategory2;
    protected static $accountHolder;
    protected static $pincode;
    protected static $branchcode1;
    protected static $branchcode2;
    protected static $product;
    protected static $manager;


    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
        $faker = self::getFaker('ms_MY');

        self::$app->partnerStore();
        self::$app->priceValidationStore();
        self::$app->userStore();

        self::$app->mybankstore();
        self::$app->redemptionstore();
        self::$app->myoccupationcategoryStore();
        self::$app->myoccupationsubcategoryStore();
        self::$app->myaddressStore();
        self::$app->myconversionstore();
        self::$app->productstore();
        self::$app->orderstore();
        self::$app->mygoldtransactionstore();
        self::$app->myledgerstore();
        self::$app->myscreeningliststore();
        self::$app->myscreeningmatchlogstore();
        self::$app->mykycsubmissionstore();
        self::$app->myaccountholderstore();

        // Tempory fix for event trigger view requiring eventmessage table 
        self::$app->eventMessageStore();
        
        self::$partner1 = self::createDummyPartner();        
        self::$partner2 = self::createDummyPartner();
        self::$occupationCategory1 = self::createDummyOccupationCategory(false);
        self::$occupationCategory2 = self::createDummyOccupationCategory(false);
        self::$occupationSubCategory1 = self::createDummyOccupationSubCategory(self::$occupationCategory1, true);
        self::$occupationSubCategory2 = self::createDummyOccupationSubCategory(self::$occupationCategory2, true);
        self::$accountHolder = self::createDummyAccountHolder(self::$partner1);
        self::$pincode = $faker->numberBetween(100000, 999999);
        self::$branchcode1 = current(self::$partner1->getBranches())->code;
        self::$branchcode2 = current(self::$partner2->getBranches())->code;

        // Partner 1
        $product = self::$app->productStore()->getByField('code', 'DG-999-9');
        self::$partner1->registerService( $product,1,'abcsapgroup',0,0,1,1,1,1,1,1000,1000,1000,1000,1000,1000, 1000, 1000, 1000, 1000);
        $provider = self::$app->priceproviderStore()->getForPartnerByProduct(self::$partner1, $product);
        $newPriceStream = self::$app->priceStreamStore()->create([
            'pricesourceid' => $provider->pricesourceid,
            'providerid' => $provider->id,
            'providerpriceid' => 123,
            'pricesourceon' => new \Datetime,
            'currencyid' => 1,
            'companybuyppg' => 179,
            'companysellppg' => 200,
            'uuid' => 'abcderf11111',
            'createdon' => new \Datetime,
            'modifiedon' => new \Datetime,
            'status' => 1
        ]);
        self::$app->priceStreamStore()->save($newPriceStream);
    }

    /**
     * @dataProvider
     */
    public function testSuccessfulRegistration()
    {
        $faker = self::getFaker('ms_MY');

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();

        /** @var \Snap\object\MyBank */
        $bank  = self::createDummyBank();
        $bank2 = self::createDummyBank();

        $fullName = $faker->firstName . $faker->lastName;
        $myKadNumber = $faker->myKadNumber;
        $mobileNumber = '+' . $faker->mobileNumber(true, false);
        $occupationCategory = self::$occupationCategory1;
        $occupationSubCategory = self::$occupationSubCategory1;
        //$occupation = $faker->jobTitle;
        $email = $faker->safeEmail;
        $password = $faker->password;
        $preferredLang = MyAccountHolder::LANG_BM;
        $branchCode = self::$branchcode1;
        $nokFullName = $faker->name;
        $nokMykadNumber = $faker->myKadNumber;
        $nokPhone = '+'.$faker->mobileNumber(true, false);
        $nokEmail = $faker->safeEmail;
        $nokAddress = $faker->streetName;
        $nokRelationship = $faker->word;

        $accountHolder = $accMgr->register(
            self::$partner1,
            $fullName,
            $myKadNumber,
            $mobileNumber,
            $occupationCategory,
            $occupationSubCategory,
            $email,
            $password,
            $preferredLang,
            $branchCode,
            $nokFullName,
            $nokMykadNumber,
            $nokPhone,
            $nokEmail,
            $nokAddress,
            $nokRelationship,
            '', '',
            true
        );

        $fullName2 = $faker->firstName.$faker->lastName;
        $myKadNumber2 = $faker->myKadNumber;
        $mobileNumber2 = '+' . $faker->mobileNumber(true, false);
        $occupationCategory2 = self::$occupationCategory2;
        //$occupation2 = $faker->jobTitle;
        $occupationSubCategory2 = self::$occupationSubCategory2;
        $email2 = $faker->safeEmail;
        $password2 = $faker->password;
        $branchCode2 = self::$branchcode2;
        $nokFullName2 = $faker->name;
        $nokMykadNumber2 = $faker->myKadNumber;
        $nokPhone2 = '+'.$faker->mobileNumber(true, false);
        $nokEmail2 = $faker->safeEmail;
        $nokAddress2 = $faker->streetName;
        $nokRelationship2 = $faker->word;

        $accMgrMock = \Mockery::mock(MyGtpAccountManager::class, [self::$app]);
        $accMgrMock->shouldAllowMockingProtectedMethods()->makePartial();
        $accMgrMock->shouldReceive("sendEmailVerification")->andReturn(true);
        $accMgrMock->shouldReceive("notify")->andReturn();

        $verificationCode = $accMgrMock->sendPhoneVerification(self::$partner2, $mobileNumber2)->token;
        $accountHolder2 = $accMgrMock->register(
            self::$partner2,
            $fullName2,
            $myKadNumber2,
            $mobileNumber2,
            $occupationCategory2,
            $occupationSubCategory2,
            $email2,
            $password2,
            null,
            $branchCode2,
            $nokFullName2,
            $nokMykadNumber2,
            $nokPhone2,
            $nokEmail2,
            $nokAddress2,
            $nokRelationship2,
            $verificationCode
        );

        $refreshedAccountHolder = self::$app->myaccountHolderStore()->getById($accountHolder->id);
        $refreshedAccountHolder2 = self::$app->myaccountHolderStore()->getById($accountHolder2->id);


        $this->assertNotEquals($refreshedAccountHolder->email, $refreshedAccountHolder2->email);
        $this->assertEquals($refreshedAccountHolder->email, $accountHolder->email);
        $this->assertEquals($refreshedAccountHolder2->email, $accountHolder2->email);

        $this->assertEquals(self::$partner1->id, $accountHolder->partnerid);
        $this->assertEquals($fullName, $accountHolder->fullname);
        $this->assertEquals($myKadNumber, $accountHolder->mykadno);
        $this->assertEquals($mobileNumber, $accountHolder->phoneno);
        $this->assertEquals($occupationCategory->id, $accountHolder->occupationcategoryid);
        $this->assertEquals($occupationSubCategory->id, $accountHolder->occupationsubcategoryid);
        //$this->assertEquals($occupation, $accountHolder->occupation);
        $this->assertEquals($email, $accountHolder->email);
        $this->assertNotEquals($password, $accountHolder->password);
        $this->assertEquals($preferredLang, $accountHolder->preferredlang);
        $this->assertEquals($branchCode, $accountHolder->referralbranchcode);
        $this->assertEquals($nokFullName, $accountHolder->nokfullname);
        $this->assertEquals($nokMykadNumber, $accountHolder->nokmykadno);

        // Check default lang
        $this->assertNotNull($accountHolder2->preferredlang);
        $this->assertEquals(MyAccountHolder::LANG_EN, $accountHolder2->preferredlang);
        $this->assertEquals(MyAccountHolder::STATUS_ACTIVE, $accountHolder->status);
        $this->assertEquals(MyAccountHolder::STATUS_INACTIVE, $accountHolder2->status);

        return $refreshedAccountHolder;
    }

    public function testValidSubCategory(){

        // Create dummy partner
        $partner = self::createDummyPartner();
        // Create Dummy Occupation Category
        $faker = self::getFaker("ms_MY");
        // fake branch code
        $branchCode = current($partner->getBranches())->code;

        $occupationCat =  self::createDummyOccupationCategory();
        
        // Create dummy Sub Occupation Category
        $occupationSubCat = self::createDummyOccupationSubCategory($occupationCat, false);
        self::$app->mygtpaccountManager()->register(
            $partner,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $occupationCat,
            $occupationSubCat,
            $faker->safeEmail,
            $faker->password,
            MyLocalizedContent::LANG_ENGLISH,
            $branchCode,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $faker->safeEmail,
            $faker->address,
            $faker->word,
            '',
            '',
            true
        );
        
        // Check if occupation id matches subcat occupationcategoryid
        $this->assertEquals($occupationCat->id, $occupationSubCat->occupationcategoryid);
        
    }

    /**
     * @expectedException Snap\api\exception\MyGtpOccupationCategoryWithIllegalSub
     */
    public function testHasSubButNotMatchingCategoryId(){
        // Create dummy partner
        $partner = self::createDummyPartner();
        // Create Dummy Occupation Category
        $faker = self::getFaker("ms_MY");
        // fake branch code
        $branchCode = current($partner->getBranches())->code;
        $occupationCat =  self::createDummyOccupationCategory();
        // Create dummy Sub Occupation Category
        $occupationSubCatDB = self::createDummyOccupationSubCategory($occupationCat, false);
        $invalidSubCat = self::createDummyOccupationSubCategory(self::createDummyOccupationCategory(), false);
        $account = self::$app->mygtpaccountManager()->register(
            $partner,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $occupationCat,
            $invalidSubCat,
            $faker->safeEmail,
            $faker->password,
            MyLocalizedContent::LANG_ENGLISH,
            $branchCode,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $faker->safeEmail,
            $faker->address,
            $faker->word,
            '',
            '',
            true
        );
    }

    /**
     * @expectedException Snap\api\exception\MyGtpOccupationCategoryWithIllegalSub
     */
    public function testNoSubButHasSubInput(){

        // Create dummy partner
        $partner = self::createDummyPartner();
        // fake branch code
        $branchCode = current($partner->getBranches())->code;

        $occupationCat =  self::createDummyOccupationCategory();
        
        // Create dummy Sub Occupation Category
        $faker = self::getFaker("ms_MY");
        // Create dummy subcat without saving
        $occupationSubCat = self::$app->myoccupationsubcategoryStore()->create([
            'occupationcategoryid' => $occupationCat->id,
            'language'  => MyLocalizedContent::LANG_BAHASA,
            'politicallyexposed' => (int)filter_var(false, FILTER_VALIDATE_BOOLEAN),
            'name'   => $faker->sentence($faker->numberBetween(1,4)),
            'code'  => $faker->word,
            'status'    => MyOccupationSubCategory::STATUS_ACTIVE
        ]);

        self::$app->mygtpaccountManager()->register(
            $partner,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $occupationCat,
            $occupationSubCat,
            $faker->safeEmail,
            $faker->password,
            MyLocalizedContent::LANG_ENGLISH,
            $branchCode,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $faker->safeEmail,
            $faker->address,
            $faker->word,
            '',
            '',
            true
        );
    }
    
    /**
     * @expectedException Snap\api\exception\MyGtpOccupationCategoryWithIllegalSub
     */
    public function testHasSubButEmptySubInput(){
        // Create dummy partner
        $partner = self::createDummyPartner();

        // fake branch code
        $branchCode = current($partner->getBranches())->code;
        $occupationCat =  self::createDummyOccupationCategory();
        
        // Create dummy Sub Occupation Category
        $faker = self::getFaker("ms_MY");
        $occupationSubCatDB = self::createDummyOccupationSubCategory($occupationCat, false);

        self::$app->mygtpaccountManager()->register(
            $partner,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $occupationCat,
            null,
            $faker->safeEmail,
            $faker->password,
            MyLocalizedContent::LANG_ENGLISH,
            $branchCode,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $faker->safeEmail,
            $faker->address,
            $faker->word,
            '',
            '',
            true
        );
    }

    public function testSuccessfulRegistrationWithEmptySubCategory(){
        // Create dummy partner
        $partner = self::createDummyPartner();
        // fake branch code
        $branchCode = current($partner->getBranches())->code;
        $occupationCat =  self::createDummyOccupationCategory();

        // Create dummy Sub Occupation Category
        $faker = self::getFaker("ms_MY");

        self::$app->mygtpaccountManager()->register(
            $partner,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $occupationCat,
            null,
            $faker->safeEmail,
            $faker->password,
            MyLocalizedContent::LANG_ENGLISH,
            $branchCode,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $faker->safeEmail,
            $faker->address,
            $faker->word,
            '',
            '',
            true
        );
        
    }

    public function testAddEventJobIfOccupationSubCategoryHasPep(){

        // Create dummy partner
        $partner = self::createDummyPartner();

        // This partner id is only for local testing (since event subscriber refers to partnerid)
        // $partnerID = (self::$app->getConfig()->{'gtp.bmmb.partner.id'} != null) ?self::$app->getConfig()->{'gtp.bmmb.partner.id'} : 45;
        // $partner = self::$app->partnerStore()->getById($partnerID);

        // Create Dummy Occupation Category
        $faker = self::getFaker("ms_MY");
        // Old event job
        $eventjobOldMax = self::$app->eventjobStore()->searchTable(false)->select()->max('id');

        // Create dummy message
        $message = self::$app->eventMessageStore()->create([
            'code' => self::createDummyCode(),
            'replace' => '',
            'subject' => $faker->sentence(),
            'content' => $faker->paragraph(),
            'status'  => EventMessage::STATUS_ACTIVE
        ]);
        $message = self::$app->eventMessageStore()->save($message);
        $partnerid = $partner->id;

        // Create A Dummy Trigger
        // Follow action 
        $eventTrigger =  self::$app->eventTriggerStore()->create([
            'grouptypeid' => 100,
            'moduleid' => 100,
            'actionid' => IObservation::ACTION_OTHER,
            'matcherclass' => '\Snap\object\MyGtpEventTriggerMatcher',
            'processorclass' => '\Snap\object\EmailEventProcessor',
            'messageid' => $message->id,
            'observableclass' => '\Snap\manager\MyGtpAccountManager',
            'oldstatus' => SnapObject::STATUS_ACTIVE,
            'newstatus' =>  SnapObject::STATUS_ACTIVE,
            'objectclass' => '\Snap\object\MyAccountHolder',
            'storetolog' => SnapObject::STATUS_ACTIVE,
            'groupidfieldname' => 'partnerid',
            'evalcode' => '',
            'status' => SnapObject::STATUS_ACTIVE,
            
        ]);

        $eventTrigger = self::$app->eventTriggerStore()->save($eventTrigger);
        // Create Dummy Subscriber
        $eventSubscriber = self::$app->eventSubscriberStore()->create([
            'triggerid' => $eventTrigger->id,
            'groupid' => $partner->id,
            'receiver' => $faker->safeEmail,
            'status' => SnapObject::STATUS_ACTIVE
        ]);

        $eventSubscriber = self::$app->eventSubscriberStore()->save($eventSubscriber);
        // fake branch code
        $branchCode = current($partner->getBranches())->code;

        $occupationCat =  self::createDummyOccupationCategory();
        
        // Create dummy Sub Occupation Category
        $occupationSubCat = self::createDummyOccupationSubCategory($occupationCat, true);
         
        $account = self::$app->mygtpaccountManager()->register(
            $partner,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $occupationCat,
            $occupationSubCat,
            $faker->safeEmail,
            $faker->password,
            MyLocalizedContent::LANG_ENGLISH,
            $branchCode,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $faker->safeEmail,
            $faker->address,
            $faker->word,
            '',
            '',
            true
        );

        $latestJob = self::$app->eventjobStore()->searchTable()->select()
            ->orderBy('id', 'DESC')
            ->one();
        $this->assertGreaterThan($eventjobOldMax, $latestJob->id);
        
    }

    // Test for main if main has pep
    public function testAddEventJobIfOccupationCategoryHasPep(){

        // Create dummy partner
        $partner = self::createDummyPartner();

        // Create Dummy Occupation Category
        $faker = self::getFaker("ms_MY");

        // fake password
        $password = "dummy";

        // Old event job
        $eventjobOldMax = self::$app->eventjobStore()->searchTable(false)->select()->max('id');

        // Bandaid due to vw_testeventtrigger requiring test_eventmessage
        self::$app->eventMessageStore();
        $partnerid = $partner->id;

        // Create A Dummy Trigger
        // Follow action 
        $eventTrigger =  self::$app->eventTriggerStore()->create([
            'grouptypeid' => 100,
            'moduleid' => 100,
            'actionid' => 11,
            'matcherclass' => '\Snap\object\MyGtpEventTriggerMatcher',
            'processorclass' => '\Snap\object\EmailEventProcessor',
            'messageid' => 9,
            'observableclass' => '\Snap\manager\MyGtpAccountManager',
            'oldstatus' => SnapObject::STATUS_ACTIVE,
            'newstatus' =>  SnapObject::STATUS_ACTIVE,
            'objectclass' => '\Snap\object\MyAccountHolder',
            'storetolog' => SnapObject::STATUS_ACTIVE,
            'groupidfieldname' => 'partnerid',
            'evalcode' => '',
            'status' => SnapObject::STATUS_ACTIVE,
            
        ]);

        $eventTrigger = self::$app->eventTriggerStore()->save($eventTrigger);
        // Create Dummy Subscriber
        $eventSubscriber = self::$app->eventSubscriberStore()->create([
            'triggerid' => $eventTrigger->id,
            'groupid' => $partner->id,
            'receiver' => $faker->safeEmail,
            'status' => SnapObject::STATUS_ACTIVE
        ]);

        $eventSubscriber = self::$app->eventSubscriberStore()->save($eventSubscriber);


        // fake branch code
        $branchCode = current($partner->getBranches())->code;

        $occupationCat =  self::createDummyOccupationCategory(true);
       
        $occupationCat = self::$app->myoccupationcategoryStore()->save($occupationCat);
        
        // Create dummy Sub Occupation Category
        //$occupationSubCat = self::createDummyOccupationSubCategory($occupationCat, true);

        //$occupationSubCat = self::$app->myoccupationsubcategoryStore()->save($occupationSubCat);
         
        $account = self::$app->mygtpaccountManager()->register(
            $partner,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $occupationCat,
            null,
            $faker->safeEmail,
            $password,
            MyLocalizedContent::LANG_ENGLISH,
            $branchCode,
            $faker->sentence($faker->numberBetween(1,4)),
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $faker->safeEmail,
            $faker->address,
            $faker->word,
            '',
            '',
            true
        );
            
        //$token = self::$app->mytokenStore()->save($token);

        $latestJob = self::$app->eventjobStore()->searchTable()->select()
            ->orderBy('id', 'DESC')
            ->one();
        $this->assertGreaterThan($eventjobOldMax, $latestJob->id);
        
    }

    /**
     * @depends             testSuccessfulRegistration
     * @expectedException   Snap\api\exception\EmailAddressTakenException
     */
    public function testDuplicateEmailAddress(MyAccountHolder $accountHolder)
    {
        $faker = self::getFaker('ms_MY');

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();
        $partner = $accountHolder->getPartner();

        $accMgr->register(
            $partner,
            $faker->firstName . $faker->lastName,
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            self::$occupationCategory1,
            self::$occupationSubCategory1,
            $accountHolder->email,
            $faker->password,
            MyLocalizedContent::LANG_BAHASA,
            self::$branchcode1,
            $faker->name,
            $faker->myKadNumber,
            $faker->phoneNumber,
            $faker->safeEmail,
            $faker->address,
            $faker->word,
            '','',true
        );
    }

    /**
     * @depends             testSuccessfulRegistration
     * @expectedException   Snap\api\exception\MyGtpAccountHolderMyKadExists
     */
    public function testSameMykadSamePartner(MyAccountHolder $accountHolder)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();
        $faker = self::getFaker();
        $accountHolder = self::$app->myaccountholderStore()->getById($accountHolder->id);
        $accountHolder->kycstatus = MyAccountHolder::KYC_PASSED;
        $accountHolder = self::$app->myaccountholderStore()->save($accountHolder);

        $accMgr->register(
            $accountHolder->getPartner(), $accountHolder->fullname, $accountHolder->mykadno,
            '+'.$faker->mobileNumber(true, false), self::$occupationCategory1, self::$occupationSubCategory1,
            $faker->safeEmail, $faker->password, MyLocalizedContent::LANG_ENGLISH, '',
            $accountHolder->nokfullname, $accountHolder->nokmykadno, $accountHolder->nokphoneno,
            $accountHolder->nokemail, $accountHolder->nokaddress, $accountHolder->nokrelationship,
            '', '', true);
    }

    /**
     * @depends             testSuccessfulRegistration
     */
    public function testSameMykadDifferentPartner(MyAccountHolder $accountHolder)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();
        $faker = self::getFaker();
        $accountHolder = self::$app->myaccountHolderStore()->getById($accountHolder->id);

        $accMgr->register(
            self::$partner2, $accountHolder->fullname, $accountHolder->mykadno,
            '+'.$faker->mobileNumber(true, false), self::$occupationCategory1, self::$occupationSubCategory1,
            $accountHolder->email, $faker->password, MyLocalizedContent::LANG_ENGLISH, '',
            $accountHolder->nokfullname, $accountHolder->nokmykadno, $accountHolder->nokphoneno,
            $accountHolder->nokemail, $accountHolder->nokaddress, $accountHolder->nokrelationship,
            '', '', true);
    }

    /**
     * @depends testSuccessfulRegistration
     */
    public function testForgotPassword(MyAccountHolder $accountHolder)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();

        $partner = self::$partner1;

        $token = $accMgr->forgotPassword($partner, $accountHolder->email, true);

        $this->assertNotNull($token);
        return [$accountHolder->email, $token];
    }

    /**
     * @depends testForgotPassword
     * */
    public function testResetPassword($data)
    {
        list($email, $token) = $data;

        $faker = self::getFaker('ms_MY');

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();

        $newPassword  = $faker->password;
        $partner = self::$partner1;

        $accMgr->resetPassword($partner, $email, $newPassword, $token);

        $accountHolder = self::$app->myaccountholderStore()
            ->searchTable()
            ->select()
            ->where('partnerid', $partner->id)
            ->andWhere('email', $email)
            ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
            ->one();

        $this->assertTrue(password_verify($newPassword, $accountHolder->password));

        return $token;
    }

    /**
     * @depends testForgotPassword
     * @expectedException Snap\api\exception\ResetTokenInvalid
     * */
    public function testCannotResetPasswordUsingUsedToken($data)
    {
        list($email, $token) = $data;
        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();

        $newPassword  = 'newPassword123';
        $partner = self::$partner1;

        $accMgr->resetPassword($partner, $email, $newPassword, $token);
    }

    public function testProcessEkycFlow()
    {
        $partner = self::createDummyPartner();
        $accHolder = self::createDummyAccountHolder($partner);
        $accManager = self::$app->mygtpaccountManager();

        $accHolder->kycstatus = MyAccountHolder::KYC_INCOMPLETE;
        $accHolder->investmentmade = MyAccountHolder::INVESTMENT_MADE;
        $accHolder = self::$app->myaccountholderStore()->save($accHolder);

        $accManager->processEkycVerification($accHolder, $partner, ['kycpass' => true]);
    }

    public function testFirstTimeSetupPincode()
    {
        $app = self::$app;

        $accountHolder = self::$accountHolder;

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();

        $accMgr->editPincode($accountHolder, '008800');

        // reload account
        $accountHolder = $app->myaccountholderStore()->getById($accountHolder->id);

        $this->assertNotNull($accountHolder->pincode);
        $this->assertNotEquals($accountHolder->pincode, '008800');
        $this->assertTrue($accMgr->verifyPincode('008800', $accountHolder->pincode));
    }

    /** @expectedException Snap\api\exception\MyGtpAccountHolderWrongPin */
    public function testFailedEditPincode()
    {
        $app = self::$app;

        $accountHolder = self::$accountHolder;

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();

        $accMgr->editPincode($accountHolder, '008800');
    }

    /** @depends testFirstTimeSetupPincode */
    public function testSuccessEditPincode()
    {
        $app = self::$app;

        $accountHolder = self::$accountHolder;

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();

        $accMgr->editPincode($accountHolder, self::$pincode, '008800');

        // reload account
        $accountHolder = $app->myaccountholderStore()->getById($accountHolder->id);

        $this->assertNotNull($accountHolder->pincode);
        $this->assertNotEquals($accountHolder->pincode, self::$pincode);
        $this->assertFalse($accMgr->verifyPincode('008800', $accountHolder->pincode));
        $this->assertTrue($accMgr->verifyPincode(self::$pincode, $accountHolder->pincode));
    }

    public function testEditPassword()
    {
        $app = self::$app;

        $accountHolder = self::$accountHolder;

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();

        $accMgr->editPassword($accountHolder, "!newPass", "dummy");

        // reload account
        $latestAccountHolder = $app->myaccountholderStore()->getById($accountHolder->id);

        $this->assertNotNull($latestAccountHolder->password);
        $this->assertFalse(password_verify('dummy', $latestAccountHolder->password));
        $this->assertTrue(password_verify('!newPass', $latestAccountHolder->password));
    }

    public function testEditBankAccount()
    {
        $app = self::$app;

        $accountHolder = self::$accountHolder;
        $bank = self::createDummyBank();

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();

        $accMgr->editBankAccount($accountHolder, $bank, 'Account Holder Name', '1110292912312', self::$pincode);

        // reload account
        $latestAccountHolder = $app->myaccountholderStore()->getById($accountHolder->id);

        $this->assertNotNull($latestAccountHolder->bankid);
        $this->assertEquals($latestAccountHolder->bankid, $bank->id);
        $this->assertEquals($latestAccountHolder->accountname, 'Account Holder Name');
        $this->assertEquals($latestAccountHolder->accountnumber, '1110292912312');
    }

    public function testEditProfile()
    {
        $faker = self::getFaker('ms_MY');

        /** @var \Snap\object\MyAccountHolder */
        $accountHolder = self::$accountHolder;

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();

        $line1 = $faker->streetName;
        $line2 = $faker->township;
        $postcode = $faker->postcode;
        $city = $faker->city;
        $state = $faker->state;
        $nokFullName = $faker->name;
        $nokMykadNumber = $faker->myKadNumber;

        $occupationCategory = self::$occupationCategory1;
        $occupationSubCategory = self::$occupationSubCategory1;
        //$occupation = $faker->jobTitle;

        $accMgr->editProfile(
            $accountHolder,
            $line1,
            $line2,
            $postcode,
            $city,
            $state,
            $nokFullName,
            $nokMykadNumber,
            $accountHolder->nokphoneno,
            $accountHolder->nokemail,
            $accountHolder->nokaddress,
            $accountHolder->nokrelationship,
            $occupationCategory,
            $occupationSubCategory,
            '','',
            self::$pincode
        );

        $address = $accountHolder->getAddress();

        $this->assertNotNull($address->id);
        $this->assertEquals($address->line1, $line1);
        $this->assertEquals($address->line2, $line2);
        $this->assertEquals($address->postcode, $postcode);
        $this->assertEquals($address->city, $city);
        $this->assertEquals($address->state, $state);
        $this->assertEquals($accountHolder->nokfullname, $nokFullName);
        $this->assertEquals($accountHolder->nokmykadno, $nokMykadNumber);
        $this->assertEquals($accountHolder->occupationcategoryid, $occupationCategory->id);
        $this->assertEquals($accountHolder->occupationsubcategoryid, $occupationSubCategory->id);
        //$this->assertEquals($accountHolder->occupation, $occupation);
    }

    public function testChangeLanguage()
    {
        /** @var \Snap\object\MyAccountHolder */
        $accountHolder = self::$accountHolder;
        $initialLang   = $accountHolder->preferredlang;

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();
        $accMgr->changeLanguage($accountHolder, MyAccountHolder::LANG_BM);

        $refreshedAccountHolder = self::$app->myaccountholderStore()->getById($accountHolder->id);
        $this->assertNotEquals($initialLang, $refreshedAccountHolder->preferredlang);
        $this->assertEquals(MyAccountHolder::LANG_BM, $refreshedAccountHolder->preferredlang);
    }

    public function testProcessCloseAccountWithInitialInvestment()
    {
        /** @var \Snap\object\MyAccountHolder */
        $accountHolder = self::$accountHolder;
        $accountHolder->bankid = 1;
        $accountHolder->accountname = 'ABC';
        $accountHolder->accountnumber = '123';
        $accountHolder->investmentmade = 1;
        $accountHolder = self::$app->myaccountholderStore()->save($accountHolder);
        $reason = self::createDummyCloseReason();

        $yesterday = new \DateTime('midnight yesterday', self::$app->getUserTimezone());
        $yesterday->setTimezone(self::$app->getServerTimezone());
        $this->createLedger($accountHolder->id, $accountHolder->partnerid, MyLedger::TYPE_BUY_FPX, 10, false, $yesterday);

        $transactionMgr = \Mockery::mock(MyGtpTransactionManager::class, [self::$app]);
        $transactionMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $transactionMgr->shouldReceive('submitOrderToSAP')->once()->andReturn(true);

        $storageMgr = \Mockery::mock(MyGtpStorageManager::class, [self::$app]);
        $storageMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $storageMgr->shouldReceive('submitFeeChargeToSAP')->times(2)->andReturn(true);
        
        $accMgr = \Mockery::mock(MyGtpAccountManager::class, [self::$app]);
        $accMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $accMgr->shouldReceive("notify")->andReturn(null);
        
        $this->assertEquals(10, $accountHolder->getCurrentGoldBalance());
        $this->assertEquals(MyAccountHolder::STATUS_ACTIVE, $accountHolder->status);

        $accountClosure = $accMgr->closeAccount(self::$partner1, $accountHolder, $reason, 'MANUAL', self::$pincode, true, 'TEST', false, $transactionMgr, $storageMgr);
        $accountHolder = self::$app->myaccountholderStore()->getById($accountClosure->accountholderid);
        $this->assertEquals(MyAccountHolder::STATUS_SUSPENDED, $accountHolder->status);
        $this->assertEquals(MyAccountClosure::STATUS_IN_PROGRESS, $accountClosure->status);
        $this->assertNull($accountClosure->closedon);
        $this->assertEquals(0, $accountHolder->getCurrentGoldBalance());

        return $accountClosure;
    }

    /** @depends testProcessCloseAccountWithInitialInvestment */
    public function testCloseAccountWhenDisbursementUpdated($accountClosure)
    {
        $accountHolder = self::$app->myaccountholderStore()->getById($accountClosure->accountholderid);
        $disbursement = self::$app->mydisbursementStore()->create([
            'transactionrefno' => $accountClosure->transactionrefno,
            'status' => MyDisbursement::STATUS_COMPLETED
        ]);

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = \Mockery::mock(MyGtpAccountManager::class, [self::$app]);
        $accMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $accMgr->shouldReceive("notify")->andReturn(null);

        $accMgr->onObservableEventFired(self::$app->mygtpdisbursementManager(), new IObservation($disbursement, IObservation::ACTION_CONFIRM, 0));

        $accountClosure = self::$app->myaccountclosureStore()->getById($accountClosure->id);
        $accountHolder = self::$app->myaccountholderStore()->getById($accountClosure->accountholderid);
        $this->assertNotNull($accountClosure->closedon);
        $this->assertEquals(MyAccountHolder::STATUS_CLOSED, $accountHolder->status);
        $this->assertEquals(MyAccountClosure::STATUS_APPROVED, $accountClosure->status);
    }

    public function testCloseAccountWithoutInitialInvestment()
    {
        /** @var \Snap\manager\MyGtpAccountManager $accMgr */
        $accMgr = \Mockery::mock(MyGtpAccountManager::class, [self::$app]);
        $accMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $accMgr->shouldReceive("notify")->andReturn(null);
        $accMgr->shouldNotReceive("onProcessAccountClosure");

        $reason = self::createDummyCloseReason();

        $accountHolder = self::createDummyAccountHolder(self::$partner1);
        $accMgr->editPincode($accountHolder, self::$pincode);
        $this->assertEquals(MyAccountHolder::STATUS_ACTIVE, $accountHolder->status);
        
        $accountClosure = $accMgr->closeAccount(self::$partner1, $accountHolder, $reason, '1.0my', self::$pincode);
        $accountHolder = self::$app->myaccountholderStore()->getById($accountClosure->accountholderid);
        $this->assertEquals(MyAccountHolder::STATUS_CLOSED, $accountHolder->status);
        $this->assertEquals(MyAccountClosure::STATUS_APPROVED, $accountClosure->status);
        $this->assertNotNull($accountClosure->closedon);
    }
    
    public function testShouldNotCloseActiveAccount()
    {
        self::createDummyPushNotification(MyPushNotification::TYPE_DORMANT_ACCOUNT, 1);

        /** @var \Snap\manager\MyGtpTokenManager */
        $tokenMgr = self::$app->mygtptokenManager();

        $sixMonthsAgo = new \DateTime('now');
        $sixMonthsAgo->modify('-6 months');
        $sixMonthsAgo->setTimezone(self::$app->getUserTimezone());
        $sevenMonthsAgo = new \DateTime('7 months ago');

        $accHolder1 = self::createDummyAccountHolder(self::$partner1);
        $accHolder1->investmentmade = MyAccountHolder::INVESTMENT_MADE;
        $accHolder1 = self::$app->myaccountholderStore()->save($accHolder1);
        // Simulate has transaction
        $ledger1 = $this->createLedger($accHolder1->id, $accHolder1->partnerid, MyLedger::TYPE_BUY_FPX, 0, false, $sevenMonthsAgo);

        $accHolder2 = self::createDummyAccountHolder(self::$partner1);        
        $accHolder2->bankid = 1;
        $accHolder2->accountname = self::getFaker()->text(20);
        $accHolder2->accountnumber = self::getFaker()->bankAccountNumber;
        $accHolder2->investmentmade = MyAccountHolder::INVESTMENT_MADE;
        $accHolder2 = self::$app->myaccountholderStore()->save($accHolder2);

        // Simulate has transaction
        $ledger2 = $this->createLedger($accHolder2->id, $accHolder2->partnerid, MyLedger::TYPE_BUY_FPX, 0, false, $sevenMonthsAgo);
        
        /** @var \Snap\manager\MyGtpAccountManager $accMgr */
        $accMgr = \Mockery::mock(MyGtpAccountManager::class, [self::$app]);
        $accMgr->shouldAllowMockingProtectedMethods()->makePartial();
        
               
        
        $pushMgr = \Mockery::mock(MyGtpPushNotificationManager::class, [self::$app]);
        $pushMgr->shouldAllowMockingProtectedMethods()->makePartial();
        // 1 time notified then active + 2 time notified but inactive
        $pushMgr->shouldReceive('doSendNotification')->times(3);        

        /** @var \Snap\manager\MyGtpAccountManager $accMgr */
        $accMgr = \Mockery::mock(MyGtpAccountManager::class, [self::$app]);
        $accMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $accMgr->attach($pushMgr);

        $sixMonthsAgo = new \DateTime('now');
        $sixMonthsAgo->modify('-6 months');
        $oneMonthsAgo = new \DateTime('1 month ago');


        $transactionMgr = \Mockery::mock(MyGtpTransactionManager::class, [self::$app]);
        $transactionMgr->shouldAllowMockingProtectedMethods()->makePartial();

        // No submitOrderToSAP bcs no sell trx balance 0
        $transactionMgr->shouldNotReceive('submitOrderToSAP');

        $storageMgr = \Mockery::mock(MyGtpStorageManager::class, [self::$app]);
        $storageMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $storageMgr->shouldNotReceive('submitFeeChargeToSAP');
        
        $accMgr->checkDormantAccount(self::$partner1, 'MANUAL', $transactionMgr, $storageMgr);

        // Made a transaction
        $this->createLedger($accHolder1->id, $accHolder1->partnerid, MyLedger::TYPE_BUY_FPX, 1, false, $oneMonthsAgo);
        $oneMonthsAgo->setTimezone(self::$app->getUserTimezone());
        $accHolder1 = self::$app->myaccountholderStore()->getById($accHolder1->id);
        $accHolder1->lastnotifiedon = $oneMonthsAgo;
        $accHolder1 = self::$app->myaccountholderStore()->save($accHolder1);
        
        // Login but no transaction
        $accHolder2 = self::$app->myaccountholderStore()->getById($accHolder2->id);
        $accHolder2->lastnotifiedon = $oneMonthsAgo;
        $accHolder2 = self::$app->myaccountholderStore()->save($accHolder2);

        $accMgr->checkDormantAccount(self::$partner1, 'MANUAL', $transactionMgr, $storageMgr);

        // Simulate no transaction for more than 12 months        
        $accHolder2 = self::$app->myaccountholderStore()->getById($accHolder2->id);
        $accHolder2->lastnotifiedon = $oneMonthsAgo;
        $accHolder2 = self::$app->myaccountholderStore()->save($accHolder2);
        self::$app->myledgerStore()->delete($ledger2);

        $accMgr->checkDormantAccount(self::$partner1, 'MANUAL', $transactionMgr, $storageMgr);
        /** @var \Mockery\MockInterface|\Mockery\LegacyMockInterface $accMgr */
        $accMgr->shouldHaveReceived('closeAccount');

        // Extra wont do anything
        $accMgr->checkDormantAccount(self::$partner1, 'MANUAL', $transactionMgr, $storageMgr);
    }

    public function testGetIncompleteProfileAccountHolder()
    {
        /** @var \Snap\manager\MyGtpTokenManager */
        $tokenMgr = self::$app->mygtptokenManager();        
        self::createDummyAccountHolder(self::$partner);

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();
        $accHolders = $accMgr->getIncompleteProfileAccountHolders(self::$partner);
        $this->assertNotEmpty($accHolders);
        foreach ($accHolders as $accHolder) {
            $tokenMgr->registerPushToken($accHolder, md5($accHolder->id));
            $incomplete = !$accHolder->bankid || !$accHolder->accountname || !$accHolder->accountnumber || !$accHolder->getAddress();
            $this->assertTrue($incomplete);
        }

        return $accHolders;
    }

    /** @depends testGetIncompleteProfileAccountHolder */
    public function testRemindIncompleteProfile($accHolders)
    {
        self::createDummyPushNotification(MyPushNotification::TYPE_INCOMPLETE_PROFILE, 1);
        
        $pushMgr = \Mockery::mock(MyGtpPushNotificationManager::class, [self::$app]);
        $pushMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $pushMgr->shouldReceive('doSendNotification')->times(count($accHolders));
        
        $accMgr = \Mockery::mock(MyGtpAccountManager::class, [self::$app]);
        $accMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $accMgr->attach($pushMgr);
 
        foreach ($accHolders as $accHolder) {
            $accMgr->remindIncompleteProfile($accHolder);
        }        
    }

    /**
     * @depends testSuccessfulRegistration
     */
    public function testVerifyAccountHolder($accountHolder)
    {
        // Set inactive
        $accHolder = self::$app->myaccountholderStore()->getById($accountHolder->id);
        $accHolder->status = MyAccountHolder::STATUS_INACTIVE;
        $accHolder->phoneverifiedon = new \DateTime();
        $accHolder = self::$app->myaccountholderStore()->save($accHolder);

        // Verify account
        $token = self::$app->mygtptokenManager()->generateAccountVerificationToken($accHolder);
        $accHolder = self::$app->mygtpaccountManager()->verifyAccountHolderEmail($accHolder->getPartner(), $accHolder->email, $token->token);

        // Updated by Cheok on 2021-05-06 due to change in flow of phone verification.
        // Phone verification now done during registration

        // $phoneToken = self::$app->mygtptokenManager()->generatePhoneVerificationToken($accHolder);
        // $accHolder = self::$app->mygtpaccountManager()->verifyAccountHolderPhone($accHolder->getPartner(), $accHolder->phoneno, $phoneToken->token);

        // End update by Cheok

        $this->assertEquals(MyAccountHolder::STATUS_ACTIVE, $accHolder->status);
    }

    public function testDisableAccountHolder()
    {
        $accHolder = $this->createDummyAccountHolder();

        $accHolder = self::$app->mygtpaccountManager()->disableAccountHolder($accHolder);
        $this->assertEquals(MyAccountHolder::STATUS_CLOSED ,$accHolder->status);
    }

    public function testCanEditSalespersonCode()
    {
        $accHolder = self::createDummyAccountHolder();
        $accHolder->referralsalespersoncode = "TEST";
        $accHolder = self::$app->myaccountholderStore()->save($accHolder);
        $addr = $accHolder->getAddress();

        $accHolder = self::$app->mygtpaccountManager()
                         ->editProfile($accHolder, $addr->line1, '', $addr->postcode,
                                       $addr->city, $addr->state, $accHolder->nokfullname, $accHolder->nokmykadno, $accHolder->nokphoneno,
                                       $accHolder->nokemail, $accHolder->nokaddress, $accHolder->nokrelationship,
                                       $accHolder->getOccupationCategory(), $accHolder->getOccupationSubCategory(), 'ASDFASD');
    }

    /**
     * @expectedException \Snap\api\exception\MyGtpProfileUpdateNotAllowed
     */
    public function testCannotEditSalespersonCode()
    {
        $accHolder = self::createDummyAccountHolder();
        $accHolder = self::$app->myaccountholderStore()->save($accHolder);
        $addr = $accHolder->getAddress();

        $accHolder = self::$app->mygtpaccountManager()
                         ->editProfile($accHolder, $addr->line1, '', $addr->postcode,
                                       $addr->city, $addr->state, $accHolder->nokfullname, $accHolder->nokmykadno, $accHolder->nokphoneno,
                                       $accHolder->nokemail, $accHolder->nokaddress, $accHolder->nokrelationship,
                                       $accHolder->getOccupationCategory(), $accHolder->getOccupationSubCategory(), 'ASDFASD');
    }

    public function testCanGetUnsuccessfulRegistrationReport()
    {
        $yesterday = new DateTime('now', self::$app->getUserTimezone());
        $yesterday->sub(new DateInterval('P1D'));

        $dateStart = new DateTime($yesterday->format('Y-m-d 00:00:00'), self::$app->getUserTimezone());
        $dateStart->setTimezone(self::$app->getServerTimezone());
        $dateEnd = new DateTime($yesterday->format('Y-m-d 23:59:59'), self::$app->getUserTimezone());
        $dateEnd->setTimezone(self::$app->getServerTimezone());
        
        $report = self::$app->mygtpaccountManager()->getUnsuccessfullRegistrationReport(self::$partner1, $dateStart, $dateEnd);
        
        $this->assertNotNull($report);
    }

/**
     * Create ledger for test use case
     *
     * @param  int  $accHolderId
     * @param  int  $partnerId
     * @param  stirng $ledgerType
     * @param  float $amount
     * @param  boolean $debit
     * @return MyLedger
     */
    private function createLedger($accHolderId, $partnerId, $ledgerType, $amount, $debit = false, $date = null)
    {
        $ledger = self::$app->myledgerStore()->create([
            'type'              => $ledgerType,
            'typeid'            => 1,
            'accountholderid'   => $accHolderId,
            'partnerid'         => $partnerId,
            'refno'             => mt_rand(),
            'transactiondate'   => $date ? $date->format('Y-m-d H:i:s') : new \DateTime('now'),
            'status'            => MyLedger::STATUS_ACTIVE,
        ]);

        if ($debit) {
            $ledger->debit = $amount;
            $ledger->credit = 0.00;
        } else {
            $ledger->credit = $amount;
            $ledger->debit = 0.00;
        }

        $ledger = self::$app->myledgerStore()->save($ledger);

        return $ledger;
    }

}
