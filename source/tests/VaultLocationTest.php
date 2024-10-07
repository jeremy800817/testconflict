<?php
Use Snap\object\SnapObject;
Use Snap\object\VaultLocation;

/**
 * @covers vault location  objects
 * @backupGlobals disabled
 */
final class VaultLocationTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('vaultlocation', VaultLocation::class, 'stl');
    }

    /**
     * @return array
     */
    public function addTestProvider(): array
    {
        return [
            [1, 'ACE 32', 1, 1, 1, 1,1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($partnerid, $name, $owner, $minimumlevel, $reorderlevel, $defaultlocation, $expectedId):void
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->partnerid = $partnerid;
        $new->name = $name;
        $new->owner = $owner;
        $new->minimumlevel = $minimumlevel;
        $new->reorderlevel = $reorderlevel;
        $new->defaultlocation = $defaultlocation;
        $new->createdon = new \DateTime('now');
        $new->modifiedon = new \DateTime('now');
        $new->status = 0;
        $new->createdby = 1;
        $new->modifiedby = 1;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals($expectedId, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['partnerid', 'name', 'owner', 'minimumlevel', 'reorderlevel', 'defaultlocation'] as $field) {
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
     * @depends testUpdate
     */
    public function testUpdate():void
    {
        $store = $this->setupStores();
        $inDB = $store->getById(1);
        $inDB->status = 1;
        $updated = $store->save($inDB);
        $this->assertEquals(1, $updated->status);
    }
}

?>
