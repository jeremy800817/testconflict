<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;
use Snap\object\MyGoldTransaction;
use Exception;
use Snap\handler\OrderHandler;
use Snap\object\Order;

/**
 *
 * @author Rinston <rinston@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class ProcessDailyOrderReportJob  extends basejob
{
    protected $arr = array();
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()){
        try{
            $partner = $app->partnerStore()->searchTable()->select()->where('id', $params['partnerid'])->one();
            if(!$partner) Throw new exception("Partner not found");
            
            $now                = new \DateTime('now', $app->getUserTimezone());
            $nowDate            = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone()); 
            $genDate            = $nowDate->format('Y-m-d H:i:s'); //GMT
            $currDate           = date('Y-m-d', strtotime('-1 days', strtotime($genDate)));

            $startDate          = $currDate." 00:00:00";
            $endDate            = $currDate." 23:59:59";
            if(isset($params['date'])){
                $startDate          = $params['date']." 00:00:00";
                $endDate            = $params['date']." 23:59:59";
            }

            $jobMode = '';
            if(isset($params['mode'])){
                if($params['mode'] == 'monthly'){
                    $jobMode = 'monthly';
                    $currentdate = strtotime($genDate);
                    $startDate = date("Y-m-01 00:00:00", strtotime("first day of previous month",$currentdate));
                    $endDate = date("Y-m-j 23:59:59", strtotime("last day of previous month",$currentdate));

                    if(isset($params['date'])){
                        $currentdate = strtotime($params['date']." 00:00:00");
                        $startDate = date("Y-m-01 00:00:00", strtotime("first day of this month", $currentdate));
                        $endDate = date("Y-m-j 23:59:59", strtotime("last day of this month", $currentdate));
                    }
                }
                else{
                    // default to daily
                    $jobMode = 'daily';
                }
            }
            
            $store = $app->orderFactory();
            $header = [
                json_decode('{"text":"Status","index":"status"}'),
                json_decode('{"text":"Date","index":"bookingon"}'),
                json_decode('{"text":"ID","index":"id"}'),
                json_decode('{"text":"Order No.","index":"orderno"}'),
                json_decode('{"text":"Price Validation ID","index":"uuid","decimal":2}'),
                json_decode('{"text":"Remarks","index":"remarks"}'),
                json_decode('{"text":"Ace Buy/Sell","index":"type"}'),
                json_decode('{"text":"Xau Weight (g)","index":"xau","decimal":6}'),
                json_decode('{"text":"GP/P1 Price","index":"price","decimal":2}'),
                // json_decode('{"text":"P2 Price","index":"bookingprice","decimal":2}'),
                json_decode('{"text":"Total Amount (RM)","index":"amount","decimal":2}'),
                json_decode('{"text":"Settlement Method","index":"discountinfo"}')
            ];
            
            $conditions = ['partnerid' => $partner->id];
            $prefix = $store->getColumnPrefix();
            foreach ($header as $key => $column) {
                // Overwrite index value with expression
                $original = $column->index;
                if ('status' === $column->index) {
                        
                    $header[$key]->index = $store->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}status` = " . Order::STATUS_PENDING . " THEN 'Pending'
                        WHEN `{$prefix}status` = " . Order::STATUS_CONFIRMED . " THEN 'Confirmed'
                        WHEN `{$prefix}status` = " . Order::STATUS_COMPLETED . " THEN 'Completed'
                        WHEN `{$prefix}status` = " . Order::STATUS_EXPIRED . " THEN 'Expired'
                        WHEN `{$prefix}status` = " . Order::STATUS_PENDINGCANCEL . " THEN 'Pending Cancel'
                        WHEN `{$prefix}status` = " . Order::STATUS_CANCELLED . " THEN 'Cancelled'
                        WHEN `{$prefix}status` = " . Order::STATUS_PENDINGPAYMENT . " THEN 'Pending Payment' END as `{$prefix}status`"
                    );
                    $header[$key]->index->original = $original;
                }
            }

            $statusRenderer = null;
            // $statusRenderer = [
            //     0 => "Pending",
            //     1 => "Confirmed",
            //     2 => "Pending Payment",
            //     3 => "Pending Cancel",
            //     4 => "Reversal",
            //     5 => "Completed",
            //     6 => "Expired"
            // ];

            $report = $app->reportingManager()->generateExportFile($store,$header, $startDate, $endDate, $params['modulename'], '', '', $conditions, null, null, $statusRenderer,true, $jobMode);
            if(count($report) == 2 ){
                $p = '';
                if($jobMode == 'monthly'){
                    $p = 'MONTHLY';
                    $reportDate = date('M Y', strtotime($startDate));
                }
                else if($jobMode = 'daily'){
                    $p = 'DAILY';
                    $reportDate = date('Ymd', strtotime($startDate));
                }
                else{
                    // do nothing
                }

                $bodyEmail    = "Please find the attached file ".$report[1]." for your reference.";
                // $reportDate = date('Y-m-d', strtotime($startDate));
                $emailSubject = $params['modulename']."_".$p."TRN_{$reportDate}";
                if(isset($params['emaillist'])) {
                    $sendToEmail = $params['emaillist'];
                    $mail = $app->reportingManager()->sendNotifyEmailReport($bodyEmail, $emailSubject, $sendToEmail, $report[0], $report[1]);
                }
            }
        }
        catch(\Throwable $e){
            echo $e->getMessage();
        }
        
    }
    
    /**
     * This method is used to display options parameter for this job.
     * @return Array of associative array of parameters.
     *         E.g.[
     *            'param1' => array('required' => true, 'type' => 'int', 'desc' => 'Some description'),
     *            'param2' => array('required' => false, 'default' => 1, type' => 'string', 'desc' => 'Some description 22222'),
     *         ]
     *         -Where [required] indicates if the params is required for the job to run.  The cli will ensure this parameter is provided
     *                [type] is the expected data type of the parameter or its valid values.
     *                [default] is the default value for the field.
     *                [desc] is the description of the parameter and what it does.
     */
    function describeOptions()
    {
        return [
            'partnerid' => array('required' => true,  'type' => 'string', 'desc' => 'one partner id'),
            'modulename' => array('required'=> true, 'type' => 'string', 'desc' => 'for the filename later'),
            'mode' => array('required' => true, 'type' => 'string', 'desc' => 'to determine either monthly or daily')
        ];
    }
}
