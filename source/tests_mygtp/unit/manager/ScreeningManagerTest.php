<?php

use Snap\manager\notificationManager;
use Snap\object\MyAccountHolder;
use Snap\object\MyScreeningList;
use Snap\object\MyScreeningMatchLog;

final class ScreeningManagerTest extends BaseTestCase
{
    protected static $fileDir;

    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
        // Tempory fix for event trigger view requiring eventmessage table 
        self::$app->eventMessageStore();

        self::$fileDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'files';
    }

    public function testCanImportMohaList()
    {
        $file = self::$fileDir . DIRECTORY_SEPARATOR . 'amlamoha.pdf';

        $originalCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        /** @var \Snap\manager\MyGtpScreeningManager */
        $screeningManager = self::$app->mygtpscreeningManager();
        $screeningManager->importMohaList($file);

        $importCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        $this->assertNotEquals($originalCount, $importCount);
    }

    public function testCanImportUnList()
    {
        $file = self::$fileDir . DIRECTORY_SEPARATOR . 'amlaun.xml';

        $originalCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        /** @var \Snap\manager\MyGtpScreeningManager */
        $screeningManager = self::$app->mygtpscreeningManager();
        $screeningManager->importUnList($file);

        $importCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        $this->assertNotEquals($originalCount, $importCount);
    }

    public function testCanImportBnmList()
    {
        // $url = 'https://api.bnm.gov.my/public/consumer-alert';

        $file = self::$fileDir . DIRECTORY_SEPARATOR . 'amlabnm.json';
        $originalCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        /** @var \Snap\manager\MyGtpScreeningManager */
        $screeningManager = self::$app->mygtpscreeningManager();
        $screeningManager->importBnmList($file, false);

        $importCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        $this->assertNotEquals($originalCount, $importCount);
    }

    public function testCanMatchList()
    {
        $accountHolder = self::createDummyAccountHolder();
        $settings = self::$app->mypartnersettingStore()->getByField('partnerid', $accountHolder->partnerid);

        $file = self::$fileDir . DIRECTORY_SEPARATOR . 'amlamoha.pdf';

        /** @var \Snap\manager\MyGtpScreeningManager */
        $screeningManager = self::$app->mygtpscreeningManager();
        $screeningManager->importMohaList($file);

        $aRecord = self::$app->myscreeningListStore()->searchTable()->select()->where('status', MyScreeningList::STATUS_ACTIVE)->one();
        $aRecord->icno = $accountHolder->mykadno;
        self::$app->myscreeningListStore()->save($aRecord);

        /** @var MyAccountHolder $accountHolder */
        $pass = $screeningManager->screenAccountHolder($accountHolder, filter_var($settings->amlablacklistimmediately, FILTER_VALIDATE_BOOLEAN));
        $matchLog = self::$app->myscreeningmatchlogStore()
            ->searchTable()
            ->select([])
            ->where('accountholderid', $accountHolder->id)
            ->one();

        $accountHolder = self::$app->myaccountholderStore()->getById($accountHolder->id);

        $this->assertFalse($pass);
        $this->assertNotNull($matchLog);
        
        $this->assertEquals(MyScreeningMatchLog::STATUS_BLACKLISTED, $matchLog->status);

        if (filter_var($settings->amlablacklistimmediately, FILTER_VALIDATE_BOOLEAN)) {
            $this->assertEquals(MyAccountHolder::AMLA_FAILED, $accountHolder->amlastatus);
            $this->assertEquals(MyAccountHolder::STATUS_BLACKLISTED, $accountHolder->status);
        }
    }

    public function testCannotMatchList()
    {
        /** @var MyAccountHolder $accountHolder */
        $accountHolder = self::createDummyAccountHolder();

        /** @var \Snap\manager\MyGtpScreeningManager */
        $screeningManager = self::$app->mygtpscreeningManager();

        $pass = $screeningManager->screenAccountHolder($accountHolder, true);
        $accountHolder = self::$app->myaccountholderStore()->getById($accountHolder->id);

        $this->assertTrue($pass);
        $this->assertEquals(MyAccountHolder::AMLA_PASSED, $accountHolder->amlastatus);
        $this->assertEquals(MyAccountHolder::STATUS_ACTIVE, $accountHolder->status);
    }

    public function testAccountHoldersMatchList()
    {
        $accountHolder = self::createDummyAccountHolder();

        $file = self::$fileDir . DIRECTORY_SEPARATOR . 'amlamoha.pdf';

        /** @var \Snap\manager\MyGtpScreeningManager */
        $screeningManager = self::$app->mygtpscreeningManager();
        $screeningManager->importMohaList($file);

        $aRecord = self::$app->myscreeningListStore()->searchTable()->select()->where('status', MyScreeningList::STATUS_ACTIVE)->one();
        $aRecord->icno = $accountHolder->mykadno;
        self::$app->myscreeningListStore()->save($aRecord);

        /** @var \Snap\manager\MyGtpScreeningManager */
        $screeningManager = self::$app->mygtpscreeningManager();
        $settings = self::$app->mypartnersettingStore()->getByField('partnerid', $accountHolder->partnerid);

        $failedAccountHolders = $screeningManager->reverifyAccountHolders();                
        
        $this->assertNotEmpty($failedAccountHolders);
        if (filter_var($settings->amlablacklistimmediately, FILTER_VALIDATE_BOOLEAN)) {
            $this->assertEquals(MyAccountHolder::AMLA_FAILED, current($failedAccountHolders)->amlastatus);
            $this->assertEquals(MyAccountHolder::STATUS_BLACKLISTED, current($failedAccountHolders)->status);
        } else {
            $this->assertEquals(MyAccountHolder::AMLA_PENDING, current($failedAccountHolders)->amlastatus);
        }
    }

}
