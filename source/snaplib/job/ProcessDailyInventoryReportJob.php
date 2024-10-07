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

/**
 *
 * @author Rinston <rinston@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class ProcessDailyInventoryReportJob  extends basejob
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
            $header = [
                // json_decode('{"text":"GS-999-9-0.5g","index":"GS-999-9-0.5g","decimal":0}'),
                // json_decode('{"text":"GS-999-9-1g","index":"GS-999-9-1g","decimal":0}'),
                // json_decode('{"text":"GS-999-9-2.5g","index":"GS-999-9-2.5g","decimal":0}'),
                // json_decode('{"text":"GS-999-9-5g","index":"GS-999-9-5g","decimal":0}'),
                // json_decode('{"text":"GS-999-9-10g","index":"GS-999-9-10g","decimal":0}'),
                // json_decode('{"text":"GS-999-9-50g","index":"GS-999-9-50g","decimal":0}'),
                // json_decode('{"text":"GS-999-9-100g","index":"GS-999-9-100g","decimal":0}'),
                // json_decode('{"text":"GS-999-9-1000g","index":"GS-999-9-1000g","decimal":0}'),
                json_decode('{"text":"GS-999-9-1-DINAR","index":"GS-999-9-1-DINAR","decimal":0}')
                // json_decode('{"text":"GS-999-9-5-DINAR","index":"GS-999-9-5-DINAR","decimal":0}')
            ];  
            $partner = $params['code'];         
            $apimanager = $app->apiManager();
            $result = $apimanager->sapGetSharedMintedList($partner,'1.0my');        
            
            $content = [];
            foreach($result['denominationList'] as $index => $value){
                foreach($header as $obj){
                    if(!isset($content[$obj->index])) $content[$obj->index] = 0;
    
                    if($index == $obj->index){
                        $content[$obj->index] = $value;
                        continue;
                    }
                }
            }
            $this->generateExcel($app, $content, $result['inventoryList'], $params);
            // $this->getSharedMintedList($app,$params);
        }
        catch(\Throwable $e){
            echo $e->getMessage();
        }
    }

    public function getSharedMintedList($app,$params){
        $header = [
            // json_decode('{"text":"GS-999-9-0.5g","index":"GS-999-9-0.5g","decimal":0}'),
            // json_decode('{"text":"GS-999-9-1g","index":"GS-999-9-1g","decimal":0}'),
            // json_decode('{"text":"GS-999-9-2.5g","index":"GS-999-9-2.5g","decimal":0}'),
            // json_decode('{"text":"GS-999-9-5g","index":"GS-999-9-5g","decimal":0}'),
            // json_decode('{"text":"GS-999-9-10g","index":"GS-999-9-10g","decimal":0}'),
            // json_decode('{"text":"GS-999-9-50g","index":"GS-999-9-50g","decimal":0}'),
            // json_decode('{"text":"GS-999-9-100g","index":"GS-999-9-100g","decimal":0}'),
            // json_decode('{"text":"GS-999-9-1000g","index":"GS-999-9-1000g","decimal":0}'),
            json_decode('{"text":"GS-999-9-1-DINAR","index":"GS-999-9-1-DINAR","decimal":0}')
            // json_decode('{"text":"GS-999-9-5-DINAR","index":"GS-999-9-5-DINAR","decimal":0}')
        ];  
        $partner = $params['code'];         
        $apimanager = $app->apiManager();
        $result = $apimanager->sapGetSharedMintedList($partner,'1.0my');        
        
        $content = [];
        foreach($result['denominationList'] as $index => $value){
            foreach($header as $obj){
                if(!isset($content[$obj->index])) $content[$obj->index] = 0;

                if($index == $obj->index){
                    $content[$obj->index] = $value;
                    continue;
                }
            }
        }
        $this->generateExcel($app, $content, $result['inventoryList'], $params);
    }

    public function generateExcel($app, $data, $inventory, $params){
        $start = '<table>';
        $header = $this->generateHeader($app, $data);
        $content = $this->generateContent($app, $data);
        $end = '</table>';

        $str = $start.$header.$content.$end;
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
        $spreadsheet = $reader->loadFromString($str);

        $reader->setSheetIndex(1);
        $header2 = $this->generateHeaderDeno($app);
        $content2 = $this->generateContentDeno($app, $inventory, $params);
        $str2 = $start.$header2.$content2.$end;
        $spreadsheet = $reader->loadFromString($str2, $spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);$spreadsheet->getActiveSheet()->setTitle("Summary");
        $spreadsheet->setActiveSheetIndex(1);$spreadsheet->getActiveSheet()->setTitle("Serial No");

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

        $filename = $params['modulename'].'_INVENTORY_'.date('Ymd').'.xlsx';
        $path = $app->getConfig()->{'mygtp.acereport.dailytransaction'};
        $writer->save($path.$filename);
        
        if($params['email'] == '1'){
            $dt = New \DateTime('now', New \DateTimeZone('UTC'));
            // $dt->setTimeZone(New \DateTimeZone($app->getConfig()->{'snap.timezone.user'}));
            $reportdate = $dt->format('Ymd');

            $subject = $params['modulename']."_INVENTORY_{$reportdate}";
            $bodyEmail = "Please find the attached file " . $filename . " for your reference.";
            
            $sendToEmail = $params['emaillist'];
            $this->sendNotifyEmailReport($app, $bodyEmail, $subject, $sendToEmail, $path.$filename, $filename);
        }
    }

    public function generateHeader($app, $content){
        $dt = New \DateTime('now', New \DateTimeZone('UTC'));
        $dt->setTimeZone(New \DateTimeZone($app->getConfig()->{'snap.timezone.user'}));
        $date = $dt->format('Y-m-d H:i:s');
        $header = '<tr>';
        $header .= '<td colspan="10" style="font-weight:bold;font-size:14px">Inventory Report as at '.$date.'</td>';
        $header .= '</tr>';
        $header .= '<tr>';
        $header .= '<td colspan="10"></td>';
        $header .= '</tr>';
        
        $column ='<tr>';
        foreach($content as $index => $value){
            $column .= '<td style="font-weight:bold;width:25px;">'.$index.'</td>';
        }
        $column .= '</tr>';

        return $header.$column;
    }

    public function generateContent($app, $content){
        $str = "<tr>";
        foreach($content as $index => $value){
            $str .= "<td>".$value."</td>";
        }
        $str .= "</tr>";

        return $str;
    }

    public function generateHeaderDeno($app){
        $dt = New \DateTime('now', New \DateTimeZone('UTC'));
        $dt->setTimeZone(New \DateTimeZone($app->getConfig()->{'snap.timezone.user'}));
        $date = $dt->format('d/m/Y');
        $header = '<tr>';
        $header .= '<td colspan="10" style="font-weight:bold;font-size:14px">Inventory Report as at '.$date.'</td>';
        $header .= '</tr>';
        $header .= '<tr>';
        $header .= '<td colspan="10"></td>';
        $header .= '</tr>';
        
        $column ='<tr>';
        $column .= '<td style="font-weight:bold;width:25px;">Item No</td>';
        $column .= '<td style="font-weight:bold;width:25px;">Posting Date</td>';
        $column .= '<td style="font-weight:bold;width:25px;">Serial Number</td>';
        $column .= '<td style="font-weight:bold;width:25px;">Whse</td>';
        $column .= '<td style="font-weight:bold;width:25px;">Quantity</td>';
        $column .= '</tr>';

        return $header.$column;
    }

    public function generateContentDeno($app, $content, $params){
        $dt = New \DateTime('now', New \DateTimeZone('UTC'));
        $dt->setTimeZone(New \DateTimeZone($app->getConfig()->{'snap.timezone.user'}));
        $date = $dt->format('d.m.y');

        $whse = '';
        if($params['code'] == 'BURSA') $whse = 'BG_MINT';

        $str = "";
        foreach($content as $index => $value){
            $str .= "<tr>";
            $str .= "<td>".$value['ItemCode']."</td>";
            $str .= "<td>".$date."</td>";
            $str .= "<td>".$value['Serial']."</td>";
            $str .= "<td>".$whse."</td>";
            $str .= "<td>".$value['Total Qty']."</td>";
            $str .= "</tr>";
        }
        
        return $str;
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
            'code' => array('required' => true,  'type' => 'string', 'desc' => 'SAP Entity Code?'),
            'email' => array('required' => true,  'type' => 'string', 'desc' => 'to send email or not'),
            'modulename' => array('required'=> true, 'type' => 'string', 'desc' => 'for the filename later')
        ];
    }
}
