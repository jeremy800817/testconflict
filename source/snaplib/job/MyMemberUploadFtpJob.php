<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;
use Snap\object\MyMemberUpload;
use Snap\object\MyOccupationCategory;
use Snap\object\MyAccountHolder;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Keys\Version1\AsymmetricPublicKey;
use ParagonIE\Paseto\Keys\Version1\AsymmetricSecretKey;
use ParagonIE\Paseto\Protocol\Version1;

    use ParagonIE\Paseto\Builder;
    use ParagonIE\Paseto\Purpose;
    use ParagonIE\Paseto\Parser;
    use ParagonIE\Paseto\Rules\{
        IssuedBy,
        ValidAt
    };
    use ParagonIE\Paseto\ProtocolCollection;

use Exception;

/**
 *
 * @author Rinston <rinston@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class MyMemberUploadFtpJob  extends basejob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()){

        try{
            $postdata = [
                "version" => "1.0my",
                "merchant_id" =>"BSN@UAT",
                "action" => "vault_store_inventory"
            ];
        
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
            ];
        
            $client         = new \GuzzleHttp\Client($options);
            // $url            = "https://gtp2uat.ace2u.com/mygtp.php?";
            $url            = "http://mbsbgolduatapi.ace2u.com/mygtp.php";
            $response       = $client->post($url, ['json' => $postdata]);
    
    
    
            // print_r($response->getBody()->getContents());
            // exit;
    
            print_r(json_decode($response->getBody()->getContents(),true));
            exit;
        }
        catch(\Throwable $e){
            echo $e->getMessage();
            exit;
        }





    echo "Starting MyMemberUploadFtpJob... \n";
        $this->log("Starting MyMemberUploadFtpJob... ", SNAP_LOG_INFO);

        try{
            echo "checking prerequisites...\n";
            if($app->getConfig()->{'mygtp.mbsb.ftp.directory'} == ''){
                Throw new exception("Config ftp Base Directory Not found");
            }
            if(!is_dir($app->getConfig()->{'mygtp.mbsb.ftp.directory'})){
                Throw new exception("Base Directory not exist");
            }

            if($app->getConfig()->{'mygtp.mbsb.ftp.privatekey'} == ''){
                Throw new exception('ftp Config Paseto key not found');
            }
            if(!file_exists($app->getConfig()->{'mygtp.mbsb.ftp.privatekey'})){
                Throw new exception('ftp Paseto key file not found');
            }

            if($app->getConfig()->{'mygtp.mbsb.ftp.done.directory'} == ''){
                Throw new exception('Config ftp done directory not found');
            }
            if(!is_dir($app->getConfig()->{'mygtp.mbsb.ftp.done.directory'})){
                Throw new exception("Done Directory not exist");
            }
            
            if($app->getConfig()->{'mygtp.mbsb.ftp.url'} == ''){
                Throw new exception('Config ftp base url not found');
            }


            $partner = $app->partnerStore()->getByField('id', $params['partner']);
            
            // $baseDirectory = dirname(__DIR__,3).'\\ftpdoc'; 
            $baseDirectory = $app->getConfig()->{'mygtp.mbsb.ftp.directory'};

            $scan = scandir($baseDirectory);
            $files = array_values(array_diff($scan, array('..', '.')));

            $counter = 0;
            foreach($files as $filename){
                $directory = $baseDirectory.'\\'.$filename;
                if(!is_dir($directory)){
                    echo "executing ".$directory." ... \n";
                    $this->log("executing ".$directory." ... ", SNAP_LOG_INFO);

                    $bool = is_readable($directory);
                    $ext = pathinfo($directory, PATHINFO_EXTENSION);
                    
                    $data = [];
                    if($bool && (strtolower($ext) == 'xls' || strtolower($ext) == 'xlsx' || strtolower($ext) == 'csv')){
                        $data = $this->readExcelFTPFile($app, $directory, $partner);
                        // $data = $this->readFTPFile($app, $directory, $partner);

                        // $doneDirectory = $app->getConfig()->{'mygtp.mbsb.ftp.done.directory'};
                        // $new_name = $doneDirectory.'\\'.$filename;
                        // rename($directory,$new_name);

                        // if(is_readable($new_name)){
                        //     $bool = true;
                        // }
                        // else{
                        //     $bool = false;
                        // }
                    }
                    $counter++;
                }
            }

            echo $counter." file(s) read. ".$data." record(s) processed\n";
            $this->log($counter." file(s) read. ".$data." record(s) processed", SNAP_LOG_INFO);
        }
        catch(Exception $e){
            echo "ERROR: ".$e->getMessage()."\n";
            $this->log("ERROR: ".$e->getMessage(), SNAP_LOG_ERROR);
        }
        echo "End MyMemberUploadFtpJob... \n";
        $this->log("End MyMemberUploadFtpJob... ", SNAP_LOG_INFO);
    }

    public function readFTPFile($app, $file, $partner){
        $cacher = $app->getCacher();

        $myfile = fopen($file, "r") or die("Unable to open file!");

        $data = [];
        while($line = fgets($myfile)){
            $record = explode("|",$line);
            if(strtolower($record['0']) == 'detail'){
                $dt['memberuniqueid'] = $record[1];
                $dt['name'] = $record[2];
                $dt['ic'] = $record[3];
                $dt['contact'] = $record[4];
                $dt['email'] = $record[5];
                $dt['pfi_credit'] = $record[6];
                $dt['address1'] = $record[7];
                $dt['address2'] = $record[8];
                $dt['address3'] = $record[9];
                $dt['address4'] = $record[10];
                $dt['postcode'] = $record[11];
                $dt['state'] = $record[12];
                $dt['requestdate'] = $record[13];
                $dt['branchid'] = $record[14];
                $dt['sellerid'] = $record[15];

                $memberupload = $app->mymemberuploadStore()->searchTable()->select()->where('ic', $dt['ic'])->andWhere('partnerid', $partner->id)->orderBy('id', 'desc')->one();
                if($memberupload){
                    $memberstatus = $memberupload->status;
                    if(isset($memberstatus) && $memberstatus > 0){
                        $dt['chk_status'] = 2;
                    }else{
                        $dt['chk_status'] = 0;
                    }
                }else{
                    $dt['chk_status'] = 1;
                }

                array_push($data,$dt);
            }
        }
        fclose($myfile);
        
        $rowcount= 0;
        foreach($data as $index => $item){
            if ($item['chk_status'] == 1) {
                $memberupload = $app->mymemberuploadStore()->create([
                    'partnerid'     => $partner->id,
                    'memberuniqueid'=> $item['memberuniqueid'],
                    'name'          => $item['name'],
                    'ic'            => $item['ic'],
                    'contact'       => $item['contact'],
                    'email'         => $item['email'],
                    'address_line1' => $item['address1'],
                    'address_line2' => $item['address2'],
                    'address_line3' => $item['address3'],
                    'address_line4' => $item['address4'],
                    'postcode'      => $item['postcode'],
                    'state'         => $item['state'],
                    'pfi_credit'    => $item['pfi_credit'],
                    'requestdate'   => $item['requestdate'],
                    'branchid'      => $item['branchid'],
                    'sellerid'      => $item['sellerid'],
                    'status'        => MyMemberUpload::STATUS_UNMAPPED,
                ]);

                $memberupload = $app->mymemberuploadStore()->save($memberupload);

                if($memberupload){
                    $rowcount++;

                    $plaintext = json_encode($item);

                    $payload = $this->signData($app, $plaintext);
                    // $this->verifySignedData($payload);

                    // send email
                    try{
                        if($item['email'] != '' && $item['email'] != NULL){
                            $mailer = $app->getMailer();
                    
                            $mailer->addAddress($item['email']);
    
                            $mailer->isHtml(true);
                            $email = $partner->senderemail;
                            $name = $partner->sendername;
    
                            // $mailer->setFrom($email, $name);
                            if(strlen($email) > 0 && strlen($name) > 0){
                                $mailer->setFrom($email, $name);
                            }

                            $baseurl = $app->getConfig()->{'mygtp.mbsb.ftp.url'};
                            $url = $baseurl.$payload;
                            $mailer->Subject = 'M-Prime Gold-i Program';
                            $mailer->Body    = $this->getEmailBodyFTPMBSB($memberupload->name, $url);
                            
                            if(!$mailer->send()){
                                // do nothing
                            }
                        }
    
                        // send sms
                        if($item['contact'] != '' && $item['contact'] != NULL){
                            $phone = $item['contact'];
                            if(substr($phone,0,1) == '+'){
                                $phone = str_replace('+',"",$item['contact']);
                            } 
    
                            if(substr($phone,0,1) != '6'){
                                $phone = '6'.$phone;
                            }
    
                            if(strlen($phone) > 10){
                                $sms = new \Snap\manager\SmsManager($app);
                                $sms->phoneNumber = $phone;
                                $sms->messagePayLoad = $this->getSMSBodyFTPMBSB($url);
                                if (!$sms->send()){
                                    // throw new \Exception("Unable to sent SMS number: ".$sms->phoneNumber." payload: ".$sms->messagePayLoad);
                                }else{
                                    // $this->log("SMS service: ".$receiver." : success", SNAP_LOG_DEBUG);
                                }
                            }
                        }
                    }
                    catch(Exception $f){
                        // Get previously created member
                        $createdMember = $app->mymemberuploadStore()->getById($memberupload->id);
                        $createdMember->status = MyMemberUpload::STATUS_SMSOREMAILFAILED;
                        $createdMember = $app->mymemberuploadStore()->save($createdMember);

                        $this->log("ERROR: ".$createdMember->email." __ ".$createdMember->contact." => ".$f->getMessage(), SNAP_LOG_ERROR);
                        echo "ERROR: ".$createdMember->email." __ ".$createdMember->contact." => ".$f->getMessage()."\n";
                    }
                    
                }
                else{
                    // do nothing
                }
            }
        }
        return $rowcount;
    }

    public function readExcelFTPFile($app, $file, $partner){
        $cacher = $app->getCacher();

        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file);
        // print_r($inputFileType);exit;
        
        if ($inputFileType == 'Xls'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        }
        if ($inputFileType == 'Xlsx'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        if ($inputFileType == 'Csv'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        }
        if (!$reader){
            throw error;
        }
        
        $spreadsheet = $reader->load($file);
        
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        
        $items_array = [];
        
        // fix memory leak on branches from cache
        $buyback = false;
        $tender = false;

        // Init counter
        $counter = 0;
        $excel_file_section = null;
        
        foreach ($sheetData as $x => $row){
            $data = [];
            if ($row["A"] >= 1){
                if ($row["A"] == 1){
                    if ($excel_file_section == null){
                        $excel_file_section = 'loan';
                    }else{
                        $excel_file_section = 'empty';
                    }
                }

                if ($excel_file_section == 'loan'){
                    $data['index'] = $row["A"];
                    $data['memberuniqueid'] = $row["B"];
                    $data['name'] = $row["C"];
                    $data['ic'] = $row["D"]; // buybackno
                    $data['contact'] = $row["E"];
                    $data['email'] = $row["F"];
                    $data['pfi_credit'] = $row["G"];
                    $data['address_line1'] = $row["H"];
                    $data['address_line2'] = $row["I"];
                    $data['address_line3'] = $row["J"];
                    $data['address_line4'] = $row["K"];
                    $data['postcode'] = $row["L"];
                    $data['state'] = $row["M"];
                    $data['requestdate'] = $row["N"];
                    $data['branchid'] = $row["O"];
                    $data['sellerid'] = $row["P"];

                    
                    // Check if NRC has account
                    // $accountHolder = $app->myaccountholderStore()->getById($params['id']);
                    $memberupload = $app->mymemberuploadStore()->searchTable()->select()->where('ic', $data['ic'])
                    ->andWhere('partnerid', $partner->id)
                    ->orderBy('id', 'desc')->one();
                    if($memberupload){
                        // If true, check for memberstatus // Perform member status check // If memberstatus is declared and not null or 0
                        $memberstatus = $memberupload->status;
                        if(isset($memberstatus) && $memberstatus > 0){
                            // Member has been mapped before // Reject! // $item_array['chk_status'] = 0;
                            $data['chk_status'] = 2;
                        }else{
                            // Member exist but status unmapped
                            $data['chk_status'] = 0;
                        }
                        // $item_array['member'] = $memberupload;
                    }else{
                        // if no record, then save said record
                        $data['chk_status'] = 1;
                    }

                }
                
                $counter++;
                $items_array[$counter] = $data;

                //limit the total record or it will memory limit exhausted
                //updated: removed because it seems if using csv file would not get the memory limit exhausted error (concluded after done few trial)
                // if($counter > 1100){exit;}
            }
        }

        $rowcount = 0;
        foreach($items_array as $item){
            if($item['chk_status'] == 1){
                $memberupload = $app->mymemberuploadStore()->create([
                    'partnerid'     => $partner->id,
                    'memberuniqueid'=> $item['memberuniqueid'],
                    'name'          => strtoupper($item['name']),
                    'ic'            => $item['ic'],
                    'contact'       => $item['contact'],
                    'email'         => $item['email'],
                    'address_line1' => strtoupper($item['address_line1']),
                    'address_line2' => strtoupper($item['address_line2']),
                    'address_line3' => strtoupper($item['address_line3']),
                    'address_line4' => strtoupper($item['address_line4']),
                    'postcode'      => $item['postcode'],
                    'state'         => strtoupper($item['state']),
                    'pfi_credit'    => $item['pfi_credit'],
                    'requestdate'   => $item['requestdate'],
                    'branchid'      => $item['branchid'],
                    'sellerid'      => $item['sellerid'],
                    'status'        => MyMemberUpload::STATUS_UNMAPPED
                ]);

                $memberupload = $app->mymemberuploadStore()->save($memberupload);

                if($memberupload){
                    // phone
                    $phone = str_replace("-","",$item['contact']);
                    if(str_contains($phone,'+')){
                        // do nothing
                    }
                    else{
                        if(substr($phone,0,1) != '6'){
                            $phone = '+6'.$phone;
                        }
                        else{
                            $phone = '+'.$phone;
                        }
                    } 
                    
                    // occupation category
                    $occupation = $app->myoccupationcategoryStore()->getById(3); //default set to 3

                    $accountManager = $app->mygtpaccountManager();

                    try {
                        $accHolder = $accountManager->register(
                            $partner,
                            $memberupload->name,
                            str_replace("-","",$memberupload->ic),
                            $phone,
                            $occupation,
                            null,
                            $memberupload->email,
                            str_replace("-","",$memberupload->ic),
                            strtoupper('EN'),
                            '',
                            $memberupload->name,
                            str_replace("-","",$memberupload->ic),
                            $phone,
                            '','','',
                            '000000',
                            '',false,'',''
                        );
    
                        $myaccountholder = $app->myaccountholderStore()->searchTable()->select()->where('mykadno','=',str_replace("-","",$memberupload->ic))->AndWhere('partnerid','=',$partner->id)->get();
                        if($myaccountholder){
                            // $rowcount++;
    
                            // $plaintext = json_encode($item);
    
                            // $payload = $this->signData($app, $plaintext);
                            $payload = bin2hex($this->randomChar()."=".$memberupload->id);
    
                            // send email
                            try{
                                if($item['email'] != '' && $item['email'] != NULL){
                                    $mailer = $app->getMailer();
                            
                                    $mailer->addAddress($item['email']);
            
                                    $mailer->isHtml(true);
                                    $email = $partner->senderemail;
                                    $name = $partner->sendername;
            
                                    // $mailer->setFrom($email, $name);
                                    if(strlen($email) > 0 && strlen($name) > 0){
                                        $mailer->setFrom($email, $name);
                                    }
    
                                    $baseurl = $app->getConfig()->{'mygtp.mbsb.ftp.url'};
                                    $url = $baseurl.$payload;
                                    $mailer->Subject = 'M-Prime Gold-i Program';
                                    $mailer->Body    = $this->getEmailBodyFTPMBSB($memberupload->name, $url);
                                    
                                    if(!$mailer->send()){
                                        // do nothing
                                    }
                                }
            
                                // send sms
                                if($item['contact'] != '' && $item['contact'] != NULL){
                                    $phone = str_replace("-","",$item['contact']);
                                    if(substr($phone,0,1) == '+'){
                                        $phone = str_replace('+',"",$item['contact']);
                                    } 
            
                                    if(substr($phone,0,1) != '6'){
                                        $phone = '6'.$phone;
                                    }
            
                                    if(strlen($phone) > 10){
                                        $sms = new \Snap\manager\SmsManager($app);
                                        $sms->phoneNumber = $phone;
                                        $sms->messagePayLoad = $this->getSMSBodyFTPMBSB($url);
                                        if (!$sms->send()){
                                            // throw new \Exception("Unable to sent SMS number: ".$sms->phoneNumber." payload: ".$sms->messagePayLoad);
                                        }else{
                                            // $this->log("SMS service: ".$receiver." : success", SNAP_LOG_DEBUG);
                                        }
                                    }
                                }
                            }
                            catch(Exception $f){
                                // Get previously created member
                                $createdMember = $app->mymemberuploadStore()->getById($memberupload->id);
                                $createdMember->status = MyMemberUpload::STATUS_SMSOREMAILFAILED;
                                $createdMember = $app->mymemberuploadStore()->save($createdMember);
    
                                $this->log("ERROR: ".$createdMember->email." __ ".$createdMember->contact." => ".$f->getMessage(), SNAP_LOG_ERROR);
                                echo "ERROR: ".$createdMember->email." __ ".$createdMember->contact." => ".$f->getMessage()."\n";
                            }
                        }
                    } catch (Exception $th) {
                        $createdMember = $app->mymemberuploadStore()->getById($memberupload->id);
                        $createdMember->status = MyMemberUpload::STATUS_MYACCOUNTHOLDERFAILED;
                        $createdMember = $app->mymemberuploadStore()->save($createdMember);
    
                        $this->log("ERROR: MYMEMBERUPLOAD->id: ".$createdMember->id." => ".$th->getMessage(), SNAP_LOG_ERROR);
                        echo "ERROR: MYMEMBERUPLOAD->id: ".$createdMember->id." => ".$th->getMessage()."\n";
                    }
                }
                else{
                    // do nothing
                }
            }
            $rowcount++;
            
        }

        return $rowcount;
    }

    public function randomChar(){
        $arr = range('a','z');
        $index = rand(0,25);
        return $arr[$index];
    }

    public function getEmailBodyFTPMBSB($name, $url){
        // $html = $url;
        $html = '<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
        <style>
            body,table,td{
                font-family:Helvetica,Arial,sans-serif!important
            }
            .ExternalClass{
                width:100%
            }
            .ExternalClass,.ExternalClass div,.ExternalClass font,.ExternalClass p,.ExternalClass span,.ExternalClass td{
                line-height:150%
            }
            a{
                text-decoration:none
            }
            *{
                color:inherit
            }
            #MessageViewBody a,a[x-apple-data-detectors],u+#body a{
                color:inherit;
                text-decoration:none;
                font-size:inherit;
                font-family:inherit;
                font-weight:inherit;
                line-height:inherit
            }
            img{
                -ms-interpolation-mode:bicubic
            }
            table:not([class^=s-]){
                font-family:Helvetica,Arial,sans-serif;
                mso-table-lspace:0;
                mso-table-rspace:0;
                border-spacing:0;
                border-collapse:collapse
            }
            table:not([class^=s-]) td{
                border-spacing:0;
                border-collapse:collapse
            }
            @media screen and (max-width:600px){
                .w-full,.w-full>tbody>tr>td{
                    width:100%!important
                }
                [class*=s-lg-]>tbody>tr>td{
                    font-size:0!important;
                    line-height:0!important;
                    height:0!important
                }
                .s-5>tbody>tr>td{
                    font-size:20px!important;
                    line-height:20px!important;
                    height:20px!important
                }
            }
        </style>
        
        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="outline:0;width:100%;min-width:100%;height:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;font-family:Helvetica,Arial,sans-serif;line-height:24px;font-weight:400;font-size:16px;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;box-sizing:border-box;color:#000;margin:0;padding:0;border:0" class="bg-light body" bgcolor="#f7fafc" valign="top"><tbody><tr><td align="left" style="line-height:24px;font-size:16px;margin:0" bgcolor="#f7fafc" valign="top"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%" class="container"><tbody><tr><td align="center" style="line-height:24px;font-size:16px;margin:0;padding:0 16px"><!--[if (gte mso 9)|(IE)]><table align=center role=presentation><tr><td width=600><![endif]--><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;max-width:600px;margin:0 auto" align="center"><tbody><tr><td align="left" style="line-height:24px;font-size:16px;margin:0"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%" class="s-5 w-full" width="100%"><tbody><tr><td align="left" style="line-height:20px;font-size:20px;width:100%;height:20px;margin:0" height="20" width="100%"></td></tr></tbody></table><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-radius:6px;border-collapse:separate!important;width:100%;overflow:hidden;box-shadow:rgba(50,50,93,.25) 0 6px 12px -2px,rgba(0,0,0,.3) 0 3px 7px -3px;border:1px solid #e2e8f0" class="card" bgcolor="#ffffff">
            <tbody>
                <tr>
                    <td align="left" style="line-height:24px;font-size:16px;width:100%;margin:0" bgcolor="#ffffff">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%" class="card-body">
                            <tbody>
                                <tr>
                                    <td align="left" style="line-height:24px;font-size:16px;width:100%;margin:0;padding:20px">
                                        <img alt="header_pic" class="img-fluid" src="https://gtp2.ace2u.com/src/resources/images/GoGold/buy_sell.png" style="height:auto;line-height:100%;outline:0;text-decoration:none;display:block;max-width:100%;width:100%;border:0 none" width="100%">##############DEMO################<br>
                                        <h3 align="left" style="padding-top:0;padding-bottom:0;font-weight:500;vertical-align:baseline;font-size:20px;line-height:33.6px;margin:0">Dear '.$name.',</h3><br>
                                        <p align="left" style="line-height:24px;font-size:16px;width:100%;margin:0">
                                            Thank you for joining our M-Prime Gold-i Program.
                                            <br/>
                                            Please activate an account by clicking the link below.
                                            <br/>
                                            M-Prime Gold-I platform.
                                            <br/>
                                            <br/>
                                            <a href="'.$url.'">Activate Your Account</a>
                                            <br/>
                                            <br/>
                                            <br/>
                                            <br/>
                                            Warmest Regards,
                                            <br/>
                                            M-Prime Gold-I
                                        </p>
                                        <br>
                                        <br>
                                        <br>
                                        <p align="left" style="line-height:24px;font-size:12px;width:100%;margin:0">Copyright A &copy; 2022 M-Prime Gold-I, All Rights Reserved</p>
                                        <p align="left" style="line-height:24px;font-size:12px;width:100%;margin:0">You are receiving this email because you have opted in at our website</p>
                                        <!--<br>-->
                                        <!--<p align="left" style="line-height:24px;font-size:12px;width:100%;margin:0">Powered by ACE Capital Growth Sdn Bhd</p>-->
                                        ##############DEMO################
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%" class="s-5 w-full" width="100%">
            <tbody>
                <tr>
                    <td align="left" style="line-height:20px;font-size:20px;width:100%;height:20px;margin:0" height="20" width="100%"></td>
                </tr>
            </tbody>
        </table>
        <div style="align-content:center;font-family:Arial,Helvetica,sans-serif;color:#756f6f7c;font-size:11px" align="center" class="footer">
            <span>Copyright © 2022 MBSB, All Rights Reserved</span>
            <br>
            <span>You are receiving this email because you have opted in at our website</span>
        </div>
        ';

        return $html;
    }

    public function getSMSBodyFTPMBSB($url){
        // $txt = "Thank you for joining our M-Prime Gold-i Program. Please activate an account by clicking the link below.\n".$url;
        $txt = "Thank you for joining our M-Prime Gold-i Program. Please activate account by clicking the link.\n".$url;

        return $txt;
    }

    public function signData($app, $unsignedData){
        // $pvtKeyLoc = dirname(__DIR__, 1) . '\\resource\\key\\private_mbsb_ftp.pem';
        $pvtKeyLoc = $app->getConfig()->{'mygtp.mbsb.ftp.privatekey'};
        $key = file_get_contents($pvtKeyLoc);

        $privateKey = new AsymmetricSecretKey($key, new Version1);           
        $signedData = Version1::sign($unsignedData, $privateKey);

        return $signedData;
    }

    // public function verifySignedData($signedData){
    //     $pubKeyLoc = dirname(__DIR__, 1) . '\\resource\\key\\public_mbsb_ftp.pem';
    //     $key = file_get_contents($pubKeyLoc);

    //     $publicKey = new AsymmetricPublicKey($key, new Version1()); 
    //     $verifiedData = Version1::verify($signedData, $publicKey);
    //     // $publicKey   = new AsymmetricPublicKey($key, new Version1); 
    //     // $verifiedData = Version1::verify($signedData, $publicKey);
    //     echo "decrypt: ".$verifiedData;
    //     echo "\n\n\n";
    // }

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
            'partner' => array('required' => true, 'type' => 'int', 'desc' => 'partner id')
        ];
    }
}
