<?php
use Snap\object\SnapObject;
Use Snap\object\GoodsReceiveNote;

/**
 * @covers medicalrecord objects
 * @backupGlobals disabled
 */
final class GoodsReceiveNoteTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('goods_receive_note', GoodsReceiveNote::class, 'grn');
    }

    public function addTestProvider(): array
    {
        return [
            [1, 1, 'comments','json',1111111111,1111111111,1111111111,1111111111,1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($partnerid, $salespersonid, $comments, $jsonpostpayload, $totalxauexpected, $totalgrossweight, $totalxaucollected, $vatsum, $expectedId)
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->partnerid = $partnerid;
        $new->salespersonid = $salespersonid;
        $new->comments = $comments;
        $new->jsonpostpayload = $jsonpostpayload;
        $new->totalxauexpected = $totalxauexpected;
        $new->totalgrossweight = $totalgrossweight;
        $new->totalxaucollected = $totalxaucollected;
        $new->vatsum = $vatsum;
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
        foreach (['partnerid', 'salespersonid', 'comments', 'jsonpostpayload', 'totalxauexpected', 'totalgrossweight', 'totalxaucollected', 'vatsum'] as $field) {
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
        $inDB->comments = 'Chang to something else';
        $inDB->jsonpostpayload = 'Chang to something else';
        $inDB->status = SnapObject::STATUS_INACTIVE;
        $updated = $store->save($inDB);
        $this->assertEquals("0", $updated->status);
    }
}

?>