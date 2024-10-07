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

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author Nurdianah <dianah@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class ProcessBulkPayment extends basejob {

    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        //default auto job - php ../cli.php -f ProcessBulkPayment.php -c ../../snapapp/gopayz.ini -p "partnerid=3513348"
        //when do manually, IMPORTANT to add "manual=1" to skip date checking.
        //if have range time, add "starttime=hh:ii:ss&endtime=hh:ii:ss". Please customize the time using GMT/user time.If want to have range time from 8:30am to 2pm, it become "&starttime=08:30:00&endtime=14:00:00"
        //situation 1 (if want to get only data at certain date) - php ../cli.php -f ProcessBulkPayment.php -c ../../snapapp/gopayz.ini -p "partnerid=3513348&date=2022-04-28&manual=1"
        //situation 2 (if want to get data at range date) - php ../cli.php -f ProcessBulkPayment.php -c ../../snapapp/gopayz.ini -p "partnerid=3513348&datestart=2022-04-26&dateend=2022-04-28&manual=1"
        /*
        situation 3 (easigold case) Two times process. Therefore, we prepare time range parameter (startime & endtime) & also process at same day parameter (firstprocess).
        Current request:
        1st process - time range 00:00:00 - 14:00:00 - process same day - php ../cli.php -f ProcessBulkPayment.php -c ../../snapapp/gopayz.ini -p "partnerid=3513348&endtime=14:00:00&firstprocess=1"
        2nd process - time range 14:01:00 - 23:59:59 - process next day - php ../cli.php -f ProcessBulkPayment.php -c ../../snapapp/gopayz.ini -p "partnerid=3513348&starttime=14:01:00"
        */
        //if partner share same sapcode,same config, please add 'customizepartner' parameter to customize file name to avoid overwrite because share same name. Example: 'partnerid=1&customizepartner=PITIHEMASMASTER'

        if(!defined('SNAPAPP_DBACTION_USERID')) define('SNAPAPP_DBACTION_USERID', 2);
        $continue       = true;
        $now            = new \DateTime('now', $app->getUserTimezone());
        $nowDate        = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone()); 
        $genDate        = $nowDate->format('Y-m-d H:i:s'); //GMT
        $customizeDate  = $nowDate->format('Y-m-d'); //GMT
        //$genDate        = '2022-05-19 00:15:00'; //for testing
        //$customizeDate  = '2022-05-19';
        //$dateTime       = $nowDate->format('Y-m-d H:i:s'); //GMT

        if(isset($params['partnerid'])) $partnerid = $params['partnerid'];
        $partner        = $app->partnerStore()->getById($partnerid);
        if($partner->sapcompanysellcode1 != '') $pCode = $partner->sapcompanysellcode1;

        if(isset($params['emailto'])) $emailto = $params['emailto'];
        if(isset($params['manual'])) $manual = $params['manual'];
        else $manual = 0;

        if(isset($params['firstprocess'])) $firstprocess = $params['firstprocess'];
        else $firstprocess = 0;

        if(isset($params['addname'])) $addname = $params['addname'];

        //Take note: This parameter is use if partner request to generate file for certain time range.
        //Example: easigold request to generate 2 times. 08:30am to 02:00pm & 02:01pm to 11:59pm 
        //If parameter not exist, will use default time which is 00:00:00 till 23:59:59
        if(isset($params['starttime'])) $starttime = $params['starttime'];
        else $starttime = "00:00:00";
        if(isset($params['endtime'])) $endtime = $params['endtime'];
        else $endtime = "23:59:59";

        //1 Important - generate manual, please add date parameter for data user want to grab. 
        //No need to add but if generate MANUAL, it is MANDOTARY
        //Example: Currentdate is 01/03/2022 but user want to get yesterday data, add "date=2022-02-28"
        if(isset($params['date'])) {
            $startDate  = $params['date']." ".$starttime; // Y-m-d 00:00:00
            $endDate    = $params['date']." ".$endtime;
            $dataDateFile       = date("dmY", strtotime($startDate));
        } 

        //2 Important - if using range date, startdate and enddate, prepare data based on range date
        //No need to add but if generate MANUAL, it is MANDOTARY
        //Example: "datestart=2022-02-01&dateend=2022-02-28"
        if(isset($params['datestart']) && isset($params['dateend'])) {
            $startDate  = $params['datestart']." ".$starttime; // Y-m-d 00:00:00
            $endDate    = $params['dateend']." ".$endtime; // Y-m-d 23:59:59
            $dataDateFile       = date("dmY", strtotime($startDate))."-".date("dmY", strtotime($endDate));
        }

        //3 Important - if using current date, prepare data previous date. Default cronjob without date parameter. Currently run at 12:15am everyday.
        if(!isset($params['date']) && !isset($params['datestart']) && !isset($params['dateend'])){
            if(!$firstprocess) $customizeDate       = date('Y-m-d', strtotime('-1 days', strtotime($genDate)));  //GMT
            $startDate      = $customizeDate." ".$starttime;
            $endDate        = $customizeDate." ".$endtime;
            $dataDateFile   = date("dmY", strtotime($genDate));
        }

        //IMPORTANT NOTE: FOR 1,2 and 3 ABOVE, only need to use one. 3 will be default(no date parameter) because it cronjob running. Use either 1 or 2 if need to generate manually.

        //if need to choose only certain transactions, add parameter "refno=xxxxx"
        //using value from "dbm_transactionrefno" at mydisbursement table
        //if have multiple refno, separate it using comma. Exp: "refno=xxxxx,yyyy,dddd,ffff"
        if(isset($params['refno'])) {
            $refno      = $params['refno']; // Y-m-d 00:00:00
            $array      = explode(',', $refno);
        }

        /*get holiday date*/
        $calendarStore        = $app->calendarStore()->searchTable()->select()->where('status',1)->execute(); //active
        if(count($calendarStore) > 0){
            foreach($calendarStore as $key=>$aCalendar){
                $arrayCalendar[]    = $aCalendar->holidayon->format('Y-m-d');
            }
        }

        /*check if Sat / Sun / Holiday, no need to run*/
        $timestamp      = strtotime($genDate);
        $day            = date('w', $timestamp); // if 0=Sun,1=Mon,2=Tue,3=Wed,4=Thu,5=Fri,6=Sat
        $currentdate    = date('Y-m-d', $timestamp);
        $maxHoliday     = 20;

        if(!$manual){ // add manual parameter to skip checking date
            //cronjob run
            if(!in_array($currentdate,$arrayCalendar)){ //if it is not holiday, continue process
                if($day != 6 && $day != 0 ){ //if it is not Sat/Sunday
                    /*Check if there is holiday before currentdate*/
                    for($i = 1; $i <= $maxHoliday; $i++){
                        if(!$firstprocess) $currentdateLoop = date('Y-m-d', strtotime('-'.$i.' days', strtotime($currentdate)));
                        else $currentdateLoop = $currentdate;
                        $startDate          = $currentdateLoop;
                        $checkingDate       = strtotime($startDate);
                        $checkingDateDay    = date('w', $checkingDate);
                        if(!in_array($startDate,$arrayCalendar) && $checkingDateDay != 6 && $checkingDateDay != 0 ) break; //break the loop when manage to get start date before holiday excluding sat&sun
                    }
                    $finalDate = $startDate; //get last date before holiday
                    $startDate = $finalDate." ".$starttime;
                    $this->logDebug(__METHOD__." Bulk payment - Final date for startdate of bulkpayment is ".$startDate);
                } else {
                    $continue = false;
                    $this->logDebug(__METHOD__." Bulk payment - Skip process because it is Sat & Sun. ");
                }
            } else {
                $continue = false;
                $this->logDebug(__METHOD__." Bulk payment - Skip process because it is holiday. ");
            }
        } 
        $this->logDebug(__METHOD__." Bulk payment - Today ".$genDate." : start date is ".$startDate." & end date is ".$endDate);
        /*end checking Sat/Sun/Holiday*/

        /*file details*/
        if(isset($params['customizepartner'])) $partnernametouse = $params['customizepartner'];
        else $partnernametouse = $pCode;
        $generatename       = 'bulkpayment_'.$dataDateFile.'_'.$partnernametouse.$addname;
        $file               = $app->getConfig()->{'gtp.bulkpayment.temp'}.$generatename.".TXT";
        $attachment         = $generatename.".TXT";
        $conditions         = ['partner' => $partner,'refno' => $array,'filename' => $generatename,'generatedate' => $genDate,'customizepartner' => $partnernametouse];
        $getManager         = $app->mygtpdisbursementManager();

        if($continue){
            $getManager->generateBulkPaymentReport($startDate,$endDate,$conditions,$genDate);

            $bodyEmail = "Please refer to attachment for RC-GEN file.";
            $subject = "RC-GEN FILE ".$partnernametouse;

            /*send email*/
            if($emailto){
                $mailer = $app->getMailer();
                $emailTo = explode(',', $emailto); //compulsory email

                foreach($emailTo as $anEmail){
                    $mailer->addAddress($anEmail);
                    $this->logDebug(__METHOD__." Bulk payment - Add email address ".$anEmail." as recipient for ".$subject);
                }

                $mailer->addAttachment($file,$attachment);
                $this->logDebug(__METHOD__." Bulk payment - Prepare to send email for ".$subject);
                $mailer->Subject = $subject;
                $mailer->Body    = $bodyEmail;
                $mailer->send();
                $this->logDebug(__METHOD__." Bulk payment - Sending email to recipient is success for ".$subject);
            }
        }
    }

    function describeOptions() {
    }

}

?>