<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;
use Snap\object\Redemption;
use Exception;
use Snap\handler\RedemptionHandler;

/**
 *
 * @author Rinston <rinston@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class ProcessDailyRedemptionReportJob  extends basejob
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
                }
                else{
                    // default to daily
                    $jobMode = 'daily';
                }
            }
            
            $store = $app->redemptionStore();
            $header = [
                json_decode('{"text":"Status","index":"status"}'),
                json_decode('{"text":"Date","index":"createdon"}'),
                json_decode('{"text":"Redemption No","index":"redemptionno"}'),
                json_decode('{"text":"XauWeight(g)","index":"totalweight","decimal":6}'),
                json_decode('{"text":"TotalAmount(RM)","index":"appointmentby"}'),
                json_decode('{"text":"SettlementMethod","index":"appointmentby"}'),
                json_decode('{"text":"Conversion Type","index":"type"}'),
                json_decode('{"text":"Item Code","index":"code","decimal":0}'),
                json_decode('{"text":"Serial Number","index":"serialnumber","decimal":0}'),
                json_decode('{"text":"Delivery Contact Name","index":"deliverycontactname1"}'),
                json_decode('{"text":"Delivery Contact No","index":"deliverycontactno1"}'),
                json_decode('{"text":"Delivery Address 1","index":"deliveryaddress1"}'),
                json_decode('{"text":"Delivery Address 2","index":"deliveryaddress2"}'),
                json_decode('{"text":"Delivery Post Code","index":"deliverypostcode"}'),
                json_decode('{"text":"Delivery State","index":"deliverystate"}'),
                json_decode('{"text":"DeliveryCountry","index":"deliverycountry"}'),
                json_decode('{"text":"Total Items","index":"totalquantity"}'),
                json_decode('{"text":"Confirm Reference","index":"confirmreference"}'),
                json_decode('{"text":"Partner Ref No","index":"partnerrefno"}')
                // json_decode('{"text":"Delivery Contact Name 2","index":"deliverycontactname2"}'),
                // json_decode('{"text":"Delivery Contact No 2","index":"deliverycontactno2"}'),
            ];
            
            
            $conditions = ['partnerid' => $partner->id];
            $prefix = $store->getColumnPrefix();
            foreach ($header as $key => $column) {
                // Overwrite index value with expression
                $original = $column->index;
                if ('status' === $column->index) {
                        
                    $header[$key]->index = $store->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}status` = " . Redemption::STATUS_PENDING . " THEN 'Pending'
                        WHEN `{$prefix}status` = " . Redemption::STATUS_CONFIRMED . " THEN 'Confirmed'
                        WHEN `{$prefix}status` = " . Redemption::STATUS_COMPLETED . " THEN 'Completed'
                        WHEN `{$prefix}status` = " . Redemption::STATUS_FAILED . " THEN 'Failed'
                        WHEN `{$prefix}status` = " . Redemption::STATUS_PROCESSDELIVERY . " THEN 'Process Delivery'
                        WHEN `{$prefix}status` = " . Redemption::STATUS_CANCELLED . " THEN 'Cancelled'
                        WHEN `{$prefix}status` = " . Redemption::STATUS_REVERSED . " THEN 'Reversed'
                        WHEN `{$prefix}status` = " . Redemption::STATUS_FAILEDDELIVERY . " THEN 'Failed Delivery'
                        WHEN `{$prefix}status` = " . Redemption::STATUS_SUCCESS . " THEN 'Success' END as `{$prefix}status`"
                    );
                    $header[$key]->index->original = $original;
                }

                if ('deliverycountry' === $column->index) {
                        
                    $header[$key]->index = $store->searchTable(false)->raw(
                        "'Malaysia' AS `{$prefix}deliverycountry`"
                    );
                    $header[$key]->index->original = $original;
                }

                if ('code' === $column->index) {
                    $header[$key]->index = $store->searchTable(false)->raw(
                        "JSON_EXTRACT(`{$prefix}items`, '$[*].code')  as `{$prefix}code`"
                    );
                    $header[$key]->index->original = $original;
                }
                if ('serialnumber' === $column->index) {
                    $header[$key]->index = $store->searchTable(false)->raw(
                        "JSON_EXTRACT(`{$prefix}items`, '$[*].serialnumber')  as `{$prefix}serialnumber`"
                    );
                    $header[$key]->index->original = $original;
                }
            }

            $specialRenderer = [
                'decode' => 'json',
                'sqlfield' => 'items',
                'displayfield' => ['sapreturnid','serialnumber', 'code'],
                'separatefield' => ['serialnumber', 'code', 'branchid', 'branchname','status','type','createdon'],
                'isdisplayedinreport' => false
            ];

            $statusRenderer = [
                0 => "Pending",
                1 => "Confirmed",
                2 => "Completed",
                3 => "Failed",
                4 => "Process Delivery",
                5 => "Cancelled",
                6 => "Reversed",
                7 => "Failed Delivery",
                8 => "Success"
            ];

            // $report = $app->reportingManager()->generateExportFile($store,$header, $startDate, $endDate, $params['modulename'], '', '', $conditions, null, null, $statusRenderer,true);
            $report = $app->reportingManager()->generateExportFileWithJsonFields($store, $header, $startDate, $endDate, $params['modulename'], '', '', $conditions, $specialRenderer,null,null,true,$jobMode);
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
                
                $emailSubject = $params['modulename']."_CONVERSION_{$reportDate}";
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
            'modulename' => array('required'=> true, 'type' => 'string', 'desc' => 'for the filename later')
        ];
    }
}
