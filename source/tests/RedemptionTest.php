<?php
Use Snap\object\SnapObject;
Use Snap\object\Redemption;

/**
 * @covers redemption objects
 * @backupGlobals disabled
 */
final class RedemptionTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('redemption', Redemption::class, 'rdm');
    }


    /**
     * @return array 
     */
    public function addTestProvider(): array
    {
        return [
            [1, 1, 1, 'refno123', 'redemptionno1234', '1.0', 'Redeem', 1, 
            3000.00, 100.00, 120.00, 300.00, 'ACE', 'ACE123', 10.00, 12.00, 11.00, 1, 
            10.00, 10.00, 'refcon10', 'Jalan 10 Bukit Puchong', '46200 Puchong', 'Selangor Dahrul Ehsan', '46200', 'Selangor', 
            '0123456789', 'Inventory', 'abc', 1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($partnerid, $branchid, $salespersonid, $partnerrefno, $redemptionno, $apiversion, $type, $productid,
    $redemptionfee, $insurancefee, $handlingfee, $specialdeliveryfee, $xaubrand, $xauserialno, $xau, $fee , $bookingprice, $confirmby, 
    $confirmpricestreamid, $confirmprice, $confirmreference, $deliveryaddress1, $deliveryaddress2, $deliveryaddress3, $deliverypostcode, $deliverystate, 
    $deliverycontactno, $inventory, $remarks, $expectedId):void
    {
        $store = $this->setupStores();
        $new = $store->create();

        $new->partnerid = $partnerid;
        $new->branchid = $branchid;
        $new->salespersonid = $salespersonid;
        $new->partnerrefno = $partnerrefno;
        $new->redemptionno = $redemptionno;
        $new->apiversion = $apiversion;
        $new->type = $type;
        $new->productid = $productid;
        $new->redemptionfee = $redemptionfee;
        $new->insurancefee = $insurancefee;
        $new->handlingfee = $handlingfee;
        $new->specialdeliveryfee = $specialdeliveryfee;
        $new->xaubrand = $xaubrand;
        $new->xauserialno = $xauserialno;
        $new->xau = $xau;
        $new->fee = $fee;
        $new->bookingon = new \DateTime('now');
        $new->bookingprice = $bookingprice;
        $new->bookingpricestreamid = new \DateTime('now');
        $new->confirmon = new \DateTime('now');
        $new->confirmby = $confirmby;
        $new->confirmpricestreamid = $confirmpricestreamid;
        $new->confirmprice = $confirmprice;
        $new->confirmreference = $confirmreference;
        $new->deliveryaddress1 = $deliveryaddress1;
        $new->deliveryaddress2 = $deliveryaddress2;
        $new->deliveryaddress3 = $deliveryaddress3;
        $new->deliverypostcode = $deliverypostcode;
        $new->deliverystate = $deliverystate;
        $new->deliverycontactno = $deliverycontactno;
        $new->inventory = $inventory;
        $new->processedon = new \DateTime('now');
        $new->deliveredon = new \DateTime('now');
        $new->createdon = new \DateTime('now');
        $new->createdby = 1;
        $new->modifiedon = new \DateTime('now');
        $new->modifiedby = 1;
        $new->status = 0;
        $new->remarks = $remarks;

        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals($expectedId, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['partnerid', 'branchid', 'salespersonid', 'partnerrefno', 'redemptionno', 'apiversion', 'type', 'productid',
        'redemptionfee', 'insurancefee', 'handlingfee', 'specialdeliveryfee', 'xaubrand', 'xauserialno', 'xau', 'fee', 'bookingprice',
        'confirmby', 'confirmpricestreamid', 'confirmedprice', 'confirmreference', 'deliveryaddress1','deliveryaddress2', 'deliveryaddress3', 
        'deliverypostcode', 'deliverystate', 'deliverycontactno', 'inventory','remarks'] as $field) {
            $this->assertEquals($new->{$field}, $inDB->{$field});
        }
    }

    /**
     * @depends testAddNew
     */
    public function testRead():void
    {
        $store = $this->setupStores();
        $inDB = $store->getById(1);
        $this->assertNotNull($inDB);
        $this->assertEquals(1, $inDB->id);
    }

    /**
     * @depends testAddNew
     */
    public function testUpdate():void
    {
        $store = $this->setupStores();
        $inDB = $store->getById(1);
        $inDB->status = 1;
        $updated = $store->save($inDB);
        $this->assertEquals("1", $updated->status);
    }
}

?>
