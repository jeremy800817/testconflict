<?php
use Snap\object\SnapObject;
Use Snap\object\PriceProvider;

/**
 * @covers medicalrecord objects
 * @backupGlobals disabled
 */
final class PriceProviderTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('priceprovider', PriceProvider::class, 'prp');
    }

    public function addTestProvider(): array
    {
        return [
            ['CODE01', 'Maybank_GOLD', 1, 1, 1, 1, '22.33.222.44', 'www.sds.com', 'info', 1, 1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($code, $name, $pricesourceid, $productcategoryid, $pullmode, $currencyid, $whitelistip, $url, $connectinfo, $lapsetimeallowance, $expectedId)
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->code = $code;
        $new->name = $name;
        $new->pricesourceid = $pricesourceid;
        $new->productcategoryid = $productcategoryid;
        $new->pullmode = $pullmode;
        $new->currencyid = $currencyid;
        $new->whitelistip = $whitelistip;
        $new->url = $url;
        $new->connectinfo = $connectinfo;
        $new->lapsetimeallowance = $lapsetimeallowance;
        $new->createdon = new \DateTime('now');
        $new->modifiedon = new \DateTime('now');
        $new->status = SnapObject::STATUS_ACTIVE;
        $new->createdby = 1;
        $new->modifiedby = 1;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals($expectedId, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['code', 'name', 'pricesourceid', 'productcategoryid','pullmode', 'currencyid', 'whitelistip', 'url', 'connectinfo', 'lapsetimeallowance'] as $field) {
            $this->assertEquals($new->{$field}, $inDB->{$field});
        }
    }

    /**
     * @depends testAddNew
     */
    public function testRead()
    {
        $store = $this->setupStores();
        $inDB = $store->getById(1);
        $this->assertNotNull($inDB);
        $this->assertEquals(1, $inDB->id);
    }

    /**
     * @depends testAddNew
     */
    public function testUpdate()
    {
        $store = $this->setupStores();
        $inDB = $store->getById(1);
        $inDB->code = 'Code02';
        $inDB->name = 'New Name';
        $inDB->status = SnapObject::STATUS_INACTIVE;
        $updated = $store->save($inDB);
        $this->assertEquals("0", $updated->status);
    }
}

?>