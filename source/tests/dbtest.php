<?php
use PHPUnit\Framework\TestCase;
/**
 * @covers Email
 * @backupGlobals disabled
 */
final class dbtest extends TestCase {
	static public $config;

	public static function setUpBeforeClass() {
		self::$config = new Snap\config(TEST_DIR . DIRECTORY_SEPARATOR . 'testconfig.ini');
		self::$config->load();
	}

    public static function tearDownAfterClass() {
        self::$config = null;
    }

    public function testConnectionToDb() {
    	$dbConn = new Snap\db( self::$config->{'snap.db.type'}, self::$config->{'snap.db.host'}, self::$config->{'snap.db.username'}, self::$config->{'snap.db.password'}, self::$config->{'snap.db.name'}, false);
    	$this->assertNotNull($dbConn);
    	$this->assertInstanceOf('PDO', $dbConn);

    	$this->assertTrue(method_exists($dbConn, 'close'));
    	$dbConn->close();
    }
}
?>