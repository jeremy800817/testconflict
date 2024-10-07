<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\MyAccountHolder;
use Snap\object\AchAdditionalData;
use Snap\object\Partner;
use Snap\object\MyLedger;
use Spipu\Html2Pdf\Html2Pdf;
use Snap\object\MyMonthlyStatementLog;
use Exception;
use Throwable;
use DateTime;
use DateTimeZone;
use Snap\util\tcpdf\StatementBimb;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class myyearlystatementhandler extends CompositeHandler
{
    protected $year;
    function __construct(App $app)
    {
        $this->mapActionToRights('printStatement', '/all/access');
        $this->mapActionToRights('list', '/all/access');

        $this->app = $app;

        $this->currentStore = $app->mymonthlystatementlogStore();
        // $this->sqlRecorder = new sqlrecorder();
        $this->addChild(new ext6gridhandler($this, $this->currentStore, 1));
    }

    public function printStatement($app,$params){
        $response = ['success' => false, 'data' => null, 'message' => 'initialize'];
      
        // print_r($params);exit;
        try{
            if(isset($params['date'])){
                $this->param_date = $params['date'];

                $rdate = New DateTime($params['date']);
                $tdate = $rdate->format('Y-m-t 23:59:59');

                $this->param_enddate = $tdate;
                $this->param_startdate = $rdate->format('Y-m-01 00:00:00');

                $this->date = New Datetime($tdate, new DateTimeZone("UTC"));
            }
            elseif (isset($params['firstdate']) && isset($params['lastdate'])) {
                $firstDate = new DateTime($params['firstdate']);
                $lastDate = new DateTime($params['lastdate']);
                $this->param_startdate = $firstDate->format('Y-m-d');
                $this->param_enddate = $lastDate->format('Y-m-d');

                $year = $firstDate->format('Y');

                $this->logDebug(__METHOD__ . "Print Statement Between" . $this->param_startdate . " - " . $this->param_enddate);

                // If you want to use UTC, you can set the timezone explicitly
                $this->date = new DateTime($this->param_enddate, new DateTimeZone("UTC"));
            }
            else{
                $this->date = New Datetime("last day of previous month", new DateTimeZone("UTC"));
            }

            if($app->getConfig()->{'otc.job.diffserver'} == '1'){
                $partners = $app->partnerStore()->searchTable()->select()->where('group',$params['partner'])->execute();
                $partnerIds = [];
                foreach($partners as $partner){
                    array_push($partnerIds,$partner->id);
                }
            }
            else{
                $partnerIds = explode(',', $params['partner']);
            }
            

            $accountholder = $app->myaccountholderStore()->searchTable()->select()
                //->where('investmentmade', MyAccountHolder::INVESTMENT_MADE)
                // ->andWhere('status', 1)
                ->andWhere('id',$params['id'])
                ->one();
            $branch = $app->partnerStore()->searchTable()->select()->where('id', $accountholder->partnerid)->one();
            $partnerBranchMap = $branch->name;
            if(empty($accountholder)){
                $this->log(__METHOD__."(): AcountHolder for sending email not Found... ", SNAP_LOG_ERROR);
            }

            $return = $this->generateStatement($app, $accountholder);
            if($app->getConfig()->{'projectBase'} == 'ALRAJHI'){
            $html2pdf = new Html2Pdf('P', 'A4', 'en', true, 'UTF-8', 3);
            
            $html2pdf->pdf->setTitle('Account Statement');
			$html2pdf->writeHTML($return);
            }
            if($app->getConfig()->{'projectBase'} == 'BIMB'){
                $this->log(__METHOD__."(): Make PDF for Statement BIMB... ", SNAP_LOG_INFO);
                try {
                    $html2pdf = new StatementBimb(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                    $html2pdf->setHeaderStatement($accountholder,$year,$partnerBranchMap);
                    $html2pdf->SetMargins(10, 40, 10); // Set margins in millimeters (left, top, right)
                    $html2pdf->SetAutoPageBreak(true, 40); // Enable auto page breaks with a margin of 10 mm
                    $html2pdf->SetFooterMargin(40);
                    $html2pdf->setPrintHeader(true);
                    $html2pdf->setPrintFooter(true);
                    $html2pdf->setCellPaddings(0, 0, 0, 0);
                    $html2pdf->AddPage('P', 'A4');
                    $html2pdf->writeHTML($return, true, false, true, false, '');
                    $this->log(__METHOD__."(): SUCCESS Make PDF for Statement BIMB ", SNAP_LOG_INFO);   
                } catch (\Throwable $th) {
                    $this->log(__METHOD__."(): ERROR Make PDF for Statement BIMB : {$th->getMessage()}", SNAP_LOG_INFO);
                }
                             
            }
            try {
                $this->log(__METHOD__."(): DO SAVE PDF TO FOLDER ", SNAP_LOG_INFO);
                $partner = $app->partnerStore()->searchTable()->select()->where('id', $accountholder->partnerid)->one();
                $parcode = "";
                if($partner) $parcode = $partner->code;
                $relationtype = $app->getConfig()->{'gtp.alrajhi.relationshiptypecode'};
                $this->log(__METHOD__."(): TEST1 ", SNAP_LOG_INFO);
                $acc_number = $accountholder->accountholdercode;
                $acc_number = str_replace(' ', '', $acc_number);
                $acc_number = substr_replace($acc_number, 'XXXXX', 5, 5);
                $gii = str_replace('','',$parcode.$relationtype.$acc_number);
                $cic = str_replace('','',$accountholder->partnercusid);
                if($app->getConfig()->{'projectBase'} == 'BIMB'){
                    $firstdayofmonth = $this->param_startdate;
                    $lastdayofmonth = $this->param_enddate;
                }else{
                    $firstdayofmonth = $this->date->format('Ym01');
                    $lastdayofmonth = $this->date->format('Ymt');
                }
                
                $filename = $gii.$cic.$firstdayofmonth.$lastdayofmonth.".pdf";
            
                $path = $app->getConfig()->{'gtp.monthlystatement.savepath'};
                $this->log(__METHOD__."(): TEST 2 ", SNAP_LOG_INFO);
                $filepath = $path.$filename;
                if($app->getConfig()->{'projectBase'} == 'ALRAJHI'){
                    $pass = $this->generatePasswordAlrajhi($accountholder);
                    $html2pdf->pdf->SetProtection('', $pass, null, 0, null);
                }
                if($app->getConfig()->{'projectBase'} == 'BIMB'){
                    $pass = $this->generatePasswordBIMB($app,$accountholder);
                    $html2pdf->SetProtection('', $pass, null, 0, null);
                    $this->log(__METHOD__."(): TEST3  {$filepath}", SNAP_LOG_INFO);
                }
                $html2pdf->output($filepath, 'F');
                $this->log(__METHOD__."(): SUCCESS OUT PDF ", SNAP_LOG_INFO);
            } catch (\Throwable $th) {
                $this->log(__METHOD__."(): ERROR PDF OUTPUT : {$th->getMessage()}", SNAP_LOG_ERROR);
            }
            
            
            // return "/monthlystatement/".$filename;
            $this->log(__METHOD__."(): do Send to Email please Wait... ", SNAP_LOG_INFO);
            $send = $this->sendEmail($app, $accountholder->email,$filepath,$filename,$partner,$accountholder);
            
            if($send){
                $this->log(__METHOD__."(): the email has been sent", SNAP_LOG_INFO);
                $response['success'] = true;
            }
            else{
                Throw new Exception("Email not sent");
            }
        }
        catch(\Throwable $e){
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        echo json_encode($response);
    }

    public function sendEmail($app, $email, $filepath,$filename,$partner,$accountholder){
        if($app->getConfig()->{'projectBase'} == 'ALRAJHI'){
            $bodyEmail = $this->alrajhiEmailBody($app,$accountholder);
            $subject = "GOLDINVEST-i MONTHLY STATEMENT";
        }
        elseif($app->getConfig()->{'projectBase'} == 'BIMB'){
            $bodyEmail = $this->bimbEmailBody($app,$accountholder);
            $subject = "Bank Islam Gold Account-i e-Statement(Year {$this->year})";
        }
        else{
            $bodyEmail = "Attached are the Monthly Statement for your Gold account in ".$this->getMonthYear($app);
            $subject = "Gold Account Statement ".$this->getMonthYear($app);
        }
        
        $sendToEmail = $email;
        return $this->sendNotifyEmailReport($app, $bodyEmail, $subject, $sendToEmail, $filepath, $filename,$partner,$accountholder);
    }

    public function sendNotifyEmailReport($app, $bodyEmail, $subject, $sendToEmail, $file = null, $attachment = null,$partner,$accountholder)
    {
        $mailer = $app->getMailer();
        $mailer->isHTML();
        $emailTo = explode(',', $sendToEmail); //compulsory email

        foreach ($emailTo as $anEmail) {
            $mailer->addAddress($anEmail);
            $this->logDebug(__METHOD__ . " Add email address " . $anEmail . " as recipient for " . $subject);
        }

        $mailer->addAttachment($file, $attachment);
        $mailer->SMTPDebug = 0;
        $this->logDebug(__METHOD__ . " Prepare to send email for " . $subject);
        $mailer->Subject = $subject;
        $mailer->Body    = $bodyEmail;

        if($app->getConfig()->{'otc.ssl.disable'} == 1){
            $mailer->SMTPOptions = array(
                    'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );  
        }

        $now = New \Datetime();
        try {
            if($mailer->send()){
                $log = $app->mymonthlystatementlogStore()->create([
                    "partnerid"  => $partner->id,
                    "accountholderid"    => $accountholder->id,
                    "email"   => $accountholder->email,
                    "filename"  => $attachment,
                    "sendon"    => $now->format('Y-m-d H:i:s'),
                    "status" => 1
                ]);
                $app->mymonthlystatementlogStore()->save($log);
                $this->logDebug(__METHOD__ . " Sending email to recipient is success for " . $subject);
                return true;
            }
            else{
                $log = $app->mymonthlystatementlogStore()->create([
                    "partnerid"  => $partner->id,
                    "accountholderid"    => $accountholder->id,
                    "email"   => $accountholder->email,
                    "filename"  => $attachment,
                    "sendon"    => $now->format('Y-m-d H:i:s'),
                    "status" => 0
                ]);
                $app->mymonthlystatementlogStore()->save($log);
                $this->logDebug(__METHOD__ . " Sending email to recipient is failed for " . $subject);
                return false;
            }
        } catch (\Throwable $th) {
            $this->logDebug(__METHOD__ . " ERROR SENDING EMAIL :" . $th->getMessage());
        }
    }

    public function getMonthYear($app){
        $this->date->setTimezone(new DateTimeZone($app->getConfig()->{'snap.timezone.user'}));
        return $this->date->format("M Y");
    }

    public function getFirstDayOfMonth($app, $type="text"){
        $this->date->setTimezone(new DateTimeZone($app->getConfig()->{'snap.timezone.user'}));
        if($type == "text") return $this->date->format("01/m/Y");
        else return $this->date->format("Y-m-01 00:00:00");
    }

    public function getLastDayOfMonth($app, $type="text"){
        $this->date->setTimezone(new DateTimeZone($app->getConfig()->{'snap.timezone.user'}));
        if($type == "text") return $this->date->format("d/m/Y");
        else return $this->date->format("Y-m-d 23:59:59");
    }

    public function getCurrentDate($app, $type="text"){
        $dt = new DateTime("now", new DateTimeZone($app->getConfig()->{'snap.timezone.Server'}));
        $dt->setTimezone($app->getConfig()->{'snap.timezone.user'});

        if($type == "text") return $dt->format('d/m/Y');
        else return $dt->format('Y-m-d H:i:s');
        
    }

    public function getStatementDate($app, $type="text"){
        $this->date->setTimezone(new DateTimeZone($app->getConfig()->{'snap.timezone.user'}));
        $givenDate = $this->date->format('Y-m-d H:i:s');
        $dateTime = new DateTime($givenDate);

        // Set the date to the first day of the next month
        $dateTime->modify('first day of next month');

        if($type == "text") return $dateTime->format("d/m/Y");
        else return $this->date->format("Y-m-d 00:00:00");
    }

    public function generateStatement($app, $accountholder){
        if($app->getConfig()->{'projectBase'} == 'ALRAJHI') return $this->generateStatementAlrajhi($app, $accountholder);
        if($app->getConfig()->{'projectBase'} == 'BIMB') return $this->generateStatementBIMB($app, $accountholder);
    }

    public function closingBalance($app, $accountholder, $openingBalance){
        if($app->getConfig()->{'projectBase'} == 'BIMB') {
            $startdate = New DateTime($this->param_startdate);
            $enddate = New DateTime($this->param_enddate);
        }else{
            $startdate = New DateTime($this->getFirstDayOfMonth($app, "query"));
            $enddate = New DateTime($this->getLastDayOfMonth($app, "query"));
        }
        $ledger = $app->myledgerStore()->searchView()->select()
            ->where('accountholderid', $accountholder->id)
            ->andWhere('createdon','>=', $startdate->format('Y-m-d H:i:s'))
            ->andWhere('createdon','<=', $enddate->format('Y-m-d H:i:s'))
            ->andWhere('status',1)
            ->execute();

        $xauBalance = $openingBalance;
        foreach($ledger as $ledgers){
            if($ledgers->debit != 0){
                $xauBalance -= $ledgers->debit;
            }else if($ledgers->credit != 0){
                $xauBalance += $ledgers->credit;
            }
        }

        $closingbalance = $xauBalance;

        return $closingbalance;
    }

    public function getTags($app){
        $tag = [];
        if($app->getConfig()->{'projectBase'} == 'ALRAJHI') {
            $tag = ['/##CUSTOMERNAME##/','/##CUSTOMERADDRESS1##/','/##CUSTOMERADDRESS2##/','/##CUSTOMERADDRESS3##/','/##ACCOUNTNO##/','/##STARTDATE##/','/##ENDDATE##/','/##STATEMENTDATE##/','/##OPENING##/','/##CLOSING##/','/##TABLEDATA##/'];
        }
        if($app->getConfig()->{'projectBase'} == 'BIMB') {
            $tag = ['/##CUSTOMERNAME##/','/##CUSTOMERADDRESS1##/','/##CUSTOMERADDRESS2##/','/##CUSTOMERADDRESS3##/','/##ACCOUNTNO##/','/##YEAR##/','/##STATEMENTDATE##/','/##OPENING##/','/##CLOSING##/','/##TABLEDATA##/','/##BRANCH##/','/##TOTALPURCHASE##/','/##TOTALSALE##/'];
        }

        return $tag;
    }

    /*ALRAJHI FUNCTIONS*/
    public function generateStatementAlrajhi($app, $accountholder){
        $templatefile = $app->getConfig()->{'gtp.monthlystatement.template'};
        $html = file_get_contents($templatefile);

        $tags = $this->getTags($app);
        $value = [];
        array_push($value, $accountholder->fullname);
        $address = $app->myaddressStore()->searchTable()->select()->where('accountholderid',$accountholder->id)->orderBy('id', 'DESC')->one();
        array_push($value,$address->line1);
        array_push($value,$address->line2);
        array_push($value,$address->city." ".$address->postcode." ".$address->state);

        $partner = $app->partnerStore()->searchTable()->select()->where('id',$accountholder->partnerid)->one();
        $relationshipcode = $app->getConfig()->{'gtp.alrajhi.relationshiptypecode'};
        $accountno = $partner->code."-".$relationshipcode."-".$accountholder->accountholdercode;
        array_push($value,$accountno);
        array_push($value,$this->getFirstDayOfMonth($app));
        array_push($value,$this->getLastDayOfMonth($app));
        // array_push($value,$this->getCurrentDate($app));
        array_push($value,$this->getStatementDate($app));
        
        if(isset($this->param_startdate)){
            $dt = New DateTime($this->param_startdate);
            // $firstday = $dt->format('Y-m-01 00:00:00');
            $dt2 = $dt->sub(New \DateInterval('P1M'));
            $rdt = $dt2->format('Y-m-t 23:59:59');
            $dt3 = New DateTime($rdt);
        }

        $openingBalance = $app->MyGtpStatementManager()->getStatementOpeningBalance($accountholder, $dt3, null);
        array_push($value, number_format($openingBalance['xaubalance'],6));
        
        $closingBalance = $this->closingBalance($app, $accountholder, $openingBalance['xaubalance']);
        array_push($value,number_format($closingBalance,6));

        $tabledata = $this->generateTableContentAlrajhi($app, $accountholder, $openingBalance['xaubalance']);
        // echo $tabledata;exit;
        array_push($value,$tabledata);

        $html = preg_replace($tags,$value,$html);

        return $html;
    }
    /**
     * BIMB generate Statement
     */
    public function generateStatementBIMB($app, $accountholder){
        try {
            $this->logDebug(__METHOD__ . " Start Generate Statement... ".SNAP_LOG_INFO);
            $year = date('Y');
            $templatefile = $app->getConfig()->{'gtp.monthlystatement.BIMB.template'};
            $html = file_get_contents($templatefile);
            $tags = $this->getTags($app);
            $value = [];
            array_push($value, $accountholder->fullname);
            $address = $app->myaddressStore()->searchTable()->select()->where('accountholderid',$accountholder->id)->orderBy('id', 'DESC')->one();
            array_push($value,$address->line1);
            array_push($value,$address->line2);
            array_push($value,$address->city." ".$address->postcode." ".$address->state);

            $partner = $app->partnerStore()->searchTable()->select()->where('id',$accountholder->partnerid)->one();
            $relationshipcode = $app->getConfig()->{'gtp.alrajhi.relationshiptypecode'};
            $accountno = /*$partner->code."-".$relationshipcode."-".*/$accountholder->accountholdercode;
            array_push($value,$accountno);
            array_push($value,$year);
            // array_push($value,$this->param_startdate);
            // array_push($value,$this->param_enddate);
            // array_push($value,$this->getCurrentDate($app));
            array_push($value,$this->getStatementDate($app));
            $dt3 = New DateTime($this->param_startdate);
            // $dt = New DateTime("-1 months");
            // $dt2 = $dt->format('Y-m-t 23:59:59');
            // $dt3 = New DateTime($dt2);
            $this->logDebug(__METHOD__ . " Get Opening Balance... ".SNAP_LOG_INFO);
            $openingBalance = $app->MyGtpStatementManager()->getStatementOpeningBalance($accountholder, $dt3, null);
            array_push($value, number_format($openingBalance['xaubalance'],6));
            
            $closingBalance = $this->closingBalance($app, $accountholder, $openingBalance['xaubalance']);
            array_push($value,number_format($closingBalance,6));

            $tabledata = $this->generateTableContentBIMB($app, $accountholder, $openingBalance['xaubalance']);
            // echo $tabledata;exit;
            array_push($value,$tabledata);
            $branch = $app->partnerStore()->searchTable()->select()->where('id', $accountholder->partnerid)->one();
            $partnerBranchMap = $branch->name;
            array_push($value,$partnerBranchMap);

            $total = $app->MyGtpStatementManager()->getTotalTransactionPriod($accountholder,$this->param_startdate,$this->param_enddate);
            array_push($value,$total['totalPurchaseOfYear']);
            array_push($value,$total['totalSaleOfYear']);
            $html = preg_replace($tags,$value,$html);
            $this->logDebug(__METHOD__ . " Success Generate Statement... ".SNAP_LOG_INFO);
            return $html;
        } catch (\Throwable $th) {
            return $th->getMessage();
            $this->logDebug(__METHOD__ . " Error Generate Statement {$th->getMessage()} ".SNAP_LOG_ERROR);
        }
        

       
    }
    
    public function generatePasswordAlrajhi($accountholder){
        if($accountholder->accounttype == 24){
            $pass = substr($accountholder->mykadno, -4).substr($accountholder->accountholdercode, -4);
        }
        else{
            $pass = substr($accountholder->partnercusid, -4).substr($accountholder->accountholdercode, -4);
        }

        return $pass;
    }
    public function generatePasswordBIMB($app,$accountholder){
        /***
         * 
            If your name is AXMAD FAZLF BIN MOHD and IC Number is 290111-63-0497, your default password is AX630497.
            If your name is MZCHAEL JOHN and passport number is XJ31305752, your default password is MZ305752.
            If your registered company name is QWFASHION SDN. BHD and company number registered with the Bank is 1187123-X, your default password is QW87123X.
            If your registered company name is GMILLENIUM SDN. BHD and company number registered with the Bank is 4574, your default password is GM4574**.
         */
        $additionalAccount = $app->achadditionaldataStore()->searchTable()->select()
        ->where('accountholderid', $accountholder->id)->one();

        ///IF type register use NRIC (AdditionalAccount type 1)
        if($additionalAccount->idtype == 1){
            $passName = substr($accountholder->fullname, 0, 2);
            $passNumber = substr(str_replace('-', '', $accountholder->mykadno), -6);
            $pass = $passName.$passNumber;
        }
        // WHEN Register with passport (AdditionalAccount type 2)
        elseif($additionalAccount->idtype == 2){
            $passName = substr($accountholder->fullname, 0, 2);
            $passNumber = substr(str_replace('-', '', $accountholder->mykadno), -6);
            $pass = $passName.$passNumber;
        }
        // WHEN Register with COMPANY (AdditionalAccount type 3)
        else {
            $passName = substr($accountholder->fullname, 0, 2);
            $cleanedMykadno = str_replace('-', '', $accountholder->mykadno);
            $passNumber = substr($cleanedMykadno, -6);
            $pass = $passName . $passNumber;
            if (strlen($pass) < 8) {
                $pass = str_pad($pass, 8, '*', STR_PAD_RIGHT);
            }
        }
        $pass = '12345678';
        return $pass;
    }

    public function generateTableContentAlrajhi($app, $accountholder, $openingBalance){
        $startdate = New DateTime($this->getFirstDayOfMonth($app, "query"));
        $enddate = New DateTime($this->getLastDayOfMonth($app, "query"));

        $ledger = $app->myledgerStore()->searchView()->select()
            ->where('accountholderid', $accountholder->id)
            ->andWhere('createdon','>=', $startdate->format('Y-m-d H:i:s'))
            ->andWhere('createdon','<=', $enddate->format('Y-m-d H:i:s'))
            ->andWhere('status',1)
            ->execute();

        $str = "";
        $xauBalance = $openingBalance;
        foreach($ledger as $ledgers){
            $price = true;
            $order = $app->orderStore()->searchTable()->select()
                ->where('partnerrefid', $ledgers->refno)
                ->one();

            if($ledgers->type != MyLedger::TYPE_BUY_CASA && $ledgers->type != MyLedger::TYPE_SELL_CASA){
                $price = false;
            }

            if($ledgers->type == '' || $ledgers->type == null){
                if($order){
                    $price = true;
                }
            }

            
            if($ledgers->debit != 0){
                $xauBalance -= $ledgers->debit;
            }else if($ledgers->credit != 0){
                $xauBalance += $ledgers->credit;
            }

            $type = $ledgers->type;
            if($type == ''){
                if($ledgers->debit != 0){
                    $type = 'SELL';
                }
                else{
                    $type = 'BUY';
                }
            }
            if($ledgers->debit !=0 || $ledgers->credit != 0){
                $str .= '<tr>';
                $str .= '<td style="padding: 8px; border: 1px solid black;">'.$ledgers->transactiondate->format('d/m/Y').'</td>';
                $str .= '<td style="padding: 8px; border: 1px solid black;">'.$type.'</td>';
                $str .= '<td style="padding: 8px; border: 1px solid black;">'.$ledgers->debit.'</td>';
                $str .= '<td style="padding: 8px; border: 1px solid black;">'.$ledgers->credit.'</td>';
                $str .= '<td style="padding: 8px; border: 1px solid black;">'.($price ? ($order->price + $order->discountprice) : "-").'</td>';
                $str .= '<td style="padding: 8px; border: 1px solid black;">'.number_format($xauBalance,6).'</td>';
                $str .= '</tr>';
            } 
        }

        return $str;

    }
    public function generateTableContentBIMB($app, $accountholder, $openingBalance){
        $startdate = New DateTime($this->param_startdate);
        $enddate = New DateTime($this->param_enddate);

        $ledger = $app->myledgerStore()->searchView()->select()
            ->where('accountholderid', $accountholder->id)
            ->andWhere('createdon','>=', $startdate->format('Y-m-d H:i:s'))
            ->andWhere('createdon','<=', $enddate->format('Y-m-d H:i:s'))
            ->andWhere('status',1)
            ->execute();

        $str = "";
        $xauBalance = $openingBalance;
        foreach($ledger as $ledgers){
            $price = true;
            $order = $app->orderStore()->searchTable()->select()
                ->where('partnerrefid', $ledgers->refno)
                ->one();

            // if($ledgers->type != MyLedger::TYPE_BUY_CASA && $ledgers->type != MyLedger::TYPE_SELL_CASA ){
            //     $price = false;
            // }

            if($ledgers->type == '' || $ledgers->type == null){
                if($order){
                    $price = true;
                }
            }

            if($ledgers->debit != 0){
                $xauBalance -= $ledgers->debit;
            }else if($ledgers->credit != 0){
                $xauBalance += $ledgers->credit;
            }

            $type = $ledgers->type;
            if($type == ''){
                if($ledgers->debit != 0){
                    $type = 'SELL';
                }
                else{
                    $type = 'BUY';
                }
            }
            if($ledgers->debit != 0 || $ledgers->credit != 0){
                $purchase = ($ledgers->credit != 0) ? $ledgers->credit:'';
                $sale     = ($ledgers->debit != 0) ? $ledgers->debit:'';
                $valuePrice = 0;
                if($purchase != ''){
                    $price = ($price ? ($order->price + $order->discountprice) : 0);
                    $valuePrice = $price * $purchase;
                }
                if($sale != ''){
                    $price = ($price ? ($order->price + $order->discountprice) : 0);
                    $valuePrice = $price * $sale;
                }
                $str .= '<tr>';
                $str .= '<td style="padding: 8px; border-left: 1px solid black;border-right: 1px solid black;"><p class="c6" style="text-align: center;font-size:12px;line-height: 2"><span class="c4">'.$ledgers->transactiondate->format('d/m/Y').'</span></p></td>';
                $str .= '<td style="padding: 8px; border-left: 1px solid black;border-right: 1px solid black;"><p class="c6" style="text-align: center;font-size:12px;line-height: 2"><span class="c4">'.$ledgers->refno.'</span></p></td>';
                $str .= '<td style="padding: 8px; border-left: 1px solid black;border-right: 1px solid black;"><p class="c6" style="text-align: center;font-size:12px;line-height: 2"><span class="c4">'.$type.'</span></p></td>';
                $str .= '<td style="padding: 8px; border-left: 1px solid black;border-right: 1px solid black;"><p class="c6" style="text-align: center;font-size:12px;line-height: 2"><span class="c4">'.($price ? ($order->price + $order->discountprice) : "-").'</span></p></td>';
                $str .= '<td style="padding: 8px; border-left: 1px solid black;border-right: 1px solid black;"><p class="c6" style="text-align: center;font-size:12px;line-height: 2"><span class="c4">'.$valuePrice.'</span></p></td>';
                $str .= '<td style="padding: 8px; border-left: 1px solid black;border-right: 1px solid black;"><p class="c6" style="text-align: center;font-size:12px;line-height: 2"><span class="c4">'.$sale.'</span></p></td>';
                $str .= '<td style="padding: 8px; border-left: 1px solid black;border-right: 1px solid black;"><p class="c6" style="text-align: center;font-size:12px;line-height: 2"><span class="c4">'.$purchase.'</span></p></td>';
                $str .= '<td style="padding: 8px; border-left: 1px solid black;border-right: 1px solid black;"><p class="c6" style="text-align: center;font-size:12px;line-height: 2"><span class="c4">'.number_format($xauBalance,6).'</span></p></td>';
                $str .= '</tr>';
            } 
        }

        return $str;

    }
    /*ALRAJHI FUNCTIONS*/
    public function alrajhiEmailBody($app, $accountholder){
        $html = "
        <div style='font-size:15px'>
            Dear ".$accountholder->fullname.",<br/><br/>
            GOLDINVEST-i MONTHLY STATEMENT<br/><br/>
        
            We are pleased to enclose your GoldInvest-i account statement for ".$this->getMonthYear($app).".
            <br/><br/> 
            Please note that the eStatement is in PDF format and it has been password protected for security purposes.
            <br/><br/>
            For individual accounts your password shall be 8 digits i.e comprising the last 4 numbers of your Malaysian Identity Card Number (MyKad) or Passport No. as registered with the Bank
            followed by the last 4 digits of your account number.
            <br/><br/>
            E.g. if your registered MyKad Number is XXXXXX-XX-6176 and your account number is XXXXX-XXXXXX0175, your sample password should be 61760175. 
            <br/><br/>
            For individual joint / company / business / club / association account, the password shall comprise the last 4 digits of your Customer Information Code (CIC) and followed by the last 4 digits of your account number.
            <br/><br/>
            Should you have any enquiries, please do not hesitate to contact our 24-hour Customer Care Consultants at +603 2332 6000.
            <br/><br/>
            Yours sincerely
            <br/><br/>
            AL RAJHI BANK (Malaysia)
            <br/><br/>
        </div>
        <div style='font-size:13.333px'>
            Note: This is an auto generated email by Al Rajhi Bank e-Statement service. Please do not reply to this email address.
            <br/>
            Disclaimer
            <br/><br/>
            <span style='text-align:justify'>
                This email and all its attachments are strictly confidential and may contain information privileged to the intended addressee. 
                If you are not the intended addressee, please delete this email and its attachment immediately and do not disclose, copy, circulate 
                or in any other way use or rely on the information contained in this email or any of its attachments. Any unauthorised use or dissemination 
                of this message in whole or in part is strictly prohibited. Emails cannot be guaranteed to be secure or error free as the message and any 
                attachments could be intercepted, corrupted, lost, delayed, incomplete or amended. Al Rajhi Banking &amp; Investment Corporation (M) Berhad 
                does not accept any liability for damage caused by this email or any of its attachments.
            </span>
        </div>";

        return $html;
    }
    /*BIMB FUNCTIONS*/
    public function bimbEmailBody($app, $accountholder){
        $html = "
        <div style='font-size:15px'>
        Dear Valued Customer,<br/><br/>
        We are pleased to enclose your Bank Islam Gold Account-i with account number ending ".substr(str_replace(' ', '',$accountholder->accountholdercode), -4)." e-Statement.
        <br/><br/> 
        For security reasons, the attachment is password protected. Please key in the default password which consists of 8-characters unique password as per the format below to view the attachment:
        <br/><br/>
        <li> <u><b>Character 1 to 2</b></u></li>
        <br/>
        <b>The following entire paragraph aligned with the first letter of the title </b>
        <br/>
        <br/>
        Type the first two characters of your name (in uppercase), as per your Identity Card (for Individual Malaysians), Passport (for Individual Foreigners) or your registered company name with the Registrar of Companies (for Corporations).
        <br/><br/>
        <li> <u><b>Character 3 to 8</b></u></li>
        <br/>
        <b>The following entire paragraph aligned with the first letter of the title </b>
        <br/>
        <br/>
        Type the last 6 digits Identification Document (ID) number (<i>without the dash symbol '-'</i>) - Identity Card (for Individual Malaysians)<sup>1</sup>, Passport (for Individual Foreigners)<sup>2</sup> or Company Number (for Corporations) registered with the Bank<sup>3</sup>.
        <br/><br/>
        Note: If your ID number length is less than 6 digits, you are required to key in * (asterisk) as the last digit(s) to form a 6-digit length<sup>4</sup>.
        <br/><br/>
        Thank you for choosing Bank Islam. Should you have any enquiries, please contact our Contact Centre at +603-26 900 900 or email <a style='color:black;text-decoration: none;'>contactcentre@bankislam.com.my</a>.
        <br/><br/>
        Regards,
        <br/>
        <b>BANK ISLAM MALAYSIA BERHAD</b>
        <br/><br/>
        </div>
        <div style='font-size:13.333px'>
            <i><b><u>NOTICE:</u></b> This email and all its attachments are strictly confidential and may contain information privileged to the intended addressee. If you are not the intended addressee, you are hereby notified that any use, reliance on, reference to, review, disclosure or copying of the email and all its attachments for any purpose is strictly prohibited. If you have received this message in error, please disregard and delete all its content immediately.</i><br/><br/>
            <i>This is an auto-generated message. Please do not reply to this mail.</i>
        </div>
        <br/>
        <hr width='50%' align='left'>
        <div style='font-size:13px; margin-right: 100px;'>
        <ul style='list-style-type: none; padding-left: 0;'>
            <li style='display: flex;'>
                <sup style='margin-right: 5px;align-self:flex-start;'>1</sup>
                <p style='margin: 0;'>If your name is <b>AXMAD FAZLF BIN MOHD</b> and IC Number is <b>290111-63-0497</b>, your default password is <b>AX630497</b>.</p>
            </li>
            <li style='display: flex;'>
                <sup style='margin-right: 5px;align-self:flex-start;'>2</sup>
                <p style='margin: 0;'>If your name is <b>MZCHAEL JOHN</b> and passport number is <b>XJ31305752</b>, your default password is <b>MZ305752</b>.</p>
            </li>
            <li style='display: flex;'>
                <sup style='margin-right: 5px;align-self:flex-start;'>3</sup>
                <p style='margin: 0;'>If your registered company name is <b>QWFASHION SDN. BHD</b> and company number registered with the Bank is <b>1187123-X</b>, your default password is <b>QW87123X</b>.</p>
            </li>
            <li style='display: flex;'>
                <sup style='margin-right: 5px;align-self:flex-start;'>4</sup>
                <p style='margin: 0;'>If your registered company name is <b>GMILLENIUM SDN. BHD</b> and company number registered with the Bank is <b>4574</b>, your default password is <b>GM4574**</b>.</p>
            </li>
        </ul>
        </div>
    </div>";
        return $html;
    }
}
