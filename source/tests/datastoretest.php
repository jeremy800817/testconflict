<?php
use PHPUnit\Framework\TestCase;
use Snap\object\snapObject;

/**
 * @covers Email
 * @backupGlobals disabled
 */
final class dbstoretest extends TestCase {
	static public $config;
    static public $db;
    static private $tables = [
        'test_a' => 'CREATE TABLE `test_a` ( `aaa_id` int(11) NOT NULL AUTO_INCREMENT,  `aaa_name` varchar(255) NOT NULL, `aaa_linkb` int(11) NOT NULL, `aaa_linkc` int(11) NOT NULL, `aaa_createdon` datetime NOT NULL,
                         `aaa_modifiedon` datetime NOT NULL,PRIMARY KEY (`aaa_id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1',
        'test_b' => "   CREATE TABLE `test_b` (
                         `bbb_id` int(11) NOT NULL AUTO_INCREMENT, \n`bbb_int` int(11) NOT NULL,\n `bbb_string` varchar(255) NOT NULL,
                         `bbb_enum` enum('option1','option2','option3','option4') NOT NULL, \n`bbb_datetime` datetime NOT NULL,\n`bbb_date` date NOT NULL,
                         `bbb_time` time NOT NULL,\n`bbb_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n`bbb_createdon` datetime NOT NULL,
                         `bbb_modifiedon` datetime NOT NULL,\nPRIMARY KEY (`bbb_id`)\n) ENGINE=InnoDB DEFAULT CHARSET=latin1",
        'vw_alinkb' => 'create view vw_alinkb AS select test_b.*, test_a.*, aaa_linkb as bbb_linkb, aaa_linkc as bbb_linkc, aaa_name as bbb_name from test_a left join test_b on (aaa_linkb = bbb_id);'
    ];

	public static function setUpBeforeClass() {
		self::$config = new Snap\config(TEST_DIR . DIRECTORY_SEPARATOR . 'testconfig.ini');
		self::$config->load();
        self::$db = new Snap\db( self::$config->{'snap.db.type'}, self::$config->{'snap.db.host'}, self::$config->{'snap.db.username'}, self::$config->{'snap.db.password'}, self::$config->{'snap.db.name'}, false);

        $db = self::$db;
        foreach(self::$tables as $tableName =>$createSQL) {
            $db->exec($createSQL);
        }
	}

    public static function tearDownAfterClass() {
        self::$config = null;
        $db = self::$db;
        foreach(self::$tables as $tableName =>$createSQL) {
            if('vw' == substr($tableName,0,2)) $db->exec("drop view $tableName");
            else $db->exec("drop table $tableName");
        }
        self::$db->close();
    }

    public function testDatastoreAAndMethods() {
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $this->assertInstanceOf('Snap\store\dbdatastore', $itemAStore);
        $this->assertTrue(method_exists($itemAStore, 'create'));
        $this->assertTrue(method_exists($itemAStore, 'getById'));
        $this->assertTrue(method_exists($itemAStore, 'save'));
        $this->assertTrue(method_exists($itemAStore, 'delete'));
    }

    /**
     * depends testDatastoreAAndMethods
     */
    public function testDatastoreACreateMethod() {
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $itemA = $itemAStore->create();
        $this->assertInstanceOf('ItemA', $itemA);
        $this->assertNull($itemA->id);
        $this->assertNull($itemA->name);
        $this->assertNull($itemA->linkb);
        $this->assertNull($itemA->linkc);
        $itemA->id = 3;
        $itemA->name = 'some names from people of somewhere';
        $itemA->linkb = '1101';
        $itemA->linkc = '2202';
        $this->assertEquals(1101, $itemA->linkb);
        $this->assertEquals(2202, $itemA->linkc);
        $this->assertEquals('some names from people of somewhere', $itemA->name);
        $this->assertEquals(0, $itemA->id);  //can not assign ID to the object.

        $itemA2 = $itemAStore->create(['id' => 5, 'name' => 'some name', 'linka' => 1111, 'linkb' => 2222, 'linkc' => 3333]);
        $this->assertNull($itemA2->linka);
        $this->assertEquals(2222, $itemA2->linkb);
        $this->assertEquals(3333, $itemA2->linkc);
        $this->assertEquals('some name', $itemA2->name);
        $this->assertEquals(5, $itemA2->id);
    }

    /**
     * depends testDatastoreACreateMethod
     */
    public function testDatastoreASave() {
        $counter = 1;
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        for($counter = 1; $counter < 21; $counter++) {
            $itemA = $itemAStore->create(['name' => 'some name ' . $counter, 'linka' => 1111 + $counter, 'linkb' => $counter, 'linkc' => 3333 + $counter]);
            $itemSavedA = $itemAStore->save($itemA);
            $this->assertTrue(0 < $itemSavedA->id);
            $this->assertEquals($counter, $itemSavedA->id);
            $this->assertEquals($itemA->name, $itemSavedA->name);
            $this->assertEquals($itemA->linkb, $itemSavedA->linkb);
            $this->assertEquals($itemA->linkc, $itemSavedA->linkc);
            $this->assertNotEquals($itemA->modifiedon, $itemSavedA->modifiedon);
            $this->assertNotEquals($itemA->createdon, $itemSavedA->createdon);
            $this->assertNotNull($itemSavedA->createdon);
            $this->assertNotNull($itemSavedA->modifiedon);
        }
        //Ensure the database table really have 20 items that are created.
        $this->assertEquals(20, count($itemAStore->searchTable()->select(['id'])->execute()));

        //Testing retrieving data from the DB and comparing with original data that we have inserted
        $itemByID = $itemAStore->getById(15);
        $this->assertEquals(15, $itemByID->id);
        $this->assertEquals('some name 15', $itemByID->name);
        $this->assertEquals(15, $itemByID->linkb);
        $this->assertEquals(3333+15, $itemByID->linkc);
        $this->assertEquals($itemByID->createdon, $itemByID->modifiedon);

        //Testing updating of data
        $itemByID->linkc += 30;
        $itemByID->linkb = 4000;
        sleep(2);
        $itemByIDUpdated = $itemAStore->save($itemByID);
        $this->assertEquals($itemByID->id, $itemByIDUpdated->id);
        $this->assertEquals($itemByID->name, $itemByIDUpdated->name);
        $this->assertEquals(4000, $itemByIDUpdated->linkb);
        $this->assertEquals(3333+15+30, $itemByIDUpdated->linkc);
        //ensure modifiedon is always being updated by system.
        $this->assertGreaterThan($itemByIDUpdated->createdon, $itemByIDUpdated->modifiedon);

        //Testing delete method
        $itemAStore->delete($itemByID);
        $nonExist = $itemAStore->getByID($itemByID->id);
        $this->assertNull($nonExist);
    }

     /**
     * depends testDatastoreASave
     */
    public function testDatastoreAGetById() {
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $item1 = $itemAStore->getById( 10);
        $this->assertEquals(10, $item1->id);
        $this->assertEquals('some name 10', $item1->name);
        $this->assertEquals(10, $item1->linkb);
        $this->assertEquals(3333+10, $item1->linkc);
        $this->assertEquals($item1->createdon, $item1->modifiedon);

        $item2 = $itemAStore->getById( 10, ['id', 'linkb']);
        $this->assertEquals(10, $item2->id);
        $this->assertNull($item2->name);
        $this->assertEquals(10, $item2->linkb);
        $this->assertNull($item2->linkc);
        $this->assertNull($item2->createdon);
        $this->assertNull($item2->modifiedon);

        $item3 = $itemAStore->getById( 10, [], true);
        $this->assertEquals(10, $item3->id);
        $this->assertEquals('some name 10', $item3->name);
        $this->assertEquals(10, $item3->linkb);
        $this->assertEquals(3333+10, $item3->linkc);
        $this->assertEquals($item3->createdon, $item3->modifiedon);

        $item4 = $itemAStore->getById( -100000, ['id', 'name']);
        $this->assertNull($item4);
        // $itemAStore->getById( $id, $fields = array(), $forUpdate = false, $tableOrViewIndex = 0)
        // $itemAStore->getById( $id, $fields = array(), $forUpdate = false, $tableOrViewIndex = 0)
    }

     /**
     * depends testDatastoreASave
     */
    public function testDatastoreAGetByField() {
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $item1 = $itemAStore->getByField('linkb', 11);
        $this->assertEquals(11, $item1->id);
        $this->assertEquals('some name 11', $item1->name);
        $this->assertEquals(11, $item1->linkb);
        $this->assertEquals(3333+11, $item1->linkc);
        $this->assertEquals($item1->createdon, $item1->modifiedon);

        $item2 = $itemAStore->getByField('linkc', 3333+11 );
        $this->assertEquals(11, $item2->id);
        $this->assertEquals('some name 11', $item2->name);
        $this->assertEquals(11, $item2->linkb);
        $this->assertEquals(3333+11, $item2->linkc);
        $this->assertEquals($item2->createdon, $item2->modifiedon);

        $item3 = $itemAStore->getByField('linkb', 11, ['id', 'linkb']);
        $this->assertEquals(11, $item3->id);
        $this->assertNull($item3->name);
        $this->assertEquals(11, $item3->linkb);
        $this->assertNull($item3->linkc);
        $this->assertNull($item3->createdon);
        $this->assertNull($item3->modifiedon);

        $item4 = $itemAStore->getByField('linkb', 17, [], true);
        $this->assertEquals(17, $item4->id);
        $this->assertEquals('some name 17', $item4->name);
        $this->assertEquals(17, $item4->linkb);
        $this->assertEquals(3333+17, $item4->linkc);
        $this->assertEquals($item4->createdon, $item4->modifiedon);

        // $itemAStore->getByField($column, $value, $fields = array(), $forUpdate = false, $tableOrViewIndex = 0)
    }

     /**
     * depends testDatastoreASave
     */
    public function testDatastoreASearchTable() {
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);

        $totalRecords = count($itemAStore->searchTable()->select()->execute());
        $this->assertGreaterThan(18, $totalRecords);

        $items1 = $itemAStore->searchTable( false)->select(['id', 'name' => 'name', 'linkb' => 'foreignKey', 'linkc' => 'linkc'])->execute();
        $test3Count = count($items1);
        $this->assertTrue(is_array($items1[4]));
        $this->assertArrayHasKey('aaa_id', $items1[4]);
        $this->assertArrayHasKey('name', $items1[4]);
        $this->assertArrayHasKey('foreignKey', $items1[4]);
        $this->assertArrayHasKey('linkc', $items1[4]);
        $this->assertEquals($totalRecords, $test3Count);

        //With simple where conditions
        $this->assertCount(10, $itemAStore->searchTable(true)->select(['id'])
                                          ->where('id','<=',10)
                                          ->execute());
        $this->assertCount(5, $itemAStore->searchTable(false)->select(['id'])
                                          ->where('id','<=',10)
                                          ->andWhere('id','>',5)
                                          ->execute());
        $this->assertCount(2, $itemAStore->searchTable(true)->select(['id'])
                                          ->where('id',11)
                                          ->orWhere('id',5)
                                          ->execute());
        $this->assertCount(3, $itemAStore->searchTable(false)->select(['id'])
                                          ->where('linkb',11)
                                          ->orWhere('linkc',3333+8)
                                          ->orWhere('linkc',3333+17)->execute());
        $this->assertCount(2, $itemAStore->searchTable(false)->select(['id'])
                                          ->where('linkb',11)
                                          ->orWhere('linkc',3333+8)
                                          ->orWhere('id',11)->execute());

        //With nested where condition (more complicated)
        //where (aaa_id = 10 and aaa_linkb = 10) or (aaa_id = 9 and aaa_linkc = 3333+9) or (aaa_id = 18 and aaa_linkb = 18)
        $sqlHandle = $itemAStore->searchTable(false)->select(['id']);
        $sqlHandle->where(function($q) {
                    $q->where('id', 10)->andWhere('linkb',10);
                  });
        $sqlHandle->orWhere(function($q){
                    $q->where('id',9)->andWhere('linkc',3333+9);
                  });
        $sqlHandle->orWhere(function($q){
                        $q->where('id',18)->andWhere('linkb',18);
                      });
        $this->assertCount(3, $sqlHandle->execute());

        $this->assertCount(3, $itemAStore->searchTable(false)->select(['id'])
                                          ->where(function($q) {
                                            $q->where('id', 10)->andWhere('linkb',10);
                                          })->orWhere(function($q){
                                            $q->where('id',9)->andWhere('linkc',3333+9);
                                          })->orWhere(function($q){
                                            $q->where('id',18)->andWhere('linkb',18);
                                          })->execute());

        //where (aaa_id = 10 and aaa_linkb = 10) or (aaa_id = 9 and aaa_linkc = 3333+9) or (aaa_id = 18 and aaa_linkb = -22)
        //Also test for order by keyword
        //
        $result1 = $itemAStore->searchTable()->select(['id'])
                                          ->where(function($q) {
                                            $q->where('id', 10)->andWhere('linkb',10);
                                          })->orWhere(function($q){
                                            $q->where('id',9)->andWhere('linkc',3333+9);
                                          })->orWhere(function($q){
                                            $q->where('id',18)->andWhere('linkb',-22);
                                          })->orderby('linkb', 'desc')->execute();
        $this->assertCount(2, $result1);
        $result2 = $itemAStore->searchTable()->select(['id'])
                                          ->where(function($q) {
                                            $q->where('id', 10)->andWhere('linkb',10);
                                          })->orWhere(function($q){
                                            $q->where('id',9)->andWhere('linkc',3333+9);
                                          })->orWhere(function($q){
                                            $q->where('id',18)->andWhere('linkb',-22);
                                          })->orderby('linkb', 'asc')->execute();
        $this->assertCount(2, $result2);
        $this->assertEquals($result1[0]->id, $result2[1]->id);
        $this->assertEquals($result1[1]->id, $result2[0]->id);

        //where (aaa_id <= 10 and aaa_id > 0) and (linkc <= 3333+18 and linkc >= 3333+5)
        $this->assertCount(6, $itemAStore->searchTable(false)->select(['id'])
                                          ->where(function($q) {
                                            $q->where('id', '<=', 10)->andWhere('id','>', 0);
                                          })->andWhere(function($q){
                                            $q->where('linkc','<=', 3333+18)->andWhere('linkc','>=',3333+5);
                                          })->execute());


        //where DATE(aaa_modifiedon) = 'xxxx-xx-xx'
        $date = date('Y-m-d');
        $handle = $itemAStore->searchTable(false);
        $this->assertCount($totalRecords, $itemAStore->searchTable(false)->select(['id'])
                                          ->where($handle->raw("DATE(`aaa_modifiedon`)"), '=', $date)
                                          ->execute());

        //Functions of on selected column

        //select count(aaa_id) as cnt from test_a;
        $handle = $itemAStore->searchTable(false);
        $return = $handle->select([$handle->raw('count(`aaa_id`) as cnt')])
                                          ->execute();
        $this->assertEquals($totalRecords, $return[0]['cnt']);

        //select max(aaa_id) as cnt from test_a;
        $handle = $itemAStore->searchTable(false);
        $return = $handle->select([$handle->raw('max(`aaa_id`) as max')])
                                          ->execute();
        $this->assertEquals(20, $return[0]['max']);

        //select min(`aaa_id`) as min, max(aaa_id) as max, max(`aaa_linkc`) as maxlinkc from test_a;
        $handle = $itemAStore->searchTable(false);
        $return = $handle->select([$handle->raw('min(`aaa_id`) as min'), $handle->raw('max(`aaa_id`) as max'), $handle->raw('max(`aaa_linkc`) as maxlinkc')])
                                          ->execute();
        $this->assertEquals(20, $return[0]['max']);
        $this->assertEquals(1, $return[0]['min']);
        $this->assertEquals(3333+20, $return[0]['maxlinkc']);
    }


    /**
     * depends testDatastoreACreateMethod
     */
    public function testDatastoreAThrowException() {
        //Make sure that the isValid() method is called for any Save() method.
        $this->expectException(Snap\InputException::class);
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $throwExceptionItem = $itemAStore->create(['name' => 'some name ', 'linka' => 'sdfsfdsf', 'linkb' => 'fsfsdfsdf', 'linkc' => 3333]);
        $itemAStore->save($throwExceptionItem);
    }

    /**
     * depends testDatastoreACreateMethod
     */
    public function testDatastoreBViewJoinTable() {
        $itemBStore = new Snap\store\dbdatastore(self::$db, 'test_b', null, 'bbb', 'ItemB', array('vw_alinkb'), array(), true);
        $options = array('Option1', 'Option2', 'Option3');
        $now = new \DateTime();
        for($i = 0; $i < 100; $i++) {
            $props = array('int' => rand(90, 9999), 'string' => $this->RandomString(), 'enum' => $options[rand(0,2)], 'date' => date('Y-m-d'),
                            'datetime' => new \DateTime(), 'time' => $now->format('H:i:s'), 'timestamp' => '2017-03-01 04:33:45');
            $item = $itemBStore->create($props);
            $itemBStore->save($item);
        }
        $this->assertEquals(100, count($itemBStore->searchTable()->select(['id'])->execute()));

        $items = $itemBStore->searchView(false)->select()->execute();
        $this->assertEquals(19, count($items));
        $this->assertArrayHasKey('bbb_id', $items[9]);
        $this->assertArrayHasKey('bbb_int', $items[9]);
        $this->assertArrayHasKey('bbb_string', $items[9]);
        $this->assertArrayHasKey('bbb_enum', $items[9]);
        $this->assertArrayHasKey('aaa_id', $items[9]);
        $this->assertArrayHasKey('aaa_linkb', $items[9]);
        $this->assertArrayHasKey('aaa_linkc', $items[9]);
        $this->assertArrayHasKey('aaa_name', $items[9]);

        //Test can update to the object member views correctly
        $items = $itemBStore->searchView()->select()->where('id', 18)->execute();
        $this->assertEquals(1, count($items));
        $this->assertInstanceOf('ItemB', $items[0]);
        $this->assertEquals(18, $items[0]->id);
        $this->assertEquals(18, $items[0]->linkb);
        $this->assertEquals(3333+18, $items[0]->linkc);
        $this->assertNotNull($items[0]->name);


        //Test more complex join statements instead of view

        //select * from test_b left join test_a on aaa_linkb = bbb_id where aaa_linkc = 3333+7
        $items = $itemBStore->searchTable(false)->select()
                            ->join('test_a', 'aaa_linkb', '=', 'bbb_id')
                            ->where('aaa_linkc', 3333+7)
                            ->execute();
        $this->assertEquals(1, count($items));
        $this->assertEquals(7, $items[0]['aaa_id']);
        $this->assertEquals(7, $items[0]['aaa_linkb']);
        $this->assertEquals(7, $items[0]['bbb_id']);
        $this->assertEquals(3333+7, $items[0]['aaa_linkc']);
        $this->assertArrayHasKey('bbb_int', $items[0]);
        $this->assertArrayHasKey('bbb_string', $items[0]);
        $this->assertArrayHasKey('bbb_enum', $items[0]);
        $this->assertArrayHasKey('bbb_modifiedon', $items[0]);
        $this->assertArrayHasKey('bbb_createdon', $items[0]);
        $this->assertArrayHasKey('aaa_name', $items[0]);
        $this->assertArrayHasKey('aaa_linkc', $items[0]);
        $this->assertArrayHasKey('aaa_linkb', $items[0]);
        $this->assertArrayHasKey('aaa_createdon', $items[0]);
        $this->assertArrayHasKey('aaa_modifiedon', $items[0]);

        //select * from test_b left join test_a on aaa_linkb = bbb_id where bbb_id = 7
        $items = $itemBStore->searchTable(false)->select()
                            ->join('test_a', 'aaa_linkb', '=', 'bbb_id')
                            ->where('id', 7)
                            ->execute();
        $this->assertEquals(1, count($items));
        $this->assertEquals(7, $items[0]['aaa_id']);
        $this->assertEquals(7, $items[0]['aaa_linkb']);
        $this->assertEquals(7, $items[0]['bbb_id']);
        $this->assertEquals(3333+7, $items[0]['aaa_linkc']);
        $this->assertArrayHasKey('bbb_int', $items[0]);
        $this->assertArrayHasKey('bbb_string', $items[0]);
        $this->assertArrayHasKey('bbb_enum', $items[0]);
        $this->assertArrayHasKey('bbb_modifiedon', $items[0]);
        $this->assertArrayHasKey('bbb_createdon', $items[0]);
        $this->assertArrayHasKey('aaa_name', $items[0]);
        $this->assertArrayHasKey('aaa_linkc', $items[0]);
        $this->assertArrayHasKey('aaa_linkb', $items[0]);
        $this->assertArrayHasKey('aaa_createdon', $items[0]);
        $this->assertArrayHasKey('aaa_modifiedon', $items[0]);
    }

    function RandomString()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < 10; $i++) {
            $randstring .= $characters[rand(0, strlen($characters)-1)];
        }
        return $randstring;
    }

    /**
     * depends testDatastoreBViewJoinTable
     */
    function testObjectGetStore() {
        $itemBStore = new Snap\store\dbdatastore(self::$db, 'test_b', null, 'bbb', 'ItemB', array('vw_alinkb'), array(), true);
        $items = $itemBStore->searchView()->select()
                            ->where('linkc', 3333+7)
                            ->execute();
        $this->assertEquals(1, count($items));
        $theItem = $items[0];
        $this->assertEquals(7, $theItem->id);
        $this->assertEquals(7, $theItem->linkb);

        $this->assertEquals($itemBStore, $theItem->getStorePublic());

    }

    function testRelatedStore() {
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $itemBStore = new Snap\store\dbdatastore(self::$db, 'test_b', null, 'bbb', 'ItemB', array('vw_alinkb'), array('storeA' => $itemAStore), true);
        $items = $itemBStore->searchView()->select()
                            ->where('linkc', 3333+7)
                            ->execute();
        $this->assertEquals(1, count($items));
        $itemB = $items[0];
        $this->assertEquals(7, $itemB->id);
        $this->assertEquals(7, $itemB->linkb);

        $this->assertEquals($itemBStore, $itemB->getStorePublic());
        $theAStore = $itemB->getStorePublic()->getRelatedStore('storeA');
        $this->assertEquals( $itemAStore, $theAStore);
        $relatedItemA = $itemB->getStorePublic()->getRelatedStore('storeA')->getByField('linkb', $itemB->id);
        $this->assertNotNull($relatedItemA);
        $this->assertEquals($itemB->linkbb, $relatedItemA->linkbb);
    }

    function testObjectCaching() {
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $newItem = $itemAStore->create(['id' => 10, 'linkb' => 100, 'name' => 'testing only', 'modifiedon' => '2017-03-08 16:49:45']);
        $dateFormat = $newItem->modifiedon->format('Y-m-d H:i:sO');
        $this->assertEquals("id|+|10~-~name|+|testing only~-~linkb|+|100~-~modifiedon|+|DT=$dateFormat=",
                            $newItem->toCache());
        $cacheItem = $itemAStore->create()->fromCache($newItem->toCache());
        $this->assertEquals($newItem->id, $cacheItem->id);
        $this->assertEquals($newItem->linkb, $cacheItem->linkb);
        $this->assertEquals($newItem->linkc, $cacheItem->linkc);
        $this->assertEquals($newItem->name, $cacheItem->name);
        $this->assertEquals($newItem->modifiedon, $cacheItem->modifiedon);
    } 

    function testObjectLockFromEdit() {
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $itemBStore = new Snap\store\dbdatastore(self::$db, 'test_b', null, 'bbb', 'ItemB', array('vw_alinkb'), array('storeA' => $itemAStore), true);
        $items = $itemBStore->searchView()->select()
                            ->where('linkc', 3333+7)
                            ->execute();
        $this->assertEquals(1, count($items));
        $itemB = $items[0];
        $this->assertFalse($itemB->isLockedFromEdit());
        $itemB->lockFromEdit();
        $this->assertTrue($itemB->isLockedFromEdit());
        $oldLink = $itemB->linkc;
        $oldInt = $itemB->int;
        $itemB->linkc = 0;  //view  member
        $itemB->int = -8;  //own field
        $itemBStore->save($itemB);
        $this->assertNotEquals(0, $itemB->linkc);

        $savedItemB = $itemBStore->getById($itemB->id, array(), false, 1);
        $this->assertNotNull($savedItemB);
        $this->assertNotEquals(0, $itemB->linkc);
        $this->assertNotEquals(0, $savedItemB->linkc);
        $this->assertEquals($oldLink, $itemB->linkc);
        $this->assertEquals($oldLink, $savedItemB->linkc);
        $this->assertEquals($savedItemB->linkc, $itemB->linkc);
        $this->assertNotEquals(-8, $itemB->int);
        $this->assertNotEquals(-8, $savedItemB->int);
        $this->assertEquals($oldInt, $itemB->int);
        $this->assertEquals($oldInt, $savedItemB->int);
        $this->assertEquals($savedItemB->int, $itemB->int);
    }

    function testObjectLockFromEditPopulate() {
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $itemBStore = new Snap\store\dbdatastore(self::$db, 'test_b', null, 'bbb', 'ItemB', array('vw_alinkb'), array('storeA' => $itemAStore), true);
        $items = $itemBStore->searchView()->select()
                            ->where('linkc', 3333+7)
                            ->execute();
        $this->assertEquals(1, count($items));
        $itemB = $items[0];
        $itemB->lockFromEdit();
        $itemB->linkc = 0;
        $itemB->int = -8;
        $itemBStore->save($itemB);
        $savedItemB = $itemBStore->getById($itemB->id, array(), false, 1);
        $this->assertNotNull($savedItemB);
        $this->assertNotEquals(0, $itemB->linkc);
        $this->assertNotEquals(0, $savedItemB->linkc);
        $this->assertEquals($savedItemB->linkc, $itemB->linkc);
        $this->assertNotEquals(-8, $itemB->int);
        $this->assertNotEquals(-8, $savedItemB->int);
        $this->assertEquals($savedItemB->int, $itemB->int);
    }

    function testObjectLockFromEditPopulateException() {
        $this->expectException(Snap\InputException::class);

        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $itemBStore = new Snap\store\dbdatastore(self::$db, 'test_b', null, 'bbb', 'ItemB', array('vw_alinkb'), array('storeA' => $itemAStore), true);
        $items = $itemBStore->searchView()->select()
                            ->where('linkc', 3333+7)
                            ->execute();
        $this->assertEquals(1, count($items));
        $itemB = $items[0];
        $itemB->lockFromEdit(true);
        $itemB->linkc = 0;
        $itemB->int = -8;
        $itemBStore->save($itemB);
        $savedItemB = $itemBStore->getById($itemB->id, array(), false, 1);
        $this->assertNotNull($savedItemB);
        $this->assertNotEquals(0, $itemB->linkc);
        $this->assertNotEquals(0, $savedItemB->linkc);
        $this->assertEquals($savedItemB->linkc, $itemB->linkc);
        $this->assertNotEquals(-8, $itemB->int);
        $this->assertNotEquals(-8, $savedItemB->int);
        $this->assertEquals($savedItemB->int, $itemB->int);
    }

    function testObjectLockFromEditException() {
        $this->expectException(Snap\InputException::class);

        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $itemBStore = new Snap\store\dbdatastore(self::$db, 'test_b', null, 'bbb', 'ItemB', array('vw_alinkb'), array('storeA' => $itemAStore), true);
        $items = $itemBStore->searchView()->select()
                            ->where('linkc', 3333+7)
                            ->execute();
        $this->assertEquals(1, count($items));
        $itemB = $items[0];
        $itemB->lockFromEdit(true);
        $itemB->linkc = 0;
        $itemBStore->save($itemB);
        $this->assertNotEquals(0, $itemB->linkc);

        $savedItemB = $itemBStore->getById($itemB->id);
        $this->assertNotEquals(0, $itemB->linkc);
        $this->assertEquals($savedItemB->linkc, $itemB->linkc);
    }

    function testObjectUpdateOutdatedVersion() {
        //Saving the record that is older / outdated version will cause an exception to be thrown.
        $this->expectException(Snap\InputException::class);

        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $itemA = $itemAStore->getById(3);  //can not assign ID to the object.
        self::$db->query("update test_a set aaa_modifiedon = '2038-01-01 16:00:00'");
        $itemA->linkb = $itemA->linkb - 1;
        $itemAStore->save($itemA);
    }

    function testForceIdWhenSavingItem() {
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $nonExists = $itemAStore->getById(8888855);  //can not assign ID to the object.
        $this->assertNull($nonExists);
        $itemA = $itemAStore->create([
          'id' => 8888855,
          'name' => 'creating an item with a fixed ID',
          'linkb' => 2323,
          'linkc' => 3262888
        ]);
        $itemAStore->save($itemA);

        $fromDb = $itemAStore->getById(8888855);
        $this->assertNotNull($fromDb);
        $this->assertEquals($fromDb->id, $itemA->id);
        $this->assertEquals($fromDb->name, $itemA->name);
        $this->assertEquals($fromDb->linkb, $itemA->linkb);
        $this->assertEquals($fromDb->linkc, $itemA->linkc);
    }

    function testAllHydrahonRunnerMethods() {
        $itemAStore = new Snap\store\dbdatastore(self::$db, 'test_a', null, 'aaa', 'ItemA', array(), array(), true);
        $itemBStore = new Snap\store\dbdatastore(self::$db, 'test_b', null, 'bbb', 'ItemB', array('vw_alinkb'), array('storeA' => $itemAStore), true);

        //Testing count() method
        $this->assertEquals(1, $itemBStore->searchView(false)->select()->where('linkc', 3333+7)->count(), "Testing using hydrahon count() with array return");
        $this->assertEquals(1, $itemBStore->searchView()->select()->where('linkc', 3333+7)->count(), "Testing using hydrahon count() with entity object return");
        $this->assertEquals(1, $itemBStore->searchView(false)->select(['id'])->where('linkc', 3333+7)->count(), "Testing using hydrahon count() with array return");
        $this->assertEquals(1, $itemBStore->searchView()->select('id')->where('linkc', 3333+7)->count(), "Testing using hydrahon count() with entity object return");
        $this->assertEquals(1, $itemBStore->searchView(false)->select('id')->where('linkc', 3333+7)->count(), "Testing using hydrahon count() with array return");
        $this->assertEquals(1, $itemBStore->searchView()->select(['id'])->where('linkc', 3333+7)->count(), "Testing using hydrahon count() with entity object return");
        $object = $itemBStore->searchView()->select()->where('linkc', 3333+7)->get();
        $this->assertTrue(is_array($object));
        $this->assertEquals(1, count($object));
        $object = $object[0];

        //Testing find() method
        $objectCopy = $itemBStore->searchView()->select()->find($object->id);
        $this->assertNotNull($objectCopy);
        $this->assertEquals($object->id, $objectCopy->id);
        $this->assertEquals($object->int, $objectCopy->int);

        //Testing exists method
        $this->assertTrue($itemBStore->searchView()->select()->where('id', $object->id)->andWhere('int', $object->int)->exists());
        $this->assertFalse($itemBStore->searchView()->select()->where('id', $object->id)->andWhere('int', $object->int+1)->exists());

        //testing all sum, avg, min and max methods
        $items = $itemBStore->searchView()->select()->get();
        $counter = $itemBStore->searchView()->select()->count();
        $this->assertEquals(count($items), $counter);
        $calculation = ['totalInt' => 0, 'totalB' => 0, 'minInt' => 101000000, 'maxInt' => 0, 'countInt' => 0, 'minB' => 101000000, 'maxB' => 0, 'countB' => 0];
        foreach( $items as $aB) {
          $calculation['totalInt'] += $aB->int;
          $calculation['minInt'] = ($calculation['minInt'] > $aB->int) ? $aB->int : $calculation['minInt'];
          $calculation['maxInt'] = ($calculation['maxInt'] < $aB->int) ? $aB->int : $calculation['maxInt'];
          $calculation['countInt']++;
          $calculation['totalB'] += $aB->linkb;
          $calculation['minB'] = ($calculation['minB'] > $aB->linkb) ? $aB->linkb : $calculation['minB'];
          $calculation['maxB'] = ($calculation['maxB'] < $aB->linkb) ? $aB->linkb : $calculation['maxB'];
          $calculation['countB']++;
        }
        $calculation['avgInt'] = $calculation['totalInt'] / $calculation['countInt'];
        $calculation['avgB'] = $calculation['totalB'] / $calculation['countB'];

        $this->assertEquals($calculation['totalB'], $itemBStore->searchView()->select()->sum('linkb'));
        $this->assertEquals($calculation['totalInt'], $itemBStore->searchView()->select()->sum('int'));
        $this->assertEquals($calculation['avgB'], $itemBStore->searchView()->select()->avg('linkb'));
        $this->assertEquals($calculation['minB'], $itemBStore->searchView()->select()->min('linkb'));
        $this->assertEquals($calculation['maxB'], $itemBStore->searchView()->select()->max('linkb'));
        $this->assertEquals($calculation['maxInt'], $itemBStore->searchView()->select()->max('int'));

        //With a where query....
        $items = $itemBStore->searchView()->select()->where('id', '>', $object->id)->execute();
        $counter = $itemBStore->searchView()->select()->where('id', '>', $object->id)->count();
        $this->assertEquals(count($items), $counter);
        $calculation = ['totalInt' => 0, 'totalB' => 0, 'minInt' => 101000000, 'maxInt' => 0, 'countInt' => 0, 'minB' => 101000000, 'maxB' => 0, 'countB' => 0];
        foreach( $items as $aB) {
          $calculation['totalInt'] += $aB->int;
          $calculation['minInt'] = ($calculation['minInt'] > $aB->int) ? $aB->int : $calculation['minInt'];
          $calculation['maxInt'] = ($calculation['maxInt'] < $aB->int) ? $aB->int : $calculation['maxInt'];
          $calculation['countInt']++;
          $calculation['totalB'] += $aB->linkb;
          $calculation['minB'] = ($calculation['minB'] > $aB->linkb) ? $aB->linkb : $calculation['minB'];
          $calculation['maxB'] = ($calculation['maxB'] < $aB->linkb) ? $aB->linkb : $calculation['maxB'];
          $calculation['countB']++;
        }
        $calculation['avgInt'] = $calculation['totalInt'] / $calculation['countInt'];
        $calculation['avgB'] = $calculation['totalB'] / $calculation['countB'];

        $this->assertEquals($calculation['totalB'], $itemBStore->searchView()->select()->where('id', '>', $object->id)->sum('linkb'));
        $this->assertEquals($calculation['totalInt'], $itemBStore->searchView()->select()->where('id', '>', $object->id)->sum('int'));
        $this->assertEquals(intval($calculation['avgB']), intval($itemBStore->searchView()->select()->where('id', '>', $object->id)->avg('linkb')));
        $this->assertEquals(intval($calculation['avgInt']), intval($itemBStore->searchView()->select()->where('id', '>', $object->id)->avg('int')));
        $this->assertEquals(intval($calculation['minInt']), intval($itemBStore->searchView()->select()->where('id', '>', $object->id)->min('int')));
        $this->assertEquals($calculation['minB'], $itemBStore->searchView()->select()->where('id', '>', $object->id)->min('linkb'));
        $this->assertEquals($calculation['maxB'], $itemBStore->searchView()->select()->where('id', '>', $object->id)->max('linkb'));
        $this->assertEquals($calculation['maxInt'], $itemBStore->searchView()->select()->where('id', '>', $object->id)->max('int'));
    }
}

if(!class_exists('ItemA')) {
    class ItemA extends snapobject {
        public function getStorePublic() {
          return $this->getStore(); 
        }
        protected function reset() {
            $this->members = array( 'id' => null, 'name' => null, 'linkb' => null, 'linkc' => null, 'createdon' => null, 'modifiedon' => null);
        }

        public function isValid() {
            //if(is_numeric($this->members['name'])) throw new Snap\InputException('Testing throwing of exception in valid method', Snap\InputException::GENERAL_ERROR, 'field');
            if(!is_numeric($this->members['linkb'])) throw new Snap\InputException('Testing throwing of exception in valid method', Snap\InputException::GENERAL_ERROR, 'field');
            if(!is_numeric($this->members['linkc'])) throw new Snap\InputException('Testing throwing of exception in valid method', Snap\InputException::GENERAL_ERROR, 'field');
            return true;
        }
    }
}

if(! class_exists('ItemB')) {
    class ItemB extends snapobject {
        public function getStorePublic() {
          return $this->getStore(); 
        }
        protected function reset() {
            $this->members = array( 'id' => null, 'int' => null, 'string' => null, 'enum' => null, 'datetime' => null,
                                    'date' => null, 'time' => null, 'timestamp' => null, 'createdon' => null, 'modifiedon' => null);
            $this->viewMembers = array('linkb' => null, 'linkc' => null, 'name' => null);
        }

        public function isValid() {
            if(is_numeric($this->members['string'])) throw new Snap\InputException('Testing throwing of exception in valid method', Snap\InputException::GENERAL_ERROR, 'field');
            if(is_numeric($this->members['datetime'])) throw new Snap\InputException('Testing throwing of exception in valid method', Snap\InputException::GENERAL_ERROR, 'field');
            if(is_numeric($this->members['date'])) throw new Snap\InputException('Testing throwing of exception in valid method', Snap\InputException::GENERAL_ERROR, 'field');
            if(is_numeric($this->members['string'])) throw new Snap\InputException('Testing throwing of exception in valid method', Snap\InputException::GENERAL_ERROR, 'field');
            if(!is_numeric($this->members['int'])) throw new Snap\InputException('Testing throwing of exception in valid method', Snap\InputException::GENERAL_ERROR, 'field');
            return true;
        }
    }
}
?>