<?php
use PHPUnit\Framework\TestCase;
/**
 * This LazyTestCase file is a template for writing Test cases that only have 1 table to test (Not including Views)
 * @covers common objects
 */
class LazyTestCase extends TestCase {
    static public $config;
    static public $db;
    static private $tables;
    static private $testTableName;
    static private $objectClass;
    static private $tablePrefix;

    public function __construct()
    {
        $this->backupGlobals = false;
    }
    /**
     * This method is called before the first test of this test class is run.
     * @param string $tableNameString
     * @param string $objectClass
     * @param string $tablePrefix
     */
    public static function setUpBeforeClass($tableNameString='', string $objectClass='', string $tablePrefix='', array $tableNameArray=null)
    {
        self::$objectClass = $objectClass;
        self::$tablePrefix = $tablePrefix;
        $tableNameArray[] = $tableNameString;
        foreach ($tableNameArray as $tableNameString) {
            self::$testTableName = TEST_TABLE_PREFIX. $tableNameString;
            self::$tables[self::$testTableName] = 'CREATE TABLE ' . self::$testTableName . " like `$tableNameString`;";
        }
        self::$config = new Snap\config(TEST_CONFIG_FILE);
        self::$config->load();
        self::$db = new Snap\db(
            self::$config->{'snap.db.type'},
            self::$config->{'snap.db.host'},
            self::$config->{'snap.db.username'},
            self::$config->{'snap.db.password'},
            self::$config->{'snap.db.name'},
            false);

        $db = self::$db;
        foreach (self::$tables as $tableName => $createSQL) {
            $db->exec($createSQL);
        }
    }

    /**
     * This method is called after the last test of this test class is run.
    */
    public static function tearDownAfterClass():void
    {
        self::$config = null;
        foreach (self::$tables as $tableName => $sqlString) {
            if (strpos($tableName, 'vw') === 0) {
                self::$db->exec("DROP VIEW $tableName");
            } else {
                self::$db->exec("DROP TABLE $tableName");
            }
        }
        self::$db->close();
    }

    /**
     * @return object
     */
    public function setupStores(): ?object
    {
        return new Snap\store\dbdatastore(
            self::$db,
            self::$testTableName,
            null,
            self::$tablePrefix,
            self::$objectClass, [], [], true);
    }
}
?>