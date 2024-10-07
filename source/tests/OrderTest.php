<?php
use Snap\object\SnapObject;
Use Snap\object\Order;

/**
 * @covers order objects
 * @backupGlobals disabled
 */
final class OrderTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('order', Order::class, 'ord');
    }


    /**
     * @dataProvider addTestProvider
     */
    public function addTestProvider(): array
    {
        return [
            [1, 1, 1, 12345, 1, 1, 'v2.0', Order::TYPE_COMPANYBUY, 1, 1, 1111111111, 1, 1111111111, 1111111111, 1111111111, 'remarks', '2018-10-21 12:00:00', 1111111111, 1, '2018-10-21 12:00:00', 1, 1, 1111111111, 1, '2018-10-21 12:00:00', 1, 1, 1111111, 'WWW.HASH.COM',1]
        ];
    }

    //public function testAddNew($partnerid, $buyerid, $partnerrefid, $orderno, $pricestreamid, $salespersonid, $apiversion, $type, $productid, $isspot, $price, $byweight, $xau, $amount, $fee, $remarks, $bookingon, $bookingprice, $bookingpricestreamid, $confirmon, $confirmby, $confirmpricestreamid, $confirmprice, $confirmreference, $cancelon, $cancelby, $cancelpriceid, $cancelprice, $notifyurl, $expectedId)
    public function testAddNew()
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->partnerid = 1;
        $new->buyerid = 1;
        $new->partnerrefid = 1;
        $new->orderno = 12345;
        $new->pricestreamid = 1;
        $new->salespersonid = 1;
        $new->apiversion = 'v2.0';
        $new->type = Order::TYPE_COMPANYBUY;
        $new->productid = 1;
        $new->isspot = 1;
        $new->price = 1111111111;
        $new->byweight = 1;
        $new->xau = 1;
        $new->amount = 1111111111;
        $new->fee = 1111111111;
        $new->remarks = 'remarks';
        $new->bookingon = '2018-10-21 12:00:00';
        $new->bookingprice = 1111111111;
        $new->bookingpricestreamid = 1;
        $new->confirmon = '2018-10-21 12:00:00';
        $new->confirmby = 1;
        $new->confirmpricestreamid = 1;
        $new->confirmprice = 1111111111;
        $new->confirmreference = 1;
        $new->cancelon = '2018-10-21 12:00:00';
        $new->cancelby = 1;
        $new->cancelpriceid = 1;
        $new->cancelprice = 1111111111;
        $new->notifyurl = 'WWW.HASH.COM';
        /*$new->partnerid = $partnerid;
        $new->buyerid = $buyerid;
        $new->partnerrefid = $partnerrefid;
        $new->orderno = $orderno;
        $new->pricestreamid = $pricestreamid;
        $new->salespersonid = $salespersonid;
        $new->apiversion = $apiversion;
        $new->type = $type;
        $new->productid = $productid;
        $new->isspot = $isspot;
        $new->price = $price;
        $new->byweight = $byweight;
        $new->xau = $xau;
        $new->amount = $amount;
        $new->fee = $fee;
        $new->remarks = $remarks;
        $new->bookingon = $bookingon;
        $new->bookingprice = $bookingprice;
        $new->bookingpricestreamid = $bookingpricestreamid;
        $new->confirmon = $confirmon;
        $new->confirmby = $confirmby;
        $new->confirmpricestreamid = $confirmpricestreamid;
        $new->confirmprice = $confirmprice;
        $new->confirmreference = $confirmreference;
        $new->cancelon = $cancelon;
        $new->cancelby = $cancelby;
        $new->cancelpriceid = $cancelpriceid;
        $new->cancelprice = $cancelprice;
        $new->notifyurl = $notifyurl;*/
        $new->createdon = new \DateTime('now');
        $new->modifiedon = new \DateTime('now');
        $new->status = Order::STATUS_PENDING;
        $new->createdby = 1;
        $new->modifiedby = 1;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals(1, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['partnerid', 'buyerid', 'partnerrefid', 'orderno', 'pricestreamid', 'salespersonid', 'apiversion', 'type', 'productid', 'isspot', 'price', 'byweight', 'xau', 'amount', 'fee', 'remarks', 'bookingon', 'bookingprice', 'bookingpricestreamid', 'confirmon', 'confirmby', 'confirmpricestreamid', 'confirmprice', 'confirmreference', 'cancelon', 'cancelby', 'cancelpriceid', 'cancelprice', 'notifyurl'] as $field) {
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
        $inDB->remarks = 'Change to something else';
        $inDB->status = Order::STATUS_PENDINGPAYMENT;
        $updated = $store->save($inDB);
        $this->assertEquals("2", $updated->status);
    }
}

?>
