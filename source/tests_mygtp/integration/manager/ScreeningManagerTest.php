<?php

use Snap\manager\notificationManager;
use Snap\object\MyAccountHolder;
use Snap\object\MyScreeningList;
use Snap\object\MyScreeningMatchLog;

final class ScreeningManagerTest extends BaseTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
    }

    // Source format change, unable to import pdf
    // public function testCanImportMohaList()
    // {
    //     $url = 'https://www.moha.gov.my/images/maklumat_bahagian/KK/kdndomestic.pdf';

    //     $originalCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

    //     /** @var \Snap\manager\MyGtpScreeningManager */
    //     $screeningManager = self::$app->mygtpscreeningManager();
    //     $screeningManager->importMohaList($url);

    //     $importCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

    //     $this->assertNotEquals($originalCount, $importCount);
    // }

    public function testCanImportUnList()
    {
        $url = 'https://scsanctions.un.org/resources/xml/en/consolidated.xml';

        $originalCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        /** @var \Snap\manager\MyGtpScreeningManager */
        $screeningManager = self::$app->mygtpscreeningManager();
        $screeningManager->importUnList($url);

        $importCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        $this->assertNotEquals($originalCount, $importCount);
    }

    public function testCanImportBnmList()
    {
        $url = 'https://api.bnm.gov.my/public/consumer-alert';

        $originalCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        /** @var \Snap\manager\MyGtpScreeningManager */
        $screeningManager = self::$app->mygtpscreeningManager();
        $screeningManager->importBnmList($url, true);

        $importCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        $this->assertNotEquals($originalCount, $importCount);
    }

    public function testCanImportAllAmlaList()
    {
        $originalCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        /** @var \Snap\manager\MyGtpScreeningManager */
        $screeningManager = self::$app->mygtpscreeningManager();
        $screeningManager->importAllAmlaList();

        $importCount = self::$app->myscreeningListStore()->searchTable()->select()->count();

        $this->assertNotEquals($originalCount, $importCount);
    }
}
