<?php

use PHPUnit\Framework\TestCase;
use Snap\object\SnapObject;
Use Snap\object\VaultItem;
Use Snap\object\VaultLocation;
Use Snap\manager\BankVaultManager;
Use Snap\Object\Redemption;
Use Snap\Object\Logistic;

/**
 * @covers partner objects
 */
final class banktest extends TestCase
{
    // >>>>>> gtp\source\tests> ../vendor/bin/phpunit --bootstrap ./startup.php manager/logisticmanagertest.php


    // php source/snaplib/cli.php -f source/tests/manager/bankVaultManagerTest.php -c source/tests/testconfiglocal.ini
    // php source/snaplib/cli.php -f source/tests/startup.php -c source/tests/testconfiglocal.ini
    // php source/tests/startup.php -f source/tests/manager/bankVaultManager.php -c source/tests/testconfiglocal.ini
    // ../vendor/bin/phpunit --bootstrap ./startup.php manager/bankVaultManagerTest.php
    static public $app = null;
    static public $allocatedItemSerialno = [];
    public function __construct() {
        $this->backupGlobals = false;
    }

    public static function setUpBeforeClass() {
        self::$app = \Snap\App::getInstance();
    }

    public function testThatWillBeExecuted()
    {
        $this->changeStatus();

    }

    public function createLogisticRedemption_Delivery(){
        $app = self::$app;
        $logisticStore = $app->logisticStore();

        $type = Redemption::TYPE_DELIVERY;
        $redemption = $app->redemptionStore()->searchTable()->select()->where('type', $type)->andWhere('status', Redemption::STATUS_CONFIRMED)->one();

        $vendorId = 1; // GDEX;
        // $vendorId = 2; // ACE_DELIVERY;
        $awbNo = 'AWB0000'.$type;
        // $senderId = 8; // user_id _ACE_SALESMEN_WITH_LOGISTIC
        // $deliveryDate = '09-10-2020 00:00:00'; // for type=appoinment only

        $return = $app->LogisticManager()->createLogisticRedemption($redemption, $vendorId, $awbNo);
        $this->assertTrue($return);
        $this->assertEquals(Logistic::STATUS_PROCESSING, $return->status);
    }

    public function createLogisticRedemption_SpecialDelivery(){
        $app = self::$app;
        $logisticStore = $app->logisticStore();

        $type = Redemption::TYPE_SPECIALDELIVERY;
        $redemption = $app->redemptionStore()->searchTable()->select()->where('type', $type)->andWhere('status', Redemption::STATUS_CONFIRMED)->one();

        // $vendorId = 1; // GDEX;
        $vendorId = 2; // ACE_DELIVERY;
        $awbNo = 'AWB0000'.$type;
        $senderId = 8; // user_id _ACE_SALESMEN_WITH_LOGISTIC
        // $deliveryDate = '09-10-2020 00:00:00'; // for type=appoinment only

        $return = $app->LogisticManager()->createLogisticRedemption($redemption, $vendorId, $awbNo, $senderId);
        $this->assertTrue($return);
        $this->assertEquals(Logistic::STATUS_PROCESSING, $return->status);
    }

    public function createLogisticRedemption_Appointment(){
        $app = self::$app;
        $logisticStore = $app->logisticStore();

        $type = Redemption::TYPE_SPECIALDELIVERY;
        $redemption = $app->redemptionStore()->searchTable()->select()->where('type', $type)->andWhere('status', Redemption::STATUS_CONFIRMED)->one();

        // $vendorId = 1; // GDEX;
        $vendorId = 2; // ACE_DELIVERY;
        $awbNo = 'AWB0000'.$type;
        $senderId = 8; // user_id _ACE_SALESMEN_WITH_LOGISTIC
        $deliveryDate = '09-10-2020 15:00:00'; // for type=appoinment only

        $return = $app->LogisticManager()->createLogisticRedemption($redemption, $vendorId, $awbNo, $senderId, $deliveryDate);
        $this->assertTrue($return);
        $this->assertEquals(Logistic::STATUS_PROCESSING, $return->status);
    }

    public function attemps_logistic(){
        $app = self::$app;
        $logisticStore = $app->logisticStore();

        $logistic = $logisticStore->searchTable()->select()->where('status', Logistic::STATUS_SENDING)->one(); // status = SENDING/IN_TRANSIT

        $return = $app->LogisticManager()->attemps($logistic);
        $this->assertTrue($return);
        $this->assertGreaterThan($return->attemps, $logistic->attemps);
    }

    public function changeStatus(){
        $app = self::$app;
        $logisticStore = $app->logisticStore();

        // INIT STATUS
        // $status = Logistic::STATUS_PROCESSING;
        $status = Logistic::STATUS_SENDING;
        // $logistic = $logisticStore->searchTable()->select()->where('status', $status)->one(); 
        $logistic = $logisticStore->getById(1); 
        // print_r($logistic);exit;

        // PACKING STATUS
        // packing required info as REMARKS for packing info(who packing)
        // $update_status = logistic::STATUS_PACKING;
        $update_status = logistic::STATUS_DELIVERED;
        $remarks = 'MR. DELLLV - PACKING - SURV NO. 0067';

        $return = $app->LogisticManager()->logisticStatus($logistic, $update_status, null, null, $remarks);
        // $this->assertTrue($return);
        $this->assertEquals($update_status, $return->status);
        $logisticLogs = $app->logisticlogStore()->searchTable()->select()->where('logisticid', $return->id)->execute();

        $assertHAS = false;
        foreach ($logisticLogs as $logisticLog){
            if ($logisticLog->remarks = $remarks){
                $assertHAS = true;
            }
        }
        $this->assertEquals($assertHAS, true); //  if failed log is not insert;
        
    }

    public function falseStatus(){
        $app = self::$app;
        $logisticStore = $app->logisticStore();
        
        // INIT STATUS
        $status = Logistic::STATUS_PROCESSING;
        $logistic = $logisticStore->searchTable()->select()->where('status', $status)->one(); 

        // PACKING STATUS
        // FALSE status -- skipped Logistic::STATUS_PACKING
        $update_status = logistic::STATUS_PACKED;
        $remarks = 'PACKED - ACE HQ vault';

        $return = $app->LogisticManager()->logisticStatus($logistic, $update_status, null, null, $remarks);
        $this->assertFalse($return); // no status is updated
        $logisticLogs = $app->logisticlogStore()->searchTable()->select()->where('logisticid', $return->id)->execute();
        $this->assertFalse($logisticLogs); // no log is created

        $logistic = $logisticStore->searchTable()->getById($logistic->id);
        $this->assertEquals($status, $logistic->status); // no status change
    }

    
    public function createLogisticReplenishment(Replenishment $replenishment, $vendorId, $awbNo = null, $senderId = null, $deliveryDate){
       
    }

    public function logisticStatus(Logistic $logistic, $status, $senderId = null, $recievedPerson = null, $remarks = null){
        
    }

    

}