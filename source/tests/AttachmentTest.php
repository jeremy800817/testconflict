<?php
use Snap\object\SnapObject;
Use Snap\object\Attachment;

/**
 * @covers Attachment objects
 * @backupGlobals disabled
 */
final class AttachmentTest extends LazyTestCase {

    /**
     * Setup Test Configurations
     */
    public function setUp() {
        parent::setUpBeforeClass('attachment', Attachment::class, 'att');
    }

    public function addAttachment(): array
    {
        return [
                [ Attachment::REDEMPTION, 123, 'desc here1', 'filename1', '13333', 'application/pdf', '', 1],
        ];
    }

    /**
     * @dataProvider addAttachment
     * @param $sourcetype
     * @param $sourceid
     * @param $description
     * @param $filename
     * @param $filesize
     * @param $mimetype
     * @param $data
     */
    public function testAddAttachment($sourcetype, $sourceid, $description, $filename, $filesize, $mimetype, $data, $expectedId): void
    {
        $theStore = $this->setupStores();

        $newObj = $theStore->create();
        $newObj->sourcetype = $sourcetype;
        $newObj->sourceid = $sourceid;
        $newObj->description = $description;
        $newObj->filename = $filename;
        $newObj->filesize = $filesize;
        
        $newObj->createdon = new \DateTime('now');
        $newObj->modifiedon = '';  
        $newObj->status = SnapObject::STATUS_ACTIVE;
        $newObj->createdby = '';
        $newObj->modifiedby = '';

        $savedObj = $theStore->save($newObj);
        
        $this->assertNotNull($savedObj);
        $this->assertGreaterThan(0, $savedObj->id);
        $this->assertEquals($expectedId, $savedObj->id);

        $objInDb = $theStore->getById( $savedObj->id);
        $this->assertNotNull($objInDb);
        $this->assertEquals($savedObj->id, $objInDb->id);
        foreach(['sourcetype', 'sourceid', 'description', 'filename', 'filesize'] as $field) {
            $this->assertEquals($newObj->{$field}, $objInDb->{$field});
        }
    }
    
    /**
     * @depends testAddAttachment
     */
    public function testReadAttachment() {
        $calendarStore = $this->setupStores();
        $calendar = $calendarStore->getById(2);
        $this->assertNotNull($calendar);
        $this->assertEquals(2, $calendar->id);
        $this->assertEquals('desc here2', $calendar->description);
    }

    /**
     * @depends testAddAttachment
     */
    public function testUpdateAttachment() {
        $calendarStore = $this->setupStores();
        $calendar = $calendarStore->getById(1);
        $calendar->status = '0';
        $updatedCalendar = $calendarStore->save($calendar);
        $this->assertEquals('0', $updatedCalendar->status);
    }
}
?>