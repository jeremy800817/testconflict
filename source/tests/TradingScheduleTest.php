<?php
use PHPUnit\Framework\TestCase;
Use Snap\object\SnapObject;
Use Snap\object\TradingSchedule;

/**
 * @covers Trading Schedule objects
 * @backupGlobals disabled
 */
final class TradingScheduleTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('tradingschedule', TradingSchedule::class, 'tds');
    }

    /**
     * @return array
     */
    public function addTestProvider(): array
    {
        $now = new \DateTime();
        $stopTime = gmdate("Y-m-d H:i:s", time() + 2);
        return [
            [1, 1, TradingSchedule::TYPE_DAILY, $stopTime, $now->format('Y-m-d 23:59:59')],
            [1, 2, TradingSchedule::TYPE_STOP, $stopTime, $now->format('Y-m-d 23:59:59')],
            [2, 3, TradingSchedule::TYPE_WEEKDAYS, $now->format('Y-m-d 00:00:00'), $now->format('Y-m-d 23:59:59')],
            [2, 4, TradingSchedule::TYPE_WEEKENDS, $now->format('Y-m-d 00:00:00'), $now->format('Y-m-d 23:59:59')],
            [2, 5, TradingSchedule::TYPE_STOP, $stopTime, $now->format('Y-m-d 23:59:59')],
        ];
    }
    
    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($categoryID, $expectedId, $typeId, $startAt, $endAt):void
    {
        $store = $this->setupStores();

        $now = new \DateTime();

        $new = $store->create();
        $new->categoryid = $categoryID;
        $new->type = $typeId;
        $new->startat = new \DateTime($startAt);
        $new->endat = new \DateTime($endAt);
        $new->createdon = $now;
        $new->createdby = 1;
        $new->modifiedon = $now;
        $new->modifiedby = 1;
        $new->status = 0;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals($expectedId, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['categoryID'] as $field) {
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
    public function testUpdate()
    {
        $store = $this->setupStores();
        $inDB = $store->getById(1);
        $inDB->status = 1;
        $updated = $store->save($inDB);
        $this->assertEquals(1, $updated->status);
    }

    /**
     * @depends testAddNew
     */
    public function testCanTradeNow()
    {
        $now = new \DateTime();
        $isWeekdayToday = ($now->format('N') <= 5);
        $store = $this->setupStores();
        $allRecords = $store->searchTable()->select()->execute();
        $expectedResults = [ false, true, $isWeekdayToday ? true : false, $isWeekdayToday ? false : true, true ];
        $counter = 0;
        foreach($allRecords as $aRecord) {
            $this->assertEquals($expectedResults[$counter++], $aRecord->canTradeNow(),
                sprintf('Failed for record id %d, type %s, start %s, ends %s, now is %s', $aRecord->id, $aRecord->type, 
                        $aRecord->startat->format('Y-m-d H:i:s'), $aRecord->endat->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s')));
        }
    }

    /**
     * @depends testCanTradeNow
     */
    public function testCanTradeNow2()
    {
        sleep(3);
        $now = new \DateTime();
        $isWeekdayToday = ($now->format('N') <= 5);
        $store = $this->setupStores();
        $allRecords = $store->searchTable()->select()->execute();
        $expectedResults = [ true, false, $isWeekdayToday ? true : false, $isWeekdayToday ? false : true, false ];
        $counter = 0;
        foreach($allRecords as $aRecord) {
            $this->assertEquals($expectedResults[$counter++], $aRecord->canTradeNow($now), 
                    sprintf('Failed for record id %d, type %s, start %s, ends %s, now is %s', $aRecord->id, $aRecord->type, 
                        $aRecord->startat->format('Y-m-d H:i:s'), $aRecord->endat->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s')));
        }
    }
}

?>
