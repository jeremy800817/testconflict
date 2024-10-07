<?php
Use Snap\object\SnapObject;
Use Snap\object\Tag;

/**
 * @covers tag objects
 * @backupGlobals disabled
 */
final class TagTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('tag', Tag::class, 'tag');
    }


    /**
     * @dataProvider addTestProvider
     */
    public function addTestProvider(): array
    {
        return [
            ['PriceSource', 'CODE12', 'Long Description', 'Value']
        ];
    }


    public function testAddNew()
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->category = 'PriceSource';
        $new->code = 'CODE12';
        $new->description = 'Long Description';
        $new->value = 'Value';
        $new->createdon = new \DateTime('now');
        $new->createdby = 1;
        $new->modifiedby = 1;
        $new->modifiedon = new \DateTime('now');
        $new->status = 0;

        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals(1, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['category', 'code', 'description', 'value'] as $field) {
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
