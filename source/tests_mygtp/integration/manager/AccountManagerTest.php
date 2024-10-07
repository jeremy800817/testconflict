<?php

use Snap\manager\MyGtpAccountManager;
use Snap\manager\MyGtpPushNotificationManager;
use Snap\object\MyAccountClosure;
use Snap\object\MyAccountHolder;
use Snap\object\MyLocalizedContent;
use Snap\object\MyPartnerApi;
use Snap\object\MyPushNotification;
use Snap\util\ekyc\Innov8tifProvider;


final class AccountManagerTest extends BaseTestCase
{
    protected static $partner1;
    protected static $partner2;
    protected static $occupationCategory1;
    protected static $occupationCategory2;
    protected static $accountHolder;
    protected static $pincode;
    protected static $branchcode1;
    protected static $branchcode2;

    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
        $faker = self::getFaker('ms_MY');

        self::$app->userStore();
        self::$app->partnerStore();
        // Start dependency for myaccountholder view
        self::$app->mybankStore();
        self::$app->myaddressStore();
        self::$app->productStore();
        self::$app->orderStore();
        self::$app->redemptionStore();
        self::$app->myaccountholderStore();
        self::$app->myconversionStore();
        self::$app->mygoldtransactionStore();
        self::$app->myledgerStore();
        self::$app->myaccountholderStore();
        // End dependency for myaccountholder view

        self::$app->eventmessageStore();
        
        self::$partner1 = self::createDummyPartner();        
        self::$partner2 = self::createDummyPartner();
        self::$occupationCategory1 = self::createDummyOccupationCategory();
        self::$occupationCategory2 = self::createDummyOccupationCategory();
        self::$accountHolder = self::createDummyAccountHolder(self::$partner1);
        self::$pincode = $faker->numberBetween(100000, 999999);
        self::$branchcode1 = current(self::$partner1->getBranches())->code;
        self::$branchcode2 = current(self::$partner2->getBranches())->code;
    }

    public function testProcessEkycUsingInnov8tif()
    {
        $this->expectException('Snap\api\exception\EkycSubmissionInvalid');
        $partner = self::createDummyPartner();
        $accHolder = self::createDummyAccountHolder($partner);
        $accManager = self::$app->mygtpaccountManager();

        $accHolder->kycstatus = MyAccountHolder::KYC_INCOMPLETE;
        $accHolder->investmentmade = MyAccountHolder::INVESTMENT_MADE;
        $accHolder = self::$app->myaccountholderStore()->save($accHolder);

        $fileDir = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;

        $mykadfrontb64 = base64_encode(file_get_contents($fileDir . DIRECTORY_SEPARATOR . 'f1.png'));
        $mykadbackb64  = base64_encode(file_get_contents($fileDir . DIRECTORY_SEPARATOR . 'b1.png'));
        $faceimageb64  = base64_encode(file_get_contents($fileDir . DIRECTORY_SEPARATOR . 's1.jpg'));
        $accManager->processEkycVerification($accHolder, $partner, [
            'kycpass' => true,
            'mykad_front_b64' => $mykadfrontb64,
            'mykad_back_b64' => $mykadbackb64,
            'face_image_b64' => $faceimageb64
        ]);
    }

    public function testCanSearchPepRecords()
    {
        $this->markTestSkipped('Skipped because we do not want to waste API call');
        // ATTENTION: Need to change partnersettings to use Innov8tif

        $partner = self::$partner2;
        $accountHolder = self::$accountHolder;

        $accountHolder->fullname = 'Najib Razak';
        // $accountHolder->middlename = null;
        // $accountHolder->lastname = 'Razak';
        $accountHolder->mykadno = 530723112231;

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();
        $records = $accMgr->getPepMatches($partner, $accountHolder);

        $this->assertNotNull($records);
        $this->assertArrayHasKey('recordsFound', $records);
        $this->assertArrayHasKey('matches', $records);
        $this->assertNotEmpty($records['matches']);
        $this->assertArrayHasKey('person', $records['matches'][0]);
    }

    public function testCanGetPepPdf()
    {
        $this->markTestSkipped('Skipped because we do not want to waste API call');
        // ATTENTION: Need to change partnersettings to use Innov8tif

        $partner = self::createDummyPartner();

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();
        $file = $accMgr->getPepPdfForPerson($partner, 873938);
        $fp = tmpfile();
        fwrite($fp, $file);
        fseek($fp, 0);
        $mimeType = mime_content_type($fp);

        $this->assertNotNull($file);
        $this->assertEquals('application/pdf', $mimeType);
    }


    public function testCanGetPepJson()
    {
        $this->markTestSkipped('Skipped because we do not want to waste API call');
        // ATTENTION: Need to change partnersettings to use Innov8tif

        $partner = self::createDummyPartner();

        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = self::$app->mygtpaccountManager();
        $file = $accMgr->getPepJsonForPerson($partner, 873938);
        $this->assertNotNull($file);
        $this->assertArrayHasKey('id', $file);
        $this->assertEquals(873938, $file['id']);
    }

    
}