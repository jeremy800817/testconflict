<?php

use PHPUnit\Framework\TestCase;
use Snap\object\SnapObject;
Use Snap\object\MbbApFund;
Use Snap\manager\spotOrderManager;
Use Snap\manager\MbbAnPManager;

/**
 * @covers partner objects
 */
final class mbbAnPManagerManagerTest extends TestCase
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
        $this->testAPFUND();
    }


    public function testAPFUND(){
        $app = self::$app;
        try{
            $order = $app->orderStore()->getById(174);
            if ($order->price != $order->bookingprice){
                $type = MbbApFund::TYPE_ORDERCONFIRM;
                $amount = $this->poolCalculation($order->price, $order->bookingprice, $order, $type);
                if (!$amount){
                    throw new \Exception("AnPFund - ({$order->orderno}) - amount false, FAILED insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
                }
                $apfund = $app->mbbapfundStore()->create([
                    'partnerid' => $order->partnerid,
                    'operationtype' => $type,
                    'orderid' => $order->id,
                    'beginprice' => $order->price,
                    'beginpriceid' => $order->pricestreamid,
                    'endprice' => $order->bookingprice,
                    'endpriceid' => $order->bookingpricestreamid,
                    'amountppg' => $order->bookingprice, 
                    'amount' => $amount
                ]);
                $saveAPFund = $app->mbbapfundStore()->save($apfund);
                if ($saveAPFund){
                    // $this->log("AnPFund - ({$order->orderno}) - insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_DEBUG);
                }else{
                    throw new \Exception("AnPFund - ({$order->orderno}) - FAILED insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
                }
            }

            $order = $app->orderStore()->getById(176);
            if ($order->cancelprice){
                $type = MbbApFund::TYPE_ORDERREVERSE;
                $amount = $this->poolCalculation($order->price, $order->cancelprice, $order, $type);
                if (!$amount){
                    throw new \Exception("AnPFund - ({$order->orderno}) - amount false, FAILED insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
                }
                $apfund = $app->mbbapfundStore()->create([
                    'partnerid' => $order->partnerid,
                    'operationtype' => $type,
                    'orderid' => $order->id,
                    'beginprice' => $order->price,
                    'beginpriceid' => $order->pricestreamid,
                    'endprice' => $order->cancelprice,
                    'endpriceid' => $order->cancelpricestreamid,
                    'amountppg' => $order->cancelprice, 
                    'amount' => $amount,
                ]);
                $saveAPFund = $app->mbbapfundStore()->save($apfund);
                if ($saveAPFund){
                    // $this->log("AnPFund - ({$order->orderno}) - insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_DEBUG);
                }else{
                    throw new \Exception("AnPFund - ({$order->orderno}) - FAILED insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
                }
            }
        }catch(\Exception $e){
            // can implement extra func, alert
            // $this->log($e->getMessage(), $e->getCode());
            print_r($e->getMessage());exit;
        }
    }

   
    public function poolCalculation($price1, $price2, $order, $type = null){
        if ($type == MbbApFund::TYPE_ORDERCONFIRM){
            return $price1 - $price2;
        }
        if ($type == MbbApFund::TYPE_ORDERREVERSE){
            return $price2 - $price1;
        }
        return false;
    }
}