<?php

use PHPUnit\Framework\TestCase;
use Snap\object\SnapObject;
Use Snap\object\OrderQueue;
Use Snap\object\Order;
Use Snap\object\PriceStream;
Use Snap\manager\FutureOrderManager;

/**
 * @covers partner objects
 */
final class futureOrderMatchingTest extends TestCase
{

    // php source/snaplib/cli.php -f source/tests/manager/bankVaultManagerTest.php -c source/tests/testconfiglocal.ini
    // php source/snaplib/cli.php -f source/tests/startup.php -c source/tests/testconfiglocal.ini
    // php source/tests/startup.php -f source/tests/manager/bankVaultManager.php -c source/tests/testconfiglocal.ini
    // ../vendor/bin/phpunit --bootstrap ./startup.php manager/bankVaultManagerTest.php
    static public $app = null;
    static public $allocatedItemSerialno = [];
    public function __construct() {
        $this->backupGlobals = false;
    }

    public static function setUpBeforeClass() {
        self::$app = \Snap\App::getInstance();
        self::$app->queueManager()->empty(\Snap\manager\FutureOrderManager::newPriceStreamQueue);
        self::$app->queueManager()->empty(\Snap\manager\FutureOrderManager::matchedOrderQueue);
        $cacher = self::$app->getCacher();
        $allKeys = $cacher->keys("*Strategy*");
        foreach($allKeys as $aKey) {
            // echo "removing the key ". print_r($aKey, true) . "\n";
            $cacher->del($aKey);
        }
    }

    public static function tearDownAfterClass()
    {
        self::$app = \Snap\App::getInstance();
        $cacher = self::$app->getCacher();
        $allKeys = $cacher->keys("*Strategy*");
        foreach($allKeys as $aKey) {
            // echo "removing the key ". print_r($aKey, true) . "\n";
            $cacher->del($aKey);
        }
    }

    /**
     * Testing flow:
     *     - Test strategy and with API
     */
    
    public function testAddFutureOrder()
    {
        $app = self::$app;
        $partner = $app->partnerStore()->getById(1);
        $product = $app->productStore()->getById(1);
        $timeStamp = date('Y-m-d h:i:s');
        $futureDate = strtotime('+1 month');
        $futureDate = date('Y-m-d H:i:s', $futureDate);
        $pastDate = strtotime('-1 day');
        $pastDate = date('Y-m-d H:i:s', $pastDate);
        $orderQueue1 = $app->futureOrderManager()->createFutureOrder($partner, 'MANUAL', 'F0000001', Order::TYPE_COMPANYBUY, $product, 'weight', 100, 190, $futureDate, '', '', 'F0000001', $timeStamp);
        $app->futureOrderManager()->confirmCreateFutureOrder($orderQueue1);
        $this->assertGreaterThan(0, $orderQueue1->id);
        $this->assertEquals(OrderQueue::STATUS_PENDING, $orderQueue1->status);
        $orderQueue2 = $app->futureOrderManager()->createFutureOrder($partner, 'MANUAL', 'F0000002', Order::TYPE_COMPANYSELL, $product, 'weight', 100, 180, $futureDate, '', '', 'F0000002', $timeStamp);
        $app->futureOrderManager()->confirmCreateFutureOrder($orderQueue2);
        $this->assertGreaterThan(0, $orderQueue2->id);
        $this->assertEquals(OrderQueue::STATUS_PENDING, $orderQueue2->status);
        $orderQueue3 = $app->futureOrderManager()->createFutureOrder($partner, 'MANUAL', 'F0000003', Order::TYPE_COMPANYBUY, $product, 'weight', 100, 185, $futureDate, '', '', 'F0000003', $timeStamp);
        $app->futureOrderManager()->confirmCreateFutureOrder($orderQueue3);
        $this->assertGreaterThan(0, $orderQueue3->id);
        $this->assertEquals(OrderQueue::STATUS_PENDING, $orderQueue3->status);
        $orderQueue4 = $app->futureOrderManager()->createFutureOrder($partner, 'MANUAL', 'F0000004', Order::TYPE_COMPANYSELL, $product, 'weight', 100, 185, $futureDate, '', '', 'F0000004', $timeStamp);
        $app->futureOrderManager()->confirmCreateFutureOrder($orderQueue4);
        $this->assertGreaterThan(0, $orderQueue4->id);
        $this->assertEquals(OrderQueue::STATUS_PENDING, $orderQueue4->status);

        $orderQueue5 = $app->futureOrderManager()->createFutureOrder($partner, 'MANUAL', 'F0000005', Order::TYPE_COMPANYBUY, $product, 'weight', 100, 190, $pastDate, '', '', 'F0000005', $timeStamp);
        $app->futureOrderManager()->confirmCreateFutureOrder($orderQueue5);
        $this->assertGreaterThan(0, $orderQueue5->id);
        $this->assertEquals(OrderQueue::STATUS_PENDING, $orderQueue5->status);
        $orderQueue6 = $app->futureOrderManager()->createFutureOrder($partner, 'MANUAL', 'F0000006', Order::TYPE_COMPANYSELL, $product, 'weight', 100, 180, $pastDate, '', '', 'F0000006', $timeStamp);
        $app->futureOrderManager()->confirmCreateFutureOrder($orderQueue6);
        $this->assertGreaterThan(0, $orderQueue6->id);
        $this->assertEquals(OrderQueue::STATUS_PENDING, $orderQueue6->status);
    }

    /**
     * @depends testAddFutureOrder
     */
    public function testReceivedDefaultMatchStrategy()
    {
        $app = self::$app;
        $partner = $app->partnerStore()->getById(1);
        $product = $app->productStore()->getById(1);
        $provider = $app->priceProviderStore()->getByField('pricesourceid', $partner->pricesourceid);
        $provider->futureorderstrategy = '\Snap\util\pricematch\DefaultMatchStrategy';
        $provider->futureorderparams = '';
        $app->priceProviderStore()->save($provider);
        $this->assertEquals(1, $provider->id);
        $this->assertEquals($partner->pricesourceid, $provider->pricesourceid);

        //Test Matching code.  Not matching any trx
        $newPriceStream = $app->priceStreamStore()->create([
            'pricesourceid' => $provider->pricesourceid,
            'providerid' => $provider->id,
            'currencyid' => 1,
            'companybuyppg' => 179,
            'companysellppg' => 200,
            'uuid' => 'abcderf11111',
            'createdon' => new \Datetime,
            'modifiedon' => new \Datetime
        ]); 
        $app->priceStreamStore()->save($newPriceStream);
        $this->assertGreaterThan(0, $newPriceStream->id);
        $app->futureOrderManager()->onObservableEventFired($app->priceManager(), new \Snap\IObservation($newPriceStream, \Snap\IObservation::ACTION_NEW, 0));
        $this->assertEquals(1, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertRegExp('/DefaultMatchStrategy/', ''.$provider->futureorderstrategy);

        $app->futureOrderManager()->processReceivedPriceStreamData(1);
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::matchedOrderQueue));

        //Test Matching code - Matching 1 company buy
        $newPriceStream = $app->priceStreamStore()->create([
            'pricesourceid' => $provider->pricesourceid,
            'providerid' => $provider->id,
            'currencyid' => 1,
            'companybuyppg' => 185,
            'companysellppg' => 200,
            'uuid' => 'abcderf11112',
            'createdon' => new \Datetime,
            'modifiedon' => new \Datetime
        ]);
        $app->priceStreamStore()->save($newPriceStream);
        $this->assertGreaterThan(0, $newPriceStream->id);
        $app->futureOrderManager()->onObservableEventFired($app->priceManager(), new \Snap\IObservation($newPriceStream, \Snap\IObservation::ACTION_NEW, 0));
        $this->assertEquals(1, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertRegExp('/DefaultMatchStrategy/', ''.$provider->futureorderstrategy);

        $app->futureOrderManager()->processReceivedPriceStreamData(1);
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertEquals(1, $app->queueManager()->count(\Snap\manager\FutureOrderManager::matchedOrderQueue), 'Expect to match ');
        $futureOrder = $app->orderQueueStore()->create();
        $data = json_decode($app->queueManager()->pop(\Snap\manager\FutureOrderManager::matchedOrderQueue, 1), true);
        $futureOrder->fromCache($data['futureOrder']);
        $this->assertEquals('F0000003', $futureOrder->partnerrefid);
        $finalMatchOrder = $futureOrder;  //Use for final test

        //Test Matching code - Matching 1 company sell
        $newPriceStream = $app->priceStreamStore()->create([
            'pricesourceid' => $provider->pricesourceid,
            'providerid' => $provider->id,
            'currencyid' => 1,
            'companybuyppg' => 185,
            'companysellppg' => 184,
            'uuid' => 'abcderf11113',
            'createdon' => new \Datetime,
            'modifiedon' => new \Datetime
        ]);
        $app->priceStreamStore()->save($newPriceStream);
        $this->assertGreaterThan(0, $newPriceStream->id);
        $app->futureOrderManager()->onObservableEventFired($app->priceManager(), new \Snap\IObservation($newPriceStream, \Snap\IObservation::ACTION_NEW, 0));
        $this->assertEquals(1, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertRegExp('/DefaultMatchStrategy/', ''.$provider->futureorderstrategy);

        $app->futureOrderManager()->processReceivedPriceStreamData(1);
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertEquals(1, $app->queueManager()->count(\Snap\manager\FutureOrderManager::matchedOrderQueue), 'Expect to match ');
        $futureOrder = $app->orderQueueStore()->create();
        $data = json_decode($app->queueManager()->pop(\Snap\manager\FutureOrderManager::matchedOrderQueue, 1), true);
        $futureOrder->fromCache($data['futureOrder']);
        $this->assertEquals('F0000004', $futureOrder->partnerrefid);

        //Test Matching code - Matching 2 company sell
        $newPriceStream = $app->priceStreamStore()->create([
            'pricesourceid' => $provider->pricesourceid,
            'providerid' => $provider->id,
            'currencyid' => 1,
            'companybuyppg' => 185,
            'companysellppg' => 179,
            'uuid' => 'abcderf11114',
            'createdon' => new \Datetime,
            'modifiedon' => new \Datetime
        ]);
        $app->priceStreamStore()->save($newPriceStream);
        $this->assertGreaterThan(0, $newPriceStream->id);
        $app->futureOrderManager()->onObservableEventFired($app->priceManager(), new \Snap\IObservation($newPriceStream, \Snap\IObservation::ACTION_NEW, 0));
        $this->assertEquals(1, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertRegExp('/DefaultMatchStrategy/', ''.$provider->futureorderstrategy);

        $app->futureOrderManager()->processReceivedPriceStreamData(1);
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertEquals(2, $app->queueManager()->count(\Snap\manager\FutureOrderManager::matchedOrderQueue), 'Expect to match ');
        $futureOrder = $app->orderQueueStore()->create();
        $data = json_decode($app->queueManager()->pop(\Snap\manager\FutureOrderManager::matchedOrderQueue, 1), true);
        $futureOrder->fromCache($data['futureOrder']);
        $this->assertEquals('F0000002', $futureOrder->partnerrefid);
        $data = json_decode($app->queueManager()->pop(\Snap\manager\FutureOrderManager::matchedOrderQueue, 1), true);
        $futureOrder->fromCache($data['futureOrder']);
        $this->assertEquals('F0000004', $futureOrder->partnerrefid);

        //Match and process the future order.
        //Test Matching code - Matching 1 company buy
        $cacher = $app->getCacher();
        $allKeys = $cacher->keys("*Strategy*");
        foreach($allKeys as $aKey) {
            // echo "removing the key ". print_r($aKey, true) . "\n";
            $cacher->del($aKey);
        }
        $newPriceStream = $app->priceStreamStore()->create([
            'pricesourceid' => $provider->pricesourceid,
            'providerid' => $provider->id,
            'currencyid' => 1,
            'companybuyppg' => 185,
            'companysellppg' => 200,
            'uuid' => 'abcderf11112',
            'createdon' => new \Datetime,
            'modifiedon' => new \Datetime
        ]);
        $app->priceStreamStore()->save($newPriceStream);
        $this->assertGreaterThan(0, $newPriceStream->id);
        $app->futureOrderManager()->onObservableEventFired($app->priceManager(), new \Snap\IObservation($newPriceStream, \Snap\IObservation::ACTION_NEW, 0));
        $this->assertEquals(1, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertRegExp('/DefaultMatchStrategy/', ''.$provider->futureorderstrategy);

        $orderStartingCount = $app->orderStore()->searchTable()->select()->count();
        $app->futureOrderManager()->processReceivedPriceStreamData(1);
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertEquals(1, $app->queueManager()->count(\Snap\manager\FutureOrderManager::matchedOrderQueue), 'Expect to match ');
        $app->futureOrderManager()->processPriceMatchedFutureOrder(1);
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::matchedOrderQueue), 'Expect to match ');
        $test = $app->orderQueueStore()->getById($finalMatchOrder->id);
        $this->assertEquals(OrderQueue::STATUS_MATCHED, $test->status);
        $this->assertGreaterThan(0, $test->matchon->format('Y'));
        $this->assertEquals($orderStartingCount, $app->orderStore()->searchTable()->select()->count());  //no extra records in order.

    }

   /**
     * @depends testReceivedDefaultMatchStrategy
     */
    public function testWithFixedIntervalMatchingAutoCreateOrder()
    {
        //To test - partner = autocreateorder on match
        //To test - FixedInterval matching strategy.
        //
        //
        $app = self::$app;
        $partner = $app->partnerStore()->getById(1);
        $product = $app->productStore()->getById(1);
        $provider = $app->priceProviderStore()->getByField('pricesourceid', $partner->pricesourceid);
        $this->assertEquals(1, $provider->id);
        $this->assertEquals($partner->pricesourceid, $provider->pricesourceid);
        $cacher = self::$app->getCacher();
        $allKeys = $cacher->keys("*Strategy*");
        foreach($allKeys as $aKey) {
            // echo "removing the key ". print_r($aKey, true) . "\n";
            $cacher->del($aKey);
        }
        //Setting the partner and provider to use different strategy and method.
        $provider->futureorderstrategy = '\Snap\util\pricematch\FixedIntervalMatchStrategy';
        $provider->futureorderparams = "00:00||23:59||1"; //UTC Start time || UTC end time || interval every n minutes
        $app->priceProviderStore()->save($provider);
        $partner->autocreatematchedorder = 1;
        $partner->orderingmode = \Snap\object\Partner::MODE_BOTH;
        $app->partnerStore()->save($partner);

        //Test Matching code.  Not matching any trx
        $newPriceStream = $app->priceStreamStore()->create([
            'pricesourceid' => $provider->pricesourceid,
            'providerid' => $provider->id,
            'currencyid' => 1,
            'companybuyppg' => 179,
            'companysellppg' => 200,
            'uuid' => 'Rabcderf11211',
            'createdon' => new \Datetime,
            'modifiedon' => new \Datetime
        ]);
        $app->priceStreamStore()->save($newPriceStream);
        $this->assertGreaterThan(0, $newPriceStream->id);
        $app->futureOrderManager()->onObservableEventFired($app->priceManager(), new \Snap\IObservation($newPriceStream, \Snap\IObservation::ACTION_NEW, 0));
        $this->assertEquals(1, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertRegExp('/FixedIntervalMatchStrategy/', ''.$provider->futureorderstrategy);

        $app->futureOrderManager()->processReceivedPriceStreamData(1);
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::matchedOrderQueue));

        //Test Matching code - Matching 1 company sell
        $newPriceStream = $app->priceStreamStore()->create([
            'pricesourceid' => $provider->pricesourceid,
            'providerid' => $provider->id,
            'currencyid' => 1,
            'companybuyppg' => 185,
            'companysellppg' => 184,
            'uuid' => 'Rabcderf11113',
            'createdon' => new \Datetime,
            'modifiedon' => new \Datetime
        ]);
        $app->priceStreamStore()->save($newPriceStream);
        $this->assertGreaterThan(0, $newPriceStream->id);
        $app->futureOrderManager()->onObservableEventFired($app->priceManager(), new \Snap\IObservation($newPriceStream, \Snap\IObservation::ACTION_NEW, 0));
        $this->assertEquals(1, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertRegExp('/FixedIntervalMatchStrategy/', ''.$provider->futureorderstrategy);

        $app->futureOrderManager()->processReceivedPriceStreamData(1);
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::newPriceStreamQueue));
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::matchedOrderQueue), 'Expect not to match ');
        $app->futureOrderManager()->onReceivedNewPriceStreamData(null, $provider);
        $this->assertEquals(1, $app->queueManager()->count(\Snap\manager\FutureOrderManager::matchedOrderQueue), 'Expect to match ');
        $futureOrder = $app->orderQueueStore()->create();
        // $data = json_decode($app->queueManager()->pop(\Snap\manager\FutureOrderManager::matchedOrderQueue, 1), true);
        // $futureOrder->fromCache($data['futureOrder']);
        // $this->assertEquals('F0000004', $futureOrder->partnerrefid);

        $finalMatchOrder = $app->orderQueueStore()->getByField('partnerrefid', 'F0000004');  //Use for final test
        $this->assertGreaterThan(0, $finalMatchOrder->id);
        $orderStartingCount = $app->orderStore()->searchTable()->select()->count();
        $app->futureOrderManager()->processPriceMatchedFutureOrder(1);
        $this->assertEquals(0, $app->queueManager()->count(\Snap\manager\FutureOrderManager::matchedOrderQueue), 'Expect to match ');
        $this->assertEquals(++$orderStartingCount, $app->orderStore()->searchTable()->select()->count());  //no extra records in order.
        $test = $app->orderQueueStore()->getById($finalMatchOrder->id);
        $this->assertEquals(OrderQueue::STATUS_MATCHED, $test->status);
        $this->assertGreaterThan(0, $test->matchon->format('Y'));
    }
}
?>