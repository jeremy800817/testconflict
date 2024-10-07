<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2021
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;
use Snap\object\MyGoldTransaction;
use Snap\object\Order;
use Snap\object\Partner;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author  Cheok <cheok@silverstream.my>
 * @version 1.0
 * @package snap.job
 */
class MyGtpEmailReportJob extends basejob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array())
    {
        
        if (isset($params['partnercode']) && 0 < strlen($params['partnercode'])) {
            
            $partner = $app->partnerStore()->getByField('code', $params['partnercode']);

            if (isset($params['failedregistration']) && 0 < $params['failedregistration']) {
                $this->sendUnsuccessfullRegistrationReport($app, $partner, $params['email']);
            }

            if (isset($params['companybuy'])) {
                $this->sendCompanyBuyReport($app, $partner, $params['email']);
            }
            
        }
    }

    protected function sendUnsuccessfullRegistrationReport($app, Partner $partner, $recipient)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accMgr = $app->mygtpAccountManager();

        $yesterday = new \DateTime('now', $app->getUserTimezone());
        $yesterday->sub(new \DateInterval('P1D'));

        $dateStart = new \DateTime($yesterday->format('Y-m-d 00:00:00'), $app->getUserTimezone());
        $dateStart->setTimezone($app->getServerTimezone());
        
        $dateEnd = new \DateTime($yesterday->format('Y-m-d 23:59:59'), $app->getUserTimezone());
        $dateEnd->setTimezone($app->getServerTimezone());
        
        $projectBase = $app->getConfig()->{'projectBase'};
        
        $report = $accMgr->getUnsuccessfullRegistrationReport($partner, $dateStart, $dateEnd);
        $subject = "{$projectBase} - Daily Unsuccessful Registration Report for {$yesterday->format('Y-m-d')}";
        $body = 'Attached is the list of failed signup report.';
        $attachment = stream_get_meta_data($report)['uri'];
        $attachmentName = "{$projectBase} - Unsuccessful Registration for {$yesterday->format('Y-m-d')}.xlsx";
        $accMgr->sendEmail($subject, $body, $attachment, $attachmentName, $recipient);
    }

    function sendCompanyBuyReport($app, $partner, $email = null)
    {           
        $date = new \DateTime('now', $app->getUserTimezone());
        $dateStart = new \DateTime($date->format('Y-m-d 00:00:00'), $app->getUserTimezone());
        $dateEnd   = new \DateTime($date->format('Y-m-d 23:59:59'), $app->getUserTimezone());            
        $dateStart->setTimezone($app->getServerTimezone());
        $dateEnd->setTimezone($app->getServerTimezone());;
        $formattedDate = $date->format('Ymd');
        $code = $app->getConfig()->{'mygtp.gopayzpayout.code'};        
        $filename = "{$code}{$formattedDate}0001";

        $header     = [
            (object)['text' => 'Date', 'index' => 'createdon'],
            (object)['text' => 'Account Number', 'index' => 'dbmaccountnumber'],
            (object)['text' => 'Customer Code', 'index' => 'achcode'],
            (object)['text' => 'Customer Name', 'index' => 'achfullname'],
            (object)['text' => 'Customer NRIC', 'index' => 'achmykadno'],
            (object)['text' => 'Customer Phone', 'index' => 'achphoneno'],
            (object)['text' => 'Customer Email', 'index' => 'achemail'],
            (object)['text' => 'Type', 'index' => 'ordtype'],
            (object)['text' => 'Product', 'index' => 'ordproductname'],
            (object)['text' => 'Xau Weight (g)', 'index' => 'ordxau', 'decimal' => 3],
            (object)['text' => 'Order Price', 'index' => 'ordprice', 'decimal' => 2],
            (object)['text' => 'Original Amount (RM)', 'index' => 'originalamount', 'decimal' => 2],
            (object)['text' => 'Transaction Fee', 'index' => 'ordfee','decimal' => 2],
            (object)['text' => 'Total Amount (RM)', 'index' => 'ordamount', 'decimal' => 2],
            (object)['text' => 'Order No', 'index' => 'ordorderno'],
            (object)['text' => 'Transaction Reference No', 'index' => 'dbmtransactionrefno'],
            (object)['text' => 'Payment Ref No', 'index' => 'dbmpdtreferenceno'],
            (object)['text' => 'Settlement Method', 'index' => 'settlementmethod'],
            (object)['text' => 'Campaign Code', 'index' => 'campaigncode'],
            (object)['text' => 'Status', 'index' => 'status'],
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
                                        // ->andWhere('createdon', '>=', $dateStart->format('Y-m-d H:i:s')) // removed as we need to include old failed disburment tx as well
                                        ->andWhere('createdon', '<=', $dateEnd->format('Y-m-d H:i:s'));
        };

        $projectBase = $app->getConfig()->{'projectBase'};

        /** @var \Snap\manager\ReportingManager $reportingManager */
        $reportingManager = $app->reportingManager();        
        $report = $reportingManager->generateMyGtpReport($app->mygoldtransactionStore(), $header, $filename, null, $conditions, null, 1, false, true, 'ASC');

        $subject = "{$projectBase} - Daily Payment Request Report ({$dateStart->format('d/m/Y')})";
        $body = 'Attached is the list of payment request sent.';
        $attachment = stream_get_meta_data($report)['uri'];
        $attachmentName = "{$filename}.xlsx";        

        $mailer = $app->getMailer();

        //$emailToAce = $app->getConfig()->{'mygtp.gopayz.reportace'};
        
        if($email != null) $emailTo = explode(',', $email); //compulsory email

        foreach($emailTo as $anEmail){
            $mailer->addAddress($anEmail);
            $this->logDebug(__METHOD__." Add email address ".$anEmail." as recipient for ".$subject);
        }

        $mailer->addAttachment($attachment, $attachmentName);
        $mailer->Subject = $subject;
        $mailer->Body    = $body;
        if($email != null) $mailer->send();
    }

    function describeOptions()
    {
        return [
            'partnercode' => array('required' =>  true,  'type' => 'int', 'desc' => 'The partner code'),
            'companybuy' => array('required' =>  false,  'type' => 'int', 'desc' => 'The type of report to send'),
            'failedregistration' => array('required' =>  false,  'type' => 'int', 'desc' => 'The type of report to send'),
        ];
    }
}
