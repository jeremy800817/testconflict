<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\job;

USe Snap\App;
Use Snap\ICliJob;
// Use Snap\TLogging;
Use Snap\object\OrderQueue;
Use Snap\object\Order;
Use Snap\object\MbbApFund;
/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @version 1.0
 * @package  snap.job
 */
class TempEventTriggerChangesJob extends basejob {

    // run ONCE ONLY - on 30/03/2021 - 00:06:00;
    // php /usr/local/nginx/html/gtp/source/snaplib/cli.php -f /usr/local/nginx/html/gtp/source/snaplib/job/TempEventTriggerChangesJob.php -c /usr/local/nginx/html/gtp/source/snapapp/config.ini -p "action=initGTP2Subscriber&debug=1"
    // php source/snaplib/cli.php -f source/snaplib/job/TempEventTriggerChangesJob.php -c source/snapapp/config.ini -p "action=initGTP2Subscriber&debug=1"
    
    
    // php /usr/local/nginx/html/gtp/source/snaplib/cli.php -f /usr/local/nginx/html/gtp/source/snaplib/job/TempEventTriggerChangesJob.php -c /usr/local/nginx/html/gtp/source/snapapp/config.ini -p "action=addsalesmantrader&debug=1"
    // php /usr/local/nginx/html/gtp/source/snaplib/cli.php -f /usr/local/nginx/html/gtp/source/snaplib/job/TempEventTriggerChangesJob.php -c /usr/local/nginx/html/gtp/source/snapapp/config.ini -p "action=reset_trader_pphonenombor&replacing=601128645599&replace=123&debug=1"
    
    
    // php /usr/local/nginx/html/gtp/source/snaplib/cli.php -f /usr/local/nginx/html/gtp/source/snaplib/job/TempEventTriggerChangesJob.php -c /usr/local/nginx/html/gtp/source/snapapp/config.ini -p "action=addsalesmantrader&trader=60121111111&debug=1"
    
    public function doJob($app, $params = array()) {
        
        
        if ($params['action'] == 'changeEvent'){
            $this->changeEventTriggerStatus($app);
        }
        if ($params['action'] == 'initGTP2Subscriber'){
            $this->addgtp2subscriber($app, $params);
        }
        if ($params['action'] == 'addsalesmantrader'){
            $this->getSubscriberAdd_SALESMAN_AND_TRADER_PHONE($app, $params);
        }

        if ($params['action'] == 'reset_trader_pphonenombor'){
            $this->reset_trader_phonenomborbaru($app, $params);
        }
        
        if ($params['action'] == 'resend'){
            $this->resend($app, $params);
        }
        
    }

    function describeOptions() {

    }

    private function changeEventTriggerStatus($app){
        $eventTriggerStore = $app->eventTriggerStore();
		$allTriggers = $eventTriggerStore->searchTable()->select()->where('id', 'IN', [1,2])->execute();

        foreach ($allTriggers as $trigger){
            $trigger->actionid = 1;
            $trigger->newstatus = 0;

            $eventTriggerStore->save($trigger);
        }
    }

    private function addgtp2subscriber($app, $params){
        $excludePartners = [1938546]; // already in subscriber table, as beta test user
        $corePartners = $app->partnerStore()->searchTable()->select()
            ->where('corepartner', 1)
            ->andWhere('id', 'NOT IN', $excludePartners)
            ->execute();

        

        $debug = false;
        if ($params['debug'] == 1){
            $debug = true;
        }        
        foreach ($corePartners as $partner){
            // checking if more than 2 

            $count = $app->eventSubscriberStore()->getByField('groupid', $partner->id);
            if ($count){
                // skip
                continue;
            }

            $check = $app->userStore()->searchTable()->select()->where('partnerid', $partner->id)->count();
            if ($check >= 2){
                echo "MORE THAN 2 user have same partnerid - ".$partner->id ."(".$partner->name.")\n";
                continue;
            }
            $user = $app->userStore()->getByField('partnerid', $partner->id);
            
            if (!$user){
                echo "USER NOT FOUND - ".$partner->id ."(".$partner->name.")\n";
                continue;
            }
            if (!$user->email){
                echo "USER NO EMAIL - ".$partner->id ."(".$partner->name.")\n";
            }else{
                
                if ($debug){
                    echo "SUCCESS EMAIL - ".$partner->id." - ".$user->email ."\n";
                }else{
                    $eventSubscriberEmail = $app->eventSubscriberStore()->create();
                    $eventSubscriberEmail = [
                        'triggerid' => 36,
                        'groupid' => $partner->id,
                        'receiver' => $user->email,
                        'status' => 1,
                    ];
                    $app->eventSubscriberStore()->save($eventSubscriberEmail);
                    // $app->eventSubscriberStore()->registerSubscriber(0, 36, $partner->id, $user->email);

                    // future placed
                    $eventSubscriberEmail2 = $app->eventSubscriberStore()->create();
                    $eventSubscriberEmail2 = [
                        'triggerid' => 41,
                        'groupid' => $partner->id,
                        'receiver' => $user->email,
                        'status' => 1,
                    ];
                    $app->eventSubscriberStore()->save($eventSubscriberEmail2);

                    // future matched
                    $eventSubscriberEmail3 = $app->eventSubscriberStore()->create();
                    $eventSubscriberEmail3 = [
                        'triggerid' => 44,
                        'groupid' => $partner->id,
                        'receiver' => $user->email,
                        'status' => 1,
                    ];
                    $app->eventSubscriberStore()->save($eventSubscriberEmail3);
                }
            }
            if (!$user->phoneno){
                echo "USER NO PHONENO - ".$partner->id ."\n";
            }else{
                if ($debug){
                    echo "SUCCESS PHONE - ".$partner->id." - ".$user->phoneno ."\n";
                }else{
                    $eventSubscriberPhone = $app->eventSubscriberStore()->create();
                    $eventSubscriberPhone = [
                        'triggerid' => 37,
                        'groupid' => $partner->id,
                        'receiver' => $user->phoneno,
                        'status' => 1,
                    ];
                    $app->eventSubscriberStore()->save($eventSubscriberPhone);
                    // $app->eventSubscriberStore()->registerSubscriber(0, 37, $partner->id, $user->phoneno);

                    // future placed
                    $eventSubscriberPhone2 = $app->eventSubscriberStore()->create();
                    $eventSubscriberPhone2 = [
                        'triggerid' => 42,
                        'groupid' => $partner->id,
                        'receiver' => $user->phoneno,
                        'status' => 1,
                    ];
                    $app->eventSubscriberStore()->save($eventSubscriberPhone2);

                    // future matched
                    $eventSubscriberPhone3 = $app->eventSubscriberStore()->create();
                    $eventSubscriberPhone3 = [
                        'triggerid' => 45,
                        'groupid' => $partner->id,
                        'receiver' => $user->phoneno,
                        'status' => 1,
                    ];
                    $app->eventSubscriberStore()->save($eventSubscriberPhone3);
                }
            }
        }

    }

    public function getSubscriberAdd_SALESMAN_AND_TRADER_PHONE($app, $params){
        $debug = false;
        if ($params['debug'] == 1){
            $debug = true;
        }  
        if (!$params['trader']){
            echo 'invalid trader number'; exit;
        }
        $trader = $params['trader'];

        $add = $app->eventSubscriberStore()->searchTable()->select()->where('triggerid', 'in', [37, 42, 45])->andwhere('receiver', 'not like', $trader)->execute();
        
        foreach ($add as $subscriber){
            // $salesman = null;
            // $partner = null;
            // $subscriber->receiver = null;

            $partner = $app->partnerStore()->getById($subscriber->groupid);
            if (!$partner->salespersonid){
                echo "NO SALES PERSON ID - ".$partner->name."\n";
                continue;
            }
            $salesman = $app->userStore()->getById($partner->salespersonid);
            if (!$salesman->phoneno){
                echo "NO SALESMAN PHONE NO - ".$partner->name."\n";
                continue;
            }

            $subscriber->receiver = $subscriber->receiver.', '.$salesman->phoneno.', '.$trader;
            if ($debug){
                echo "SAVING ".$subscriber->groupid." FOR ".$subscriber->receiver."\n";
            }else{
                $app->eventSubscriberStore()->save($subscriber);
            }

        }
    }

    public function reset_trader_phonenomborbaru($app, $params){
        $debug = false;
        if ($params['debug'] == 1){
            $debug = true;
        }  

        $trader = $params['replacing'];
        $new = $params['replace'];
        $add = $app->eventSubscriberStore()->searchTable()->select()->where('triggerid', 'in', [37, 42, 45])->andwhere('receiver', 'like', $trader)->execute();
        foreach ($add as $subscriber){
            $subscriber->receiver = str_replace($params['replacing'], $params['replace'], $subscriber->receiver);

            if ($debug){
                echo "SAVING ".$subscriber->groupid." FOR ".$subscriber->receiver."\n";
            }else{
                $app->eventSubscriberStore()->save($subscriber);
            }
        }
    }


    public function resend($app, $params){
        $debug = true;

        if ($params['debugfalse']){
            $debug = false;
        }

        if ($params['orderno']){
            $order = $app->orderStore()->getByField('orderno', $params['orderno']);

            $partnerid = $order->partnerid;

            if ($order->isspot == 0){
                $future = true;
            }

            // 41 = email; 42 = sms; 
            
            // SMS
            $receiver = $app->eventSubscriberStore()->searchTable()->select()->where("groupid", $partnerid)->andWhere('triggerid', 42)->one();
            $receiverArr = explode(',', $receiver->receiver);
            if ($debug){
                $receiverArr = [60162262608, 60129686208];
            }
            
            
            $user = $app->userStore()->searchTable()->select()->where('partnerid', $partnerid)->orderby('id', "ASC")->one();
            $username = $user->username;

            $finalprice = $order->price + ($order->fee);
            $finalprice = number_format($finalprice, 3);
            $xau = $order->xau;
            $xau = number_format($xau, 3);
            $amount = $order->amount;
            $amount = number_format($amount, 3);
            $orderno = $order->orderno;
            $ordertype = '';
            if ($order->type == 'CompanyBuy'){
                $ordertype = 'CustomerSell';
            }
            if ($order->type == 'CompanySell'){
                $ordertype = 'CustomerBuy';
            } 

            $productStore = $app->productStore();
            $product = $productStore->getById($order->productid);
            $productname = $product->name;

            $MESSAGE = ':ACE2U : '.$username.', future order fulfilled, '.$ordertype.'#:'.$orderno.', Price(RM/g):'.$finalprice.', Weight(gm):'.$xau.', Amount(RM):'.$amount.', Prod :'.$productname.'';

            echo $MESSAGE;

            $sms = new \Snap\manager\SmsManager($app);
            foreach ($receiverArr as $receiver) {
                $receiver = trim($receiver, ' ');
                // $this->log("SMS service: ".$receiver." : processing", SNAP_LOG_DEBUG);
                
                    // $this->log("SMS service: ".$receiver." : validating", SNAP_LOG_DEBUG);
                    $sms->phoneNumber = $receiver;
                    $sms->messagePayLoad = $MESSAGE;
                    if (!$sms->send()){
                        // $this->log("Unable to sent SMS number: ".$sms->phoneNumber." payload: ".$sms->messagePayLoad, SNAP_LOG_ERROR);
                        // throw new \Exception("Unable to sent SMS number: ".$sms->phoneNumber." payload: ".$sms->messagePayLoad);
                        echo "Unable to sent SMS number: ".$sms->phoneNumber;
                    }else{
                        // $this->log("SMS service: ".$receiver." : success", SNAP_LOG_DEBUG);
                    }
            }



            // EMAIL;
            
            $email_message = 'Dear Sir, <br><br>We are pleased to have received and confirmed your future order fulfilled as follows :<br><br>Client Name : '.$partnername.'<br>GTP Ref no : '.$orderno.'<br>XAU (g) : '.$xau.'<br>Final price (RM/g) : '.$finalprice.'<br>Order Amount (RM) : '.$amount.'<br>Date / Time : '.$order->createdon->format('Y-m-d H:i:s').'';

            $receiver = $app->eventSubscriberStore()->searchTable()->select()->where("groupid", $partnerid)->andWhere('triggerid', 41)->one();
            $receiverArr = explode(',', $receiver->receiver);
            if ($debug){
                $receiverArr = ['jinyu@silverstream.my'];
            }

            $mailer = $app->getMailer();
            foreach ($receiverArr as $receiver) {
                $receiver = trim($receiver, ' ');
                $mailer->addAddress($receiver);
            }

            $mailer->setFrom('noreply@ace2u.com', 'ACE GTP');

            $mailer->addBCC('jeff@silverstream.my');
            $mailer->isHTML();
            $mailer->Subject = 'GTP2 Future Order Fulfilled: ordertype['.$partnername.' | XAU (g) = '.$xau.' | Price (RM/g) = '.$finalprice.' | Order (RM) = '.$amount.']';
            $mailer->Body    = $email_message;
            // $mailer->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mailer->send();
        }

        
    }
}
