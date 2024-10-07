<?php
Use Snap\object\SnapObject;
Use Snap\object\LogisticTracker;

/**
 * @covers LogisticTracker objects
 * @backupGlobals disabled
 */
final class LogisticTrackerTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('logistictracker', LogisticTracker::class, 'lot');
    }

    public function addTestProvider(): array
    {
        return [
            [1, '1.0.0', 'order', 1, 1, 'ANDY', 1, 'ANDY']
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    
    public function testAddNew($partnerid, $apiversion, $itemtype, $itemid, $senderid, $senderref, $sendby, $receiveperson)
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->partnerid = $partnerid;
        $new->apiversion = $apiversion;
        $new->itemtype = $itemtype;
        $new->itemid = $itemid;
        $new->senderid = $senderid;
        $new->senderref = $senderref;
        $new->sendon = new \DateTime('now');
        $new->sendby = $sendby;
        $new->receivedon = new \DateTime('now');
        $new->receiveperson = $receiveperson;
        $new->createdon = new \DateTime('now');
        $new->createdby = 1;
        $new->modifiedon = new \DateTime('now');
        $new->modifiedby = 1;
        $new->status = 0;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals(1, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['partnerid', 'apiversion', 'itemtype', 'itemid', 'senderid', 'senderref', 'sendby', 'receiveperson'] as $field) {
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
