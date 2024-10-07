<?php
use Snap\object\SnapObject;
Use Snap\object\PartnerService;

/**
 * @covers partnerservice objects
 * @backupGlobals disabled
 */
final class PartnerServiceTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('partnerservice', PartnerService::class, 'pas');
    }

    public function addTestProvider(): array
    {
        return [
            [1, 1, 1, 1, 1111, 1111,1, 1, 1, 1, 1, 1, 1, 123, 123, 1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($partnerid, $partnersapgroup, $productid, $pricesourcetypeid, $refineryfee, $premiumfee, $includefeeinprice, $canbuy, $cansell, $canqueue, $canredeem, $clickminxau, $clickmaxxau, $dailybuylimitxau, $dailyselllimitxau, $expectedId)
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->partnerid = $partnerid;
        $new->partnersapgroup = $partnersapgroup;
        $new->productid = $productid;
        $new->pricesourcetypeid = $pricesourcetypeid;
        $new->refineryfee = $refineryfee;
        $new->premiumfee = $premiumfee;
        $new->includefeeinprice = $includefeeinprice;
        $new->canbuy = $canbuy;
        $new->cansell = $cansell;
        $new->canqueue = $canqueue;
        $new->canredeem = $canredeem;
        $new->clickminxau = $clickminxau;
        $new->clickmaxxau = $clickmaxxau;
        $new->dailybuylimitxau = $dailybuylimitxau;
        $new->dailyselllimitxau = $dailyselllimitxau;
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
        foreach (['partnerid', 'partnersapgroup', 'productid', 'pricesourcetypeid', 'refineryfee', 'premiumfee', 'includefeeinprice', 'canbuy', 'cansell', 'canqueue', 'canredeem', 'clickminxau', 'clickmaxxau', 'dailybuylimitxau', 'dailyselllimitxau'] as $field) {
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
