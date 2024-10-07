<?php
use PHPUnit\Framework\TestCase;
Use Snap\object\SnapObject;
Use Snap\object\VaultItem;

/**
 * @covers VaultItem objects
 * @backupGlobals disabled
 */
final class VaultItemTest extends TestCase {

    static public $config;
    static public $db;
    static public $cacher;
    static public $refreshed = 0;
    static private $tables = [
        'test_vaultlocation' => 'create table test_vaultlocation like vaultlocation;',
        'test_vaultitem' => 'create table test_vaultitem like vaultitem;'
    ];
    static private $initialSQL = 'insert into test_vaultlocation (stl_id, stl_partnerid, stl_name, stl_type, stl_minimumlevel, stl_reorderlevel, stl_defaultlocation, stl_createdon, stl_createdby, stl_modifiedon, stl_modifiedby, stl_status) VALUES 
     (1, 1, \'Taipan OFfice\', \'Start\', 100, 100, 1, NOW(), 1, NOW(), 1, 1),
     (2, 1, \'G4S Taipan Rack\', \'Intermediate\', 100, 100, 1, NOW(), 1, NOW(), 1, 1),
     (3, 1, \'G4S MBB Racj\', \'End\', 100, 100, 1, NOW(), 1, NOW(), 1, 1);';

    public static function setUpBeforeClass() {
        self::$config = new Snap\config(TEST_DIR . DIRECTORY_SEPARATOR . 'testconfig.ini');
        self::$config->load();
        self::$db = new Snap\db( self::$config->{'snap.db.type'}, self::$config->{'snap.db.host'}, self::$config->{'snap.db.username'}, self::$config->{'snap.db.password'}, self::$config->{'snap.db.name'}, false);

        $db = self::$db;
        foreach(self::$tables as $tableName =>$createSQL) {
            $db->exec($createSQL);
            $db->exec(self::$initialSQL);
        }
        if ('memcache' == self::$config->isKeyExists('snap.cache.Type') || 'memcached' == self::$config->isKeyExists('snap.cache.Type')) {
            $module = self::$config->{'snap.cache.Type'};
            if (self::$config->isKeyExists('snap.cache.Servers')) {
                $Servers =  self::$config->{'snap.cache.Servers'};
            }
        }
        $cacheId = self::$config->isKeyExists('cacheid') ? self::$config->{'snap.cache.id'} : '0';

        self::$cacher = Snap\Cacher::getInstance( $cacheId, $module, $Servers);
    }

    public static function tearDownAfterClass() {
        self::$config = null;
        $db = self::$db;
        foreach(self::$tables as $tableName =>$createSQL) {
            if('vw' == substr($tableName,0,2)) $db->exec("drop view $tableName");
            else $db->exec("drop table $tableName");
        }
        self::$db->close();
    }

    /**
     * Setup Test Configurations
     */
    public function setupStores() 
    {
        $locationStore =  new Snap\store\dbdatastore(self::$db, 'test_vaultlocation', null, 'stl', '\Snap\object\VaultLocation', array(), array(), true);
        $itemStore =  new Snap\store\dbdatastore(self::$db, 'test_vaultitem', null, 'sti', '\Snap\object\VaultItem', array(), array('vaultlocation' => $locationStore), true);
        return [$locationStore, $itemStore];
    }

    /**
     * @return array
     */
    public function addTestProvider(): array
    {
        return [
            [1, 1, 1.4, 'ACE', '123141ADC', 31, 45, 1, 1]
        ];
    }
    
    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($vaultLocationID, $productID, $weight, $brand, $serialno, $newVaultLocationID, $goldRequestID, $partnerId, $expectedId)
    {
        [$locationStore, $store ]  = $this->setupStores();

        $new = $store->create();
        $new->partnerid          = $partnerid;
        $new->vaultlocationid    = $vaultLocationID;
        $new->productid          = $productID;
        $new->weight             = $weight;
        $new->brand              = $brand;
        $new->serialno           = $serialno;
        // $new->newvaultlocationid = $newVaultLocationID;

        $new->createdon = new \DateTime('now');
        $new->createdby = 1;
        $new->modifiedon = new \DateTime('now');
        $new->modifiedby = 1;
        $new->status = 1;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals($expectedId, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['vaultlocationid', 'productid', 'weight', 'brand','serialno', 'newvaultlocationid', 'goldrequestid'] as $field) {
            $this->assertEquals($new->{$field}, $inDB->{$field});
        }
    }

    /**
     * @depends testAddNew
     */
    public function testRead() :void
    {
        [$locationStore, $store ]  = $this->setupStores();
        $inDB = $store->getById(1);
        $this->assertNotNull($inDB);
        $this->assertEquals(1, $inDB->id);

        $finalLocation = $locationStore->getById(3);
        $this->assertTrue($inDB->canAllocate());
        $this->assertFalse($inDB->isAllocated());
        $this->assertFalse($inDB->canRequestMoveToLocation($finalLocation));
        $this->assertFalse($inDB->canCompleteMoveToLocation());
        $this->assertTrue($inDB->canReturn());
    }

    /**
     * @depends testAddNew
     */
    public function testUpdate() :void
    {
        [$locationStore, $store ]  = $this->setupStores();
        $inDB = $store->getById(1);
        $inDB->status = 1;
        $updated = $store->save($inDB);
        $this->assertEquals(1, $updated->status);
    }

    public function testAllStatusCheck1()
    {
        [$locationStore, $store ]  = $this->setupStores();
        $inDB = $store->getById(1);
        $inDB->allocated = 1;
        $store->save($inDB);
        $this->assertTrue($inDB->isAllocated());
        $this->assertFalse($inDB->canAllocate());
        $nextLocation = $locationStore->getById(2);
        $this->assertTrue($inDB->canRequestMoveToLocation($nextLocation));
        $inDB->requestMoveToLocation($nextLocation);
        $startLocation = $locationStore->getById(1);
        $this->assertFalse($inDB->canRequestMoveToLocation($nextLocation));
        $this->assertFalse($inDB->canRequestMoveToLocation($startLocation));
        $this->assertFalse($inDB->canReturn());
    }

    /**
     * @depends testAllStatusCheck1
     */
    public function testMoveLocationToIntermediate()
    {
        [$locationStore, $store ]  = $this->setupStores();
        $inDB = $store->getById(1);
        $this->assertTrue($inDB->canCompleteMoveToLocation());
        $inDB->completeMoveToLocation();
        $this->assertEquals(0, $inDB->movetovaultlocationid);
        $this->assertFalse($inDB->canAllocate());
        $this->assertTrue($inDB->isAllocated());
    }

    /**
     * @depends testMoveLocationToIntermediate
     */
    public function testMoveActiontoIntermediate()
    {
        [$locationStore, $store ]  = $this->setupStores();
        $inDB = $store->getById(1);

        $this->assertFalse($inDB->canCompleteMoveToLocation());
        $this->assertEquals(0, $inDB->movetovaultlocationid);
        $this->assertFalse($inDB->canAllocate());
        $this->assertTrue($inDB->isAllocated());
        $nextLocation = $locationStore->getById(3);
        $this->assertTrue($inDB->canRequestMoveToLocation($nextLocation));
        $inDB->requestMoveToLocation($nextLocation);
    }

    /**
     * @depends testMoveActiontoIntermediate
     */
    public function testMoveToFinalLocation()
    {
        [$locationStore, $store ]  = $this->setupStores();
        $inDB = $store->getById(1);
        $this->assertTrue($inDB->canCompleteMoveToLocation());
        $inDB->completeMoveToLocation();
        $this->assertEquals(0, $inDB->movetovaultlocationid);
        $this->assertEquals(3, $inDB->vaultlocationid);
        $this->assertFalse($inDB->canAllocate());
        $this->assertTrue($inDB->isAllocated());
        $this->assertFalse($inDB->canReturn());
    }

    /**
     * @depends testMoveToFinalLocation
     * 
     */
    public function testDeallocation() {
        [$locationStore, $store ]  = $this->setupStores();
        $inDB = $store->getById(1);
        $this->assertFalse($inDB->canAllocate());
        $this->assertTrue($inDB->isAllocated());
        $inDB->allocated = 0;
        $store->save($inDB);
        $this->assertTrue($inDB->canAllocate());
        $this->assertFalse($inDB->isAllocated());
        $inDB = $store->getById(1);
        $nextLocation = $locationStore->getById(2);
        $this->assertTrue($inDB->canRequestMoveToLocation($nextLocation));
        $inDB->requestMoveToLocation($nextLocation);
        $this->assertTrue($inDB->canCompleteMoveToLocation($nextLocation));
        $inDB->CompleteMoveToLocation();
        $this->assertTrue($inDB->canReturn());
        $inDB->completeReturn();
        $this->assertFalse($inDB->canAllocate());
    }

    /**
     * @expectedException Snap\InputException
     */
    public function testDuplicate()
    {
        [$locationStore, $store ]  = $this->setupStores();
        $inDB = $store->getById(1);
        $duplicateData = $inDB->toArray();
        $duplicateData['id'] = 0;
        $duplicateItem = $store->create($duplicateData);
        $store->save($duplicateItem);
    }
}

?>