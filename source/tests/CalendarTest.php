<?php
use Snap\object\SnapObject;
Use Snap\object\Calendar;

/**
 * @covers calendar objects
 * @backupGlobals disabled
 */
final class CalendarTest extends LazyTestCase {

    /**
     * Setup Test Configurations
     */
    public function setUp() {
        parent::setUpBeforeClass('calendar', Calendar::class, 'cal');
    }

    public function addCalendarProvider(): array
    {
        return [
                [ 0, 'Free Holiday', '2018-10-19 12:00:00', '1'],
                [ 0, 'Free Holiday 2', '2018-10-20 12:00:00', '2'],
                [ 0, 'Free Holiday 3', '2018-10-21 12:00:00', '3'],
                [ 0, 'Free Holiday 4', '2018-10-22 12:00:00', '4']
        ];
    }

    /**
     * @dataProvider addCalendarProvider
     * @param $branchid
     * @param $title
     * @param $holidayon
     * @param $expectedId
     */
    public function testAddNewCalendar($branchid, $title, $holidayon, $expectedId): void
    {
        $calendarStore = $this->setupStores();

        $newCalendar = $calendarStore->create();
        $newCalendar->branchid = $branchid;
        $newCalendar->title = $title;
        $newCalendar->holidayon = $holidayon;
        $newCalendar->createdon = new \DateTime('now');
        $newCalendar->modifiedon = '';
        $newCalendar->status = SnapObject::STATUS_ACTIVE;
        $newCalendar->createdby = '';
        $newCalendar->modifiedby = '';

        $savedCalendar = $calendarStore->save($newCalendar);

        $this->assertNotNull($savedCalendar);
        $this->assertGreaterThan(0, $savedCalendar->id);
        $this->assertEquals($expectedId, $savedCalendar->id);

        $CalendarInDb = $calendarStore->getById( $savedCalendar->id);
        $this->assertNotNull($CalendarInDb);
        $this->assertEquals($savedCalendar->id, $CalendarInDb->id);
        foreach(['branchid', 'title', 'holidayon'] as $field) {
            if ( 'holidayon' !== $field) {
                $this->assertEquals($newCalendar->{$field}, $CalendarInDb->{$field});
            } else {
                if (! empty($newCalendar->{$field})) {
                    $datetime = date_format($newCalendar->{$field}, "Y-m-d");
                    $datetime2 = date_format($CalendarInDb->{$field}, "Y-m-d");
                    $this->assertEquals($datetime, $datetime2);
                }
            }
        }
    }

    /**
     * @depends testAddNewCalendar
     */
    public function testReadCalendarInfo() {
        $calendarStore = $this->setupStores();
        $calendar = $calendarStore->getById(1);
        $this->assertNotNull($calendar);
        $this->assertEquals(1, $calendar->id);
    }

    /**
     * @depends testAddNewCalendar
     */
    public function testUpdateCalendarInfo() {
        $calendarStore = $this->setupStores();
        $calendar = $calendarStore->getById(1);
        $calendar->status = '0';
        $updatedCalendar = $calendarStore->save($calendar);
        $this->assertEquals('0', $updatedCalendar->status);
    }
}
?>
