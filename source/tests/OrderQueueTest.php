<?php
use Snap\object\SnapObject;
Use Snap\object\Order;
Use Snap\object\OrderQueue;

/**
 * @covers order queue objects
 * @backupGlobals disabled
 */
final class OrderQueueTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('order_queue', OrderQueue::class, 'orq');
    }


    /**
     * @dataProvider addTestProvider
     */
    public function addTestProvider(): array
    {
        return [
            [1, 1, 1, 1, 12345, 1, 'v2.0', Order::TYPE_COMPANYBUY, OrderQueue::TYPE_DAY, '2018-10-21 12:00:00', 1, 1111111111, 1, 1, 1111111111, 'remarks','2018-10-21 12:00:00', 1, 1, '2018-10-21 12:00:00', 'www.hash.com', 'www.hashmatch.com', 'www.hashsuccess.com',1]
        ];
    }


    //public function testAddNew($orderid, $partnerid, $buyerid, $partnerrefid, $orderqueueno, $salespersonid, $apiversion, $ordertype, $queuetype, $expireon, $productid, $pricetarget, $byweight, $xau, $amount, $remarks, $cancelon, $cancelby, $matchpriceid, $matchon, $notifyurl, $notifymatchurl, $successnotifyurl, $expectedId)
    public function testAddNew()
    {
        $store = $this->setupStores();

        $new = $store->create();
        /*$new->orderid = $orderid;
        $new->partnerid = $partnerid;
        $new->buyerid = $buyerid;
        $new->partnerrefid = $partnerrefid;
        $new->orderqueueno = $orderqueueno;
        $new->salespersonid = $salespersonid;
        $new->apiversion = $apiversion;
        $new->ordertype = $ordertype;
        $new->queuetype = $queuetype;
        $new->expireon = $expireon;
        $new->productid = $productid;
        $new->pricetarget = $pricetarget;
        $new->byweight = $byweight;
        $new->xau = $xau;
        $new->amount = $amount;
        $new->remarks = $remarks;
        $new->cancelon = $cancelon;
        $new->cancelby = $cancelby;
        $new->matchpriceid = $matchpriceid;
        $new->matchon = $matchon;
        $new->notifyurl = $notifyurl;
        $new->notifymatchurl = $notifymatchurl;
        $new->successnotifyurl = $successnotifyurl;*/
        $new->orderid = 1;
        $new->partnerid = 1;
        $new->buyerid = 1;
        $new->partnerrefid = 1;
        $new->orderqueueno = 12345;
        $new->salespersonid = 1;
        $new->apiversion = 'v2.0';
        $new->ordertype = Order::TYPE_COMPANYBUY;
        $new->queuetype = OrderQueue::TYPE_DAY;
        $new->expireon = '2018-10-21 12:00:00';
        $new->productid = 1;
        $new->pricetarget = 1111111111;
        $new->byweight = 1;
        $new->xau = 1;
        $new->amount = 1111111111;
        $new->remarks = 'remarks';
        $new->cancelon = '2018-10-21 12:00:00';
        $new->cancelby = 1;
        $new->matchpriceid = 1;
        $new->matchon = '2018-10-21 12:00:00';
        $new->notifyurl = 'www.hash.com';
        $new->notifymatchurl = 'www.hashmatch.com';
        $new->successnotifyurl = 'www.hashsuccess.com';
        $new->createdon = new \DateTime('now');
        $new->modifiedon = new \DateTime('now');
        $new->status = OrderQueue::STATUS_EXPIRED;
        $new->createdby = 1;
        $new->modifiedby = 1;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals(1, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['orderid', 'partnerid', 'buyerid', 'partnerrefid', 'orderqueueno', 'salespersonid', 'apiversion', 'ordertype', 'queuetype', 'expireon', 'productid', 'pricetarget' ,'byweight', 'xau', 'amount', 'remarks', 'cancelon', 'cancelby', 'matchpriceid', 'matchon', 'notifyurl', 'notifymatchurl', 'successnotifyurl'] as $field) {
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
        $inDB->queuetype = OrderQueue::TYPE_GOODTILLDATE;
        $inDB->remarks = 'Change to something else';
        $inDB->status = OrderQueue::STATUS_PENDING;
        $updated = $store->save($inDB);
        $this->assertEquals("0", $updated->status);
    }
}

?>
