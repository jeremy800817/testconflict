<?php

use Snap\object\SnapObject;
use Snap\object\ApiGoldRequest;

/**
 * @covers ApiGoldRequest objects
 * @backupGlobals disabled
 */
final class ApiGoldRequestTest extends LazyTestCase {

    /**
     * Setup Test Configurations
     */
    public function setUp() {
        parent::setUpBeforeClass('apigoldrequest', ApiGoldRequest::class, 'agr');
    }

    /**
     * @return array
     */
     public function addTestProvider(): array
     {
         return [
             [123, 'PARTNER123', '1.0', 1, 'ref123', 1]
         ];
     }


    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($partnerID, $partnerrefID, $apiversion, $quantity, $reference, $expectedId): void
    {
        $Store = $this->setupStores();
        $new = $Store->create();
        $new->partnerid = $partnerID;
        $new->partnerrefid = $partnerrefID;
        $new->apiversion = $apiversion;
        $new->quantity = $quantity;
        $new->reference = $reference;
        $new->timestamp = new \DateTime('now');

        $new->createdon = new \DateTime('now');
        $new->createdby = 1;
        $new->modifiedon = new \DateTime('now');
        $new->modifiedby = 1;

        $saved = $Store->save($new);
        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals($expectedId, $saved->id);
        $InDb = $Store->getById( $saved->id);
        $this->assertNotNull($InDb);
        foreach(['partnerid', 'partnerrefid', 'apiversion', 'quantity', 'reference'] as $field) {
            $this->assertEquals( $new->{$field}, $InDb->{$field});  //all fields from DB and from what we save must be the same.
        }
    }

    /**
     * @depends testAddnew
     */
    public function testRead(): void
     {
        $Store = $this->setupStores();
        $InDb = $theStore->getById(1);
        $this->assertNotNull($InDb);
        $this->assertEquals(1, $InDb->id);
    }

    /**
     * @depends testAddnew
     */
    public function testUpdate(): void
    {
        $Store = $this->setupStores();
        $InDb = $theStore->getById(1);
        $updated = $theStore->save($InDb);
        $this->assertEquals('ref123u', $updated->reference);
    }
}

?>