<?php

use PHPUnit\Framework\TestCase;
use Snap\object\SnapObject;
Use Snap\object\VaultItem;
Use Snap\object\VaultLocation;
Use Snap\manager\BankVaultManager;

/**
 * @covers partner objects
 */
final class bankVaultManagerTest extends TestCase
{

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

    /*
       Testing flow:
        1)  Request a gold serial number (none available yet)
        2)  Import 10 serials by simulating SAP import call.
        3)  Testing of importing a duplicated serial.
        4)  Make half of the serial numbers to be physically available. (even numbers one)
        5)  Testing of requesting 2 serial number, then test another 3
        6)  Testing of deallocation of gold.
        7)  Simulate movement of gold to MBB final location
        8)  Simulate deallocation of gold
        9)  Simulate movement of deallocated gold
        10)  Simulate reassignment of deallocated gold
        11) Test of return deallocated gold.
        12)  Removed serial reimported to active
     */
    
    //----------- START CONDITIONS --------------
    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902242 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       N      |       N       |        N           0       |       NA          | ACTIVE
     */
    /**
     * 1)  Request a gold serial number (none available yet)
     * @expectedException Snap\api\exception\VaultItemError
     */
    public function test_GoldBarRequestWithoutSerials() {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $product = $app->productStore()->searchTable()->select()->where('weight', 1000)->one();
        $app->getDBHandle()->query("TRUNCATE TABLE test_vaultitem");
        $this->assertEquals(0, $app->vaultItemStore()->searchTable()->select()->count());
        $this->assertEquals(1, $partner->id);
        $app->bankvaultManager()->requestItemSerial($partner, $product, 2, '_REF1', $now, '1.0m');
    }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902242 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       N      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       N      |       N       |        N           0       |       NA          | ACTIVE
     */
    /**
     * 2)  Import 10 serials by simulating SAP import call
     * @depends test_GoldBarRequestWithoutSerials
     */
    public function test_onSapNotifyReceiveNewSerial(){
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);

        //Make sure location is available.
        $locationStore = $app->vaultLocationStore();
        if(0 == $locationStore->searchTable()->select()->where('partnerid', $partner->id)->count()) {
            $location = $locationStore->create([
                            'partnerid' => $partner->id,
                            'name' => 'ACE HQ',
                            'type' => VaultLocation::TYPE_START,
                            'minimumlevel' => 0,
                            'reorderlevel' => 10,
                            'defaultlocation' => 1,
                            'status' => 1
                        ]);
            $locationStore->save($location);
            $this->assertGreaterThan(0, $location->id);
            $location = $locationStore->create([
                            'partnerid' => $partner->id,
                            'name' => 'ACE G4S Rack',
                            'type' => VaultLocation::TYPE_INTERMIDIATE,
                            'minimumlevel' => 0,
                            'reorderlevel' => 10,
                            'defaultlocation' => 0,
                            'status' => 1
                        ]);
            $locationStore->save($location);
            $this->assertGreaterThan(0, $location->id);
            $location = $locationStore->create([
                            'partnerid' => $partner->id,
                            'name' => 'MBB G4S Rack',
                            'type' => VaultLocation::TYPE_END,
                            'minimumlevel' => 0,
                            'reorderlevel' => 0,
                            'defaultlocation' => 0,
                            'status' => 1
                        ]);
            $locationStore->save($location);
            $this->assertGreaterThan(0, $location->id);
        }
        
        // $sap_data = [];
        $serial_nums = [];
        
        for($i=0; $i<10; $i++){
            $sap_datax = (object) [
                "id" => 0,
                "itemCode" => "GS-999-9-1000g",
                "serialNum" => "GEX-ASSAYRF-290224".$i,
                "whsCode" => "MIB_RSV",
                "bankId" => null,
                "customerId" => "MIB",
                "createdDate" => "2020-05-07 00:00:00"
            ];
            $sap_data[$i] = $sap_datax;
            array_push($serial_nums, "GEX-ASSAYRF-290224".$i);
        }

        $return = $app->BankVaultManager()->onSapNotifyReceiveNewSerial($partner, $sap_data);
        // $checkInit = $app->vaultItemStore()->searchTable()->select()->where('serialno', 'in', $serial_nums)->execute();
        $this->assertEquals(10, count($return), '10 sap serial inserted');
        foreach ($return as $x => $check){
            $this->assertGreaterThan(0, $check->id, 'Partner id of vault item in error');
            $this->assertEquals($partner->id, $check->partnerid, 'Partner id of vault item in error');
            $this->assertEquals(0, $check->vaultlocationid, 'vault location should be 0');
            $this->assertGreaterThan($product->weight, $check->weight, 'must be the same as product weight');
            $this->assertEquals($sap_data[$x]->serialNum, $check->serialno, 'Serial number not saved correctly');
            $this->assertFalse($check->isAllocated());
            $this->assertTrue($check->canAllocate());
        }
    }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       N       |        N           0       |       NA          | ACTIVE
     */
    /**
     * 3)  Testing of importing a duplicated serial.
     * @depends test_onSapNotifyReceiveNewSerial
     * @expectedException \Snap\api\exception\VaultItemError
     */
    public function test_importDuplicateSerial() {
       $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        $vaultItem = $app->vaultItemStore()->getByField('serialno', 'GEX-ASSAYRF-2902242');
        $this->assertEquals('GEX-ASSAYRF-2902242', $vaultItem->serialno, 'Vault item Serial no obtained is not correct');
        $this->assertGreaterThan(0, $vaultItem->id, 'Vault item obtained from db is not valid');

        $sap_data[] = (object) [
            "id" => 0,
            "itemCode" => "GS-999-9-1000g",
            "serialNum" => "GEX-ASSAYRF-2902242",
            "whsCode" => "MIB_RSV",
            "bankId" => null,
            "customerId" => "MIB",
            "createdDate" => "2020-05-07 00:00:00"
        ];
        $return = $app->BankVaultManager()->onSapNotifyReceiveNewSerial($partner, $sap_data);
        $this->assertCount(0, $return);
        $this->assertFalse(true);
    }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       N       |        N           0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       N       |        N           0       |       NA          | ACTIVE
     */
    /**
     * 3)  Testing of importing a duplicated serial.
     * @depends test_importDuplicateSerial
     */
    public function test_MakePhysicalGoldAvailable() {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);

        // test exists serial with removed/pending status
        $sap_data = [];
        for($i=0; $i<10; $i++){
            $sap_datax = (object) [
                "id" => 0,
                "itemCode" => "GS-999-9-1000g",
                "serialNum" => "GEX-ASSAYRF-290224".$i,
                "whsCode" => "MIB_RSV",
                "bankId" => null,
                "customerId" => "MIB",
                "createdDate" => "2020-05-07 00:00:00"
            ];
            $i++;
            $sap_data[] = $sap_datax;
        }
        $defaultLocation = $app->vaultLocationStore()->searchTable()->select()
                                ->where("defaultlocation", 1)
                                ->andWhere("type", VaultLocation::TYPE_START)     
                                ->one(); 

        $return = $app->BankVaultManager()->onSapNotifyItemAvailable($partner, $sap_data);
        $this->assertEquals(5, count($return), '3 sap serial updated from pending to active');
        foreach ($return as $x => $check) {
            $this->assertEquals($partner->id, $check->partnerid, 'Partner ID for the vaultItem is different');
            $this->assertEquals($defaultLocation->id, $check->vaultlocationid, 'The location set for the vaultItem is not the start location');
            $this->assertGreaterThan(0, $check->weight, 'Weight data error');
            $this->assertEquals($sap_data[$x]->serialNum, $check->serialno, 'must be the same');
            $this->assertFalse($check->isAllocated());
            $this->assertTrue($check->canAllocate());
            $this->assertFalse($check->canReturn(),'');
        }
    }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
     */
    
    /**
     * 5)  Testing of requesting 2 serial number, then test another 3
     * @depends test_MakePhysicalGoldAvailable
     */
    public function testRequestSerial()
    {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        
        $product = $app->productStore()->getById(2);
        $this->assertEquals(2, $product->id);

        $return = $app->bankvaultManager()->requestItemSerial($partner, $product, 3, '_REF', $now, '1.0m');
        $this->assertCount(3,  $return, 'Ensure able to return 3 items');
        $vaultItemStore = $app->vaultitemStore();
        $this->assertEquals(3, $vaultItemStore->searchTable()->select()->where('partnerid', $partner->id)->andWhere('allocated', 1)->count(), '- allocated.');
        $this->assertGreaterThan(0, count($return));
        foreach ($return as $x => $check){
            // echo "\r\n".__METHOD__.'---output---'.'---serialno => '.$check->serialno.' ---testR ';
            $this->assertEquals($partner->id, $check->partnerid, 'must be the same');
            $this->assertEquals($product->id, $check->productid, 'must be the same');
            $this->assertTrue($check->isAllocated());
            $this->assertFalse($check->canAllocate());
            self::$allocatedItemSerialno[] = $check->serialno;
        }
        //Second round request of 2 items
        $return2 = $app->bankvaultManager()->requestItemSerial($partner, $product, 2, '_REF3', $now, '1.0m');
        $this->assertCount(2,  $return2, 'Ensure able to return 2 extra items');
        $vaultItemStore = $app->vaultitemStore();
        $this->assertEquals(5, $vaultItemStore->searchTable()->select()->where('partnerid', $partner->id)->andWhere('allocated', 1)->count(), 'Total allocated serials is now supposed to be 5');
        $this->assertGreaterThan(0, count($return2));
        foreach ($return2 as $x => $check){
            // echo "\r\n".__METHOD__.'---output---'.'---serialno => '.$check->serialno.' ---testR ';
            $this->assertEquals($partner->id, $check->partnerid, 'must be the same');
            $this->assertEquals($product->id, $check->productid, 'must be the same');
            $this->assertTrue($check->isAllocated());
            $this->assertFalse($check->canAllocate());
            $this->assertFalse($check->canReturn());
            self::$allocatedItemSerialno[] = $check->serialno;
        }
    }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
     */
    /**
     * 6)  Testing of deallocation of gold.
     * @depends testRequestSerial
     */
    public function testSerialDeallocated()
    {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        
        // $product = $app->productStore()->getById(2);
        // $this->assertEquals(2, $product->id);
        
        $return = $app->bankvaultManager()->markItemDeallocated($partner, array(self::$allocatedItemSerialno[4]));
        $vaultItemStore = $app->vaultitemStore();
        $this->assertEquals(1, $vaultItemStore->searchTable()->select()->where('partnerid', $partner->id)->andWhere('serialno', 'in', array(self::$allocatedItemSerialno[4]))->andWhere('allocated', 0)->count(), '- deallocated');

        $this->assertGreaterThan(0, count($return));
        foreach ($return as $x => $check){
            // echo "\r\n".__METHOD__.'---output---'.'---serialno => '.$check->status.' ---testR ';
            $this->assertEquals($partner->id, $check->partnerid, 'must be the same');
            $this->assertFalse($check->isAllocated());
            $this->assertTrue($check->canAllocate());
        }

        //Make sure non allocated gold can not be deallocated....
//        $this->expectException(\Snap\api\exception\ApiException::class);
//        $app->bankVaultManager()->markItemDeallocated($partner, array("GEX-ASSAYRF-290224"));
    }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
     */
    /**
     * 9)  Simulate movement of physical gold or all cases.
     * @depends testSerialDeallocated
     */
    public function testRequestMoveItemToLocation()
    {
        // init allocated to mbb

        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);

        $vaultItemStore = $app->vaultitemStore();

        $locationStore = $app->vaultLocationStore();
        $location1 = $locationStore->searchTable()->select()->where('name', 'ACE G4S Rack')->one();
        $location2 = $locationStore->searchTable()->select()->where('name', 'MBB G4S Rack')->one();
        $this->assertTrue($location2->isFinalLocation());
        //Serials [Not available, allocated], [Available, allocated], [Available, not allocated], [Not available, not allocated]
        $items = $vaultItemStore->searchTable()->select()->whereIn('serialno', ['GEX-ASSAYRF-2902241', 'GEX-ASSAYRF-2902242'/*, 'GEX-ASSAYRF-2902246', 'GEX-ASSAYRF-2902247'*/])->execute();
        
        $product = $app->productStore()->getById($items[0]->productid);
        $this->assertEquals($items[0]->productid, $product->id);
        $resultsChecker = [];
        foreach($items as $idx => $obj) {
            $this->assertEquals(0, $obj->movetovaultlocationid);
            $resultsChecker[] = ['movetovaultlocationid', $obj->movetovaultlocationid, 
                                 'vaultlocationid' => $obj->vaultlocationid,
                                  'isAllocated' => $obj->isAllocated(),
                                  'canAllocate' => $obj->canAllocate()
                                 ];
        }
        
        $return = $app->bankvaultManager()->requestMoveItemToLocation($items, $location2);
        $this->assertCount(2, $return, 'Unable to request movement for 2 items');
        $this->assertEquals(2, $vaultItemStore->searchTable()->select()->where('partnerid', $partner->id)->andWhere('movetovaultlocationid', $location2->id)->count(), '- movetolocation');

        foreach ($return as $x => $check){
            // echo "\r\n".__METHOD__.'---output---'.'---serialno => '.$check->status.' ---testR ';
            $this->assertEquals($partner->id, $check->partnerid, "Error encountered or {$check->serialno}");
            $this->assertFalse($check->canReturn(), "Error encountered or {$check->serialno}");
            $this->assertEquals($resultsChecker[$x]['canAllocate'], $return[$x]->canAllocate(), "Error encountered or {$check->serialno}");
            $this->assertEquals($resultsChecker[$x]['isAllocated'], $return[$x]->isAllocated(), "Error encountered or {$check->serialno}");
            $this->assertEquals($resultsChecker[$x]['vaultlocationid'], $return[$x]->vaultlocationid, "Error encountered or {$check->serialno}");
            $this->assertNotEquals($resultsChecker[$x]['movetovaultlocationid'], $return[$x]->movetovaultlocationid, "Error encountered or {$check->serialno}");
            $this->assertEquals($check->status, VaultItem::STATUS_TRANSFERRING, "Error encountered or {$check->serialno}");
            $this->assertEquals($check->movetovaultlocationid, $location2->id, "Error encountered or {$check->serialno}");
        }

        $this->expectException(\Snap\api\exception\VaultItemError::class);
        $items = $vaultItemStore->searchTable()->select()->whereIn('serialno', [ 'GEX-ASSAYRF-2902246'])->execute();
        $return = $app->bankvaultManager()->requestMoveItemToLocation($items, $location2);
     }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       Y       |        Y     |     HQ      |       MBB         | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       Y       |        N     |     0       |       MBB         | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
     */
     /**
      * @depends testRequestMoveItemToLocation
      * @expectedException \Snap\api\exception\VaultItemError
      */
     public function testRequestMoveItemToLocation2() {
       $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);

        $vaultItemStore = $app->vaultitemStore();

        $locationStore = $app->vaultLocationStore();
        $location1 = $locationStore->searchTable()->select()->where('name', 'ACE G4S Rack')->one();
        $location2 = $locationStore->searchTable()->select()->where('name', 'MBB G4S Rack')->one();
        $this->assertTrue($location2->isFinalLocation());
        //Serials [Not available, allocated], [Available, allocated], [Available, not allocated], [Not available, not allocated]
        $items = $vaultItemStore->searchTable()->select()->whereIn('serialno', [ 'GEX-ASSAYRF-2902247'])->execute();
        
        $product = $app->productStore()->getById($items[0]->productid);
        $this->assertEquals($items[0]->productid, $product->id);
        $return = $app->bankvaultManager()->requestMoveItemToLocation($items, $location2);
     }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       Y       |        Y     |     HQ      |       MBB         | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       Y       |        N     |     0       |       MBB         | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    */
    /**
     * 9b)  Simulate movement of physical gold or all cases - mark it conpleted
     * @depends testRequestMoveItemToLocation2
     */
    public function testMarkItemArrived()
    {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);

        $vaultItemStore = $app->vaultitemStore();

        $locationStore = $app->vaultLocationStore();
        $location1 = $locationStore->searchTable()->select()->where('name', 'ACE HQ')->one();
        $location2 = $locationStore->searchTable()->select()->where('name', 'MBB G4S Rack')->one();
        
        // $item = $vaultItemStore->searchTable()->select()->where('serialno', 'GEX-ASSAYRF-2902242')->one();
        // $item = array($item);
        $items = $vaultItemStore->searchTable()->select()->where('movetovaultlocationid', '>', 0)->execute();
        $this->assertCount(2, $items);
        $product = $app->productStore()->getById(2);
        $this->assertEquals(2, $product->id);
        $physicalAvailableItems = $noPhysicalAvailableItem = [];
        foreach($items as $anItem) {
            if(preg_match('/(0|2|4|6|8)$/', $anItem->serialno)) { //even number serial = available
                $physicalAvailableItems[] = $anItem;
            } else { //odd number serials = unavailable
                $noPhysicalAvailableItem[] = $anItem;
            }
        }
        $return = $app->bankvaultManager()->markItemsArrivedAtLocation($physicalAvailableItems);
        $this->assertCount(1, $return);
        $this->assertEquals(1, $vaultItemStore->searchTable()->select()->where('partnerid', $partner->id)->andWhere('vaultlocationid', $location2->id)->count(), '- arrieve vaulitemlocation, updates');

        foreach ($return as $x => $check){
            // echo "\r\n".__METHOD__.'---output---'.'---serialno => '.$check->status.' ---testR ';
            $this->assertEquals($partner->id, $check->partnerid, 'must be the same');
            // $this->assertFalse($check->isAllocated()); // can be mbb to ace, ace to mbb
            // $this->assertFalse($check->canAllocate());
            // $this->assertTrue($check->canReturn());  // can be mbb to ace, ace to mbb
            $this->assertEquals($check->status, VaultItem::STATUS_ACTIVE, 'must be the same');
            $this->assertEquals($check->movetovaultlocationid, 0, 'must be the same');
            $this->assertEquals($check->vaultlocationid, $location2->id, 'must be the same');
        }

        $this->expectException(\Snap\api\exception\VaultItemError::class);
        //Should throw error because item still haven't receive notification that it is available.....
        $return = $app->bankvaultManager()->markItemsArrivedAtLocation($noPhysicalAvailableItem);
    }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       Y       |        Y     |     MBB     |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       Y       |        N     |     0       |       MBB         | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    */
    /**
     * 10)  Simulate reassignment of deallocated gold -mwe previously deallocated GEX-ASSAYRF-2902248
     * @depends testMarkItemArrived
     * @expectedException Snap\api\exception\VaultItemAvailability
     */
    public function testOverRequestSerial() {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        $this->assertEquals(1, $partner->id);
        $vaultItemStore = $app->vaultitemStore();
        $product = $app->productStore()->getById(2);

        $notAllocatedSerialCount = $vaultItemStore->searchTable()->select()->where('allocated', 0)->count();
        $this->assertGreaterThan(0, $notAllocatedSerialCount);
        $serials = $app->bankVaultManager()->requestItemSerial($partner, $product, $notAllocatedSerialCount + 1, '_REF5', $now, '1.0m');
        $this->assertCount($notAllocatedSerialCount, $serials);
    }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       Y       |        Y     |     MBB     |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       Y       |        N     |     0       |       MBB         | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       N       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       N       |        N     |     0       |       NA          | ACTIVE
    */
    /**
     * 10)  Simulate reassignment of deallocated gold -mwe previously deallocated GEX-ASSAYRF-2902248
     * @depends testOverRequestSerial
     */
    public function testReassignmentOfDeallocatedGold() {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        $this->assertEquals(1, $partner->id);
        $vaultItemStore = $app->vaultitemStore();
        $product = $app->productStore()->getById(2);

        $notAllocatedSerialCount = $vaultItemStore->searchTable()->select()->where('allocated', 0)->count();
        $this->assertEquals(6, $notAllocatedSerialCount);
        $serials = $app->bankVaultManager()->requestItemSerial($partner, $product, $notAllocatedSerialCount, '_REF5', $now, '1.0m');
        $this->assertCount($notAllocatedSerialCount, $serials);
    }


    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       Y       |        Y     |     MBB     |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       Y       |        N     |     0       |       MBB         | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    */
    /**
     * @depends testReassignmentOfDeallocatedGold
     */
    public function testMarkItemReturned1()
    {
        // item at mbb and return to ace
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);

        $vaultItemStore = $app->vaultitemStore();

        $locationStore = $app->vaultLocationStore();
        $location1 = $locationStore->searchTable()->select()->where('name', 'ACE HQ')->one();
        $location2 = $locationStore->searchTable()->select()->where('name', 'MBB G4S Rack')->one();
        
        // $item = $vaultItemStore->searchTable()->select()->where('serialno', 'GEX-ASSAYRF-2902242')->one();
        // $item->vaultlocationid = $location1->id;
        // $vaultItemStore->save($item);
        // $item = array($item);
        
        // $move = $app->bankvaultManager()->requestMoveItemToLocation($item, $location2);
        
        // markitemdeallocated and move to ace hq
        $item = $app->bankvaultManager()->markItemDeallocated($partner, array('GEX-ASSAYRF-2902242'));

        $return = $app->bankvaultManager()->requestMoveItemToLocation(array($item[0]), $location1);

        $return = $app->bankvaultManager()->markItemReturned(array($return[0]));
        $check_item = $vaultItemStore->searchTable()->select()->where('partnerid', $partner->id)->andWhere('vaultlocationid', $location1->id)->andWhere('serialno', 'GEX-ASSAYRF-2902242')->count();
        $this->assertEquals(1, $check_item, '- arrive vaulitemlocation, updates');

        foreach ($return as $x => $check){
            // echo "\r\n".__METHOD__.'---output---'.'---serialno => '.$check->status.' ---testR ';
            $this->assertEquals($partner->id, $check->partnerid, 'must be the same');
            $this->assertFalse($check->isAllocated());
            $this->assertTrue($check->canAllocate());
            $this->assertFalse($check->canReturn(), $check->canReturn());
            $this->assertEquals($check->status, VaultItem::STATUS_INACTIVE, 'must be the same');
            $this->assertEquals($check->movetovaultlocationid, 0, 'must be the same');
            $this->assertEquals($check->vaultlocationid, $location1->id, 'must be the same');
        }
    }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       Y       |        Y     |     MBB     |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       Y       |        N     |     0       |       MBB         | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       N       |        Y     |     HQ      |       NA          | INACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    */
    /**
     * @depends testMarkItemReturned1
     */
    public function testMarkItemReturned2()
    {
        // item at mbb and return to ace
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);

        $vaultItemStore = $app->vaultitemStore();

        $locationStore = $app->vaultLocationStore();
        $location1 = $locationStore->searchTable()->select()->where('name', 'ACE HQ')->one();
        $location2 = $locationStore->searchTable()->select()->where('name', 'MBB G4S Rack')->one();
        
        $item = $app->bankvaultManager()->markItemDeallocated($partner, array('GEX-ASSAYRF-2902241'));
        
        //Testing cancelling of a pending move request.
        $app->bankVaultManager()->cancelMoveRequest($item[0]);

        $return = $app->bankvaultManager()->requestMoveItemToLocation(array($item[0]), $location1);

        $return = $app->bankvaultManager()->markItemReturned(array($return[0]));
        $check_item = $vaultItemStore->searchTable()->select()->where('partnerid', $partner->id)->andWhere('vaultlocationid', $location1->id)->andWhere('serialno', 'GEX-ASSAYRF-2902241')->count();

        $this->assertEquals(1, $check_item, '- arrive vaulitemlocation, updates');
        $this->assertGreaterThan(0, count($return));
        foreach ($return as $x => $check){
            // echo "\r\n".__METHOD__.'---output---'.'---serialno => '.$check->status.' ---testR ';
            $this->assertEquals($partner->id, $check->partnerid, 'must be the same');
            $this->assertFalse($check->isAllocated());
            $this->assertTrue($check->canAllocate());
            $this->assertFalse($check->canReturn(), $check->canReturn());
            $this->assertEquals($check->status, VaultItem::STATUS_INACTIVE, 'must be the same');
            $this->assertEquals($check->movetovaultlocationid, 0, 'must be the same');
            $this->assertEquals($check->vaultlocationid, $location1->id, 'must be the same');
        }
    }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       Y       |        Y     |     MBB     |       NA          | ACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       N       |        N     |     HQ      |       NA          | INACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       N       |        Y     |     HQ      |       NA          | INACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    */
    /**
     * @depends testMarkItemReturned2
     */
    public function testMarkItemReturned3()
    {
        // item at mbb and return to ace
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);

        $vaultItemStore = $app->vaultitemStore();

        $locationStore = $app->vaultLocationStore();
        $location1 = $locationStore->searchTable()->select()->where('name', 'ACE HQ')->one();
        $location2 = $locationStore->searchTable()->select()->where('name', 'MBB G4S Rack')->one();
        
        // markitemdeallocated and move to ace hq
        $item = $app->bankvaultManager()->markItemDeallocated($partner, array('GEX-ASSAYRF-2902240'));

        $return = $app->bankvaultManager()->requestMoveItemToLocation(array($item[0]), $location1);

        $return = $app->bankvaultManager()->markItemReturned(array($return[0]));
        $check_item = $vaultItemStore->searchTable()->select()->where('partnerid', $partner->id)->andWhere('vaultlocationid', $location1->id)->andWhere('serialno', 'GEX-ASSAYRF-2902240')->count();
        $this->assertEquals(1, $check_item, '- arrive vaulitemlocation, updates');

        $this->assertGreaterThan(0, count($return));
       foreach ($return as $x => $check){
            // echo "\r\n".__METHOD__.'---output---'.'---serialno => '.$check->status.' ---testR ';
            $this->assertEquals($partner->id, $check->partnerid, 'must be the same');
            $this->assertFalse($check->isAllocated());
            $this->assertTrue($check->canAllocate());
            $this->assertFalse($check->canReturn(), $check->canReturn());
            $this->assertEquals($check->status, VaultItem::STATUS_INACTIVE, 'must be the same');
            $this->assertEquals($check->movetovaultlocationid, 0, 'must be the same');
            $this->assertEquals($check->vaultlocationid, $location1->id, 'must be the same');
        }
    }

    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       N       |        Y     |     HQ      |       NA          | INACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       N       |        N     |     HQ      |       NA          | INACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       N       |        Y     |     HQ      |       NA          | INACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    */
    /**
     * @depends testMarkItemReturned3
     */
    public function test_reimportDeactivatedItem() {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);

        $serial = $app->vaultItemStore()->searchTable()->select()->where('serialno', 'GEX-ASSAYRF-2902241')->one();
        $this->assertFalse($serial->isAllocated(), 'Can not be allocated to reactivate');
        $this->assertFalse($serial->isAllocated(), 'Can not be allocated to reactivate');
        $serial->status = VaultItem::STATUS_REMOVED;
        $serial = $app->vaultItemStore()->save($serial);
        $sap_data[0] = (object) [
            "id" => 0,
            "itemCode" => "GS-999-9-1000g",
            "serialNum" => "GEX-ASSAYRF-2902241",
            "whsCode" => "MIB_RSV",
            "bankId" => null,
            "customerId" => "MIB",
            "createdDate" => "2020-05-07 00:00:00"
        ];
        $return = $app->BankVaultManager()->onSapNotifyReceiveNewSerial($partner, $sap_data);
        $checkInit = $app->vaultItemStore()->searchTable()->select()->where('serialno', 'GEX-ASSAYRF-2902241')->andWhere('status', VaultItem::STATUS_ACTIVE)->one();
        $this->assertEquals(1, count($checkInit), '1 sap serial updated from pending to active');        
    }
    /*                     Imported   |    Allocated  |    Available  | Location   |  MoveTo Location | Status
    -----------------------------------------------------------------------------------------------------------------
    GEX-ASSAYRF-2902240 |       Y      |       N       |        Y     |     HQ      |       NA          | INACTIVE
    GEX-ASSAYRF-2902241 |       Y      |       N       |        N     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902242 |       Y      |       N       |        Y     |     HQ      |       NA          | INACTIVE
    GEX-ASSAYRF-2902243 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902244 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902245 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902246 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902247 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    GEX-ASSAYRF-2902248 |       Y      |       Y       |        Y     |     HQ      |       NA          | ACTIVE
    GEX-ASSAYRF-2902249 |       Y      |       Y       |        N     |     0       |       NA          | ACTIVE
    */
}
?>