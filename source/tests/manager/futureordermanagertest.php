<?php

use PHPUnit\Framework\TestCase;
use Snap\object\SnapObject;
Use Snap\object\Order;
Use Snap\object\OrderQueue;
Use Snap\manager\FutureOrderManager;

/**
 * @covers partner objects
 */
final class futureOrderManagerTest extends TestCase
{
    static public $app = null;
    public function __construct() {
        $this->backupGlobals = false;
    }

    public static function setUpBeforeClass() {
        self::$app = \Snap\App::getInstance();
    }

    
    public function testThatWillBeExecuted()
    {

        $this->init_test_order_which_not_create_unknown();
        $this->_run_create_confirm();
        $this->_run_daily_trans_amount();
        // $this->_run_create_with_error();
        $this->_run_matched_notify();
        $this->_run_create_confirm_cancel_confirmcacnel();
        // $this->_run_price_stream_macthing();
    }

    public function _run_create_confirm(){
        $this->purge();
        $this->createFutureOrder();
        $this->confirmFutureOrder();
    }
    public function _run_create_with_error(){
        $this->createFutureOrder_with_error();
    }
    public function _run_matched_notify(){
        $this->purge();
        $this->futureOrderMatched();
        $this->futureOrderMatchedAndNotify();
    }
    public function _run_create_confirm_cancel_confirmcacnel(){
        $this->purge();
        $this->createFutureOrder();
        $this->confirmFutureOrder();
        $this->cancelFutureOrder();
        $this->confirmCancelFutureOrder();
    }
    public function _run_price_stream_macthing(){
        $this->createFutureOrder();
        $this->confirmFutureOrder();
        $this->runFutureOrderPriceMatching();
    }
    public function _run_daily_trans_amount(){
        $this->dailyTransAmount();
    }

    public function purge(){
        // purge all data with repeated depends process
        $app = self::$app;
        $app->getDBHandle()->query("TRUNCATE TABLE `test_orderqueue`;");
    }

    public function init_test_order_which_not_create_unknown(){
        $app = self::$app;
        $app->getDBHandle()->query("CREATE TABLE IF NOT EXISTS `test_order` SELECT * FROM `order`;");
    }

    // error
    // minxau = 10, == 10gram, mbb should be MYR10, currency is not using, usd is not using, default amount currency is not using too.
    public function createFutureOrder()
    {
        $app = self::$app;
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);

        
        // print_r($app->orderStore());exit;
        // init Store
        $productStore = $app->productStore();
        $orderStore = $app->orderStore();
        $orderQueueStore = $app->orderQueueStore();
        // init test table 
        // $a = $orderStore->getById(1);
        // $x = $app->orderQueueStore()->getById(1);
        // test_order not create
        // $app->getDBHandle()->query("CREATE TABLE `test_order` LIKE `order`;");
        // $app->getDBHandle()->query("INSERT INTO `test_order` SELECT * FROM `order`;");
        
        $now = new \DateTime();
        $goodTillDate = $now->modify('+10 days');
        $goodTillDate = $goodTillDate->format('Y-m-d H:i:s');
        
        // create future order with weight
        $apiVersion = '1.0m';
        $futureRefid = 'FORDER_REF_1';
        $trxType = Order::TYPE_COMPANYBUY;
        $product = $productStore->getById(1);
        $orderType = 'weight';
        $orderValue = '20.00';
        $expectedMatchingPrice = '201.00';
        $goodTillDate = $goodTillDate;
        $notifyUrl = '';
        $matchNotifyUrl = 'https://somewhere.com/notify';
        $reference = '_REF';
        $timeStamp = $now->format('Y-m-d H:i:s');
        
        // trxType = buy/sell - db=>ordertype
        // ordertype = weight/amount - db=>buy/sell
        $return = $app->futureOrderManager()->createFutureOrder($partner, $apiVersion, $futureRefid, $trxType, $product, $orderType, $orderValue, 
        $expectedMatchingPrice, $goodTillDate, $notifyUrl, $matchNotifyUrl, $reference, $timeStamp);

        $this->assertEquals(1, $orderQueueStore->searchTable()->select()->where('partnerrefid', $futureRefid)->count(), 'completed.');

        $checkInit = $orderQueueStore->searchTable()->select()->where('orderqueueno', $return->orderqueueno)->one();
        foreach ($checkInit as $x => $check){
            echo "\r\n".__METHOD__.'---output---'.'---serialno => '.$check->status.' ---create future order with weight ';
            
            $orderAmount = ($orderType == 'weight') ? (floatval($orderValue) * floatval($orderValue)) : (floatval($orderValue) / floatval($expectedMatchingPrice));
            $this->assertEquals($partner->id, $check->partnerid, 'must be the same');
            $this->assertEquals($orderAmount, $check->amount, 'must be the same');
            $this->assertGreaterThan(0, $check->weight, 'must be the same');
            $this->assertEquals($futureRefid, $check->partnerrefid, 'must be the same');
            
        }


        // create future order with amount
        $apiVersion = '1.0m';
        $futureRefid = 'FORDER_REF_2';
        $trxType = Order::TYPE_COMPANYBUY;
        $product = $productStore->getById(1);
        $orderType = 'amount';
        $orderValue = '26000.00';
        $expectedMatchingPrice = '195.50';
        $goodTillDate = $goodTillDate;
        $notifyUrl = '';
        $matchNotifyUrl = 'https://somewhere.com/notify';
        $reference = '_REF';
        $timeStamp = $now->format('Y-m-d H:i:s');

        // trxType = buy/sell - db=>ordertype
        // ordertype = weight/amount - db=>buy/sell
        $return = $app->futureOrderManager()->createFutureOrder($partner, $apiVersion, $futureRefid, $trxType, $product, $orderType, $orderValue, 
        $expectedMatchingPrice, $goodTillDate, $notifyUrl, $matchNotifyUrl, $reference, $timeStamp);

        $orderQueueStore = $app->orderQueueStore();
        $this->assertEquals(1, $orderQueueStore->searchTable()->select()->where('partnerrefid', $futureRefid)->count(), 'completed.');

        $checkInit = $orderQueueStore->searchTable()->select()->where('orderqueueno', $return->orderqueueno)->one();
        foreach ($checkInit as $x => $check){
            echo "\r\n".__METHOD__.'---output---'.'---serialno => '.$check->status.' ---create future order with amount ';
            
            $orderAmount = ($orderType == 'weight') ? (floatval($orderValue) * floatval($orderValue)) : (floatval($orderValue) / floatval($expectedMatchingPrice));
            $this->assertEquals($partner->id, $check->partnerid, 'must be the same');
            $this->assertEquals($orderAmount, $check->amount, 'must be the same');
            $this->assertGreaterThan(0, $check->weight, 'must be the same');
            $this->assertEquals($futureRefid, $check->partnerrefid, 'must be the same');
            
        }
    }

    public function createFutureOrder_with_error(){
        $app = self::$app;
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        $productStore = $app->productStore();
        $orderStore = $app->orderStore();
        $orderQueueStore = $app->orderQueueStore();
        $now = new \DateTime();
        $goodTillDate = $now->modify('+10 days');
        $goodTillDate = $goodTillDate->format('Y-m-d H:i:s');

        // create future order with weight
        $apiVersion = '1.0m';
        $futureRefid = 'FORDER_REF_1';
        $trxType = Order::TYPE_COMPANYBUY;
        $product = $productStore->getById(1);
        $orderType = 'amount';
        $orderValue = '5.00';
        $expectedMatchingPrice = '201.00';
        $goodTillDate = $goodTillDate;
        $notifyUrl = '';
        $matchNotifyUrl = 'https://somewhere.com/notify';
        $reference = '_REF';
        $timeStamp = $now->format('Y-m-d H:i:s');
        
        // trxType = buy/sell - db=>ordertype
        // ordertype = weight/amount - db=>buy/sell
        $this->expectException('Error');
        $return = $app->futureOrderManager()->createFutureOrder($partner, $apiVersion, $futureRefid, $trxType, $product, $orderType, $orderValue, 
        $expectedMatchingPrice, $goodTillDate, $notifyUrl, $matchNotifyUrl, $reference, $timeStamp);
    }

    /**
     * @depends createFutureOrder
     */
    public function confirmFutureOrder()
    {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        
        $product = $app->productStore()->getById(1);
        $this->assertEquals(1, $product->id);

        $forder = $app->orderQueueStore()->searchTable()->select()->where('partnerrefid', 'FORDER_REF_1')->andWhere('status', OrderQueue::STATUS_PENDING)->one();
        // print_r($forder);exit;
        $app->futureOrderManager()->confirmCreateFutureOrder($forder);
        $orderQueueStore = $app->orderQueueStore();
        $this->assertEquals(1, $orderQueueStore->searchTable()->select()->where('partnerrefid', 'FORDER_REF_1')->andWhere('status', OrderQueue::STATUS_ACTIVE)->count(), '- confirm f order.');
    }

    /**
     * @depends confirmFutureOrder
     */
    public function cancelFutureOrder()
    {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        
        $product = $app->productStore()->getById(1);
        $this->assertEquals(1, $product->id);

        $refid = 'FORDER_REF_1';
        $reference = '_REF';
        $notifyUrl = '';
        $apiVersion = '1.0m';

        $app->futureOrderManager()->cancelFutureOrder($partner, $apiVersion, $refid, $notifyUrl, $reference, $now);
        $orderQueueStore = $app->orderQueueStore();
        $this->assertEquals(1, $orderQueueStore->searchTable()->select()->where('partnerrefid', $refid)->andWhere('status', OrderQueue::STATUS_PENDINGCANCEL)->count(), '- pendingcancel.');
    }

    /**
     * @depends cancelFutureOrder
     */
    public function confirmCancelFutureOrder()
    {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        
        $product = $app->productStore()->getById(1);
        $this->assertEquals(1, $product->id);

        $forder = $app->orderQueueStore()->searchTable()->select()->where('partnerrefid', 'FORDER_REF_1')->andWhere('status', OrderQueue::STATUS_PENDINGCANCEL)->one();

        $app->futureOrderManager()->confirmCanceledFutureOrder($forder);
        $orderQueueStore = $app->orderQueueStore();
        $this->assertEquals(1, $orderQueueStore->searchTable()->select()->where('partnerrefid', 'FORDER_REF_1')->andWhere('status', OrderQueue::STATUS_CANCELLED)->count(), '- pendingcancel.');
    }

    public function dailyTransAmount()
    {
        $app = self::$app;

        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);

        $product = $app->productStore()->getById(1);
        $this->assertEquals(1, $product->id);

        $trxType = order::TYPE_COMPANYBUY;
        $return = $app->futureOrderManager()->getTotalTransactionWeight($partner, $product, $trxType);
        echo "\r\n".__METHOD__.'---output---'.' => '.$return.' ---dailyTransAmount('.$trxType.') in weight ';
        $trxType = order::TYPE_COMPANYSELL;
        $return = $app->futureOrderManager()->getTotalTransactionWeight($partner, $product, $trxType);
        echo "\r\n".__METHOD__.'---output---'.' => '.$return.' ---dailyTransAmount('.$trxType.') in weight ';
    }
    
    /**
     * @depends confirmFutureOrder
     */
    public function runFutureOrderPriceMatching()
    {
        $app = self::$app;
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        
        // create price stream, too large to test on insert into select *
        // $priceStream = $app->pricestreamStore()->getById(1000);
        // print_r($priceStream);exit;
        $priceStream = (object) [
            "id" => "1000",
            "providerid" => "1",
            "providerpriceid" => "2abd4ace-7e1e-11ea-8fe8-0671ab1b7502",
            "uuid" => "PS100000000000003E8",
            "currencyid" => "5",
            // "companybuyppg" => "236.551160",
            "companybuyppg" => "201.000000",
            "companysellppg" => "238.474033",
            "pricesourceid" => "1",
            "pricesourceon" => new \DateTime(),
            "createdon" => new \DateTime(),
            "createdby" => 0,
            "modifiedon" => new \DateTime(),
            "modifiedby" => "0",
            "status" => "1"
        ];
        // print_r($priceStream);exit;
        
        // data = [
        //     partnerrefid = FORDER_REF_1
        //     expected_matching_price = 201.00
        // ]
        $return = $app->futureOrderManager()->runFutureOrderPriceMatching($priceStream);
        
        $checkInit = $app->orderQueueStore()->searchTable()->select()->where('partnerrefid', 'FORDER_REF_1')->one();
        // print_r($checkInit);exit;
        $this->assertEquals(OrderQueue::STATUS_MATCHED, $checkInit->status, "must be same");
    }

    public function futureOrderMatched()
    {
        
    }

    public function futureOrderMatchedAndNotify()
    {

    }
   
}
?>