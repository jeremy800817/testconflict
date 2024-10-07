<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
Use \Snap\TLogging;
USe Snap\App;
/**
 * Handler class for all Maybank API to GTP.
 *
 * @author Devon <devon@silverstream.my>
 * @version 1.0
 */
class MbbApiHandler implements \Snap\IHandler {
    private $app = null;
    Use \Snap\TLogging;

    function __construct(App $app) {
        $this->app = $app;
    }

    /**
     * This is the main method that will be used to handle any requests from the handler
     *
     * @param  App    $app    The application instance
     * @param  String $action The action (string) to operate on
     * @param  Array  $params Query parameters parsed in
     * @return void
     */
    public function doAction($app, $action, $params)
    {
        $starttime = microtime(true);
        $this->log("----GTP-processGtpRequest - Start --request ", SNAP_LOG_DEBUG, $params);
        // $_REQUEST['starttime'] = microtime(true);
        // GtpApiProcessor1_0m.php line 172, get starttime for timer conditions before ->response(); and change response to failed
        $return = $app->apiManager()->processGtpRequest($params);
        
        $endtime = microtime(true);
        $timediff = number_format($endtime - $starttime, 3, '.', '');
        $this->log("----GTP-processGtpRequest - End --time({".$timediff."})", SNAP_LOG_DEBUG);
        
        // 2023-06-13: Calvin say remove jinyu and ryan email and replace with notification+gtp@ace2u.com
        // $receiverArr = ['jinyu@silverstream.my'];
        $receiverArr = ['notification+gtp@ace2u.com'];

        $dev = $this->app->getConfig()->{'snap.environtment.development'};
        if (floatval($timediff) >= 14 && !$dev){
            
            if ($params['reference'] == 'UPTIMEROBOT'){
                return false;
            }

            $this->log("----GTP-processGtpRequest - timeout_condition", SNAP_LOG_DEBUG);
            // TEMP until root causes be identified and fixed - start
            if ($return['action_requested'] == 'spot_acebuy' || $return['action_requested'] == 'spot_acesell'){
                // fail the order
                $this->log("----GTP-processGtpRequest - timeout_condition fail OrderNo(".$return['order_id'].")", SNAP_LOG_DEBUG);
                $orderId = $return['order_id'];
                $order = $app->orderStore()->getByField('orderno', $orderId);
                $order->status = \Snap\object\Order::STATUS_EXPIRED; // _PENDING temp use, expired for mbb = cancelled/failed, will change on later update (branch)
                $order->remarks = $order->remarks.'-TIMEOUT:'.$timediff;
                $app->orderStore()->save($order);

                // 2023-06-13: Calvin say remove jinyu and ryan email and replace with notification+gtp@ace2u.com
                // $receiverArr = ['jinyu@silverstream.my', 'ryanleong@ace2u.com'];
                $receiverArr = ['notification+gtp@ace2u.com'];
            }
            // TEMP until root causes be identified and fixed - end

            $now = new \DateTime(gmdate('Y-m-d\TH:i:s'));  
            $_now = \Snap\common::convertUTCToUserDatetime($now); // email format use GMT time

            $graylog_time_diff = floatval($timediff) + 1;
            $request_start = new \DateTime(gmdate('Y-m-d\TH:i:s')); // graylog use UTC
            $email_now = new \DateTime(gmdate('Y-m-d\TH:i:s')); // graylog use UTC
            $request_start = $request_start->modify('-'.intval($graylog_time_diff).' seconds');
            $request_start = $request_start->format('Y-m-d\TH:i:s\Z');
            $request_start = urlencode($request_start);
            $email_now = $email_now->format('Y-m-d\TH:i:s\Z');
            $request_end = urlencode($email_now);

            $link = 'http://192.168.70.22:9000/streams/000000000000000000000001/search?q=&rangetype=absolute&streams=000000000000000000000001&from='.$request_start.'&to='.$request_end;

            // send email
            $mailer = $app->getMailer();
            
            foreach ($receiverArr as $receiver) {
                $receiver = trim($receiver, ' ');
                $mailer->addAddress($receiver);
            }
            $mailer->isHTML();
            $mailer->SMTPDebug = 0; // return debug code off
            $mailer->Subject = 'GTP - slow request - duration - '.$timediff. ' at '.$_now->format('Y-m-d H:i:s');
            $message = $return['action_requested']."<br><br>GrayLog (OpenVPN) - link <a href='".$link."' target='_blank'>".$link."</a>";
            if ($params['ref_id']){
                $message .= '<br><br>Ref ID: '.$params['ref_id'];
            }
            if ($params['reference']){
                $message .= '<br><br>Reference: '.$params['reference'];
            }
            $mailer->Body = $message;

            $mailer->send();

        }
    }

    /**
     * This method will return the rights that are applicable for this handler with this particular user type
     *
     * @param  String  $action  Action requested by user
     * @return String   The permission string representing the permissions to check for
     */
    public function getRights($action)
    {
    }

    /**
     * This method will determine is this particular handler is able to handle the action given.
     *
     * @param  App    $app    The application object (for getting user session etc to test?)
     * @param  String $action The action name to be handled
     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
     */
    public function canHandleAction($app, $action)
    {
    }

    /**
     * This method adds in additional handler to form a composite handler chain that is able to
     * perform certain types of actions.
     *
     * @param IHandler $child The handler that would be added into.
     */
    public function addChild(\Snap\IHandler $child)
    {
    }
}
