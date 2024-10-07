<?php
use Snap\object\SnapObject;
Use Snap\object\GoodsReceiveNoteOrder;

/**
 * @covers medicalrecord objects
 * @backupGlobals disabled
 */
final class GoodsReceiveNoteOrderTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('goods_receive_note_order', GoodsReceiveNoteOrder::class, 'gro');
    }

    public function addTestProvider(): array
    {
        return [
            [1, 1, 1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($orderid, $goodsreceivenoteid, $expectedId)
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->orderid = $orderid;
        $new->goodsreceivenoteid = $goodsreceivenoteid;
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
        foreach (['orderid', 'goodsreceivenoteid'] as $field) {
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