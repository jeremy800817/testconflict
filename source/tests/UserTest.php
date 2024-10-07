<?php
Use Snap\object\User;

/**
 * @covers user objects
 * @backupGlobals disabled
 */
final class UserTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('user', User::class, 'usr');
    }

    public function addTestProvider(): array
    {
        return [
            ['ADMIN123', '12345', 'OLDPASSWORD', 'ADMIN NAME', '0123456789', 'admin123@ace2u.com', 1, 'Operator', 1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($username, $password, $oldpassword, $name, $phoneno, $email, $partnerID, $type,$expectedId)
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->username = $username;
        $new->password = $password;
        $new->oldpassword = $oldpassword;
        $new->name = $name;
        $new->phoneno = $phoneno;
        $new->email = $email;
        $new->partnerid = $partnerID;
        $new->type = $type;
        $new->passwordmodifiedon = new \DateTime('now');
        $new->createdon = new \DateTime('now');
        $new->createdby = 1;
        $new->modifiedon = new \DateTime('now');
        $new->modifiedby = 1;
        $new->status = 0;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals($expectedId, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['username', 'password', 'oldpassword', 'name', 'phoneno', 'email', 'partnerid', 'type'] as $field) {
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
