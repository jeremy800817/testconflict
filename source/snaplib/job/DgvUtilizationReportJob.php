<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;
use Snap\object\Order;
use Snap\object\Redemption;
use Exception;

/**
 *
 * @author Rinston <rinston@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class DgvUtilizationReportJob  extends basejob
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

            $ordstore = $app->orderStore();
            $ordhdl = $ordstore->searchTable(false);

            $redemptionstore = $app->redemptionStore();
            $redemptionhdl = $redemptionstore->searchTable(false);

            $vaultLocation = $app->vaultlocationStore()->searchView()->select()->where('partnerid', $partner->id)->one();
            // $items = $app->vaultitemStore()->searchView()->select()->where('partnerid', $partner->id)->andWhere('vaultlocationid', $vaultLocation->id)->execute();
            $items = $app->vaultitemStore()->searchView()->select()->where('partnerid', $partner->id)->andWhere('status',1)->execute();

            $vault = 0;

            foreach ($items as $item){
                $vault = $item->weight + $vault;
                $vault = $partner->calculator(false)->round($vault);
            }

            $p = $ordstore->getColumnPrefix();
            $companySell = $ordhdl->select([$ordhdl->raw("SUM({$p}xau) AS companysell")])
                ->where('partnerid', $partner->id)
                ->andWhere('type','CompanySell')
                ->andWhere('status', 'IN', [Order::STATUS_CONFIRMED,Order::STATUS_COMPLETED])
                ->one()['companysell'];
            
            $companyBuy = $ordhdl->select([$ordhdl->raw("SUM({$p}xau) AS companybuy")])
                ->where('partnerid', $partner->id)
                ->andWhere('type','CompanyBuy')
                ->andWhere('status', 'IN', [Order::STATUS_CONFIRMED,Order::STATUS_COMPLETED])
                ->one()['companybuy'];

            $q = $redemptionstore->getColumnPrefix();
            $redemption = $redemptionhdl->select([$redemptionhdl->raw("SUM({$q}totalweight) AS redemption")])
                ->where('partnerid', $partner->id)
                ->andWhere('status', 'NOT IN', [Redemption::STATUS_PENDING,Redemption::STATUS_FAILED,Redemption::STATUS_CANCELLED,Redemption::STATUS_REVERSED])
                ->one()['redemption'];
            
            // $utilised = $vault - floatVal($companySell) + floatVal($companyBuy) + floatVal($redemption);
            $utilised = floatVal($companySell) - floatVal($companyBuy) - floatVal($redemption);

            $data['vault'] = $vault;
            $data['companybuy'] = $companyBuy;
            $data['companysell'] = $companySell;
            $data['redemption'] = $redemption;
            $data['utilised'] = $utilised;
            $data['vaultitem'] = $items;
            // $data['vaultlocation'] = $vaultLocation->name;
            $data['vaultlocation'] = 'ACE VAULT';

            $generate = $this->generateExcel($app, $data, $params);
            
        }
        catch(\Throwable $e){
            echo $e->getMessage();
        }
        
    }

    public function generateExcel($app, $data, $params){
        $start = '<table>';
        $header = $this->generateHeader($app);
        $content = $this->generateContent($app, $data);
        $end = '</table>';

        $str = $start.$header.$content.$end;
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString($str);
        
        $reader->setSheetIndex(1);
        $header2 = $this->generateHeaderVault($app, $data);
        $content2 = $this->generateContentVault($app, $data);
        $str2 = $start.$header2.$content2.$end;
        $spreadsheet = $reader->loadFromString($str2, $spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);$spreadsheet->getActiveSheet()->setTitle("Utilization");
        $spreadsheet->setActiveSheetIndex(1);$spreadsheet->getActiveSheet()->setTitle("DGV Kilobar List");
        
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        
        $filename = "DAILY_DGV_".$app->getConfig()->{'projectBase'}."_".date('Ymd').'.xlsx';
        $path = $app->getConfig()->{'mygtp.acereport.dailytransaction'};
        $writer->save($path.$filename);
        
        if($params['email'] == '1'){
            $dt = New \DateTime('now', New \DateTimeZone('UTC'));
            // $dt->setTimeZone(New \DateTimeZone($app->getConfig()->{'snap.timezone.user'}));
            $reportdate = $dt->format('Ymd');

            $subject = "DAILY_DGV_".$app->getConfig()->{'projectBase'} . "_{$reportdate}";
            $bodyEmail = "Please find the attached file " . $filename . " for your reference.";
            
            $sendToEmail = $params['emaillist'];
            $this->sendNotifyEmailReport($app, $bodyEmail, $subject, $sendToEmail, $path.$filename, $filename);
        }
    }

    public function generateHeader($app){
        $dt = New \DateTime('now', New \DateTimeZone('UTC'));
        $dt->setTimeZone(New \DateTimeZone($app->getConfig()->{'snap.timezone.user'}));
        $date = $dt->format('d-m-Y');
        $header = '<tr>';
        $header .= '<td colspan="10" style="font-weight:bold;font-size:14px">DGV Utilization as at '.$date.'</td>';
        $header .= '</tr>';
        $header .= '<tr>';
        $header .= '<td colspan="10"></td>';
        $header .= '</tr>';
        
        $column ='<tr>';
        $column .= '<td style="font-weight:bold;width:250px;background-color:#ADD8E6">PARTNER</td>';
        $column .= '<td style="font-weight:bold;width:250px;background-color:#ADD8E6">TOTAL SELL (g)</td>';
        $column .= '<td style="font-weight:bold;width:250px;background-color:#ADD8E6">TOTAL BUY (g)</td>';
        $column .= '<td style="font-weight:bold;width:250px;background-color:#ADD8E6">TOTAL REDEMPTION</td>';
        $column .= '<td style="font-weight:bold;width:250px;background-color:#ADD8E6">DGV UTILIZED</td>';
        $column .= '</tr>';

        return $header.$column;
    }

    public function generateContent($app, $data){
        $string .= '<tr>';
        $string .= '<td>'.$app->getConfig()->{'projectBase'}.'</td>';
        $string .= '<td>'.$data['companysell'].'</td>';
        $string .= '<td>'.$data['companybuy'].'</td>';
        $string .= '<td>'.$data['redemption'].'</td>';
        $string .= '<td>'.$data['utilised'].'</td>';
        $string .= '</tr>';

        return $string;
    }

    public function generateHeaderVault($app, $data){
        $dt = New \DateTime('now', New \DateTimeZone('UTC'));
        $dt->setTimeZone(New \DateTimeZone($app->getConfig()->{'snap.timezone.user'}));
        $date = $dt->format('Y-m-d H:i:s');
        $header = '<tr>';
        $header .= '<td colspan="10" style="font-weight:bold;font-size:14px">DGV Kilobar as at '.$date.'</td>';
        $header .= '</tr>';
        $header .= '<tr>';
        $header .= '<td colspan="10"></td>';
        $header .= '</tr>';
        
        $column ='<tr>';
        $column .= '<td style="font-weight:bold;width:250px;background-color:#ADD8E6">SERIAL NO</td>';
        $column .= '<td style="font-weight:bold;width:250px;background-color:#ADD8E6">WEIGHT (g)</td>';
        $column .= '<td style="font-weight:bold;width:250px;background-color:#ADD8E6">LOCATION</td>';
        $column .= '<td style="font-weight:bold;width:250px;background-color:#ADD8E6">STATUS</td>';
        $column .= '</tr>';

        return $header.$column;
    }
    public function generateContentVault($app, $data){

        $status = "";
        $string = "";
        foreach($data['vaultitem'] as $record){
            // STATUS_PENDING = 0; 
            // STATUS_ACTIVE = 1; // 
            // STATUS_TRANSFERRING = 2; // transferring to other vault location
            // STATUS_INACTIVE = 3; 
            // STATUS_REMOVED = 4; // 
            // STATUS_PENDING_ALLOCATION = 5; // 
            switch($record->status){
                case '0':
                    $status = "Pending";
                    break;
                case '1':
                    $status = 'Active';
                    break;
                case '2':
                    $status = 'Transferring';
                    break;
                case '3':
                    $status = 'Inactive';
                    break;
                case '4':
                    $status = 'Removed';
                    break;
                case '5':
                    $status = 'Pending Allocation';
                    break;
                default:
                    $status = '';
                    break;
            }
            $string .= '<tr>';
            $string .= '<td>'.$record->serialno.'</td>';
            $string .= '<td>'.$record->weight.'</td>';
            $string .= '<td>'.$data['vaultlocation'].'</td>';
            $string .= '<td>'.$status.'</td>';
            $string .= '</tr>';
        }
        

        return $string;
    }

    public function sendNotifyEmailReport($app,$bodyEmail, $subject, $sendToEmail, $file = null, $attachment = null){
        $mailer = $app->getMailer();
        $emailTo = explode(',', $sendToEmail); //compulsory email

        foreach ($emailTo as $anEmail) {
            $mailer->addAddress($anEmail);
        }

        $mailer->addAttachment($file, $attachment);
        $mailer->Subject = $subject;
        $mailer->Body    = $bodyEmail;
        $mailer->send();
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
            'email' => array('required' => true,  'type' => 'string', 'desc' => 'to send email or not')
        ];
    }
}
