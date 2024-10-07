<?php

use PHPUnit\Framework\TestCase;
use Snap\object\SnapObject;
Use Snap\object\Partner;
Use Snap\object\PartnerService;
Use Snap\object\PartnerBranchmap;

/**
 * @covers partner objects
 * @backupGlobals disabled
 */
final class PartnerTest extends TestCase
{

    static public $config;
    static public $db;
    static public $cacher;
    static public $refreshed = 0;
    static private $tables = [
        'test_partner' => 'create table test_partner like partner;',
        'test_partnerservices' => 'create table test_partnerservices like partnerservice;',
        'test_partnerbranchmap' => 'create table test_partnerbranchmap like partnerbranchmap;',
        'test_product' => 'create table test_product like product; insert into test_product select * from product;',
        'test_tag' => 'create table test_tag like tag; insert into test_tag select * from tag;'
    ];

    public static function setUpBeforeClass() {
        self::$config = new Snap\config(TEST_DIR . DIRECTORY_SEPARATOR . 'testconfig.ini');
        self::$config->load();
        self::$db = new Snap\db( self::$config->{'snap.db.type'}, self::$config->{'snap.db.host'}, self::$config->{'snap.db.username'}, self::$config->{'snap.db.password'}, self::$config->{'snap.db.name'}, false);

        $db = self::$db;
        foreach(self::$tables as $tableName =>$createSQL) {
            $db->exec($createSQL);
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

    private function setupStores() {
        $tagStore =  new Snap\store\dbdatastore(self::$db, 'test_tag', null, 'tag', '\Snap\object\Tag', array(), array(), true);
        $productStore =  new Snap\store\dbdatastore(self::$db, 'test_product', null, 'pdt', '\Snap\object\Product', array(), array(), true);
        $serviceStore =  new Snap\store\dbdatastore(self::$db, 'test_partnerservices', null, 'pas', '\Snap\object\partnerservice', array(), array('product' => $productStore), true);
        $branchStore =  new Snap\store\dbdatastore(self::$db, 'test_partnerbranchmap', null, 'pbm', '\Snap\object\partnerbranchmap', array(), array(), true);
        $partnerStore =  new Snap\store\dbdatastore(self::$db, 'test_partner', null, 'par', '\Snap\object\partner', array(), array('services' => $serviceStore, 'branches' => $branchStore), true);
        return [$partnerStore, $productStore];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function addTestProvider(): array
    {
        return [
            ['CODE01', 'Maybank', 'Jalan Putero Tower 1', '43000', 'Selangor', Partner::TYPE_CUSTOMER, 1, 1, 1, 'SellCode01', 'BuyCode01', 'SellCode02', 'BuyCode02', 1.2, 1.2, 12, Partner::MODE_WEB, 1, 1, 1, 1, 1234, 1]
        ];
    }


    //public function testAddNew($code, $name, $address, $postcode, $state, $type, $pricesourceid, $salespersonid, $tradingscheduleid,  $sapcompanysellcode1, $sapcompanybuycode1, $sapcompanysellcode2, $sapcompanybuycode2, $dailybuylimitxau, $dailyselllimitxau, $pricelapsetimeallowance, $orderingmode, $autosubmitorder, $autocreatematchedorder, $orderconfirmallowance, $ordercancelallowance, $apikey, $expectedId)
    public function testAddNew()
    {
        list($store, $productStore) = $this->setupStores();

        $new = $store->create();
        /*$new->code = $code;
        $new->name = $name;
        $new->address = $address;
        $new->postcode = $postcode;
        $new->state = $state;
        $new->type = $type;
        $new->pricesourceid = $pricesourceid;
        $new->salespersonid = $salespersonid;
        $new->tradingscheduleid = $tradingscheduleid;
        $new->sapcompanysellcode1 = $sapcompanysellcode1;
        $new->sapcompanybuycode1 = $sapcompanybuycode1;
        $new->sapcompanysellcode2 = $sapcompanysellcode2;
        $new->sapcompanybuycode2 = $sapcompanybuycode2;
        $new->dailybuylimitxau = $dailybuylimitxau;
        $new->dailyselllimitxau = $dailyselllimitxau;
        $new->pricelapsetimeallowance = $pricelapsetimeallowance;
        $new->orderingmode = $orderingmode;
        $new->autosubmitorder = $autosubmitorder;
        $new->autocreatematchedorder = $autocreatematchedorder;
        $new->orderconfirmallowance = $orderconfirmallowance;
        $new->ordercancelallowance = $ordercancelallowance;
        $new->apikey = $apikey;*/
        $new->code = 'CODE01';
        $new->name = 'Maybank';
        $new->address = 'Jalan Putero Tower 1';
        $new->postcode = '43000';
        $new->state = 'Selangor';
        $new->type = Partner::TYPE_CUSTOMER;
        $new->pricesourceid = 1;
        $new->salespersonid = 1;
        $new->tradingscheduleid = 1;
        $new->sapcompanysellcode1 = 'SellCode01';
        $new->sapcompanybuycode1 = 'BuyCode01';
        $new->sapcompanysellcode2 = 'SellCode02';
        $new->sapcompanybuycode2 = 'BuyCode02';
        $new->dailybuylimitxau = 1.2;
        $new->dailyselllimitxau = 1.2;
        $new->pricelapsetimeallowance = 12;
        $new->orderingmode = Partner::MODE_WEB;
        $new->autosubmitorder = 1;
        $new->autocreatematchedorder = 1;
        $new->orderconfirmallowance = 1;
        $new->ordercancelallowance = 1;
        $new->apikey = 1234;
        $new->createdon = new \DateTime('now');
        $new->modifiedon = new \DateTime('now');
        $new->status = Partner::STATUS_ACTIVE;
        $new->createdby = 1;
        $new->modifiedby = 1;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals(1, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['code', 'name', 'address', 'postcode', 'state', 'type', 'pricesourceid', 'salespersonid', 'tradingscheduleid', 'sapcompanysellcode1', 'sapcompanybuycode1', 'sapcompanysellcode2', 'sapcompanybuycode2', 'dailybuylimitxau', 'dailyselllimitxau', 'pricelapsetimeallowance', 'orderingmode', 'autosubmitorder', 'autocreatematchedorder', 'orderconfirmallowance', 'ordercancelallowance', 'apikey'] as $field) {
            $this->assertEquals($new->{$field}, $inDB->{$field});
        }
    }

    /**
     * @depends testAddNew
     * @expectedException Snap\InputException
     * 
     */
    public function testDuplicateCode()
    {
        list($store, $productStore) = $this->setupStores();
        $duplicatePartner = $store->create();
        $duplicatePartner->code = 'CODE01';
        $duplicatePartner->name = 'Maybank';
        $duplicatePartner->address = 'Jalan Putero Tower 1';
        $duplicatePartner->postcode = '43000';
        $duplicatePartner->state = 'Selangor';
        $duplicatePartner->type = Partner::TYPE_CUSTOMER;
        $duplicatePartner->pricesourceid = 1;
        $duplicatePartner->salespersonid = 1;
        $duplicatePartner->tradingscheduleid = 1;
        $duplicatePartner->sapcompanysellcode1 = 'SellCode01';
        $duplicatePartner->sapcompanybuycode1 = 'BuyCode01';
        $duplicatePartner->sapcompanysellcode2 = 'SellCode02';
        $duplicatePartner->sapcompanybuycode2 = 'BuyCode02';
        $duplicatePartner->dailybuylimitxau = 1.2;
        $duplicatePartner->dailyselllimitxau = 1.2;
        $duplicatePartner->pricelapsetimeallowance = 12;
        $duplicatePartner->orderingmode = Partner::MODE_WEB;
        $duplicatePartner->autosubmitorder = 1;
        $duplicatePartner->autocreatematchedorder = 1;
        $duplicatePartner->orderconfirmallowance = 1;
        $duplicatePartner->ordercancelallowance = 1;
        $duplicatePartner->apikey = 1234;
        $duplicatePartner->createdon = new \DateTime('now');
        $duplicatePartner->modifiedon = new \DateTime('now');
        $duplicatePartner->status = Partner::STATUS_ACTIVE;
        $duplicatePartner->createdby = 1;
        $duplicatePartner->modifiedby = 1;
        $saved = $store->save($duplicatePartner);

    }

    /**
     * @depends testAddNew
     */
    public function testRead()
    {
        list($store, $productStore) = $this->setupStores();
        $inDB = $store->getById(1);
        $this->assertNotNull($inDB);
        $this->assertEquals(1, $inDB->id);
    }

    /**
     * @depends testAddNew
     */
    public function testUpdate()
    {
        list($store, $productStore) = $this->setupStores();
        $inDB = $store->getById(1);
        $inDB->code = 'Code02';
        $inDB->name = 'New Name';
        $inDB->status = Partner::STATUS_PENDING;
        $updated = $store->save($inDB);
        $this->assertEquals("0", $updated->status);
    }

    /**
     * @depends testUpdate
     */
    public function testAddPartnerService()
    {
        list($store, $productStore) = $this->setupStores();
        $partner = $store->getByField('code', 'CODE02');
        $this->assertEquals(1, $partner->id);
        $product = $productStore->getById(1);
        $this->assertEquals(1, $product->id);

        $partner->registerService( $product, 'sapgroup', '0.03', 0.02, 0, 0, 0, 0, 1, 10, 10000, 5000, 50000);
        $store->save($partner);
        $partner = $store->getByField('code', 'CODE02');
        $this->assertFalse($partner->canBuy($product));
        $this->assertFalse($partner->canSell($product));
        $this->assertTrue($partner->canRedeem($product));
        $this->assertFalse($partner->canQueue($product));
        $this->assertFalse($partner->includefeeinprice($product));

        $partner->registerService( $product, 'sapgroup', '0.03', 0.02, 1, 1, 1, 1, 0, 10, 10000, 5000, 50000);
        $store->save($partner);
        $partner = $store->getByField('code', 'CODE02');
        $this->assertTrue($partner->canBuy($product));
        $this->assertTrue($partner->canSell($product));
        $this->assertFalse($partner->canRedeem($product));
        $this->assertTrue($partner->canQueue($product));
        $this->assertFalse($partner->includefeeinprice($product));
    }

    /**
     * @depends testAddPartnerService
     */
    public function testCheckPartnerService()
    {
        list($store, $productStore) = $this->setupStores();
        $partner = $store->getByField('code', 'CODE02');
        $this->assertEquals(1, $partner->id);
        $product = $productStore->getById(1);
        $this->assertEquals(1, $product->id);

        $this->assertTrue($partner->hasService($product));
        $this->assertTrue($partner->canBuy($product));
        $this->assertTrue($partner->canSell($product));
        $this->assertFalse($partner->canRedeem($product));
        $this->assertTrue($partner->canQueue($product));
        $this->assertFalse($partner->includefeeinprice($product));
        $this->assertEquals($partner->getProductClickMin($product), 10);
        $this->assertEquals($partner->getProductClickMax($product), 10000);
        $this->assertEquals($partner->getProductDailyBuyLimit($product), 5000);
        $this->assertEquals($partner->getProductDailySellLimit($product), 50000);
    }

    public function testBranchRegister()
    {
        list($store, $productStore) = $this->setupStores();
        $partner = $store->getByField('code', 'CODE02');
        $this->assertEquals(1, $partner->id);
        $partner->registerBranch('KIJANG_1', 'Kuala Pilah Branch', 'ASAHI');
        $partner->registerBranch('KIJANG_2', 'Kuala Lumpur Branch', 'MATAHARI');
        $store->save($partner);
    }

    /**
     * @depends testBranchRegister
     */
    public function testCacheData() {
        list($store, $productStore) = $this->setupStores();
        $partner = $store->getByField('code', 'CODE02');
        $this->assertEquals(1, $partner->id);
        $cachedData = $partner->toCache();
        $this->assertContains('*BRC*', $cachedData);
        $this->assertContains('*V*', $cachedData);

        $product = $productStore->getById(1);
        $this->assertEquals(1, $product->id);
        $partner2 = $store->create();
        $partner2->fromCache($cachedData);
        $this->assertEquals(1, $partner2->id);
        $this->assertEquals('Code02', $partner2->code);
        $this->assertTrue($partner2->hasService($product));
        $this->assertTrue($partner2->canBuy($product));
        $this->assertTrue($partner2->canSell($product));
        $this->assertFalse($partner2->canRedeem($product));
        $this->assertTrue($partner2->canQueue($product));
        $this->assertFalse($partner2->includefeeinprice($product));
        $this->assertEquals($partner2->getProductClickMin($product), 10);
        $this->assertEquals($partner2->getProductClickMax($product), 10000);
        $this->assertEquals($partner2->getProductDailyBuyLimit($product), 5000);
        $this->assertEquals($partner2->getProductDailySellLimit($product), 50000);
        $this->assertCount(2, $partner2->getBranches());
    }

    /**
     * @depends testBranchRegister
     */
    public function testUnregisterBranch()
    {
        list($store, $productStore) = $this->setupStores();
        $partner = $store->getByField('code', 'CODE02');
        $this->assertEquals(1, $partner->id);
        $branch = $partner->getBranch('KIJANG_1');
        $this->assertNotNull($branch);
        $this->assertGreaterThan(0, $branch->id);
        $partner->unregisterBranch($branch);
        $this->assertCount(1, $partner->getBranches());

        $store->save($partner);
        $partner2 = $store->getById(1);
        $this->assertCount(1, $partner->getBranches());
    }

    /**
     * @depends testCacheData
     */
    public function testUnregPartnerService()
    {
        list($store, $productStore) = $this->setupStores();
        $partner = $store->getByField('code', 'CODE02');
        $this->assertEquals(1, $partner->id);
        $product = $productStore->getById(1);
        $this->assertEquals(1, $product->id);
        $partner->unregisterService($product);
        $store->save($partner);
        $partner = $store->getById(1);

        $this->assertFalse($partner->canBuy($product));
        $this->assertFalse($partner->canSell($product));
        $this->assertFalse($partner->canRedeem($product));
        $this->assertFalse($partner->canQueue($product));
        $this->assertFalse($partner->includefeeinprice($product));
        $this->assertFalse($partner->hasService($product));
    }

}

?>
