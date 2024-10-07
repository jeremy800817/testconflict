<?php
use Snap\object\SnapObject;
Use Snap\object\Product;

/**
 * @covers medicalrecord objects
 * @backupGlobals disabled
 */
final class ProductTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('product', Product::class, 'pdt');
    }

    public function addTestProvider(): array
    {
        return [
            [1, 'CODE01', 'GOLD', 1, 1, 12, 12, 2, 'SAPCODE', 1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($categoryid, $code, $name, $companycansell, $companycanbuy, $trxbyweight, $trxbycurrency, $deliverable, $sapitemcode, $expectedId)
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->categoryid = $categoryid;
        $new->code = $code;
        $new->name = $name;
        $new->companycansell = $companycansell;
        $new->companycanbuy = $companycanbuy;
        $new->trxbyweight = $trxbyweight;
        $new->trxbycurrency = $trxbycurrency;
        $new->deliverable = $deliverable;
        $new->sapitemcode = $sapitemcode;
        $new->createdon = new \DateTime('now');
        $new->modifiedon = new \DateTime('now');
        $new->status = Product::STATUS_ACTIVE;
        $new->createdby = 1;
        $new->modifiedby = 1;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals($expectedId, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['categoryid', 'code', 'name', 'companycansell', 'companycanbuy', 'trxbyweight', 'trxbycurrency', 'deliverable', 'sapitemcode'] as $field) {
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
        $inDB->status = Product::STATUS_PENDING;
        $updated = $store->save($inDB);
        $this->assertEquals("0", $updated->status);
    }
}

?>