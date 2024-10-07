<?php
use Snap\object\SnapObject;
Use Snap\object\IPRestriction;

/**
 * @covers \Snap\object\IPRestriction objects
 * @backupGlobals disabled
 */

final class IPRestrictionTest extends LazyTestCase {

    /**
     * Setup Test Configurations
     */
    public function setUp() {
        parent::setUpBeforeClass('iprestriction', IPRestriction::class, 'ipr');
    }

    public function addTestProvider(): array
    {
        return [
            [IPRestriction::RESTRICT_LOGIN, IPRestriction::PARTNER_HQ, 12, '102.78.283.211', 'Bla-bla-bla', 1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     * @param $id
     * @param $restricttype
     * @param $partnertype
     * @param $partnerid
     * @param $ip
     * @param $remark
     * @param $expectedId
     */
    public function testAddNew($restricttype, $partnertype, $partnerid, $ip, $remark, $expectedId): void
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->restricttype = $restricttype;
        $new->partnertype = $partnertype;
        $new->partnerid = $partnerid;
        $new->ip = $ip;
        $new->remark = $remark;
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
        foreach(['restricttype', 'partnertype', 'partnerid', 'ip', 'remark'] as $field) {
            $this->assertEquals($new->{$field}, $inDB->{$field});
        }
    }
    
    /**
     * @depends testAddNew
     */
    public function testRead() {
        $store = $this->setupStores();
        $inDB = $store->getById(1);
        $this->assertNotNull($inDB);
        $this->assertEquals(1, $inDB->id);
    }

    /**
     * @depends testAddNew
     */
    public function testUpdate() {
        $store = $this->setupStores();
        $inDB = $store->getById(1);
        $inDB->remark = 'Ok-ok';
        $inDB->status = SnapObject::STATUS_INACTIVE;
        $updated = $store->save($inDB);
        $this->assertEquals('0', $updated->status);
    }
}
?>