<?php
use Snap\object\SnapObject;
Use Snap\object\PriceStream;

/**
 * @covers medicalrecord objects
 * @backupGlobals disabled
 */
final class PriceStreamTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('pricestream', PriceStream::class, 'pst');
    }

    public function addTestProvider(): array
    {
        return [
            [1, 1, 1, 1, 1, 1, 1, '2018-10-21 12:00:00', 1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($providerid, $providerpriceid, $uuid, $currencyid, $companybuyppg, $companysellppg, $pricesourceid, $pricesourceon, $expectedId)
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->providerid = $providerid;
        $new->providerpriceid = $providerpriceid;
        $new->uuid = $uuid;
        $new->currencyid = $currencyid;
        $new->companybuyppg = $companybuyppg;
        $new->companysellppg = $companysellppg;
        $new->pricesourceid = $pricesourceid;
        $new->pricesourceon = $pricesourceon;
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
        foreach (['providerid', 'providerpriceid', 'uuid', 'currencyid', 'companybuyppg', 'companysellppg', 'pricesourceid', 'pricesourceon'] as $field) {
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
        $inDB->status = SnapObject::STATUS_INACTIVE;
        $updated = $store->save($inDB);
        $this->assertEquals("0", $updated->status);
    }
}

?>