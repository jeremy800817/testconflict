<?php
Use Snap\object\SnapObject;
Use Snap\object\PartnerBranchMap;

/**
 * @covers partnerbranchmap objects
 * @backupGlobals disabled
 */
final class PartnerBranchMapTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('partnerbranchmap', PartnerBranchMap::class, 'pbm');
    }

    /**
     * @dataProvider addTestProvider
     */
    public function addTestProvider(): array
    {
        return [
            [1, 'CODE01', 'ANDY', 'ACE123', 'SAP123', 1]
        ];
    }

    //$partnerid, $branchcode, $name, $partnercode, $sapcode, $expectedId
    public function testAddNew()
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->partnerid = 1;
        $new->branchcode = 'CODE01';
        $new->name = 'ANDY';
        $new->partnercode = 'ACE123';
        $new->sapcode = 'SAP123';
        $new->createdon = new \DateTime('now');
        $new->modifiedon = new \DateTime('now');
        $new->status = 0;
        $new->createdby = 1;
        $new->modifiedby = 1;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals(1, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['partnerid', 'branchcode', 'name', 'partnercode', 'sapcode'] as $field) {
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
        $inDB->status = 1;
        $updated = $store->save($inDB);
        $this->assertEquals(1, $updated->status);
    }
}

?>
