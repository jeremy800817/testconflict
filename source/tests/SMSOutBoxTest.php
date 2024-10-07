<?php
use Snap\object\SnapObject;
Use Snap\object\SMSOutBox;

/**
 * @covers medicalrecord objects
 * @backupGlobals disabled
 */
final class SMSOutBoxTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('smsoutbox', SMSOutBox::class, 'sms');
    }

    public function addTestProvider(): array
    {
        return [
            ['001238291', 'Message', '2018-10-21 12:00:00', 'Refernence', 'MsgType', 'Operator', 'errormsg', 1, 'response', 1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($phoneno, $msg, $senttime, $reference, $msgtype, $operator, $errormsg, $retrycount, $rawresponse, $expectedId)
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->phoneno = $phoneno;
        $new->msg = $msg;
        $new->senttime = new \DateTime($senttime);
        $new->reference = $reference;
        $new->msgtype = $msgtype;
        $new->operator = $operator;
        $new->errormsg = $errormsg;
        $new->retrycount = $retrycount;
        $new->rawresponse = $rawresponse;
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
        foreach (['phoneno', 'msg', 'senttime', 'reference', 'msgtype', 'operator', 'errormsg', 'retrycount', 'rawresponse'] as $field) {
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