<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2021
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use DateTime;
use Exception;
use Snap\api\payout\BasePayout;
use Snap\App;
use Snap\manager\MyGtpDisbursementManager;
use Snap\object\MyAccountHolder;
use Snap\object\MyDisbursement;
use Snap\object\MyGoldTransaction;
use Snap\object\Order;
use Snap\object\Partner;
use stdClass;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author  Azam <azam@silverstream.my>
 * @version 1.0
 * @package snap.job
 */
class MyGtpGenerateReportJob extends basejob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array())
    {
        if (! isset($params['partnercode']) || 0 == strlen($params['partnercode'])) {
            $this->logDebug(__METHOD__ . '(): No partner code was given');
            return;
        }

        try {
            $partnercode = $params['partnercode'];            
            $partner = $app->partnerStore()->getByField('code', $partnercode);
            
            $now = new \DateTime('now',$app->getUserTimezone());
            $dateStart = new \DateTime($now->format('Y-m-d 00:00:00'), $app->getUserTimezone());
            $dateEnd   = new \DateTime($now->format('Y-m-d 23:59:59'), $app->getUserTimezone());            
            
    
            $dateStart->setTimezone($app->getServerTimezone());
            $dateEnd->setTimezone($app->getServerTimezone());
            
            if (isset($params['report']) && 'gopayzcompanybuy' === $params['report']) {
                $dateStart->modify('-1 day');
                $dateEnd->modify('-1 day');

                $this->gopayzCompanyBuyReport($app, $partner, $dateStart, $dateEnd);
                $this->log('Finished generating report for Go Payz CompanyBuy Transactions');
            }
          

        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Error when trying to generate report file for partner ({$partnercode}), " . $e->getMessage(), SNAP_LOG_ERROR);
        }
       
    }



    function describeOptions()
    {
        return [
            'partnercode' => ['required' => true, 'type' => 'string', 'desc' => "Partner code to get the payment provider setting."],            
            'report' => ['required' => true, 'type' => 'string', 'desc' => "The type of report to generate."],
        ];
    }

    function gopayzCompanyBuyReport($app, $partner, $dateStart, $dateEnd)
    {   
        $date = new \DateTime('now', $app->getUserTimezone());
        $formattedDate = $date->format('Ymd');
        $code = $app->getConfig()->{'mygtp.gopayzpayout.code'};        
        $requestPath = $app->getConfig()->{'mygtp.gopayzpayout.ftp.reportpath'};
        $pattern = $requestPath . DIRECTORY_SEPARATOR . "{$code}{$formattedDate}*.xlsx";
        $files = glob($pattern);        
        $sequence = sprintf('%04d', max(count($files), 1));
        $modulename = "{$code}{$formattedDate}{$sequence}";

        $header     = [
            (object)['text' => 'Date', 'index' => 'createdon'],
            (object)['text' => 'Account Number', 'index' => 'dbmaccountnumber'],
            (object)['text' => 'Customer Name', 'index' => 'achfullname'],
            (object)['text' => 'Customer Code', 'index' => 'achcode'],
            (object)['text' => 'Customer NRIC', 'index' => 'achmykadno'],
            (object)['text' => 'Customer Phone', 'index' => 'achphoneno'],
            (object)['text' => 'Customer Email', 'index' => 'achemail'],
            (object)['text' => 'Booking On', 'index' => 'ordbookingon'],
            (object)['text' => 'Order No', 'index' => 'ordorderno'],
            (object)['text' => 'Order Price', 'index' => 'ordprice', 'decimal' => 2],
            (object)['text' => 'Xau Weight (g)', 'index' => 'ordxau', 'decimal' => 3],
            (object)['text' => 'Total Amount (RM)', 'index' => 'ordamount', 'decimal' => 2],
            (object)['text' => 'Product', 'index' => 'ordproductname'],
            (object)['text' => 'Ace Buy/Sell', 'index' => 'ordtype'],
            (object)['text' => 'Status', 'index' => 'status'],
            (object)['text' => 'Transaction Fee', 'index' => 'ordfee','decimal' => 2],
            (object)['text' => 'Original Amount (RM)', 'index' => 'originalamount', 'decimal' => 2],
            (object)['text' => 'Transaction Reference No', 'index' => 'dbmtransactionrefno'],
            (object)['text' => 'Payment Ref No', 'index' => 'dbmpdtreferenceno'],
            (object)['text' => 'Settlement Method', 'index' => 'settlementmethod'],
            (object)['text' => 'Campaign Code', 'index' => 'campaigncode']
        ];

        $prefix = $app->mygoldtransactionStore()->getColumnPrefix();

        foreach ($header as $key => $column) {
            // Overwrite index value with expression
            $original = $column->index;            
            if ('status' === $column->index) {
                $header[$key]->index = $app->mygoldtransactionStore()->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_PENDING_PAYMENT . " THEN 'Pending Payment'" .
                    "WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_CONFIRMED . " THEN 'Confirmed'" .
                    "WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_PAID . " THEN 'Paid'" .
                    "WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_FAILED . " THEN 'Failed'" .
                    "WHEN `{$prefix}status` = " . MyGoldTransaction::STATUS_REVERSED . " THEN 'Reversed'" .
                    "ELSE 'UNKNOWN' END as `{$prefix}status`"
                );
                $header[$key]->index->original = $original;
            }
        }

        
        $conditions = function ($query) use ($dateStart, $dateEnd, $partner) {
            $query->where('ordpartnerid', $partner->id)
                                        ->whereIn('status', [MyGoldTransaction::STATUS_CONFIRMED, MyGoldTransaction::STATUS_PENDING_PAYMENT])
                                        ->andWhere('ordtype', Order::TYPE_COMPANYBUY)
                                        ->whereIn('ordstatus', [Order::STATUS_CONFIRMED, Order::STATUS_PENDING])
                                        ->andWhere('settlementmethod', MyGoldTransaction::SETTLEMENT_METHOD_WALLET)                                        
                                        ->andWhere('createdon', '>=', $dateStart->format('Y-m-d H:i:s'))
                                        ->andWhere('createdon', '<=', $dateEnd->format('Y-m-d H:i:s'));
        };


        /** @var \Snap\manager\ReportingManager $reportingManager */
        $reportingManager = $app->reportingManager();
        ob_start();       
        $reportingManager->generateMyGtpReport($app->mygoldtransactionStore(), $header, $modulename, null, $conditions, null, 1, false);
        $content = ob_get_contents(); 
        file_put_contents($requestPath . DIRECTORY_SEPARATOR . $modulename . '.xlsx', $content);
        ob_end_clean();
    }
}
