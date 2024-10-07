<?php

use Snap\object\SnapObject;
Use Snap\object\ApiLogs;

/**
 * @covers ApiLogsTest objects
 * @backupGlobals disabled
 */
final class ApiLogsTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('api_logs', ApiLogs::class, 'api');
    }



    /**
     * @dataProvider addTestProvider
     */

     public function addTestProvider(): array
     {
         return [
             ['SapOrder', '192.210.301.000', 1, 'ACEDATA', 'RESPONSE']
         ];
     }

    public function testAddNew()
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->type = 'SapOrder';
        $new->fromip = '192.210.301.000';
        $new->systeminitiate = 1;
        $new->requestdata = 'ACEDATA';
        $new->responsedata = 'RESPONSE';
        $new->createdon = new \DateTime('now');
        $new->createdby = 1;
        $new->modifiedon = new \DateTime('now');
        $new->status = 0;
        $new->modifiedby = 1;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals(1, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['type', 'fromip', 'systeminitiate', 'requestdata', 'responsedata'] as $field) {
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
