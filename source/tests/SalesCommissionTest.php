<?php
use Snap\object\SnapObject;
Use Snap\object\SalesComission;

/**
 * @covers medicalrecord objects
 * @backupGlobals disabled
 */
final class SalesComissionTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('sales_commission', SalesComission::class, 'com');
    }

    public function addTestProvider(): array
    {
        return [
            [1, '2018-10-21 00:00:00', '2018-10-21 00:00:00', 1111, 1111, 111, 111, 1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($salespersonid, $startdate, $enddate, $totalcompanybuy, $totalcompanysell, $totalxau, $totalfee, $expectedId)
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->salespersonid = $salespersonid;
        $new->startdate = new \DateTime($startdate);
        $new->enddate = new \DateTime($enddate);
        $new->totalcompanybuy = $totalcompanybuy;
        $new->totalcompanysell = $totalcompanysell;
        $new->totalxau = $totalxau;
        $new->totalfee = $totalfee;
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
        foreach (['salespersonid', 'startdate', 'enddate', 'totalcompanybuy', 'totalcompanysell', 'totalxau', 'totalfee'] as $field) {
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