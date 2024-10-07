<?php

use PHPUnit\Framework\TestCase;
use Snap\object\SnapObject;
Use Snap\manager\EventTestManager;

/**
 * @covers partner objects
 */
final class eventTest extends TestCase
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
        // $this->notification();
        $this->runProcessorJob();
        // $this->eventSMS_COMPUTE();
        // $this->bugx();
    }


    public function main(){
        $app = self::$app;

        $return = $app->eventtestManager()->call();

        
        
        // $this->assertEquals($refid, $return->partnerrefno);
        // $this->assertEquals(Buyback::STATUS_CONFIRMED, $return->status);
    }



    public function notification(){
        $app = self::$app;
        //$changed = new \Snap\manager\EventTestManager;
        // $changed = $app->notificationManager();
        $changed = $app->eventtestManager();
        
        $order = $app->orderStore()->getById(1);
        $order->status = 0;
        $old_status = $order->status;
        $new_order = $app->orderStore()->save($order);
        $new_order->status = 1;
        $new_order_update = $app->orderStore()->save($order);

        
        $state = new \Snap\IObservation(
            $new_order_update, 
            \Snap\IObservation::ACTION_NEW, 
            $old_status, 
            []);
        $manager = $app->notificationManager()->onObservableEventFired($changed, $state);
    }

    public function runProcessorJob(){
        $app = self::$app;
        $app->notificationManager()->runProcessorJob();
    }

    public function eventSMS_COMPUTE(){
        $app = self::$app;
        $string = '88324-9sad9iufaDLa0023lc8zlca0i90aOoo3k2oal3l1la0s0BULKSMSACE2U60129686208str';
        $v = $app->smsManager()->computeCheckDigit($string, 32, 661, 179, 241, 211, 17, 509);
    }

    public function bugx(){
        $app = self::$app;
        $sms = new \Snap\manager\SmsManager($app);
        $sms->messagePayLoad = 'asdasdasdefafas';
        $sms->phoneNumber = '60129686208';
        
        $sms->send();
    }
}