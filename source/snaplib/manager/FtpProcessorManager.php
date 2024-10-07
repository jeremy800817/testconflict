<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\manager;

Use \Snap\InputException;
Use \Snap\TLogging;
Use \Snap\IObserver;
Use \Snap\IObservable;
Use \Snap\IObservation;
Use \Snap\object\Replenishment;
Use \Snap\object\VaultItem;
Use \Snap\object\VaultLocation;
Use \Snap\object\Logistic;
Use \Snap\object\Redemption;
Use \Snap\object\OrderQueue;
Use \Snap\object\Order;
Use \Snap\object\Buyback;
Use \Snap\common;
/*spreadsheet/excel*/
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// FTP process
class FtpProcessorManager implements IObservable, IObserver
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;
    
    private $app = null;
    private $path;
    private $reportpath;
    private $limit = 5000;
    protected $file;

    public function __construct($app)
    {
        $this->app = $app;
        $this->path = $this->app->getConfig()->{'gtp.ftp.tombb'};
        $this->reportpath = $this->app->getConfig()->{'gtp.ftp.report'};
    }

    /**
     * Setter for the file to be parsed
     * @param string $pathToFile /path/to/file.dat
     */
    public function setFilePath($pathToFile)
    {
        $this->file = (string) $pathToFile;
    }  

    /**
     * Listen to the following events and update future order as appropriate:
     * 1)  Check if after getting total MBB order sold nearing the warning treshold to request for more serials
     * 2)  Check when GRN receive whether to do transfer to G4S vault....
     *
     * @param  IObservable  $changed The initiating object
     * @param  IObservation $state   Change information
     */
    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        
    }

    public function getReplenishmentFtp($date,$filename,$partner,$server){
        $now = common::convertUTCToUserDatetime(new \DateTime());
        $getDate            = date('Y-m-d h:i:s',$date);
        $dateFile           = date('dmY',$date);
        $mbbFtp = new \Snap\api\mbb\MbbFtpOutput($this->app,false);

        if(null != $server) $extraFlag = "#####".strtoupper($server)."#####"; 
        
        try {
            $handle = $this->app->replenishmentStore()->searchTable(false);
            $replenishItemC = $handle
                    ->select()
                    ->where('partnerid',$partner->id)
                    ->andWhere('status', Replenishment::STATUS_CONFIRMED)
                    ->andWhere('statusexport', 0)
                    ->andWhere('sapresponsestatus','<=>',$handle->raw("null"))
                    ->execute();
            
            if (count($replenishItemC) == 0){
                //throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'No data for replenishment']);
                $bodyEmail .= "There is no new records to generate for date - ".$dateFile.".\n";
            } 

            $totalItem = count($replenishItemC);
            if($totalItem > $this->limit) $paging = ceil($totalItem / $this->limit);
            else $paging = 1;

            for($i=1;$i<=$paging;$i++){
                $offset = ($i-1) * $this->limit;
                $handle = $this->app->replenishmentStore()->searchTable();
                $replenishItem = $handle
                    ->select()
                    ->where('partnerid',$partner->id)
                    ->andWhere('status', Replenishment::STATUS_CONFIRMED)
                    ->andWhere('statusexport', 0)
                    ->andWhere('sapresponsestatus','<=>',$handle->raw("null"))
                    ->limit($offset, $this->limit)
                    ->execute();

                //$ftpOutput = $this->app->apiManager()->ftpAceToMbb(strtoupper($filename),$filename,$replenishItem,$date);
                $ftpOutput = $mbbFtp->checkFile(strtoupper($filename),$filename,$replenishItem,$date,$totalItem,$paging,$i);
            }

            $this->log("Start create configuration PHYREP on ".$dateFile);
            //get file path
            $path = $this->path;
            $result = glob ($path.strtoupper($filename).".*");
            $this->setFilePath($result[0]); 
            $filename = basename($result[0]).PHP_EOL;

            $emailConfig = 'sendtophyrep';
            //$emailSubject = $extraFlag.' FTP(DAILY) - ACE - PHYREP '.$dateFile;
            $emailSubject = 'FTP(DAILY) - ACE - PHYREP '.$dateFile;
            $bodyEmail .= "\nSUCCESSFULLY GENERATED.\n";
            $sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject,$emailConfig,$this->file,$filename);
            $this->log("Send PHYREP on ".$dateFile." from ftpprocessormanager to sendNotifyEmail apimanager.");

            /*change statusexport to 1*/
            if(count($replenishItem) != 0){
                foreach($replenishItem as $aItem){
                    $aItem->statusexport = 1;
                    $aItem->statusexporton = $now->format('Y-m-d H:i:s');
                    $this->app->replenishmentStore()->save($aItem);
                }
            }
            /*change statusexport to 1 end*/
        }
        catch(\Exception $e) {
            //$emailSubject = $extraFlag.' FTP(DAILY) - ACE - PHYREP '.$dateFile.' - Error Notification';
            $emailSubject = 'FTP(DAILY) - ACE - PHYREP '.$dateFile.' - Error Notification';
            $bodyEmail .= "\nError in processing file PHYREP:\n".$e->getMessage().".";
            $sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject);

            $this->log(__METHOD__."Error to get data for PHYREP", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        } 
    }

    public function getCourierFtp($date,$filename,$partner,$server){
        if(null != $server) $extraFlag = "#####".strtoupper($server)."#####";
        try{
            /*
            courierrefno        -
            trackingno          - awbno
            delivery-date       - 
            courier-agent       - using vendor id to add name at vw_table. add courier details at tag table
            return-undelivered  - if lgs_attemps reach 3, it 'Y', else 'N'
            remark              - currently no column at logistic table
            */
            $getDate            = date('Y-m-d h:i:s',$date);
            $dateFile           = date('dmY',$date);
            $createDateStart    = date_create($getDate);
            $modifyDateStart    = date_modify($createDateStart,"-2 day");
            $startDate          = date_format($modifyDateStart,"Y-m-d 16:00:00");
            $createDateEnd      = date_create($getDate);
            $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
            $endDate            = date_format($modifyDateEnd,"Y-m-d 15:59:59");
            $mbbFtp             = new \Snap\api\mbb\MbbFtpOutput($this->app,false);


            $logisticCHandle = $this->app->logisticStore()->searchView(false)->select();
            $logisticItemC      = $logisticCHandle
                                ->where('partnerid', $partner->id)
                                ->andWhere($logisticCHandle->raw("(lgs_modifiedstatusexport != 1"), 'OR', $logisticCHandle->raw("lgs_modifiedstatusexport IS NULL)"))
                                ->andWhere('type',"NOT IN", ['SpecialDelivery', 'Appointment'])
                                /*->andWhere('modifiedon', '>=', $startDate)
                                ->andWhere('modifiedon', '<=', $endDate)*/
                                ->execute();

            if (count($logisticItemC) == 0){
                $bodyEmail .= "No data for logistic for date - ".$dateFile.".\n";
            }

            $totalItem = count($logisticItemC);
            if($totalItem > $this->limit) $paging = ceil($totalItem / $this->limit);
            else $paging = 1;

            for($i=1;$i<=$paging;$i++){
                $logisticHandle = $this->app->logisticStore()->searchView()->select();
                $logisticItem = $logisticHandle
                            ->where('partnerid', $partner->id)
                            ->andWhere($logisticHandle->raw("(lgs_modifiedstatusexport != 1"), 'OR', $logisticHandle->raw("lgs_modifiedstatusexport IS NULL)"))
                            ->andWhere('type',"NOT IN", ['SpecialDelivery', 'Appointment'])
                            /*->andWhere('modifiedon', '>=', $startDate)
                            ->andWhere('modifiedon', '<=', $endDate)*/
                            ->limit($offset, $this->limit)
                            ->execute();
                if (count($logisticItem) != 0){
                    foreach($logisticItem as $aLogisticItem){
                        if($aLogisticItem->type == 'Redemption'){
                            $transactionItem = $this->app->redemptionStore()->getByField('id', $aLogisticItem->typeid);
                            if($transactionItem){
                                $refNo = $transactionItem->partnerrefno;
                                $remarks = $transactionItem->remarks;
                            }
                        } elseif($aLogisticItem->type == 'Buyback'){
                            $transactionItem = $this->app->buybackStore()->getByField('id', $aLogisticItem->typeid);
                            if($transactionItem){
                                $refNo = $transactionItem->partnerrefno;
                                $remarks = $transactionItem->remarks;
                            }
                        } else {
                            $transactionItem = $this->app->replenishmentStore()->getByField('id', $aLogisticItem->typeid);
                            if($transactionItem){
                                $refNo = $transactionItem->replenishmentno;
                                $remarks = $transactionItem->saprefno;
                            }
                        }
                        $aLogisticItem->modifiedstatusexport = 1;
                        $update = $this->app->logisticStore()->save($aLogisticItem);
                        if(!$update){
                            $bodyEmail .= "Unable to update logistic data id ".$aLogisticItem->id.".\n";
                        } else {
                            $logisticDetail[] = array(
                                'id' => $aLogisticItem->id,
                                'vendorrefno' => $aLogisticItem->vendorrefno,
                                'transactionRefNo' => $refNo,
                                'awbno' => $aLogisticItem->awbno,
                                'deliverydate' => $aLogisticItem->deliverydate,
                                'attemps' => $aLogisticItem->attemps,
                                'vendorname' => $aLogisticItem->vendorname,
                                'remarks' => $remarks
                            );
                        }
                    }
                }
                //$ftpOutput = $this->app->apiManager()->ftpAceToMbb(strtoupper($filename),$filename,$logisticDetail,$date); 
                $ftpOutput = $mbbFtp->checkFile(strtoupper($filename),$filename,$logisticDetail,$date,$totalItem,$paging,$i); 
            }

            $this->log("Start create configuration PHYCOURIER on ".$dateFile);
            //get file path
            $path = $this->path;
            $result = glob ($path.strtoupper($filename).".*");
            $this->setFilePath($result[0]); 
            $filename = basename($result[0]).PHP_EOL;

            $emailConfig = 'sendtophycourier';
            //$emailSubject = $extraFlag.' FTP(DAILY) - ACE - PHYCOURIER '.$dateFile;
            $emailSubject = 'FTP(DAILY) - ACE - PHYCOURIER '.$dateFile;
            $bodyEmail .= "\nSUCCESSFULLY GENERATED.\n";
            $sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject,$emailConfig,$this->file,$filename);
            $this->log("Send PHYCOURIER on ".$dateFile." from ftpprocessormanager to sendNotifyEmail apimanager.");
        } catch(\Exception $e) {
            //$emailSubject = $extraFlag.' FTP(DAILY) - ACE - PHYCOURIER '.$dateFile.' - Error Notification';
            $emailSubject = 'FTP(DAILY) - ACE - PHYCOURIER '.$dateFile.' - Error Notification';
            $bodyEmail .= "\nError in processing file PHYCOURIER:\n".$e->getMessage().".";
            $sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject);

            $this->log(__METHOD__."Error to get data for PHYCOURIER", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function getSpDeliveryAppointmentFtp($date,$filename,$partner,$server){
        if(null != $server) $extraFlag = "#####".strtoupper($server)."#####";
        try{
            //GET REDEMPTION FIRST
            $getDate            = date('Y-m-d h:i:s',$date);
            $dateFile           = date('dmY',$date);
            $createDateStart    = date_create($getDate);
            $modifyDateStart    = date_modify($createDateStart,"-2 day");
            $startDate          = date_format($modifyDateStart,"Y-m-d 16:00:00");
            $createDateEnd      = date_create($getDate);
            $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
            $endDate            = date_format($modifyDateEnd,"Y-m-d 15:59:59");
            $mbbFtp             = new \Snap\api\mbb\MbbFtpOutput($this->app,false);

            $redemptionItemC = $this->app->redemptionStore()->searchTable(false)->select()
                            ->where('partnerid',$partner->id)
                            /*->andWhere('createdon', '>=', $startDate)
                            ->andWhere('createdon', '<=', $endDate)*/
                            ->andWhere('status','IN',[Redemption::STATUS_COMPLETED,Redemption::STATUS_CONFIRMED,Redemption::STATUS_PROCESSDELIVERY])
                            ->andWhere('type',"IN", ['SpecialDelivery', 'Appointment'])
                            ->execute();
            if (count($redemptionItemC) == 0){
                $bodyEmail .= "No special delivery/pre-appointment redemption data for date - ".$dateFile.".\n";
            }

            $totalItem = count($redemptionItemC);
            if($totalItem > $this->limit) $paging = ceil($totalItem / $this->limit);
            else $paging = 1;

            for($i=1;$i<=$paging;$i++){
                $redemptionItem = $this->app->redemptionStore()->searchTable()->select()
                            ->where('partnerid',$partner->id)
                            /*->andWhere('createdon', '>=', $startDate)
                            ->andWhere('createdon', '<=', $endDate)*/
                            ->andWhere('status','IN',[Redemption::STATUS_COMPLETED,Redemption::STATUS_CONFIRMED,Redemption::STATUS_PROCESSDELIVERY])
                            ->andWhere('type',"IN", ['SpecialDelivery', 'Appointment'])
                            ->limit($offset, $this->limit)
                            ->execute();
                if (count($redemptionItem) != 0){
                    foreach($redemptionItem as $aRedemption){
                        $logisticItem = $this->app->logisticStore()->searchView()->select()
                                ->where('type', 'Redemption')
                                ->andWhere('partnerid', $aRedemption->partnerid)
                                ->andWhere('typeid', $aRedemption->id)
                                ->andWhere('status','NOT IN',[Logistic::STATUS_FAILED,Logistic::STATUS_MISSING])
                                ->execute();
                        if (count($logisticItem) != 0){
                            foreach($logisticItem as $aLogistic){
                                if($aRedemption->type == 'SpecialDelivery') $branchid = $aRedemption->appointmentbranchid;
                                else $branchid = $aRedemption->branchid;
                                //insert to new array
                                if($aLogistic->modifiedstatusexport != 1){
                                    $joinStatement[] = array(
                                        'redemptionid' => $aRedemption->id,
                                        'logisticid'=> $aLogistic->id,
                                        'transactionRefNo'=>$aRedemption->partnerrefno,
                                        'vendorrefno'=> $aLogistic->vendorrefno,
                                        'awbno' => $aLogistic->awbno,
                                        'deliverydate' => $aLogistic->deliverydate,
                                        'vendorname' => $aLogistic->vendorname,
                                        'attemps' => $aLogistic->attemps,
                                        'remarks' => $aRedemption->remarks, //logistic dont have remarks
                                        'branchid' => $branchid
                                    );
                                }
                            }
                            $aLogistic->modifiedstatusexport = 1;
                            $update = $this->app->logisticStore()->save($aLogistic);
                            if(!$update){
                                $bodyEmail .= "Unable to update logistic data id ".$aLogistic->id.".\n";
                            }
                        } else {
                            $bodyEmail .= "No logistic data for redemption id - ".$aRedemption->redemptionno.". ";
                            $bodyEmail .= "Please recheck to confirm.\n";

                            if($aRedemption->type == 'SpecialDelivery') $branchid = $aRedemption->appointmentbranchid;
                            else $branchid = $aRedemption->branchid;
                            //insert to new array
                            $joinStatement[] = array(
                                'redemptionid' => $aRedemption->id,
                                'logisticid'=> '',
                                'transactionRefNo'=>$aRedemption->partnerrefno,
                                'vendorrefno'=> '',
                                'awbno' => '',
                                'deliverydate' => '',
                                'vendorname' => '',
                                'attemps' => '',
                                'remarks' => $aRedemption->remarks, //logistic dont have remarks
                                'branchid' => $branchid
                            );
                        }
                    }
                }
                //$ftpOutput = $this->app->apiManager()->ftpAceToMbb(strtoupper($filename),$filename,$joinStatement,$date);
                $ftpOutput = $mbbFtp->checkFile(strtoupper($filename),$filename,$joinStatement,$date,$totalItem,$paging,$i);
            }

            $this->log("Start create configuration SPCDELV on ".$dateFile);
            //get file path
            $path = $this->path;
            $result = glob ($path.strtoupper($filename).".*");
            $this->setFilePath($result[0]); 
            $filename = basename($result[0]).PHP_EOL;

            $emailConfig = 'sendtospcdelv';
            //$emailSubject = $extraFlag.' FTP(DAILY) - ACE - SPCDELV '.$dateFile;
            $emailSubject = 'FTP(DAILY) - ACE - SPCDELV '.$dateFile;
            $bodyEmail .= "\nSUCCESSFULLY GENERATED.\n";
            $sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject,$emailConfig,$this->file,$filename);
            $this->log("Send SPCDELV on ".$dateFile." from ftpprocessormanager to sendNotifyEmail apimanager.");
        } catch(\Exception $e) {
            //$emailSubject = $extraFlag.' FTP(DAILY) - ACE - SPCDELV '.$dateFile.' - Error Notification';
            $emailSubject = 'FTP(DAILY) - ACE - SPCDELV '.$dateFile.' - Error Notification';
            $bodyEmail .= "\nError in processing file SPCDELV:\n".$e->getMessage();
            $sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject);

            $this->log(__METHOD__."Error to get data for SPCDELV", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function getDailyGPPriceFtp($date,$filename,$partner,$server){
        if(null != $server) $extraFlag = "#####".strtoupper($server)."#####";
        try {
            //get yesteday date
            $getDate            = date('Y-m-d h:i:s',$date);
            $dateFile           = date('dmY',$date);
            $createDateStart    = date_create($getDate);
            $modifyDateStart    = date_modify($createDateStart,"-2 day");
            $startDate          = date_format($modifyDateStart,"Y-m-d 16:00:00");
            $createDateEnd      = date_create($getDate);
            $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
            $endDate            = date_format($modifyDateEnd,"Y-m-d 15:59:59");
            
            $mbbFtp = new \Snap\api\mbb\MbbFtpOutput($this->app,false);
            //P1
            $filename1 = $filename.'1';
            $pricestreamItemC = $this->app->pricevalidationStore()
                            ->searchTable(false)
                            ->select()
                            ->where('partnerid',$partner->id)
                            ->andWhere('createdon', '>=', $startDate)
                            ->andWhere('createdon', '<=', $endDate)
                            ->execute();
            if(count($pricestreamItemC) == 0){
                $bodyEmail .= "No query price data for date - ".$dateFile.".\n";
            } 

            $totalItem = count($pricestreamItemC);
            if($totalItem > $this->limit) $paging = ceil($totalItem / $this->limit);
            else $paging = 1;
            for($i=1;$i<=$paging;$i++){
               $offset = ($i-1) * $this->limit;
               $pricestreamItem = $this->app->pricevalidationStore()
                           ->searchTable()
                           ->select()
                           ->where('partnerid',$partner->id)
                           ->andWhere('createdon', '>=', $startDate)
                           ->andWhere('createdon', '<=', $endDate)
                           ->limit($offset, $this->limit)
                           ->execute();
                //$ftpOutput = $this->app->apiManager()->ftpAceToMbb(strtoupper($filename1),$filename1,$pricestreamItem,$currentdate,$totalItem,$paging,$i);
                $ftpOutput = $mbbFtp->checkFile(strtoupper($filename1),$filename1,$pricestreamItem,$date,$totalItem,$paging,$i);
            }

            //P2
            $filename2 = $filename.'2';
            $pricevalidationItemC = $this->app->pricevalidationStore()
                            ->searchTable(false)
                            ->select()
                            ->where('partnerid',$partner->id)
                            ->andWhere('createdon', '>=', $startDate)
                            ->andWhere('createdon', '<=', $endDate)
                            ->andWhere('orderid','<>',0)
                            ->execute();
            if(count($pricevalidationItemC) == 0){
                $bodyEmail .= "No pricevalidation data for date - ".$dateFile.".\n";
            }

            $totalItem2 = count($pricevalidationItemC);
            if($totalItem2 > $this->limit) $paging2 = ceil($totalItem2 / $this->limit);
            else $paging2 = 1;
            for($i=1;$i<=$paging2;$i++){
               $offset2 = ($i-1) * $this->limit;
               $pricevalidationItem = $this->app->pricevalidationStore()
                           ->searchTable()
                           ->select()
                           ->where('partnerid',$partner->id)
                           ->andWhere('createdon', '>=', $startDate)
                           ->andWhere('createdon', '<=', $endDate)
                           ->andWhere('orderid','<>',0)
                           ->limit($offset2, $this->limit)
                           ->execute();
                //$ftpOutput = $this->app->apiManager()->ftpAceToMbb(strtoupper($filename2),$filename2,$pricevalidationItem,$currentdate,$totalItem2,$paging2,$i);
                $ftpOutput = $mbbFtp->checkFile(strtoupper($filename2),$filename2,$pricevalidationItem,$date,$totalItem2,$paging2,$i);
            }

            //P3
            $filename3 = $filename.'3';
            $orderItemC = $this->app->orderStore()
                            ->searchTable(false)
                            ->select()
                            ->where('partnerid',$partner->id)
                            ->andWhere('createdon', '>=', $startDate)
                            ->andWhere('createdon', '<=', $endDate)
                            ->andWhere('cancelpricestreamid','<>',0)
                            ->andWhere('status',Order::STATUS_CANCELLED)
                            ->execute();
            if(count($orderItemC) == 0){
                $bodyEmail .= "No reversal order data for date - ".$dateFile.".\n";
            }

            $totalItem3 = count($orderItemC);
            if($totalItem3 > $this->limit) $paging3 = ceil($totalItem3 / $this->limit);
            else $paging3 = 1;
            for($i=1;$i<=$paging3;$i++){
                 $offset3 = ($i-1) * $this->limit;
                 $orderItem = $this->app->orderStore()
                             ->searchTable()
                             ->select()
                             ->where('partnerid',$partner->id)
                             ->andWhere('createdon', '>=', $startDate)
                             ->andWhere('createdon', '<=', $endDate)
                             ->andWhere('cancelpricestreamid','<>',0)
                             ->andWhere('status',Order::STATUS_CANCELLED)
                             ->limit($offset3, $this->limit)
                             ->execute();
                //$ftpOutput = $this->app->apiManager()->ftpAceToMbb(strtoupper($filename3),$filename3,$orderItem,$currentdate,$totalItem3,$paging3,$i);
                $ftpOutput = $mbbFtp->checkFile(strtoupper($filename3),$filename3,$orderItem,$date,$totalItem3,$paging3,$i);
             }

            //fo target price
            $filename4 = $filename.'4';
            $orderQItemC = $this->app->orderqueueStore()
                            ->searchView(false)
                            ->select()
                            ->where('partnerid',$partner->id)
                            ->andWhere('createdon', '>=', $startDate)
                            ->andWhere('createdon', '<=', $endDate)
                            ->andWhere('status','IN',[OrderQueue::STATUS_ACTIVE,OrderQueue::STATUS_FULLFILLED])
                            ->execute();
            if(count($orderQItemC) == 0){
                $bodyEmail .= "No future order data for date - ".$dateFile.".\n";
            }

            $totalItem4 = count($orderQItemC);
             if($totalItem4 > $this->limit) $paging4 = ceil($totalItem4 / $this->limit);
             else $paging4 = 1;
             for($i=1;$i<=$paging4;$i++){
                 $offset4 = ($i-1) * $this->limit;
                 $orderQItem = $this->app->orderqueueStore()
                             ->searchView()
                             ->select()
                             ->where('partnerid',$partner->id)
                             ->andWhere('createdon', '>=', $startDate)
                             ->andWhere('createdon', '<=', $endDate)
                             ->andWhere('status','IN',[OrderQueue::STATUS_ACTIVE,OrderQueue::STATUS_FULLFILLED])
                             ->limit($offset4, $this->limit)
                             ->execute();
                //$ftpOutput = $this->app->apiManager()->ftpAceToMbb(strtoupper($filename4),$filename4,$orderQItem,$currentdate,$totalItem4,$paging4,$i);
                $ftpOutput = $mbbFtp->checkFile(strtoupper($filename4),$filename4,$orderQItem,$date,$totalItem4,$paging4,$i);
             }
            
            $fileArr = array($filename1,$filename2,$filename3,$filename4);

            $this->log("Start create configuration DAILYGPPRICE on ".$dateFile);
            //get file path
            $path = $this->path;
            foreach($fileArr as $filename){
                $result = glob ($path.strtoupper($filename).".*");
                $this->setFilePath($result[0]); 
                $pathArray[$filename][] = array($this->file,basename($result[0]).PHP_EOL);
            }

            $this->log("Start create email config for DAILYGPPRICE on ".$dateFile);
            $emailConfig = 'sendtodailygpprice';
            //$emailSubject = $extraFlag.' FTP(DAILY) - ACE - DAILYGPPRICE '.$dateFile;
            $emailSubject = 'FTP(DAILY) - ACE - DAILYGPPRICE '.$dateFile;
            $bodyEmail .= "\nSUCCESSFULLY GENERATED.\n";
            $this->log("Email subject from ftpprocessormanager : ".$emailSubject); // checking emailSubject
            $this->log("Email body from ftpprocessormanager : ".$bodyEmail); // checking bodyemail
            $this->log("Send DAILYGPPRICE on ".$dateFile." from ftpprocessormanager to sendNotifyEmail apimanager.");
            $sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject,$emailConfig,$pathArray);
         }
        catch(\Exception $e) {
            //$emailSubject = $extraFlag.' FTP(DAILY) - ACE - DAILYGPPRICE '.$dateFile.' - Error Notification';
            $emailSubject = 'FTP(DAILY) - ACE - DAILYGPPRICE '.$dateFile.' - Error Notification';
            $bodyEmail .= "\nError in processing file DAILYGPPRICE:\n".$e->getMessage().".";
            $sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject);

            $this->log(__METHOD__."Error to get data for DailyGPPrice", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function reconFO($transaction,$flag){
        try{
            $now = common::convertUTCToUserDatetime(new \DateTime());
            $date               = date('Y-m-d h:i:s',$transaction['currentdate']);
            $createDateStart    = date_create($date);
            $modifyDateStart    = date_modify($createDateStart,"-2 day");
            $startDate          = date_format($modifyDateStart,"Y-m-d 16:00:00");
            $createDateEnd      = date_create($date);
            $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
            $endDate            = date_format($modifyDateEnd,"Y-m-d 15:59:59");
            $dateOfData         = date_format($modifyDateEnd,"d-m-Y");
            $partner            = $transaction['partner'];
            $detailCount        = $transaction[0]['header_totalline']+1;
            $dateFile           = $transaction[0]['header_filedate'];
            
            $ftpHeader = $transaction[0]['header_code'].str_pad($transaction[0]['header_filename'],10, ' ', STR_PAD_RIGHT).$transaction[0]['header_filedate'].$transaction[0]['header_filesequence'].$transaction[0]['header_totalline'];

            /*Array
            (
                [id] => 1
                [code] => MBISMY@SIT
                [name] => Maybank Islamic Gold Account
            )*/
            //MBB STATUS
            /*
            PENDING = ACTIVE / STATUS_ACTIVE = 1
            POSTED = MATCHED / STATUS_MATCHED = 3
            CANCELLED = CANCELLED / STATUS_CANCELLED = 5
            REJECT = PENDINGCANCEL/CANCELLED / STATUS_PENDINGCANCEL=4, STATUS_CANCELLED = 5
            */
            for($i=1;$i<$detailCount;$i++){
                $foNo = $transaction[$i]['detail_forefno'];
                $transactionType = ucfirst(strtolower($transaction[$i]['detail_type']));
                $bookingPrice = $transaction[$i]['detail_bookingprice'];
                $bookingGram = $transaction[$i]['detail_bookgram'];
                $bookingDate = $transaction[$i]['detail_bookingdate'];
                $expiredDate = $transaction[$i]['detail_expireddate'];
                $mbbStatus = $transaction[$i]['detail_status'];

                if($mbbStatus == 'PENDING') $checkStatus = OrderQueue::STATUS_ACTIVE;
                if($mbbStatus == 'POSTED' || $mbbStatus == 'MATCHED') $checkStatus = OrderQueue::STATUS_FULLFILLED;
                if($mbbStatus == 'CANCEL' || $mbbStatus == 'REJECT') $checkStatus = OrderQueue::STATUS_CANCELLED;

                $foItem = $this->app->orderqueueStore()
                    ->searchView()
                    ->select()
                    ->where('partnerid',$partner['id'])
                    ->andWhere('partnerrefid',$foNo)
                    ->execute();
                if(count($foItem) > 0){
                    foreach($foItem as $anOrderQueue){
                        if($anOrderQueue->status == 0) $status_name = 'PENDING';
                        if($anOrderQueue->status == 1) $status_name = 'ACTIVE';
                        if($anOrderQueue->status == 2) $status_name = 'FULLFILLED';
                        if($anOrderQueue->status == 3) $status_name = 'MATCHED';
                        if($anOrderQueue->status == 4) $status_name = 'PENDINGCANCEL';
                        if($anOrderQueue->status == 5) $status_name = 'CANCELLED';
                        if($anOrderQueue->status == 6) $status_name = 'EXPIRED';

                        if($anOrderQueue->ordertype == OrderQueue::OTYPE_COMPANYBUY) $pricestreamMatch = number_format($anOrderQueue->companybuyppg,6,'.','');
                        elseif($anOrderQueue->ordertype == OrderQueue::OTYPE_COMPANYSELL) $pricestreamMatch = number_format($anOrderQueue->companysellppg,6,'.','');

                        if($anOrderQueue->status == 2) $matchDate = $anOrderQueue->matchon->format('d-m-Y H:i:s');
                        else $matchDate = '00-00-00 00:00:00';

                        $gtpMatchArr = array(
                            'gtp_orderqueueno' => $anOrderQueue->orderqueueno,
                            'gtp_orderqueuetype' => $anOrderQueue->ordertype,
                            'gtp_bookingprice' => $anOrderQueue->pricetarget,
                            'gtp_bookinggram' => $anOrderQueue->xau,
                            'gtp_expireddate' => $anOrderQueue->expireon->format('d-m-Y H:i:s'),
                            'gtp_createdon' => $anOrderQueue->createdon->format('d-m-Y H:i:s'),
                            'gtp_status' => $status_name,
                            'gtp_pricematchvalue' => $pricestreamMatch,
                            'gtp_matchon' => $matchDate
                        );

                        //check status
                        if($checkStatus != $anOrderQueue->status) {
                            if($checkStatus == OrderQueue::STATUS_CANCELLED && $anOrderQueue->status != OrderQueue::STATUS_FULLFILLED){
                                $descExcel .= "Status for transaction no {$anOrderQueue->orderqueueno} in GTP is {$status_name}({$anOrderQueue->status}), however in MBB is {$mbbStatus}({$checkStatus}).\n";
                                $descExcel .= "System will change status in GTP accordingly with MBB status.\n\n";
                                $this->log("FODLYREC for {$dateOfData} - Status for transaction no {$anOrderQueue->orderqueueno} in GTP is {$status_name}({$anOrderQueue->status}), however in MBB is {$mbbStatus}({$checkStatus}). System will change status in GTP accordingly with MBB status");
                                $anOrderQueue->status = $checkStatus;
                                $anOrderQueue->cancelon = $now->format('Y-m-d H:i:s');
                                $anOrderQueue->remarks = $anOrderQueue->remarks."-FODLYREC_".$dateFile."-REJECT";
                            } else {
                                $descExcel .= "Status for transaction no {$anOrderQueue->orderqueueno} in GTP is {$status_name}({$anOrderQueue->status}), however in MBB is {$mbbStatus}({$checkStatus}).\n";
                                $descExcel .= "Please check to confirm.\n\n";
                                $this->log("FODLYREC for {$dateOfData} - Status for transaction no {$anOrderQueue->orderqueueno} in GTP is {$status_name}({$anOrderQueue->status}), however in MBB is {$mbbStatus}({$checkStatus}).");
                            }
                            $diffStatus[$foNo] = array_merge($transaction[$i],$gtpMatchArr);
                        } else {
                            $allTransaction[$foNo] = array_merge($transaction[$i],$gtpMatchArr);
                            /*total fo matched*/
                            if($checkStatus == OrderQueue::STATUS_FULLFILLED){
                                $totalXau += number_format($bookingGram,3,'.','');
                                $totalAmount += number_format(($bookingGram*$bookingPrice),2,'.','');
                            }
                            /*end total fo matches*/
                        }

                        if(ltrim($bookingPrice, '0') != $anOrderQueue->pricetarget) {
                            $finalBookingPrice = ltrim($bookingPrice, '0');
                            $descExcel .= "Target price for transaction no {$anOrderQueue->orderqueueno} in GTP is {$anOrderQueue->pricetarget}, however in MBB is {$finalBookingPrice}.\n";
                            $descExcel .= "Please check to confirm.\n\n";
                            $this->log("FODLYREC for {$dateOfData} - Target price for transaction no {$anOrderQueue->orderqueueno} in GTP is {$anOrderQueue->pricetarget}, however in MBB is {$finalBookingPrice}");
                        }

                        if(ltrim($bookingGram, '0') != $anOrderQueue->xau) {
                            $finalBookingGram = ltrim($bookingGram, '0');
                            $descExcel .= "Target weight for transaction no {$anOrderQueue->orderqueueno} in GTP is {$anOrderQueue->xau}, however in MBB is {$finalBookingGram}.\n";
                            $descExcel .= "Please check to confirm.\n\n";
                            $this->log("FODLYREC for {$dateOfData} - Target weight for transaction no {$anOrderQueue->orderqueueno} in GTP is {$anOrderQueue->xau}, however in MBB is {$finalBookingGram}");
                        }
                        $anOrderQueue->reconciled = 1;
                        $anOrderQueue->reconciledon = $now->format('Y-m-d H:i:s');
                        $update = $this->app->orderqueueStore()->save($anOrderQueue);
                        if(!$update){
                            $descExcel .= "Unable to update details for order queue item - ".$anOrderQueue->orderqueueno.".\n";
                            $this->log("Unable to update details for order queue item - ".$anOrderQueue->orderqueueno, SNAP_LOG_ERROR);
                        }
                    }
                } else {
                    $mbbUnmatchTransaction[$foNo] = $transaction[$i];
                }
            }

            /*get orderqueue not in mbb list*/
            $foItem = $this->app->orderqueueStore()
                    ->searchTable()
                    ->select()
                    ->where('partnerid',$partner['id'])
                    ->andWhere('reconciled',0)
                    ->andWhere('modifiedon', '<=', $endDate)
                    ->execute();
            if(count($foItem) > 0){
                foreach($foItem as $anOrderQueue) {
                    if($anOrderQueue->status == 0) $status_name = 'PENDING';
                    if($anOrderQueue->status == 1) $status_name = 'ACTIVE';
                    if($anOrderQueue->status == 2) $status_name = 'FULLFILLED';
                    if($anOrderQueue->status == 3) $status_name = 'MATCHED';
                    if($anOrderQueue->status == 4) $status_name = 'PENDINGCANCEL';
                    if($anOrderQueue->status == 5) $status_name = 'CANCELLED';
                    if($anOrderQueue->status == 6) $status_name = 'EXPIRED';

                    if($anOrderQueue->status == 5 || $anOrderQueue->status == 6){ //this filter is because mbb will not include transactions with cancel status in FODLYREC ftp
                        /*update reconciled*/
                        $anOrderQueue->reconciled = 1;
                        $anOrderQueue->reconciledon = $now->format('Y-m-d H:i:s');
                        $update = $this->app->orderqueueStore()->save($anOrderQueue);
                        if(!$update){
                            $descExcel .= "Unable to update details for order queue item (CANCELLED status) - ".$anOrderQueue->orderqueueno.".\n";
                            $this->log("Unable to update details for order queue item (CANCELLED status) - ".$anOrderQueue->orderqueueno, SNAP_LOG_ERROR);
                        }
                    } else {
                        $statusUnmatchFromGTPArr[] = array(
                            'gtp_partnerrefid' => $anOrderQueue->partnerrefid,
                            'gtp_orderqueueno' => $anOrderQueue->orderqueueno,
                            'gtp_orderqueuetype' => $anOrderQueue->ordertype,
                            'gtp_bookingprice' => $anOrderQueue->pricetarget,
                            'gtp_bookinggram' => $anOrderQueue->xau,
                            'gtp_expireddate' => $anOrderQueue->expireon->format('dmY'),
                            'gtp_createdon' => $anOrderQueue->createdon->format('dmY'),
                            'gtp_status' => $status_name
                        );
                    }
                }
            }

            $descExcel .= "Total xau for match/fulfilled future order = ";
            $descExcel .= ($totalXau > 0) ? $totalXau : 0;
            $descExcel .= "\n";
            $descExcel .= "Total amount for match/fulfilled future order = ";
            $descExcel .= ($totalAmount > 0) ? $totalAmount : 0;
            $descExcel .= "\n\n";

            $descExcel .= "\nPlease combine total xau and total amount from FODLYREC and DAILYTRN before compare with M2UStatement.\n";
            $descExcel .= "\nSUCCESSFULLY READING.\n";

            /*1st sheet*/
            $pathToSave = $this->reportpath.'FODLYREC_'.$dateFile.'.xlsx';
            $filename = 'FODLYREC_'.$dateFile.'.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet5 = $spreadsheet->getActiveSheet();
            $sheet5->setTitle('DESCRIPTION DETAILS');
            $sheet5->setCellValue('A1', 'Description for FODLYREC at '.$dateOfData.' based on '.$ftpHeader.' if any:');
            $sheet5->setCellValue('A3', $descExcel);
            $sheet5->getStyle('A3')->getAlignment()->setWrapText(true);
            $sheet5->getColumnDimension('A')->setAutoSize(true);

            /*2nd sheet*/
            $spreadsheet->createSheet();
            $sheet = $spreadsheet->setActiveSheetIndex(1);
            $sheet->setTitle('MATCH FO TRANSACTIONS');
            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', 'Match Future Order Transactions as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet->setCellValue('A3', 'MBB BookingDate')->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('B3', 'MBB PartnerRefId')->getColumnDimension('B')->setAutoSize(true);
            $sheet->setCellValue('C3', 'MBB PriceBooking')->getColumnDimension('C')->setAutoSize(true);
            $sheet->setCellValue('D3', 'MBB Weight')->getColumnDimension('D')->setAutoSize(true);
            $sheet->setCellValue('E3', 'MBB ExpiredDate')->getColumnDimension('E')->setAutoSize(true);
            $sheet->setCellValue('F3', 'MBB Status')->getColumnDimension('F')->setAutoSize(true);
            $sheet->setCellValue('G3', 'GTP Booking Date')->getColumnDimension('G')->setAutoSize(true);
            $sheet->setCellValue('H3', 'GTP FutureOrderNo')->getColumnDimension('H')->setAutoSize(true);
            $sheet->setCellValue('I3', 'GTP Type')->getColumnDimension('I')->setAutoSize(true);
            $sheet->setCellValue('J3', 'GTP PriceBooking')->getColumnDimension('J')->setAutoSize(true);
            $sheet->setCellValue('K3', 'GTP Pricestream Match')->getColumnDimension('K')->setAutoSize(true); 
            $sheet->setCellValue('L3', 'GTP Match Date')->getColumnDimension('L')->setAutoSize(true); 
            $sheet->setCellValue('M3', 'GTP Weight')->getColumnDimension('M')->setAutoSize(true); 
            $sheet->setCellValue('N3', 'GTP ExpiredDate')->getColumnDimension('N')->setAutoSize(true); 
            $sheet->setCellValue('O3', 'GTP Status')->getColumnDimension('O')->setAutoSize(true);

            $j=3;
            foreach($allTransaction as $key=>$aTransaction){
                $j++;
                $sheet->setCellValue('A'.$j, " ".$aTransaction['detail_bookingdate'])->getColumnDimension('A')->setAutoSize(true);
                $sheet->setCellValue('B'.$j, " ".$aTransaction['detail_forefno'])->getColumnDimension('B')->setAutoSize(true);
                $sheet->setCellValue('C'.$j, $aTransaction['detail_bookingprice'])->getColumnDimension('C')->setAutoSize(true);
                $sheet->setCellValue('D'.$j, $aTransaction['detail_bookgram'])->getColumnDimension('D')->setAutoSize(true);
                $sheet->setCellValue('E'.$j, " ".$aTransaction['detail_expireddate'])->getColumnDimension('E')->setAutoSize(true);
                $sheet->setCellValue('F'.$j, $aTransaction['detail_status'])->getColumnDimension('F')->setAutoSize(true);
                $sheet->setCellValue('G'.$j, " ".$aTransaction['gtp_createdon'])->getColumnDimension('G')->setAutoSize(true);
                $sheet->setCellValue('H'.$j, $aTransaction['gtp_orderqueueno'])->getColumnDimension('H')->setAutoSize(true);
                $sheet->setCellValue('I'.$j, $aTransaction['gtp_orderqueuetype'])->getColumnDimension('I')->setAutoSize(true);
                $sheet->getStyle('J'.$j)->getNumberFormat()->setFormatCode('0.00'); 
                $sheet->setCellValue('J'.$j, $aTransaction['gtp_bookingprice'])->getColumnDimension('J')->setAutoSize(true);
                $sheet->getStyle('K'.$j)->getNumberFormat()->setFormatCode('0.000000');  
                $sheet->setCellValue('K'.$j, $aTransaction['gtp_pricematchvalue'])->getColumnDimension('K')->setAutoSize(true); 
                $sheet->setCellValue('L'.$j, " ".$aTransaction['gtp_matchon'])->getColumnDimension('L')->setAutoSize(true); 
                $sheet->getStyle('M'.$j)->getNumberFormat()->setFormatCode('0.000');  
                $sheet->setCellValue('M'.$j, $aTransaction['gtp_bookinggram'])->getColumnDimension('M')->setAutoSize(true); 
                $sheet->setCellValue('N'.$j, " ".$aTransaction['gtp_expireddate'])->getColumnDimension('N')->setAutoSize(true); 
                $sheet->setCellValue('O'.$j, $aTransaction['gtp_status'])->getColumnDimension('O')->setAutoSize(true); 
            }

            /*3rd sheet*/
            $spreadsheet->createSheet();
            $sheet4 = $spreadsheet->setActiveSheetIndex(2);
            $sheet4->setTitle('UNMATCH STATUS TRANSACTIONS');
            $sheet4->mergeCells('A1:I1');
            $sheet4->setCellValue('A1', 'Match Future Order Transactions however status is different as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet4->setCellValue('A1', 'Match Future Order Transactions as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet4->setCellValue('A3', 'MBB BookingDate')->getColumnDimension('A')->setAutoSize(true);
            $sheet4->setCellValue('B3', 'MBB PartnerRefId')->getColumnDimension('B')->setAutoSize(true);
            $sheet4->setCellValue('C3', 'MBB PriceBooking')->getColumnDimension('C')->setAutoSize(true);
            $sheet4->setCellValue('D3', 'MBB Weight')->getColumnDimension('D')->setAutoSize(true);
            $sheet4->setCellValue('E3', 'MBB ExpiredDate')->getColumnDimension('E')->setAutoSize(true);
            $sheet4->setCellValue('F3', 'MBB Status')->getColumnDimension('F')->setAutoSize(true);
            $sheet4->setCellValue('G3', 'GTP Booking Date')->getColumnDimension('G')->setAutoSize(true);
            $sheet4->setCellValue('H3', 'GTP FutureOrderNo')->getColumnDimension('H')->setAutoSize(true);
            $sheet4->setCellValue('I3', 'GTP Type')->getColumnDimension('I')->setAutoSize(true);
            $sheet4->setCellValue('J3', 'GTP PriceBooking')->getColumnDimension('J')->setAutoSize(true);
            $sheet4->setCellValue('K3', 'GTP Weight')->getColumnDimension('K')->setAutoSize(true);
            $sheet4->setCellValue('L3', 'GTP ExpiredDate')->getColumnDimension('L')->setAutoSize(true);
            $sheet4->setCellValue('M3', 'GTP Status')->getColumnDimension('M')->setAutoSize(true);

            $m=3;
            if(!empty($diffStatus)){
                foreach($diffStatus as $key=>$aTransaction){
                    $m++;
                    $sheet4->setCellValue('A'.$m, " ".$aTransaction['detail_bookingdate'])->getColumnDimension('A')->setAutoSize(true);
                    $sheet4->setCellValue('B'.$m, " ".$aTransaction['detail_forefno'])->getColumnDimension('B')->setAutoSize(true);
                    $sheet4->setCellValue('C'.$m, $aTransaction['detail_bookingprice'])->getColumnDimension('C')->setAutoSize(true);
                    $sheet4->setCellValue('D'.$m, $aTransaction['detail_bookgram'])->getColumnDimension('D')->setAutoSize(true);
                    $sheet4->setCellValue('E'.$m, " ".$aTransaction['detail_expireddate'])->getColumnDimension('E')->setAutoSize(true);
                    $sheet4->setCellValue('F'.$m, $aTransaction['detail_status'])->getColumnDimension('F')->setAutoSize(true);
                    $sheet4->setCellValue('G'.$m, " ".$aTransaction['gtp_createdon'])->getColumnDimension('G')->setAutoSize(true);
                    $sheet4->setCellValue('H'.$m, $aTransaction['gtp_orderqueueno'])->getColumnDimension('H')->setAutoSize(true);
                    $sheet4->setCellValue('I'.$m, $aTransaction['gtp_orderqueuetype'])->getColumnDimension('I')->setAutoSize(true);
                    $sheet4->getStyle('J'.$m)->getNumberFormat()->setFormatCode('0.00'); 
                    $sheet4->setCellValue('J'.$m, $aTransaction['gtp_bookingprice'])->getColumnDimension('J')->setAutoSize(true);
                    $sheet4->getStyle('K'.$m)->getNumberFormat()->setFormatCode('0.000'); 
                    $sheet4->setCellValue('K'.$m, $aTransaction['gtp_bookinggram'])->getColumnDimension('K')->setAutoSize(true);
                    $sheet4->setCellValue('L'.$m, " ".$aTransaction['gtp_expireddate'])->getColumnDimension('L')->setAutoSize(true);
                    $sheet4->setCellValue('M'.$m, $aTransaction['gtp_status'])->getColumnDimension('M')->setAutoSize(true);
                }
            }

            /*4th sheet*/
            $spreadsheet->createSheet();
            $sheet2 = $spreadsheet->setActiveSheetIndex(3);
            $sheet2->setTitle('UNMATCH TRANSACTIONS FROM MBB');
            $sheet2->mergeCells('A1:I1');
            $sheet2->setCellValue('A1', 'Unmatch Future Order Transactions From MBB to GTP as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet2->setCellValue('A3', 'MBB BookingDate')->getColumnDimension('A')->setAutoSize(true);
            $sheet2->setCellValue('B3', 'MBB PartnerRefId')->getColumnDimension('B')->setAutoSize(true);
            $sheet2->setCellValue('C3', 'MBB Type')->getColumnDimension('C')->setAutoSize(true);
            $sheet2->setCellValue('D3', 'MBB PriceBooking')->getColumnDimension('D')->setAutoSize(true);
            $sheet2->setCellValue('E3', 'MBB Weight')->getColumnDimension('E')->setAutoSize(true);
            $sheet2->setCellValue('F3', 'MBB ExpiredDate')->getColumnDimension('F')->setAutoSize(true);
            $sheet2->setCellValue('G3', 'MBB Status')->getColumnDimension('G')->setAutoSize(true);

            $k=3;
            foreach($mbbUnmatchTransaction as $key=>$aData){
                $k++;
                $sheet2->setCellValue('A'.$k, " ".$aData['detail_bookingdate'])->getColumnDimension('A')->setAutoSize(true);
                $sheet2->setCellValue('B'.$k, " ".$aData['detail_forefno'])->getColumnDimension('B')->setAutoSize(true);
                $sheet2->setCellValue('C'.$k, $aData['detail_type'])->getColumnDimension('C')->setAutoSize(true);
                $sheet2->setCellValue('D'.$k, $aData['detail_bookingprice'])->getColumnDimension('D')->setAutoSize(true);
                $sheet2->setCellValue('E'.$k, $aData['detail_bookgram'])->getColumnDimension('E')->setAutoSize(true);
                $sheet2->setCellValue('F'.$k, " ".$aData['detail_expireddate'])->getColumnDimension('F')->setAutoSize(true);
                $sheet2->setCellValue('G'.$k, $aData['detail_status'])->getColumnDimension('G')->setAutoSize(true);
            }

            /*5th sheet*/
            $spreadsheet->createSheet();
            $sheet3 = $spreadsheet->setActiveSheetIndex(4);
            $sheet3->setTitle('UNMATCH TRANSACTIONS FROM GTP');
            $sheet3->mergeCells('A1:I1');
            $sheet3->setCellValue('A1', 'List of unknown transactions in GTP for date '.$dateOfData.'. Not yet / Never reconciled with MBB.');
            $sheet3->setCellValue('A3', 'GTP Booking Date')->getColumnDimension('A')->setAutoSize(true);
            $sheet3->setCellValue('B3', 'GTP Partner Ref Id')->getColumnDimension('B')->setAutoSize(true);
            $sheet3->setCellValue('C3', 'GTP Future Order No')->getColumnDimension('C')->setAutoSize(true);
            $sheet3->setCellValue('D3', 'GTP Type')->getColumnDimension('D')->setAutoSize(true);
            $sheet3->setCellValue('E3', 'GTP PriceBooking')->getColumnDimension('E')->setAutoSize(true);
            $sheet3->setCellValue('F3', 'GTP Weight')->getColumnDimension('F')->setAutoSize(true);
            $sheet3->setCellValue('G3', 'GTP ExpiredDate')->getColumnDimension('G')->setAutoSize(true);
            $sheet3->setCellValue('H3', 'GTP Status')->getColumnDimension('H')->setAutoSize(true);

            $l=3;
            foreach($statusUnmatchFromGTPArr as $key=>$aData){
                $l++;
                $sheet3->setCellValue('A'.$l, " ".$aData['gtp_createdon'])->getColumnDimension('A')->setAutoSize(true);
                $sheet3->setCellValue('B'.$l, " ".$aData['gtp_partnerrefid'])->getColumnDimension('B')->setAutoSize(true);
                $sheet3->setCellValue('C'.$l, $aData['gtp_orderqueueno'])->getColumnDimension('C')->setAutoSize(true);
                $sheet3->setCellValue('D'.$l, $aData['gtp_orderqueuetype'])->getColumnDimension('D')->setAutoSize(true);
                $sheet3->getStyle('E'.$l)->getNumberFormat()->setFormatCode('0.00'); 
                $sheet3->setCellValue('E'.$l, $aData['gtp_bookingprice'])->getColumnDimension('E')->setAutoSize(true);
                $sheet3->getStyle('F'.$l)->getNumberFormat()->setFormatCode('0.000'); 
                $sheet3->setCellValue('F'.$l, $aData['gtp_bookinggram'])->getColumnDimension('F')->setAutoSize(true);
                $sheet3->setCellValue('G'.$l, " ".$aData['gtp_expireddate'])->getColumnDimension('G')->setAutoSize(true);
                $sheet3->setCellValue('H'.$l, $aData['gtp_status'])->getColumnDimension('H')->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($pathToSave);
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to update extraction data from FODLYREC", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function getFOMatchReport($date,$filename,$partner,$server){
        $now = common::convertUTCToUserDatetime(new \DateTime());
        $getdate               = date('Y-m-d h:i:s',$date);
        $dateFile           = date('dmY',$date);
        $createDateStart    = date_create($getdate);
        $modifyDateStart    = date_modify($createDateStart,"-2 day");
        $startDate          = date_format($modifyDateStart,"Y-m-d 16:00:00");
        $createDateEnd      = date_create($getdate);
        $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
        $endDate            = date_format($modifyDateEnd,"Y-m-d 15:59:59");
        $dateOfData         = date_format($modifyDateEnd,"d-m-Y");

        if(null != $server) $extraFlag = "#####".strtoupper($server)."#####"; 

        try {
            $foMatchItem = $this->app->orderqueueStore()
                    ->searchView()
                    ->select()
                    ->where('partnerid',$partner->id)
                    ->andWhere('status', OrderQueue::STATUS_FULLFILLED)
                    ->andWhere('modifiedon', '>=', $startDate)
                    ->andWhere('modifiedon', '<=', $endDate)
                    ->execute();
            if (count($foMatchItem) > 0){
                foreach($foMatchItem as $key=>$aMatch){
                    $orderItem = $this->app->orderStore()->getByField('id', $aMatch->orderid);
                    $listToShow[] = array(
                        'id' => $aMatch->id,
                        'orderid' => $aMatch->orderid,
                        'orderpartnerrefid' => $orderItem->partnerrefid,
                        'partnerrefid' => $aMatch->partnerrefid,
                        'orderqueueno' => $aMatch->orderqueueno,
                        'ordertype' => $aMatch->ordertype,
                        'pricetarget' => $aMatch->pricetarget,
                        'xau'  => $aMatch->xau,
                        'amount'  => $aMatch->amount,
                        'expireon'  => $aMatch->expireon->format('dmY'),
                        'matchon'  => $aMatch->matchon->format('dmY'),
                        'companybuyppg'  => $aMatch->companybuyppg,
                        'companysellppg'  => $aMatch->companysellppg,
                        'createdon'  => $aMatch->createdon->format('dmY'),
                        'modifiedon'  => $aMatch->modifiedon->format('dmY'),
                        'status'  => $aMatch->status,
                    );
                }
            }

            /*1st sheet*/
            $pathToSave = $this->reportpath.'FUTUREORDER_FULFILLED_'.$dateFile.'.xlsx';
            $filename = 'FUTUREORDER_FULFILLED_'.$dateFile.'.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('FULFILLED FUTURE ORDER');
            $sheet->mergeCells('A1:J1');
            $sheet->setCellValue('A1', 'Match Future Order Transactions as at '.$dateOfData);
            $sheet->setCellValue('A3', 'OrderQueue Reference Id')->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('B3', 'Order Queue No')->getColumnDimension('B')->setAutoSize(true);
            $sheet->setCellValue('C3', 'Order Reference Id')->getColumnDimension('C')->setAutoSize(true);
            $sheet->setCellValue('D3', 'Price Booking (RM/g)')->getColumnDimension('D')->setAutoSize(true);
            $sheet->setCellValue('E3', 'Xau Weight (g)')->getColumnDimension('E')->setAutoSize(true);
            $sheet->setCellValue('F3', 'Amount (RM)')->getColumnDimension('F')->setAutoSize(true);
            $sheet->setCellValue('G3', 'Expire On Date')->getColumnDimension('G')->setAutoSize(true);
            $sheet->setCellValue('H3', 'Match On Date')->getColumnDimension('H')->setAutoSize(true);
            $sheet->setCellValue('I3', 'Price Match (RM/g)')->getColumnDimension('I')->setAutoSize(true);
            $sheet->setCellValue('J3', 'Created On Date')->getColumnDimension('J')->setAutoSize(true);

            $j=3;
            $totalPriceBooking = 0;
            $totalPriceMatch = 0;
            $totalAmount = 0;
            $totalXau = 0;
            foreach($listToShow as $key=>$aList){
                if($aList['partnerrefid'] == 'CompanySell') $pricematch = $aList['companysellppg'];
                else $pricematch = $aList['companybuyppg'];
                $totalPriceBooking = $totalPriceBooking+$aList['pricetarget'];
                $totalXau = $totalXau+$aList['xau'];
                $totalPriceMatch = $totalPriceMatch+$pricematch;
                $totalAmount = $totalAmount+$aList['amount'];
                $j++;
                $sheet->setCellValue('A'.$j, " ".$aList['partnerrefid'])->getColumnDimension('A')->setAutoSize(true);
                $sheet->setCellValue('B'.$j, " ".$aList['orderqueueno'])->getColumnDimension('B')->setAutoSize(true);
                $sheet->setCellValue('C'.$j, " ".$aList['orderpartnerrefid'])->getColumnDimension('C')->setAutoSize(true);
                $sheet->getStyle('D'.$j)->getNumberFormat()->setFormatCode('0.00'); 
                $sheet->setCellValue('D'.$j, $aList['pricetarget'])->getColumnDimension('D')->setAutoSize(true);
                $sheet->getStyle('E'.$j)->getNumberFormat()->setFormatCode('0.000'); 
                $sheet->setCellValue('E'.$j, $aList['xau'])->getColumnDimension('E')->setAutoSize(true);
                $sheet->getStyle('F'.$j)->getNumberFormat()->setFormatCode('0.00'); 
                $sheet->setCellValue('F'.$j, $aList['amount'])->getColumnDimension('F')->setAutoSize(true);
                $sheet->setCellValue('G'.$j, " ".$aList['expireon'])->getColumnDimension('G')->setAutoSize(true);
                $sheet->setCellValue('H'.$j, " ".$aList['matchon'])->getColumnDimension('H')->setAutoSize(true);
                $sheet->getStyle('I'.$j)->getNumberFormat()->setFormatCode('0.000000'); 
                $sheet->setCellValue('I'.$j, $pricematch)->getColumnDimension('I')->setAutoSize(true);
                $sheet->setCellValue('J'.$j, " ".$aList['createdon'])->getColumnDimension('J')->setAutoSize(true);
            }
            /*$newJ = $j+1;
            $sheet->getStyle('C'.$newJ)->getNumberFormat()->setFormatCode('0.00');
            $sheet->setCellValue('C'.$newJ, $totalPriceBooking)->getColumnDimension('C')->setAutoSize(true);
            $sheet->getStyle('D'.$newJ)->getNumberFormat()->setFormatCode('0.000');
            $sheet->setCellValue('D'.$newJ, $totalXau)->getColumnDimension('D')->setAutoSize(true);
            $sheet->getStyle('E'.$newJ)->getNumberFormat()->setFormatCode('0.00');
            $sheet->setCellValue('E'.$newJ, $totalAmount)->getColumnDimension('E')->setAutoSize(true);
            $sheet->getStyle('H'.$newJ)->getNumberFormat()->setFormatCode('0.000000');
            $sheet->setCellValue('H'.$newJ, $totalPriceMatch)->getColumnDimension('H')->setAutoSize(true);*/

            $writer = new Xlsx($spreadsheet);
            $writer->save($pathToSave);

            //sendEmail
            $emailConfig = 'sendtofofulfilled';
            //$emailSubject = $extraFlag.' FUTURE_FULFILLED (DAILY) '.$dateFile;
            $emailSubject = 'FUTURE_FULFILLED (DAILY) '.$dateFile;
            $bodyEmail .= "\nSUCCESSFULLY PROCESS AND SEND.\n";
            $sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject,$emailConfig,$pathToSave,$filename);
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to update extraction data from FODLYREC - Fulfilled list", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function checkReturnCategory($transaction,$partner){
        try{
            $vaultitem = $this->app->vaultItemStore()->searchTable()->select()
                        ->where('partnerid',$partner['id'])
                        ->andWhere('serialno', $transaction['detail_serialno'])
                        ->andWhere('allocated', 1)
                        ->andWhere('status', VaultItem::STATUS_TRANSFERRING)
                        ->execute();
            if (count($vaultitem) > 0){
                foreach($vaultitem as $aItem){
                    $return['dgvreturn'] = $transaction;
                }
            } else {
                $replenishment = $this->app->replenishmentStore()
                                ->searchTable()
                                ->select()
                                ->where('partnerid',$partner['id'])
                                ->andWhere('serialno',$transaction['detail_serialno'])
                                //->andWhere('status',Replenishment::STATUS_COMPLETED)
                                ->execute();
                if (count($replenishment) > 0){
                    $return['mintedreturn'] = $transaction;
                } else {
                    $return ['notfound'] = $transaction;
                }
            }
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to differentiate return category from PHYRTN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
        return $return;
    }

    public function kilobarDVGReturn($transaction,$partner){
        try{
            $now = common::convertUTCToUserDatetime(new \DateTime());
            foreach($transaction as $aTransaction){
                $items = $this->app->vaultItemStore()->searchTable()->select()
                            ->where('partnerid',$partner['id'])
                            ->andWhere('serialno', $aTransaction['detail_serialno'])
                            //->andWhere('allocated', 1)
                            ->execute();
                if (count($items) > 0){
                    //$acelocationid = $this->app->getConfig()->{'gtp.return.acelocation'};
                    $defaultLocation = $this->getDefaultLocation();
                    foreach($items as $aItem){ 
                        if($aItem->allocated == 1 && $aItem->status == VaultItem::STATUS_TRANSFERRING){
                            $aItem->allocated = 0;
                            $aItem->vaultlocationid = $defaultLocation->id;
                            $aItem->movetovaultlocationid = 0;
                            $aItem->moverequestedon = '0000-00-00 00:00:00';
                            $aItem->status = VaultItem::STATUS_INACTIVE;
                            $aItem->returnedon = $now->format('Y-m-d H:i:s');

                            $update = $this->app->vaultitemStore()->save($aItem);
                            if(!$update){
                                $this->log("Error in DGV serial no => {$aItem->serialno}. Unable to save.", SNAP_LOG_ERROR);
                                $return['success'][$aItem->serialno] = false;
                                $return['message'][$aItem->serialno] = "Unable to save dgv serial number ".$aItem->serialno;
                            } else {
                                $return['success'][$aItem->serialno] = true;
                            }
                        } else {
                            if($aItem->status == VaultItem::STATUS_INACTIVE) $return['success'][$aItem->serialno] = true;
                        }
                    }
                } else {
                    $this->log("DGV with serial no => {$aTransaction['detail_serialno']} not found in GTP.", SNAP_LOG_ERROR);
                    $return['success'][$aTransaction['detail_serialno']][] = false;
                    $return['message'][$aTransaction['detail_serialno']][] = "Unable to find dgv serial number ".$aItem->serialno." in GTP";
                } 
            }
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to update extraction data from PHYRTN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
        return $return;
    }

    private function getDefaultLocation(){
        $defaultLocation = $this->app->vaultLocationStore()->searchTable()->select()
                                ->where("defaultlocation", 1)
                                ->andWhere("type", VaultLocation::TYPE_START)     
                                ->one(); 
        if ($defaultLocation){
            return $defaultLocation;
        }
        return false;
    }

    public function logisticReturn($transaction,$partner){//use
        try{
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $continueCreateBuybackLogistic = false;
            $continueCreateLogistic = false;
            foreach($transaction as $aTransaction){
                $categoryReason = strtolower($aTransaction['detail_returnreason']);
                /*update minted status*/
                $replenishment = $this->app->replenishmentStore()
                                    ->searchTable()
                                    ->select()
                                    ->where('partnerid',$partner['id'])
                                    ->andWhere('serialno',$aTransaction['detail_serialno'])
                                    //->andWhere('status',Replenishment::STATUS_COMPLETED)
                                    ->execute();
                if($replenishment > 0){
                    foreach($replenishment as $aReplenishment){
                        //$aReplenishment->status = Replenishment::STATUS_RETURN;
                        if($aReplenishment->returntosapon == null || empty($aReplenishment->returntosapon)){
                            $aReplenishment->returntosapon = $now->format('Y-m-d H:i:s');
                            $aReplenishment->returnreason = $aTransaction['detail_returnreason'];
                        }
                        $update = $this->app->replenishmentStore()->save($aReplenishment);
                        if (!$update){
                            $this->log("Error in replenishment minted serial no => {$aReplenishment->serialno}. Unable to save.", SNAP_LOG_ERROR);
                            $return['unableToSaveMinted'][] = $aTransaction;
                            //throw new \Exception("Unable to update replenishment - ".$item['serialNum']);
                        } 
                    }
                } else {
                    $this->log("Error in ftp phyrtn serial no => {$aTransaction["detail_serialno"]}. Serial number not found in GTP.", SNAP_LOG_ERROR);
                    $return['notFoundMinted'][] = $aTransaction;
                }
                /*end update minted status*/

                // if($categoryReason == 'buyback') {
                //     $continueCreateBuybackLogistic = true;
                //     $statusLogistic = Logistic::TYPE_BUYBACK;
                //     $items = $this->app->buybackStore()->searchTable()->select()
                //         ->where('items', 'LIKE','%'.$aTransaction['detail_serialno'].'%')
                //         ->execute();
                //     if (count($items) > 0){
                //         $continueCreateLogistic = true;
                //         foreach($items as $aItem){
                //             $typeId = $aItem->id;
                //         }
                //     } 
                // } else {
                //     if($categoryReason == 'damage') $statusLogistic = Logistic::TYPE_RTN_DAMAGE;
                //     else if($categoryReason == 'mismatch') $statusLogistic = Logistic::TYPE_RTN_MISMATCH;
                //     $items = $this->app->replenishmentStore()->searchTable()->select()
                //         ->where('serialno', $aTransaction['detail_serialno'])
                //         ->execute();
                //     if (count($items) > 0){
                //         $continueCreateLogistic = true;
                //         foreach($items as $aItem){
                //             $typeId = $aItem->id;
                //         }
                //     } 
                // }

                // if($continueCreateLogistic){
                //     $logisticCheck = $this->app->logisticStore()->searchTable()->select()
                //                         ->where('type', $statusLogistic)
                //                         ->andWhere('typeid',$typeId)
                //                         ->execute();
                //     if (count($logisticCheck) == 0){
                //         $createLogistic = $this->app->logisticStore()->create([
                //                     'type' => $statusLogistic,
                //                     'typeid' => $typeId,
                //                     'partnerid' => $partner['id'],
                //                     'frombranchid' => $aTransaction['detail_branchid'],
                //                     'status' => Logistic::STATUS_PROCESSING
                //                 ]);
                //         $saveLogistic = $this->app->logisticStore()->save($createLogistic);
                //         if(!$saveLogistic){
                //             $this->log("Error in creating logistic for => {$statusLogistic} with id => {$typeId}. Unable to save.", SNAP_LOG_ERROR);
                //             $return['unableToSaveLogistic'][] = $aTransaction;
                //         }

                //         if($continueCreateBuybackLogistic){
                //             $createBuybackLogistic = $this->app->buybackLogisticStore()->create([
                //                                         "buybackid" => $typeId, // CHECK id
                //                                         "logisticid" => $saveLogistic->id,
                //                                     ]);
                //             $saveBuybackLogistic = $this->app->buybackLogisticStore()->save($createBuybackLogistic);
                //             if (!$saveBuybackLogistic){
                //                 $this->log("Error in creating buyback logistic for buyback id => {$typeId} with logistic id => {$saveLogistic->id}. Unable to save.", SNAP_LOG_ERROR);
                //                 $return['unableToSaveBuybackLogistic'][] = $aTransaction;
                //             }
                //         }
                //     } else {
                //         $this->log("Logistic for type=> {$statusLogistic} with id => {$typeId} already exist.", SNAP_LOG_DEBUG);
                //         $return['alreadySaveLogistic'][] = $aTransaction;
                //     }
                // }
                
            }
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to update extraction data from PHYRTN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
        return $return;
    }

    public function utilisedDVG($transaction,$flag){
        try{
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $currentdate        = $now->format('Y-m-d H:i:s');
            $date               = date('Y-m-d h:i:s',$transaction['currentdate']);
            $createDateEnd      = date_create($date);
            $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
            $dateOfData         = date_format($modifyDateEnd,"d-m-Y");
            $endDate            = date_format($modifyDateEnd,"Y-m-d 15:59:59");
            
            $detailCount    = $transaction[0]['header_totalline']+1;
            $dateFile       = $transaction[0]['header_filedate'];
            $partner        = $transaction['partner'];

            $ftpHeader = $transaction[0]['header_code'].str_pad($transaction[0]['header_filename'],10, ' ', STR_PAD_RIGHT).$transaction[0]['header_filedate'].$transaction[0]['header_filesequence'].$transaction[0]['header_totalline'];

            /*get gtp extra/no record from MBB*/
            $vaultItemList = $this->app->vaultitemStore()->searchTable()->select()
                    ->where('partnerid',$partner['id'])
                    ->andWhere('allocated', 1)
                    ->execute();
            if (count($vaultItemList) > 0){
                foreach($vaultItemList as $aVault){
                    $createArray[$aVault->serialno] = $aVault->serialno;
                }
            }
            /**/

            for($i=1;$i<$detailCount;$i++){
                $transArray = $transaction[$i];
                $items = $this->app->vaultitemStore()->searchView()->select()
                        ->where('partnerid',$partner['id'])
                        ->andWhere('serialno', $transArray['detail_serialno'])
                        ->andWhere('allocated', 1)
                        ->execute();
                if (count($items) > 0){
                    foreach($items as $aItem){
                        $utilisedNo = $transArray['detail_dgv'];
                        $originalWeight = $transArray['detail_weight'];
                        $utilisedPercentage = number_format($transArray['detail_dgv']/$transArray['detail_weight']*100,2);
                        $aItem->utilised = $utilisedNo;
                
                        $update = $this->app->vaultitemStore()->save($aItem);
                        if(!$update){
                            $descExcel .= "Update failed for ".$aItem->serialno.".\n\n";
                            $this->log("Update failed for ".$aItem->serialno, SNAP_LOG_ERROR);
                        }

                        if($aItem->status == VaultItem::STATUS_ACTIVE) $statusName = 'ACTIVE';
                        if($aItem->status == VaultItem::STATUS_INACTIVE) $statusName = 'INACTIVE/COMPLETED';
                        if($aItem->status == VaultItem::STATUS_TRANSFERRING) $statusName = 'TRANSFERRING';
                        if($aItem->status == VaultItem::STATUS_PENDING) $statusName = 'PENDING';
                        if($aItem->status == VaultItem::STATUS_REMOVED) $statusName = 'REMOVED';
                        if($aItem->status == VaultItem::STATUS_PENDING_ALLOCATION) $statusName = 'PENDING ALLOCATION';

                        if($aItem->deliveryordernumber != '' && $aItem->deliveryordernumber != '') $locationname = 'MBB G4S Rac';
                        else $locationname = 'Metalor, Singapore';
                        
                        $toPutInArray = array(
                            'gtp_serialno' => $aItem->serialno,
                            'gtp_weight' => $aItem->weight,
                            'gtp_utilised' => $aItem->utilised,
                            'gtp_status' => $statusName,
                            'gtp_locationname' => $locationname
                        );
                        $reconArray[$transArray['detail_serialno']] = array_merge($transArray,$toPutInArray);

                        if($aItem->status == VaultItem::STATUS_ACTIVE){
                            $grandTotal += $originalWeight;  
                            $grandTotalUtilised += $utilisedNo; 
                        }
                    }
                } else $unmatchMBB[$transArray['detail_serialno']] = $transArray;

                /*transfer physical gold - testing mode*/
                if($transArray['detail_ownerflag'] == 'MBB') $transferTo['MBB'][] = $transArray;
                else if($transArray['detail_ownerflag'] == 'ACE') $transferTo['ACE'][] = $transArray;
                /**/
            }

            $utilisedTotalPercentage = number_format($grandTotalUtilised/$grandTotal*100,2);
            $descExcel .= "\nDGV Stock Status - Utilised($utilisedTotalPercentage%).\n";
            if(50 < $utilisedTotalPercentage){ // when percentage is almost 100%. if more than 50%
                $descExcel .= "DGV stock for MBB allocated already more than 50% utilised- {$utilisedTotalPercentage}/100 from FTP DAILYDVG today, {$dateFile}.\n\n";
            } else {
                $descExcel .= "DGV stock for MBB allocated already utilised {$utilisedTotalPercentage}/100 from FTP DAILYDVG today, {$dateFile}.\n\n";
            }
            $descExcel .= "There are total of {$grandTotalUtilised}g out of {$grandTotal}g which are still active.\n\n";
            $descExcel .= "\nSUCCESSFULLY READING.\n";

            /*get vaultlocation mbb g4s*/
            $mbbLocation = $this->app->vaultLocationStore()->searchTable()->select()
                        ->where("partnerid",$partner['id'])
                        ->andWhere("type", VaultLocation::TYPE_END)     
                        ->one();

            /*get vaultlocation ACE g4s*/
            $aceLocation = $this->app->vaultLocationStore()->searchTable()->select()
                        ->where("type", VaultLocation::TYPE_INTERMIDIATE)     
                        ->one();

            /*Added new tab 20210303. Comparison between daily transaction and daily utilised*/
            //get total of spotorder transaction with MBB as partnerid, status = confirmed.
            $totalBuyTransaction = $this->app->orderStore()->searchTable(false)->select()
                    ->addFieldSum('xau', 'total_xau')
                    ->where('partnerid', $partner['id'])
                    ->andWhere('type', Order::TYPE_COMPANYBUY)
                    ->andWhere('createdon', '<=',$endDate)
                    ->andWhere('status','IN', [Order::STATUS_CONFIRMED,Order::STATUS_COMPLETED])
                    ->first();
            $totalBuy = $totalBuyTransaction['total_xau'];

            $totalSellTransaction = $this->app->orderStore()->searchTable(false)->select()
                    ->addFieldSum('xau', 'total_xau')
                    ->where('partnerid', $partner['id'])
                    ->andWhere('type', Order::TYPE_COMPANYSELL)
                    ->andWhere('createdon', '<=',$endDate)
                    ->andWhere('status','IN', [Order::STATUS_CONFIRMED,Order::STATUS_COMPLETED])
                    ->first();
            $totalSell = $totalSellTransaction['total_xau'];

            $totalRedemptionTransaction = $this->app->redemptionStore()->searchTable(false)->select()
                    ->addFieldSum('totalweight', 'total_xau')
                    ->where('partnerid', $partner['id'])
                    ->andWhere('createdon', '<=',$endDate)
                    ->andWhere('status','IN',[Redemption::STATUS_COMPLETED,Redemption::STATUS_CONFIRMED,Redemption::STATUS_PROCESSDELIVERY])
                    ->first();
            $totalRedemption = $totalRedemptionTransaction['total_xau'];

            $gtpUtilization = $totalSell - ($totalBuy + $totalRedemption);
            $variance = $gtpUtilization - $grandTotalUtilised;
            /**/

            $pathToSave = $this->reportpath.'DAILYDGV_'.$dateFile.'.xlsx';
            $filename = 'DAILYDGV_'.$dateFile.'.xlsx';
            $spreadsheet = new Spreadsheet();
            /*1st sheet*/
            $sheet4 = $spreadsheet->getActiveSheet();
            $sheet4->setTitle('DESCRIPTION DETAILS');
            $sheet4->setCellValue('A1', 'Description for DAILYDGV at '.$dateOfData.' based on '.$ftpHeader.' if any:');
            $sheet4->getColumnDimension('A')->setAutoSize(true);
            $sheet4->setCellValue('A3', $descExcel);
            $sheet4->getStyle('A3')->getAlignment()->setWrapText(true);

            //2nd sheet
            $spreadsheet->createSheet();
            $sheet = $spreadsheet->setActiveSheetIndex(1);
            $sheet->setTitle('MIB DGV KILOBAR RECONCILIATION');
            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', 'MIB DGV Kilobar Serial Number Reconciliation as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet->setCellValue('A3', 'MBB SerialNo')->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('B3', 'MBB Weight')->getColumnDimension('B')->setAutoSize(true);
            $sheet->setCellValue('C3', 'MBB DGV')->getColumnDimension('C')->setAutoSize(true);
            $sheet->setCellValue('D3', 'MBB OwnerFlag')->getColumnDimension('D')->setAutoSize(true);
            $sheet->setCellValue('E3', 'Vault Location')->getColumnDimension('E')->setAutoSize(true);
            $sheet->setCellValue('F3', 'GTP SerialNo')->getColumnDimension('F')->setAutoSize(true);
            $sheet->setCellValue('G3', 'GTP Status')->getColumnDimension('G')->setAutoSize(true);
            $j=3;
            foreach($reconArray as $key=>$aData){
                $j++;
                $sheet->setCellValue('A'.$j, $aData['detail_serialno'])->getColumnDimension('A')->setAutoSize(true);
                $sheet->setCellValue('B'.$j, $aData['detail_weight'])->getColumnDimension('B')->setAutoSize(true);
                $sheet->setCellValue('C'.$j, $aData['detail_dgv'])->getColumnDimension('C')->setAutoSize(true);
                $sheet->setCellValue('D'.$j, $aData['detail_ownerflag'])->getColumnDimension('D')->setAutoSize(true);
                $sheet->setCellValue('E'.$j, $aData['gtp_locationname'])->getColumnDimension('E')->setAutoSize(true);
                $sheet->setCellValue('F'.$j, $aData['gtp_serialno'])->getColumnDimension('F')->setAutoSize(true);
                $sheet->setCellValue('G'.$j, $aData['gtp_status'])->getColumnDimension('G')->setAutoSize(true);
            }
            //3rd sheet
            $spreadsheet->createSheet();
            $sheet2 = $spreadsheet->setActiveSheetIndex(2);
            $sheet2->setTitle('UNMATCH KILOBAR');
            $sheet2->mergeCells('A1:I1');
            $sheet2->setCellValue('A1', 'MIB UNmatch DGV Kilobar in GTP as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet2->setCellValue('A3', 'MBB SerialNo')->getColumnDimension('A')->setAutoSize(true);
            $sheet2->setCellValue('B3', 'MBB Weight')->getColumnDimension('B')->setAutoSize(true);
            $sheet2->setCellValue('C3', 'MBB DGV')->getColumnDimension('C')->setAutoSize(true);
            $sheet2->setCellValue('D3', 'MBB OwnerFlag')->getColumnDimension('D')->setAutoSize(true);
            $k=3;
            foreach($unmatchMBB as $key=>$aData){
                $k++;
                $sheet2->setCellValue('A'.$k, $aData['detail_serialno'])->getColumnDimension('A')->setAutoSize(true);
                $sheet2->setCellValue('B'.$k, $aData['detail_weight'])->getColumnDimension('B')->setAutoSize(true);
                $sheet2->setCellValue('C'.$k, $aData['detail_dgv'])->getColumnDimension('C')->setAutoSize(true);
                $sheet2->setCellValue('D'.$k, $aData['detail_ownerflag'])->getColumnDimension('D')->setAutoSize(true);
            }
            //4th sheet
            $spreadsheet->createSheet();
            $sheet3 = $spreadsheet->setActiveSheetIndex(3);
            $sheet3->setTitle('DGV UTILIZATION');
            $sheet3->mergeCells('A1:I1');
            $sheet3->setCellValue('A1', 'DGV Utilization as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet3->mergeCells('A3:E3')->getStyle('A3:D3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('66B2FF');
            $sheet3->setCellValue('A3', 'GTP')->getColumnDimension('A')->setAutoSize(true);
            $sheet3->mergeCells('F3:G3')->getStyle('E3:F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF66');
            $sheet3->setCellValue('F3', 'MBB')->getColumnDimension('E')->setAutoSize(true);
            $sheet3->setCellValue('A4', 'DATE')->getColumnDimension('A')->setAutoSize(true);
            $sheet3->setCellValue('B4', 'TOTAL BUY')->getColumnDimension('B')->setAutoSize(true);
            $sheet3->setCellValue('C4', 'TOTAL SELL')->getColumnDimension('C')->setAutoSize(true);
            $sheet3->setCellValue('D4', 'TOTAL REDEMPTION')->getColumnDimension('D')->setAutoSize(true);
            $sheet3->setCellValue('E4', 'UTILIZATION')->getColumnDimension('E')->setAutoSize(true);
            $sheet3->setCellValue('F4', 'DATE')->getColumnDimension('F')->setAutoSize(true);
            $sheet3->setCellValue('G4', 'UTILIZATION')->getColumnDimension('G')->setAutoSize(true);
            $sheet3->mergeCells('H3:H4');
            $sheet3->setCellValue('H3', 'VARIANCE(GTP-MBB)')->getColumnDimension('G')->setAutoSize(true);

            $sheet3->setCellValue('A5', $dateOfData)->getColumnDimension('A')->setAutoSize(true);
            $sheet3->getStyle('B5')->getNumberFormat()->setFormatCode('0.000'); 
            $sheet3->setCellValue('B5', $totalBuy)->getColumnDimension('B')->setAutoSize(true);
            $sheet3->getStyle('C5')->getNumberFormat()->setFormatCode('0.000'); 
            $sheet3->setCellValue('C5', $totalSell)->getColumnDimension('C')->setAutoSize(true);
            $sheet3->getStyle('D5')->getNumberFormat()->setFormatCode('0.000'); 
            $sheet3->setCellValue('D5', $totalRedemption)->getColumnDimension('D')->setAutoSize(true);
            $sheet3->getStyle('E5')->getNumberFormat()->setFormatCode('0.000'); 
            $sheet3->setCellValue('E5', $gtpUtilization)->getColumnDimension('E')->setAutoSize(true);
            $sheet3->setCellValue('F5', $dateOfData)->getColumnDimension('F')->setAutoSize(true);
            $sheet3->getStyle('G5')->getNumberFormat()->setFormatCode('0.000'); 
            $sheet3->setCellValue('G5', $grandTotalUtilised)->getColumnDimension('G')->setAutoSize(true);
            $sheet3->getStyle('H5')->getNumberFormat()->setFormatCode('0.000'); 
            $sheet3->setCellValue('H5', $variance)->getColumnDimension('H')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $writer->save($pathToSave);
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to update extraction data from DAILYDVG", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function summaryChecking($transaction){
        try{
            $unmatch = array();
            for($i=1;$i<count($transaction);$i++){
                $transArray = $transaction[$i];
                $countDetail[$transArray['detail_branchid']][$transArray['detail_denomination']][] = $transArray;
                $countSummary[$transArray['summary_branchid']][$transArray['summary_denomination']] = $transArray['summary_quantity'];
            }

            foreach($countDetail as $key=>$value){
                foreach ($value as $key2=>$aValue){
                    $countByRow[$key][$key2] = count($aValue);
                }
            }

            foreach($countByRow as $key=>$value){
                foreach ($value as $key2=>$aValue){
                    if($aValue != $countSummary[$key][$key2]){
                        $unmatch[$key][] = $key2;
                    }
                }
            }

            $statement = '';
            $continue = false;
            if(!empty($unmatch)){
                foreach($unmatch as $key=>$value){
                    if(!empty($key)){
                        $statement .= $key;
                        foreach($value as $key2=>$aValue){
                            $statement .= ' with denomination of '.$aValue;
                        }
                        $statement .= ', ';
                    } else $continue = true;
                }

                if(!$continue){
                    throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Number of summary and row detail is not the same for '.$statement]);
                }
            }
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to update extraction data from PHYINV", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function summaryInventory($transaction){
        for($i=1;$i<count($transaction);$i++){
            $transArray = $transaction[$i];
            $countSummary[$transArray['summary_branchid']][$transArray['summary_denomination']] = $transArray['summary_quantity'];
        }

        return $countSummary;
    }

    public function dailyReconTrans($transaction,$flag){
        try{
            $now = common::convertUTCToUserDatetime(new \DateTime());
            $date               = date('Y-m-d h:i:s',$transaction['currentdate']);
            $createDateStart    = date_create($date);
            $modifyDateStart    = date_modify($createDateStart,"-2 day");
            $startDate          = date_format($modifyDateStart,"Y-m-d 16:00:00");
            $createDateEnd      = date_create($date);
            $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
            $endDate            = date_format($modifyDateEnd,"Y-m-d 15:59:59");
            $dateOfData         = date_format($modifyDateEnd,"d-m-Y");
            $partner            = $transaction['partner'];
            $detailCount        = $transaction[0]['header_totalline']+1;
            $dateFile           = $transaction[0]['header_filedate'];
            $matchTransaction   = array();
            $unmatchTransaction = array();
            $unmatchStatus      = array();
            $ftpHeader          = $transaction[0]['header_code'].str_pad($transaction[0]['header_filename'],10, ' ', STR_PAD_RIGHT).$transaction[0]['header_filedate'].$transaction[0]['header_filesequence'].$transaction[0]['header_totalline'];

            $orderType = array('BUY','SELL');
            $redemptionType = 'REDEMPTION';
            $buybackType = 'BUYBACK';
            for($i=1;$i<$detailCount;$i++){
                $transArray = $transaction[$i];
                $type = $transArray['detail_transactiontype'];
                $transactionCode = $transArray['detail_transactioncode'];
                $description = $transArray['detail_transactiondesc'];
                $transactiontime = $transArray['detail_transactiontime'];
                $mbbXau = $transArray['detail_transactiongram'];
                $mbbAmount = $transArray['detail_transactionamount'];
                $reverseStatus = $transArray['detail_reversaltran']; //Y = reverse, N = not reverse

                foreach($orderType as $aType){
                    if (strpos($description, $aType) !== FALSE) $transactionType = 'order';
                }
                if (strpos($description, $redemptionType) !== FALSE) $transactionType = 'redemption';
                if (strpos($description, $buybackType) !== FALSE) $transactionType = 'buyback';

                if($transactionType == 'order' && $transactionCode!=null){ //add transaction code as not null because refer to 01/04/2021 ftp. MBB have unknown line.
                    $pricevalidation = $this->app->pricevalidationStore()
                                        ->searchTable()
                                        ->select()
                                        ->where('partnerid',$partner['id'])
                                        ->andWhere('uuid',$transactionCode)
                                        ->andwhere('createdon', '>=', $startDate)
                                        ->andWhere('createdon', '<=', $endDate)
                                        ->execute();
                    if(count($pricevalidation) > 0){
                        foreach($pricevalidation as $aPrice){
                            $transArray['detail_matching'] = 'PRICE';
                            $orderItem = $this->app->orderStore()->getByField('partnerrefid', $transactionCode);
                            if($orderItem){
                                $reversal = ($orderItem->status == Order::STATUS_CANCELLED) ? 'Y':'N';
                                if($orderItem->status == Order::STATUS_PENDING) $statusname = 'PENDING';
                                elseif($orderItem->status == Order::STATUS_CONFIRMED) $statusname = 'CONFIRMED';
                                elseif($orderItem->status == Order::STATUS_PENDINGPAYMENT) $statusname = 'PENDING PAYMENT';
                                elseif($orderItem->status == Order::STATUS_PENDINGCANCEL) $statusname = 'PENDING CANCEL';
                                elseif($orderItem->status == Order::STATUS_CANCELLED) $statusname = 'CANCELLED';
                                elseif($orderItem->status == Order::STATUS_COMPLETED) $statusname = 'COMPLETED';
                                elseif($orderItem->status == Order::STATUS_EXPIRED) $statusname = 'EXPIRED';
                                $toPutInArray = array(
                                    'gtp_transactioncode' => $orderItem->orderno,
                                    'gtp_partnerrefid' => $orderItem->remarks, //MBB send 'remarks' value for spot order
                                    'gtp_type' => $orderItem->type,
                                    'gtp_transactiongram' => $orderItem->xau,
                                    'gtp_transactiongoldprice' => $orderItem->price,
                                    'gtp_transactionamount' => $orderItem->amount,
                                    'gtp_reversal' => $reversal,
                                    'gtp_uuid' => $aPrice->uuid,
                                    'gtp_transactiontime' => $orderItem->createdon->format('His'),
                                    'gtp_status' => $statusname
                                );

                                /*check status cancel/reverse*/
                                if($reverseStatus == $reversal){
                                    $matchTransaction[$transactionCode][$description] = array_merge($transArray,$toPutInArray);
                                    //cr == buy at mbb ftp / buy at mbb ftp == sell at gtp
                                    //dr == sell at mbb ftp / sell at mbb ftp == buy at gtp 
                                    //PO = buy / SO = sell
                                    if($reverseStatus == 'N'){
                                        if($type == 'DR'){
                                            $totalXauBuyOrder += number_format($mbbXau,3,'.','');
                                            $totalAmountBuyOrder += number_format($mbbAmount,2,'.','');
                                        } else {
                                            $totalXauSellOrder += number_format($mbbXau,3,'.','');
                                            $totalAmountSellOrder += number_format($mbbAmount,2,'.','');
                                        }
                                    }
                                } else {
                                    $unmatchStatus[$transactionCode][$description] = array_merge($transArray,$toPutInArray);
                                }

                                $orderItem->reconciled = 1;
                                $orderItem->reconciledon = $now->format('Y-m-d H:i:s');
                                $orderSave = $this->app->orderStore()->save($orderItem);
                                if(!$orderSave){
                                    $descExcel .= "Unable to update reconciled details to order - ".$orderItem->orderno.".\n\n";
                                    $this->log("Unable to update reconciled details to order - ".$orderItem->orderno, SNAP_LOG_ERROR);
                                }
                                /**/
                            } else $unmatchTransaction[$transactionCode][$description] = $transArray;
                        }
                    } else {
                        $orderHandle = $this->app->orderStore()->searchView();
                        $orderItem = $orderHandle
                                    ->select()
                                    ->where('partnerid',$partner['id'])
                                    ->andWhere('createdon', '>=', $startDate)
                                    ->andWhere('createdon', '<=', $endDate)
                                    ->andWhere($orderHandle->raw("(ord_partnerrefid = '{$transactionCode}'"), 'OR', $orderHandle->raw("ord_remarks LIKE '%{$transactionCode}%')"))
                                    ->execute();
                        if(count($orderItem) > 0){
                            foreach($orderItem as $anOrder){
                                $transArray['detail_matching'] = 'ORDER';
                                $reversal = ($anOrder->status == Order::STATUS_CANCELLED) ? 'Y':'N';
                                if($anOrder->status == Order::STATUS_PENDING) $statusname = 'PENDING';
                                elseif($anOrder->status == Order::STATUS_CONFIRMED) $statusname = 'CONFIRMED';
                                elseif($anOrder->status == Order::STATUS_PENDINGPAYMENT) $statusname = 'PENDING PAYMENT';
                                elseif($anOrder->status == Order::STATUS_PENDINGCANCEL) $statusname = 'PENDING CANCEL';
                                elseif($anOrder->status == Order::STATUS_CANCELLED) $statusname = 'CANCELLED';
                                elseif($anOrder->status == Order::STATUS_COMPLETED) $statusname = 'COMPLETED';
                                elseif($anOrder->status == Order::STATUS_EXPIRED) $statusname = 'EXPIRED';
                                $toPutInArray = array(
                                    'gtp_transactioncode' => $anOrder->orderno,
                                    'gtp_partnerrefid' => $anOrder->remarks,
                                    'gtp_type' => $anOrder->type,
                                    'gtp_transactiongram' => $anOrder->xau,
                                    'gtp_transactiongoldprice' => $anOrder->price,
                                    'gtp_transactionamount' => $anOrder->amount,
                                    'gtp_reversal' => $reversal,
                                    'gtp_uuid' => $anOrder->uuid,
                                    'gtp_transactiontime' => $anOrder->createdon->format('His'),
                                    'gtp_status' => $statusname
                                );

                                /*check status cancel/reverse*/
                                if($reverseStatus == $reversal){
                                    $matchTransaction[$transactionCode][$description] = array_merge($transArray,$toPutInArray);
                                    //cr == buy at mbb ftp / buy at mbb ftp == sell at gtp
                                    //dr == sell at mbb ftp / sell at mbb ftp == buy at gtp 
                                    //PO = buy / SO = sell
                                    if($reverseStatus == 'N'){
                                        if($type == 'DR'){
                                            $totalXauBuyOrder += number_format($mbbXau,3,'.','');
                                            $totalAmountBuyOrder += number_format($mbbAmount,2,'.','');
                                        } else {
                                            $totalXauSellOrder += number_format($mbbXau,3,'.','');
                                            $totalAmountSellOrder += number_format($mbbAmount,2,'.','');
                                        }
                                    }
                                } else $unmatchStatus[$transactionCode][$description] = array_merge($transArray,$toPutInArray);

                                $anOrder->reconciled = 1;
                                $anOrder->reconciledon = $now->format('Y-m-d H:i:s');
                                $orderSave = $this->app->orderStore()->save($anOrder);
                                if(!$orderSave){
                                    $descExcel .= "Unable to update reconciled details to order - ".$anOrder->orderno.".\n\n";
                                    $this->log("Unable to update reconciled details to order - ".$anOrder->orderno, SNAP_LOG_ERROR);
                                }
                                /**/
                            }
                        } else {
                            $transArray['detail_matching'] = 'ORDER';
                            $unmatchTransaction[$transactionCode][$description] = $transArray;
                        }
                    }
                } else if($transactionType == 'redemption' && $transactionCode!=null){ //add transaction code as not null because refer to 01/04/2021 ftp. MBB have unknown line.
                    $redemptionItem = $this->app->redemptionStore()
                                    ->searchView()
                                    ->select()
                                    ->where('partnerid',$partner['id'])
                                    ->andWhere('partnerrefno',$transactionCode)
                                    ->andWhere('createdon', '>=', $startDate)
                                    ->andWhere('createdon', '<=', $endDate)
                                    //->andWhere('reconciled',0)
                                    ->execute();
                    if(count($redemptionItem) > 0){
                        foreach($redemptionItem as $aItem){
                            $reversal = ($aItem->status == Redemption::STATUS_REVERSED) ? 'Y':'N';
                            if($aItem->status == Redemption::STATUS_PENDING) $statusname = 'PENDING';
                            elseif($aItem->status == Redemption::STATUS_CONFIRMED) $statusname = 'CONFIRMED';
                            elseif($aItem->status == Redemption::STATUS_COMPLETED) $statusname = 'COMPLETED';
                            elseif($aItem->status == Redemption::STATUS_FAILED) $statusname = 'FAILED';
                            elseif($aItem->status == Redemption::STATUS_PROCESSDELIVERY) $statusname = 'PROCESS DELIVERY';
                            elseif($aItem->status == Redemption::STATUS_CANCELLED) $statusname = 'CANCELLED';
                            elseif($aItem->status == Redemption::STATUS_REVERSED) $statusname = 'REVERSED';
                            elseif($aItem->status == Redemption::STATUS_FAILEDDELIVERY) $statusname = 'FAILED DELIVERY';
                            $toPutInArray = array(
                                'gtp_transactioncode' => $aItem->redemptionno,
                                'gtp_partnerrefid' => $aItem->partnerrefno,
                                'gtp_type' => 'Redemption',
                                'gtp_transactiongram' => $aItem->totalweight,
                                'gtp_transactiongoldprice' => '',
                                'gtp_transactionamount' => '',
                                'gtp_reversal' => $reversal,
                                'gtp_uuid' => '',
                                'gtp_transactiontime' => $aItem->createdon->format('His'),
                                'gtp_status' => $statusname
                            );

                            /*check status cancel/reverse*/
                            if($reverseStatus == $reversal){
                                $matchTransaction[$transactionCode][$description] = array_merge($transArray,$toPutInArray);
                                if($reverseStatus == 'N') {
                                    $totalXauRedemption += number_format($mbbXau,3,'.','');
                                    $totalAmountRedemption += number_format($mbbAmount,2,'.','');
                                }
                            } else $unmatchStatus[$transactionCode][$description] = array_merge($transArray,$toPutInArray);

                            $aItem->reconciled = 1;
                            $aItem->reconciledon = $now->format('Y-m-d H:i:s');
                            $redemptionSave = $this->app->redemptionStore()->save($aItem);
                            if(!$redemptionSave){
                                $descExcel .= "Unable to update reconciled details to redemption - ".$aItem->redemptionno.".\n\n";
                                $this->log("Unable to update reconciled details to redemption - ".$aItem->redemptionno, SNAP_LOG_ERROR);
                            }
                            /**/ 
                        }
                    } else $unmatchTransaction[$transactionCode][$description] = $transArray;
                } else if($transactionType == 'buyback' && $transactionCode!=null){ //add transaction code as not null because refer to 01/04/2021 ftp. MBB have unknown line.
                    $buybackHandle = $this->app->buybackStore()->searchView();
                    $buybackItem = $buybackHandle
                                ->select()
                                ->where('partnerid',$partner['id'])
                                ->andWhere('createdon', '>=', $startDate)
                                ->andWhere('createdon', '<=', $endDate)
                                ->andWhere($buybackHandle->raw("(byb_partnerrefno = '{$transactionCode}'"), 'OR', $buybackHandle->raw("byb_remarks LIKE '%{$transactionCode}%')"))
                                //->andWhere('reconciled',0)
                                ->execute();
                    if(count($buybackItem) > 0){
                        foreach($buybackItem as $aItem){
                            $reversal = ($aItem->status == Buyback::STATUS_REVERSED) ? 'Y':'N';
                            if($aItem->status == Buyback::STATUS_PENDING) $statusname = 'PENDING';
                            elseif($aItem->status == Buyback::STATUS_CONFIRMED) $statusname = 'CONFIRMED';
                            elseif($aItem->status == Buyback::STATUS_PROCESSCOLLECT) $statusname = 'PROCESS COLLECT';
                            elseif($aItem->status == Buyback::STATUS_COMPLETED) $statusname = 'COMPLETED';
                            elseif($aItem->status == Buyback::STATUS_FAILED) $statusname = 'FAILED';
                            elseif($aItem->status == Buyback::STATUS_REVERSED) $statusname = 'REVERSED';
                            $toPutInArray = array(
                                'gtp_transactioncode' => $aItem->buybackno,
                                'gtp_partnerrefid' => $aItem->partnerrefno,
                                'gtp_type' => 'Buyback',
                                'gtp_transactiongram' => $aItem->totalweight,
                                'gtp_transactiongoldprice' => $aItem->price,
                                'gtp_transactionamount' => $aItem->totalamount,
                                'gtp_reversal' => $reversal,
                                'gtp_uuid' => $aItem->uuid,
                                'gtp_transactiontime' => $aItem->createdon->format('His'),
                                'gtp_status' => $statusname,
                            );

                            /*check status cancel/reverse*/
                            if($reverseStatus == $reversal){
                                $matchTransaction[$transactionCode][$description] = array_merge($transArray,$toPutInArray);
                                if($reverseStatus == 'N'){
                                    $totalXauBuyback += number_format($mbbXau,3,'.','');
                                    $totalAmountBuyback += number_format($mbbAmount,2,'.','');
                                }
                            } else $unmatchStatus[$transactionCode][$description] = array_merge($transArray,$toPutInArray);

                            $aItem->reconciled = 1;
                            $aItem->reconciledon = $now->format('Y-m-d H:i:s');
                            $buybackSave = $this->app->buybackStore()->save($aItem);
                            if(!$buybackSave){
                                $descExcel .= "Unable to update reconciled details to buyback - ".$aItem->buybackno.".\n\n";
                                $this->log("Unable to update reconciled details to buyback - ".$aItem->buybackno, SNAP_LOG_ERROR);
                            }
                            /**/ 
                        }
                    } else $unmatchTransaction[$transactionCode][$description] = $transArray;
                } else $unmatchTransaction[$transactionCode][$description] = $transArray;
            }

            $descExcel .= "Total xau for SO = ";
            $descExcel .= ($totalXauSellOrder > 0) ? $totalXauSellOrder : 0;
            $descExcel .= "\n";
            $descExcel .= "Total amount for SO = ";
            $descExcel .= ($totalAmountSellOrder > 0) ? $totalAmountSellOrder : 0;
            $descExcel .= "\n\n";

            $descExcel .= "Total xau for PO = ";
            $descExcel .= ($totalXauBuyOrder > 0) ? $totalXauBuyOrder : 0;
            $descExcel .= "\n";
            $descExcel .= "Total amount for PO = ";
            $descExcel .= ($totalAmountBuyOrder > 0) ? $totalAmountBuyOrder : 0;
            $descExcel .= "\n\n";

            $descExcel .= "Total xau for Redemption = ";
            $descExcel .= ($totalXauRedemption > 0) ? $totalXauRedemption : 0;
            $descExcel .= "\n";
            $descExcel .= "Total amount for Redemption = ";
            $descExcel .= ($totalAmountRedemption > 0) ? $totalAmountRedemption : 0;
            $descExcel .= "\n\n";

            $descExcel .= "Total xau for Buyback = ";
            $descExcel .= ($totalXauBuyback > 0) ? $totalXauBuyback : 0;
            $descExcel .= "\n";
            $descExcel .= "Total amount for Buyback = ";
            $descExcel .= ($totalAmountBuyback > 0) ? $totalAmountBuyback : 0;
            $descExcel .= "\n\n";

            $descExcel .= "\nPlease combine total xau and total amount from FODLYREC and DAILYTRN before compare with M2UStatement.\n";
            $descExcel .= "\nSUCCESSFULLY READING.\n";

            /*get unmatch from order/redemption/buyback*/
            $orderItem = $this->app->orderStore()
                ->searchView()
                ->select()
                ->where('partnerid',$partner['id'])
                ->andWhere('reconciled',0)
                ->andWhere('createdon', '>=', $startDate)
                ->andWhere('createdon', '<=', $endDate)
                ->execute();
            if(count($orderItem) > 0){
                foreach($orderItem as $aItem) {
                    $reversal = ($aItem->status == Order::STATUS_CANCELLED) ? 'Y':'N';
                    if($aItem->status == Order::STATUS_PENDING) $statusname = 'PENDING';
                    elseif($aItem->status == Order::STATUS_CONFIRMED) $statusname = 'CONFIRMED';
                    elseif($aItem->status == Order::STATUS_PENDINGPAYMENT) $statusname = 'PENDING PAYMENT';
                    elseif($aItem->status == Order::STATUS_PENDINGCANCEL) $statusname = 'PENDING CANCEL';
                    elseif($aItem->status == Order::STATUS_CANCELLED) $statusname = 'CANCELLED';
                    elseif($aItem->status == Order::STATUS_COMPLETED) $statusname = 'COMPLETED';
                    elseif($aItem->status == Order::STATUS_EXPIRED) $statusname = 'EXPIRED';
                    $gtpUnMatch[] = array(
                        'gtp_transactioncode' => $aItem->orderno,
                        'gtp_partnerrefid' => $aItem->remarks,
                        'gtp_type' => $aItem->type,
                        'gtp_transactiongram' => $aItem->xau,
                        'gtp_transactiongoldprice' => $aItem->price,
                        'gtp_transactionamount' => $aItem->amount,
                        'gtp_reversal' => $reversal,
                        'gtp_uuid' => $aItem->uuid,
                        'gtp_transactiontime' => $aItem->createdon->format('His'),
                        'gtp_status' => $statusname
                    );
                }
            }

            $redemptionItem = $this->app->redemptionStore()
                ->searchView()
                ->select()
                ->where('partnerid',$partner['id'])
                ->andWhere('reconciled',0)
                ->andWhere('createdon', '>=', $startDate)
                ->andWhere('createdon', '<=', $endDate)
                ->execute();
            if(count($redemptionItem) > 0){
                foreach($redemptionItem as $aItem) {
                    $reversal = ($aItem->status == Redemption::STATUS_REVERSED) ? 'Y':'N';
                    if($aItem->status == Redemption::STATUS_PENDING) $statusname = 'PENDING';
                    elseif($aItem->status == Redemption::STATUS_CONFIRMED) $statusname = 'CONFIRMED';
                    elseif($aItem->status == Redemption::STATUS_COMPLETED) $statusname = 'COMPLETED';
                    elseif($aItem->status == Redemption::STATUS_FAILED) $statusname = 'FAILED';
                    elseif($aItem->status == Redemption::STATUS_PROCESSDELIVERY) $statusname = 'PROCESS DELIVERY';
                    elseif($aItem->status == Redemption::STATUS_CANCELLED) $statusname = 'CANCELLED';
                    elseif($aItem->status == Redemption::STATUS_REVERSED) $statusname = 'REVERSED';
                    elseif($aItem->status == Redemption::STATUS_FAILEDDELIVERY) $statusname = 'FAILED DELIVERY';
                    $gtpUnMatch[] = array(
                        'gtp_transactioncode' => $aItem->redemptionno,
                        'gtp_partnerrefid' => $aItem->partnerrefno,
                        'gtp_type' => 'Redemption',
                        'gtp_transactiongram' => $aItem->totalweight,
                        'gtp_transactiongoldprice' => '',
                        'gtp_transactionamount' => '',
                        'gtp_reversal' => $reversal,
                        'gtp_uuid' => '',
                        'gtp_transactiontime' => $aItem->createdon->format('His'),
                        'gtp_status' => $statusname
                    );
                }
            }

            $buybackItem = $this->app->buybackStore()
                ->searchView()
                ->select()
                ->where('partnerid',$partner['id'])
                ->andWhere('reconciled',0)
                ->andWhere('createdon', '>=', $startDate)
                ->andWhere('createdon', '<=', $endDate)
                ->execute();
            if(count($buybackItem) > 0){
                foreach($buybackItem as $aItem) {
                    $reversal = ($aItem->status == Buyback::STATUS_REVERSED) ? 'Y':'N';
                    if($aItem->status == Buyback::STATUS_PENDING) $statusname = 'PENDING';
                    elseif($aItem->status == Buyback::STATUS_CONFIRMED) $statusname = 'CONFIRMED';
                    elseif($aItem->status == Buyback::STATUS_PROCESSCOLLECT) $statusname = 'PROCESS COLLECT';
                    elseif($aItem->status == Buyback::STATUS_COMPLETED) $statusname = 'COMPLETED';
                    elseif($aItem->status == Buyback::STATUS_FAILED) $statusname = 'FAILED';
                    elseif($aItem->status == Buyback::STATUS_REVERSED) $statusname = 'REVERSED';
                    $gtpUnMatch[] = array(
                        'gtp_transactioncode' => $aItem->buybackno,
                        'gtp_partnerrefid' => $aItem->partnerrefno,
                        'gtp_type' => 'Buyback',
                        'gtp_transactiongram' => $aItem->totalweight,
                        'gtp_transactiongoldprice' => $aItem->price,
                        'gtp_transactionamount' => $aItem->totalamount,
                        'gtp_reversal' => $reversal,
                        'gtp_uuid' => $aItem->uuid,
                        'gtp_transactiontime' => $aItem->createdon->format('His'),
                        'gtp_status' => $statusname
                    );
                }
            }
            /**/

            $pathToSave = $this->reportpath.'DAILYTRN_'.$dateFile.'.xlsx';
            $filename = 'DAILYTRN_'.$dateFile.'.xlsx';
            $spreadsheet = new Spreadsheet();
            /*1st sheet*/
            $sheet4 = $spreadsheet->getActiveSheet();
            $sheet4->setTitle('DESCRIPTION DETAILS');
            $sheet4->setCellValue('A1', 'Description for DAILYTRN at '.$dateOfData.' based on '.$ftpHeader.' if any:');
            $sheet4->getColumnDimension('A')->setAutoSize(true);
            $sheet4->setCellValue('A3', $descExcel);
            $sheet4->getStyle('A3')->getAlignment()->setWrapText(true);

            /*2nd sheet*/
            $spreadsheet->createSheet();
            $sheet = $spreadsheet->setActiveSheetIndex(1);
            $sheet->setTitle('MATCH TRANSACTION');
            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', 'Match Daily Transactions as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet->setCellValue('A3', 'MBB TransactionTime')->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('B3', 'MBB TransactionCode')->getColumnDimension('B')->setAutoSize(true);
            //$sheet->setCellValue('C3', 'MBB Type')->getColumnDimension('B')->setAutoSize(true);
            $sheet->setCellValue('C3', 'MBB Gram')->getColumnDimension('C')->setAutoSize(true);
            $sheet->setCellValue('D3', 'MBB Reversal')->getColumnDimension('D')->setAutoSize(true);
            //$sheet->setCellValue('E3', 'MBB GoldPrice')->getColumnDimension('E')->setAutoSize(true);
            $sheet->setCellValue('E3', 'MBB Amount')->getColumnDimension('E')->setAutoSize(true);
            $sheet->setCellValue('F3', 'GTP TransactionTime')->getColumnDimension('F')->setAutoSize(true);
            $sheet->setCellValue('G3', 'GTP TransactionCode')->getColumnDimension('G')->setAutoSize(true);
            $sheet->setCellValue('H3', 'GTP PriceQueryId')->getColumnDimension('H')->setAutoSize(true);
            $sheet->setCellValue('I3', 'GTP Type')->getColumnDimension('I')->setAutoSize(true);
            $sheet->setCellValue('J3', 'GTP Gram')->getColumnDimension('J')->setAutoSize(true);
            $sheet->setCellValue('K3', 'GTP Reversal')->getColumnDimension('K')->setAutoSize(true);
            $sheet->setCellValue('L3', 'GTP GoldPrice (P1)')->getColumnDimension('L')->setAutoSize(true);
            $sheet->setCellValue('M3', 'GTP Amount')->getColumnDimension('M')->setAutoSize(true);

            $j=3;
            foreach($matchTransaction as $key=>$aData){
                foreach($aData as $key2 => $aValue){
                    $j++;
                    //$sheet->getStyle('A'.$j)->getNumberFormat()->setFormatCode('000000'); 
                    $sheet->setCellValue('A'.$j, " ".$aData[$key2]['detail_transactiontime'])->getColumnDimension('A')->setAutoSize(true);
                    $sheet->setCellValue('B'.$j, " ".$aData[$key2]['detail_transactioncode'])->getColumnDimension('B')->setAutoSize(true);
                    //$sheet->setCellValue('B'.$j, $aData[$key2]['detail_transactiondesc'])->getColumnDimension('B')->setAutoSize(true);
                    $sheet->setCellValue('C'.$j, $aData[$key2]['detail_transactiongram'])->getColumnDimension('C')->setAutoSize(true);
                    $sheet->setCellValue('D'.$j, $aData[$key2]['detail_reversaltran'])->getColumnDimension('D')->setAutoSize(true);
                    //$sheet->setCellValue('E'.$j, $aData[$key2]['detail_transactiongoldprice'])->getColumnDimension('E')->setAutoSize(true);
                    $sheet->setCellValue('E'.$j, $aData[$key2]['detail_transactionamount'])->getColumnDimension('E')->setAutoSize(true);
                    $sheet->setCellValue('F'.$j, " ".$aData[$key2]['gtp_transactiontime'])->getColumnDimension('F')->setAutoSize(true);
                    $sheet->setCellValue('G'.$j, " ".$aData[$key2]['gtp_transactioncode'])->getColumnDimension('G')->setAutoSize(true);
                    $sheet->setCellValue('H'.$j, $aData[$key2]['gtp_uuid'])->getColumnDimension('H')->setAutoSize(true);
                    $sheet->setCellValue('I'.$j, $aData[$key2]['gtp_type'])->getColumnDimension('I')->setAutoSize(true);
                    $sheet->getStyle('J'.$j)->getNumberFormat()->setFormatCode('0.000'); 
                    $sheet->setCellValue('J'.$j, $aData[$key2]['gtp_transactiongram'])->getColumnDimension('J')->setAutoSize(true);
                    $sheet->setCellValue('K'.$j, $aData[$key2]['gtp_reversal'])->getColumnDimension('K')->setAutoSize(true);
                    $sheet->getStyle('L'.$j)->getNumberFormat()->setFormatCode('0.00'); 
                    $sheet->setCellValue('L'.$j, $aData[$key2]['gtp_transactiongoldprice'])->getColumnDimension('L')->setAutoSize(true);
                    $sheet->getStyle('M'.$j)->getNumberFormat()->setFormatCode('0.00'); 
                    $sheet->setCellValue('M'.$j, $aData[$key2]['gtp_transactionamount'])->getColumnDimension('M')->setAutoSize(true);
                }
            }

            //3rd sheet
            $spreadsheet->createSheet();
            $sheet2 = $spreadsheet->setActiveSheetIndex(2);
            $sheet2->setTitle('MISSING TRANSACTIONS FROM GTP');
            $sheet2->mergeCells('A1:I1');
            $sheet2->setCellValue('A1', 'Unmatch Daily Transactions MBB to GTP as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet2->mergeCells('A2:I2');
            $sheet2->setCellValue('A2', 'Remarks: MBB Type, Buy = ACE CompanySell / MBB Type, Sell = ACE CompanyBuy');
            $sheet2->setCellValue('A4', 'MBB TransactionTime')->getColumnDimension('A')->setAutoSize(true);
            $sheet2->setCellValue('B4', 'MBB TransactionCode')->getColumnDimension('B')->setAutoSize(true);
            $sheet2->setCellValue('C4', 'MBB Type')->getColumnDimension('C')->setAutoSize(true);
            //$sheet2->setCellValue('D4', 'MBB Price/Order')->getColumnDimension('D')->setAutoSize(true);
            $sheet2->setCellValue('D4', 'MBB Gram')->getColumnDimension('D')->setAutoSize(true);
            $sheet2->setCellValue('E4', 'MBB Reversal')->getColumnDimension('E')->setAutoSize(true);
            $sheet2->setCellValue('F4', 'MBB GoldPrice')->getColumnDimension('F')->setAutoSize(true);
            $sheet2->setCellValue('G4', 'MBB Amount')->getColumnDimension('G')->setAutoSize(true);
            $k=4;
            foreach($unmatchTransaction as $key=>$aData){
                foreach($aData as $key2 => $aValue){
                    $k++;
                    
                    $sheet2->setCellValue('A'.$k, " ".$aData[$key2]['detail_transactiontime'])->getColumnDimension('A')->setAutoSize(true);
                    $sheet2->setCellValue('B'.$k, " ".$aData[$key2]['detail_transactioncode'])->getColumnDimension('B')->setAutoSize(true);
                    $sheet2->setCellValue('C'.$k, $aData[$key2]['detail_transactiondesc'])->getColumnDimension('C')->setAutoSize(true);
                    //$sheet2->setCellValue('D'.$k, $aData[$key2]['detail_matching'])->getColumnDimension('D')->setAutoSize(true);
                    $sheet2->setCellValue('D'.$k, $aData[$key2]['detail_transactiongram'])->getColumnDimension('D')->setAutoSize(true);
                    $sheet2->setCellValue('E'.$k, $aData[$key2]['detail_reversaltran'])->getColumnDimension('E')->setAutoSize(true);
                    $sheet2->setCellValue('F'.$k, $aData[$key2]['detail_transactiongoldprice'])->getColumnDimension('F')->setAutoSize(true);
                    $sheet2->setCellValue('G'.$k, $aData[$key2]['detail_transactionamount'])->getColumnDimension('G')->setAutoSize(true);
                }
            }

            //4th sheet
            $spreadsheet->createSheet();
            $sheet5 = $spreadsheet->setActiveSheetIndex(3);
            $sheet5->setTitle('MISSING TRANSACTIONS FROM MBB');
            $sheet5->mergeCells('A1:I1');
            $sheet5->setCellValue('A1', 'Unmatch Daily Transactions GTP to MBB as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet5->mergeCells('A2:I2');
            $sheet5->setCellValue('A3', 'GTP TransactionTime')->getColumnDimension('A')->setAutoSize(true);
            $sheet5->setCellValue('B3', 'GTP TransactionCode')->getColumnDimension('B')->setAutoSize(true);
            $sheet5->setCellValue('C3', 'GTP PriceQueryId')->getColumnDimension('C')->setAutoSize(true);
            $sheet5->setCellValue('D3', 'GTP Type')->getColumnDimension('D')->setAutoSize(true);
            $sheet5->setCellValue('E3', 'GTP Gram')->getColumnDimension('E')->setAutoSize(true);
            $sheet5->setCellValue('F3', 'GTP Reversal')->getColumnDimension('F')->setAutoSize(true);
            $sheet5->setCellValue('G3', 'GTP GoldPrice (P1)')->getColumnDimension('G')->setAutoSize(true);
            $sheet5->setCellValue('H3', 'GTP Amount')->getColumnDimension('H')->setAutoSize(true);

            $n=3;
            foreach($gtpUnMatch as $key=>$aData){
                $n++;
                $sheet5->setCellValue('A'.$n, " ".$aData['gtp_transactiontime'])->getColumnDimension('A')->setAutoSize(true);
                $sheet5->setCellValue('B'.$n, " ".$aData['gtp_transactioncode'])->getColumnDimension('B')->setAutoSize(true);
                $sheet5->setCellValue('C'.$n, $aData['gtp_uuid'])->getColumnDimension('C')->setAutoSize(true);
                $sheet5->setCellValue('D'.$n, $aData['gtp_type'])->getColumnDimension('D')->setAutoSize(true);
                $sheet5->getStyle('E'.$n)->getNumberFormat()->setFormatCode('0.000'); 
                $sheet5->setCellValue('E'.$n, $aData['gtp_transactiongram'])->getColumnDimension('E')->setAutoSize(true);
                $sheet5->setCellValue('F'.$n, $aData['gtp_reversal'])->getColumnDimension('F')->setAutoSize(true);
                $sheet5->getStyle('G'.$n)->getNumberFormat()->setFormatCode('0.00'); 
                $sheet5->setCellValue('G'.$n, $aData['gtp_transactiongoldprice'])->getColumnDimension('G')->setAutoSize(true);
                $sheet5->getStyle('H'.$n)->getNumberFormat()->setFormatCode('0.00'); 
                $sheet5->setCellValue('H'.$n, $aData['gtp_transactionamount'])->getColumnDimension('H')->setAutoSize(true);
            }

            //5th sheet
            $spreadsheet->createSheet();
            $sheet3 = $spreadsheet->setActiveSheetIndex(4);
            $sheet3->setTitle('UNMATCH STATUS TRANSACTIONS');
            $sheet3->mergeCells('A1:I1');
            $sheet3->setCellValue('A1', 'Unmatch status transactions from MBB compare to GTP as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet3->setCellValue('A3', 'MBB TransactionTime')->getColumnDimension('A')->setAutoSize(true);
            $sheet3->setCellValue('B3', 'MBB TransactionCode')->getColumnDimension('B')->setAutoSize(true);
            //$sheet3->setCellValue('B3', 'MBB Type')->getColumnDimension('B')->setAutoSize(true);
            $sheet3->setCellValue('C3', 'MBB Gram')->getColumnDimension('C')->setAutoSize(true);
            $sheet3->setCellValue('D3', 'MBB Status')->getColumnDimension('D')->setAutoSize(true);
            $sheet3->setCellValue('E3', 'MBB Reversal')->getColumnDimension('E')->setAutoSize(true);
            //$sheet3->setCellValue('E3', 'MBB GoldPrice')->getColumnDimension('E')->setAutoSize(true);
            $sheet3->setCellValue('F3', 'MBB Amount')->getColumnDimension('F')->setAutoSize(true);
            $sheet3->setCellValue('G3', 'GTP TransactionTime')->getColumnDimension('G')->setAutoSize(true);
            $sheet3->setCellValue('H3', 'GTP TransactionCode')->getColumnDimension('H')->setAutoSize(true);
            $sheet3->setCellValue('I3', 'GTP PriceQueryId')->getColumnDimension('I')->setAutoSize(true);
            $sheet3->setCellValue('J3', 'GTP Type')->getColumnDimension('J')->setAutoSize(true);
            $sheet3->setCellValue('K3', 'GTP Gram')->getColumnDimension('K')->setAutoSize(true);
            $sheet3->setCellValue('L3', 'GTP Status')->getColumnDimension('L')->setAutoSize(true);
            $sheet3->setCellValue('M3', 'GTP Reversal')->getColumnDimension('M')->setAutoSize(true);
            $sheet3->setCellValue('N3', 'GTP GoldPrice (P1)')->getColumnDimension('N')->setAutoSize(true);
            $sheet3->setCellValue('O3', 'GTP Amount')->getColumnDimension('O')->setAutoSize(true);
            $l=3;
            foreach($unmatchStatus as $key=>$aData){
                foreach($aData as $key2 => $aValue){
                    $l++;
                    //$sheet3->getStyle('A'.$l)->getNumberFormat()->setFormatCode('000000'); 
                    $sheet3->setCellValue('A'.$l, " ".$aData[$key2]['detail_transactiontime'])->getColumnDimension('A')->setAutoSize(true);
                    $sheet3->setCellValue('B'.$l, " ".$aData[$key2]['detail_transactioncode'])->getColumnDimension('B')->setAutoSize(true);
                    //$sheet3->setCellValue('C'.$l, $aData[$key2]['detail_transactiondesc'])->getColumnDimension('B')->setAutoSize(true);
                    $sheet3->setCellValue('C'.$l, $aData[$key2]['detail_transactiongram'])->getColumnDimension('C')->setAutoSize(true);
                    $sheet3->setCellValue('D'.$l, $aData[$key2]['detail_reversaltran'])->getColumnDimension('D')->setAutoSize(true);
                    $sheet3->setCellValue('E'.$l, $aData[$key2]['detail_reversaltran'])->getColumnDimension('E')->setAutoSize(true);
                    //$sheet3->setCellValue('E'.$l, $aData[$key2]['detail_transactiongoldprice'])->getColumnDimension('E')->setAutoSize(true);
                    $sheet3->setCellValue('F'.$l, $aData[$key2]['detail_transactionamount'])->getColumnDimension('F')->setAutoSize(true);
                    $sheet3->setCellValue('G'.$l, " ".$aData[$key2]['gtp_transactiontime'])->getColumnDimension('G')->setAutoSize(true);
                    $sheet3->setCellValue('H'.$l, " ".$aData[$key2]['gtp_transactioncode'])->getColumnDimension('H')->setAutoSize(true);
                    $sheet3->setCellValue('I'.$l, $aData[$key2]['gtp_uuid'])->getColumnDimension('I')->setAutoSize(true);
                    $sheet3->setCellValue('J'.$l, $aData[$key2]['gtp_type'])->getColumnDimension('J')->setAutoSize(true);
                    $sheet3->getStyle('K'.$l)->getNumberFormat()->setFormatCode('0.000'); 
                    $sheet3->setCellValue('K'.$l, $aData[$key2]['gtp_transactiongram'])->getColumnDimension('K')->setAutoSize(true);
                    $sheet3->setCellValue('L'.$l, $aData[$key2]['gtp_status'])->getColumnDimension('L')->setAutoSize(true);
                    $sheet3->setCellValue('M'.$l, $aData[$key2]['gtp_reversal'])->getColumnDimension('M')->setAutoSize(true);
                    $sheet3->getStyle('N'.$l)->getNumberFormat()->setFormatCode('0.00'); 
                    $sheet3->setCellValue('N'.$l, $aData[$key2]['gtp_transactiongoldprice'])->getColumnDimension('N')->setAutoSize(true);
                    $sheet3->getStyle('O'.$l)->getNumberFormat()->setFormatCode('0.00'); 
                    $sheet3->setCellValue('O'.$l, $aData[$key2]['gtp_transactionamount'])->getColumnDimension('O')->setAutoSize(true);
                }
            }
            

            $writer = new Xlsx($spreadsheet);
            $writer->save($pathToSave);
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to update extraction data from DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }


    public function checkTransactionReconcile($transaction,$date,$partner,$flag,$partnername=null){
        $now = common::convertUTCToUserDatetime(new \DateTime());
        $getDate            = date('Y-m-d h:i:s',$date);
        $createDateStart    = date_create($getDate);
        $modifyDateStart    = date_modify($createDateStart,"-2 day");
        $startDate          = date_format($modifyDateStart,"Y-m-d 16:00:00");
        $createDateEnd      = date_create($getDate);
        $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
        $endDate            = date_format($modifyDateEnd,"Y-m-d 15:59:59");
        $dateFile           = date('dmY',$date);

        foreach($transaction as $atransaction){
            $ordernoSap = $atransaction['RefNo1'];
            $transactionNum = $atransaction['DocNum'];
            $orderItem = $this->app->orderStore()
                ->searchView()
                ->select()
                ->where('partnerid',$partner['id'])
                ->andWhere('orderno',$ordernoSap)
                ->andWhere('createdon', '>=', $startDate)
                ->andWhere('createdon', '<=', $endDate)
                ->execute();
            if(count($orderItem) > 0){
                foreach($orderItem as $anOrder){
                    $gtpMatchArr = array(
                        'gtp_transactioncode' => $anOrder->orderno,
                        'gtp_partnerrefid' => $anOrder->partnerrefid,
                        'gtp_type' => $anOrder->type,
                        'gtp_transactiongram' => $anOrder->xau,
                        'gtp_transactiongoldprice' => $anOrder->price,
                        'gtp_transactionamount' => $anOrder->amount,
                        'gtp_status' => $anOrder->statusname,
                        'gtp_uuid' => $anOrder->uuid,
                        'gtp_transactiontime' => $anOrder->createdon->format('Y-m-d His')
                    );
                    $reconArr[$ordernoSap] = array_merge($atransaction,$gtpMatchArr);
                    $anOrder->reconciledsaprefno = $transactionNum;
                    $update = $this->app->orderStore()->save($anOrder);
                    if(!$update){
                        $bodyEmail .= "Unable to update reconciledsaprefno details to order - $anOrder->orderno.\n";
                        //throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Unable to update reconciledsaprefno details to order '.$aItem->id]);
                    }
                }
            } else {
                $buybackCase = explode('-', $ordernoSap);
                $buybackItem = $this->app->buybackStore()
                    ->searchView()
                    ->select()
                    ->where('partnerid',$partner['id'])
                    ->andWhere('buybackno',$buybackCase[0])
                    ->andWhere('createdon', '>=', $startDate)
                    ->andWhere('createdon', '<=', $endDate)
                    ->execute();
                if(count($buybackItem) > 0){
                    foreach($buybackItem as $anBuyback){
                        $gtpMatchArr = array(
                            'gtp_transactioncode' => $anBuyback->buybackno,
                            'gtp_partnerrefid' => $anBuyback->partnerrefno,
                            'gtp_type' => 'Buyback',
                            'gtp_transactiongram' => $anBuyback->totalweight,
                            'gtp_transactiongoldprice' => $anBuyback->price,
                            'gtp_transactionamount' => $anBuyback->totalamount,
                            'gtp_status' => $anBuyback->statusname,
                            'gtp_uuid' => '',
                            'gtp_transactiontime' => $anBuyback->createdon->format('Y-m-d His')
                        );
                        $reconArr[$ordernoSap] = array_merge($atransaction,$gtpMatchArr);

                        $mergeSapRefNo = $ordernoSap.'-'.$transactionNum;
                        if(strpos($anBuyback->reconciledsaprefno, $mergeSapRefNo) !== false){
                            $anBuyback->reconciledsaprefno = $anBuyback->reconciledsaprefno;
                        } else {
                            $anBuyback->reconciledsaprefno .= $mergeSapRefNo.', ';
                        }
                        $update = $this->app->buybackStore()->save($anBuyback);
                        if(!$update){
                            $bodyEmail .= "Unable to update reconciledsaprefno details to buyback - $anBuyback->buybackno.\n";
                            //throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Unable to update reconciledsaprefno details to order '.$aItem->id]);
                        }
                    }
                } else {
                    $unmatchSap[] = $atransaction;
                }  
            }
        }

        /*Get gtp transaaction unmatch*/
        $orderItem = $this->app->orderStore()
            ->searchView()
            ->select()
            ->where('partnerid',$partner['id'])
            ->andWhere('reconciledsaprefno',0)
            ->andWhere('createdon', '>=', $startDate)
            ->andWhere('createdon', '<=', $endDate)
            ->execute();
        if(count($orderItem) > 0){
            foreach($orderItem as $anOrder) {
                $gtpUnmatch[] = array(
                    'gtp_transactioncode' => $anOrder->orderno,
                    'gtp_partnerrefid' => $anOrder->partnerrefid,
                    'gtp_type' => $anOrder->type,
                    'gtp_transactiongram' => $anOrder->xau,
                    'gtp_transactiongoldprice' => $anOrder->price,
                    'gtp_transactionamount' => $anOrder->amount,
                    'gtp_status' => $anOrder->statusname,
                    'gtp_uuid' => $anOrder->uuid,
                    'gtp_transactiontime' => $anOrder->createdon->format('Y-m-d His')
                );
            }
        }

        /*Get gtp transaaction unmatch*/
        $buybackItem = $this->app->buybackStore()
            ->searchView()
            ->select()
            ->where('partnerid',$partner['id'])
            ->andWhere('reconciledsaprefno',0)
            ->andWhere('createdon', '>=', $startDate)
            ->andWhere('createdon', '<=', $endDate)
            ->execute();
        if(count($buybackItem) > 0){
            foreach($buybackItem as $anBuyback) {
                $gtpUnmatch[] = array(
                    'gtp_transactioncode' => $anBuyback->buybackno,
                    'gtp_partnerrefid' => $anBuyback->partnerrefno,
                    'gtp_type' => 'Buyback',
                    'gtp_transactiongram' => $anBuyback->totalweight,
                    'gtp_transactiongoldprice' => $anBuyback->price,
                    'gtp_transactionamount' => $anBuyback->totalamount,
                    'gtp_status' => $anBuyback->statusname,
                    'gtp_uuid' => '',
                    'gtp_transactiontime' => $anBuyback->createdon->format('Y-m-d His')
                );
            }
        }

        $reportpath = $this->app->getConfig()->{'gtp.ftp.report'};
        if($partnername != null) $partnername = "_".$partnername."_";

        $pathToSave = $reportpath.'RECON_SO_PO_'.$partnername.$dateFile.'.xlsx';

        $filename = 'RECON_SO_PO_'.$partnername.$dateFile.'.xlsx';
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Match Reconcile SAP and GTP');
        $sheet->setCellValue('A1', 'SAP DocDate')->getColumnDimension('A')->setAutoSize(true);
        $sheet->setCellValue('B1', 'SAP DocNum')->getColumnDimension('B')->setAutoSize(true);
        $sheet->setCellValue('C1', 'SAP DocType')->getColumnDimension('C')->setAutoSize(true);
        $sheet->setCellValue('D1', 'SAP CardCode')->getColumnDimension('D')->setAutoSize(true);
        $sheet->setCellValue('E1', 'SAP CardName')->getColumnDimension('E')->setAutoSize(true);
        $sheet->setCellValue('F1', 'SAP Price')->getColumnDimension('F')->setAutoSize(true);
        $sheet->setCellValue('G1', 'SAP Quantity')->getColumnDimension('F')->setAutoSize(true);
        $sheet->setCellValue('H1', 'SAP LineTotal')->getColumnDimension('H')->setAutoSize(true);
        $sheet->setCellValue('I1', 'SAP NumAtCard')->getColumnDimension('I')->setAutoSize(true);
        $sheet->setCellValue('J1', 'SAP RefNo')->getColumnDimension('J')->setAutoSize(true);
        $sheet->setCellValue('K1', 'SAP RefNo1')->getColumnDimension('K')->setAutoSize(true);
        $sheet->setCellValue('L1', 'SAP RefNo3')->getColumnDimension('L')->setAutoSize(true);
        $sheet->setCellValue('M1', 'GTP DateTime')->getColumnDimension('M')->setAutoSize(true);
        $sheet->setCellValue('N1', 'GTP PartnerRefId')->getColumnDimension('N')->setAutoSize(true);
        $sheet->setCellValue('O1', 'GTP OrderNo')->getColumnDimension('O')->setAutoSize(true);
        $sheet->setCellValue('P1', 'GTP Price')->getColumnDimension('P')->setAutoSize(true);
        $sheet->setCellValue('Q1', 'GTP Quantity')->getColumnDimension('Q')->setAutoSize(true);
        $sheet->setCellValue('R1', 'GTP Amount')->getColumnDimension('R')->setAutoSize(true);
        $sheet->setCellValue('S1', 'GTP PriceId')->getColumnDimension('S')->setAutoSize(true);
        $sheet->setCellValue('T1', 'GTP Status')->getColumnDimension('T')->setAutoSize(true);

        $j=1;
        foreach($reconArr as $key=>$aData){
            $j++;
            $sheet->setCellValue('A'.$j, $aData['DocDate'])->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('B'.$j, $aData['DocNum'])->getColumnDimension('B')->setAutoSize(true);
            $sheet->setCellValue('C'.$j, $aData['DocType'])->getColumnDimension('C')->setAutoSize(true);
            $sheet->setCellValue('D'.$j, $aData['CardCode'])->getColumnDimension('D')->setAutoSize(true);
            $sheet->setCellValue('E'.$j, $aData['CardName'])->getColumnDimension('E')->setAutoSize(true);
            $sheet->getStyle('F'.$j)->getNumberFormat()->setFormatCode('0.00'); 
            $sheet->setCellValue('F'.$j, $aData['Price'])->getColumnDimension('F')->setAutoSize(true);
            $sheet->getStyle('G'.$j)->getNumberFormat()->setFormatCode('0.000'); 
            $sheet->setCellValue('G'.$j, $aData['Quantity'])->getColumnDimension('G')->setAutoSize(true);
            $sheet->setCellValue('H'.$j, $aData['LineTotal'])->getColumnDimension('H')->setAutoSize(true);
            $sheet->setCellValue('I'.$j, $aData['NumAtCard'])->getColumnDimension('I')->setAutoSize(true);
            $sheet->setCellValue('J'.$j, $aData['RefNo'])->getColumnDimension('J')->setAutoSize(true);
            $sheet->setCellValue('K'.$j, $aData['RefNo1'])->getColumnDimension('K')->setAutoSize(true);
            $sheet->setCellValue('L'.$j, $aData['RefNo3'])->getColumnDimension('L')->setAutoSize(true);
            $sheet->setCellValue('M'.$j, $aData['gtp_transactiontime'])->getColumnDimension('M')->setAutoSize(true);
            $sheet->setCellValue('N'.$j, " ".$aData['gtp_partnerrefid'])->getColumnDimension('N')->setAutoSize(true);
            $sheet->setCellValue('O'.$j, $aData['gtp_transactioncode'])->getColumnDimension('O')->setAutoSize(true);
            $sheet->getStyle('P'.$j)->getNumberFormat()->setFormatCode('0.00'); 
            $sheet->setCellValue('P'.$j, $aData['gtp_transactiongoldprice'])->getColumnDimension('P')->setAutoSize(true);
            $sheet->getStyle('Q'.$j)->getNumberFormat()->setFormatCode('0.000'); 
            $sheet->setCellValue('Q'.$j, $aData['gtp_transactiongram'])->getColumnDimension('Q')->setAutoSize(true);
            $sheet->getStyle('R'.$j)->getNumberFormat()->setFormatCode('0.00'); 
            $sheet->setCellValue('R'.$j, $aData['gtp_transactionamount'])->getColumnDimension('R')->setAutoSize(true);
            $sheet->setCellValue('S'.$j, $aData['gtp_uuid'])->getColumnDimension('S')->setAutoSize(true);
            $sheet->setCellValue('T'.$j, $aData['gtp_status'])->getColumnDimension('T')->setAutoSize(true); 
        }

        /*create new tab*/
        $spreadsheet->createSheet();
        $sheet2 = $spreadsheet->setActiveSheetIndex(1);
        $sheet2->setTitle('Unmatch Transaction SAP');
        $sheet2->setCellValue('A1', 'SAP DocDate')->getColumnDimension('A')->setAutoSize(true);
        $sheet2->setCellValue('B1', 'SAP DocNum')->getColumnDimension('B')->setAutoSize(true);
        $sheet2->setCellValue('C1', 'SAP DocType')->getColumnDimension('C')->setAutoSize(true);
        $sheet2->setCellValue('D1', 'SAP CardCode')->getColumnDimension('D')->setAutoSize(true);
        $sheet2->setCellValue('E1', 'SAP CardName')->getColumnDimension('E')->setAutoSize(true);
        $sheet2->setCellValue('F1', 'SAP Price')->getColumnDimension('F')->setAutoSize(true);
        $sheet2->setCellValue('G1', 'SAP Quantity')->getColumnDimension('G')->setAutoSize(true);
        $sheet2->setCellValue('H1', 'SAP LineTotal')->getColumnDimension('H')->setAutoSize(true);
        $sheet2->setCellValue('I1', 'SAP NumAtCard')->getColumnDimension('I')->setAutoSize(true);
        $sheet2->setCellValue('J1', 'SAP RefNo')->getColumnDimension('J')->setAutoSize(true);
        $sheet2->setCellValue('K1', 'SAP RefNo1')->getColumnDimension('K')->setAutoSize(true);
        $sheet2->setCellValue('L1', 'SAP RefNo3')->getColumnDimension('L')->setAutoSize(true);

        $k=1;
        foreach($unmatchSap as $key=>$aData){
            $k++;
            $sheet2->setCellValue('A'.$k, $aData['DocDate'])->getColumnDimension('A')->setAutoSize(true);
            $sheet2->setCellValue('B'.$k, $aData['DocNum'])->getColumnDimension('B')->setAutoSize(true);
            $sheet2->setCellValue('C'.$k, $aData['DocType'])->getColumnDimension('C')->setAutoSize(true);
            $sheet2->setCellValue('D'.$k, $aData['CardCode'])->getColumnDimension('D')->setAutoSize(true);
            $sheet2->setCellValue('E'.$k, $aData['CardName'])->getColumnDimension('E')->setAutoSize(true);
            $sheet2->getStyle('F'.$j)->getNumberFormat()->setFormatCode('0.00'); 
            $sheet2->setCellValue('F'.$k, $aData['Price'])->getColumnDimension('F')->setAutoSize(true);
            $sheet2->getStyle('G'.$j)->getNumberFormat()->setFormatCode('0.000'); 
            $sheet2->setCellValue('G'.$k, $aData['Quantity'])->getColumnDimension('G')->setAutoSize(true);
            $sheet2->setCellValue('H'.$k, $aData['LineTotal'])->getColumnDimension('H')->setAutoSize(true);
            $sheet2->setCellValue('I'.$k, $aData['NumAtCard'])->getColumnDimension('I')->setAutoSize(true);
            $sheet2->setCellValue('J'.$k, $aData['RefNo'])->getColumnDimension('J')->setAutoSize(true);
            $sheet2->setCellValue('K'.$k, $aData['RefNo1'])->getColumnDimension('K')->setAutoSize(true);
            $sheet2->setCellValue('L'.$k, $aData['RefNo3'])->getColumnDimension('L')->setAutoSize(true);
        }
        
        /*create new tab*/
        $spreadsheet->createSheet();
        $sheet3 = $spreadsheet->setActiveSheetIndex(2);
        $sheet3->setTitle('Unmatch Transaction GTP');
        $sheet3->setCellValue('A1', 'GTP DateTime')->getColumnDimension('A')->setAutoSize(true);
        $sheet3->setCellValue('B1', 'GTP PartnerRefId')->getColumnDimension('B')->setAutoSize(true);
        $sheet3->setCellValue('C1', 'GTP OrderNo')->getColumnDimension('C')->setAutoSize(true);
        $sheet3->setCellValue('D1', 'GTP Price')->getColumnDimension('D')->setAutoSize(true);
        $sheet3->setCellValue('E1', 'GTP Quantity')->getColumnDimension('E')->setAutoSize(true);
        $sheet3->setCellValue('F1', 'GTP Amount')->getColumnDimension('F')->setAutoSize(true);
        $sheet3->setCellValue('G1', 'GTP PriceId')->getColumnDimension('G')->setAutoSize(true);
        $sheet3->setCellValue('H1', 'GTP Status')->getColumnDimension('H')->setAutoSize(true);

        $l=1;
        foreach($gtpUnmatch as $key=>$aData){
            $l++;
            $sheet3->setCellValue('A'.$l, $aData['gtp_transactiontime'])->getColumnDimension('A')->setAutoSize(true);
            $sheet3->setCellValue('B'.$l, " ".$aData['gtp_partnerrefid'])->getColumnDimension('B')->setAutoSize(true);
            $sheet3->setCellValue('C'.$l, $aData['gtp_transactioncode'])->getColumnDimension('C')->setAutoSize(true);
            $sheet3->getStyle('D'.$l)->getNumberFormat()->setFormatCode('0.00'); 
            $sheet3->setCellValue('D'.$l, $aData['gtp_transactiongoldprice'])->getColumnDimension('D')->setAutoSize(true);
            $sheet3->getStyle('E'.$l)->getNumberFormat()->setFormatCode('0.000'); 
            $sheet3->setCellValue('E'.$l, $aData['gtp_transactiongram'])->getColumnDimension('E')->setAutoSize(true);
            $sheet3->getStyle('F'.$l)->getNumberFormat()->setFormatCode('0.00'); 
            $sheet3->setCellValue('F'.$l, $aData['gtp_transactionamount'])->getColumnDimension('F')->setAutoSize(true);
            $sheet3->setCellValue('G'.$l, $aData['gtp_uuid'])->getColumnDimension('G')->setAutoSize(true);
            $sheet3->setCellValue('H'.$l, $aData['gtp_statusname'])->getColumnDimension('H')->setAutoSize(true);
            
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($pathToSave);

        //send/notify email.
        //$sendTo = $this->app->getConfig()->{'gtp.ftp.sendtoreconcile'};
        $emailConfig = 'sendtoreconcile';
        //$emailSubject = $flag.' RECONCILE SO/PO SAP '.$dateFile;
        $emailSubject = 'RECONCILE SO/PO SAP '.$partnername.$dateFile;
        $bodyEmail .= "\nSUCCESSFULLY GENERATED.\n";
        $sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject,$emailConfig,$pathToSave,$filename);
    }

    public function completeReplenishSAP($items,$partner){
        try{
            $now = common::convertUTCToUserDatetime(new \DateTime());
            foreach ($items as $item){
                $replenishment = $this->app->replenishmentStore()
                    ->searchTable()
                    ->select()
                    ->where('partnerid',$partner['id'])
                    ->andWhere('serialno',$item['detail_serialno'])
                    //->andWhere('status',Replenishment::STATUS_COMPLETED)
                    ->execute();
                if($replenishment > 0){
                    foreach($replenishment as $aReplenishment){
                        $aReplenishment->status = Replenishment::STATUS_COMPLETED;
                        if($aReplenishment->sapresponsestatus == null || $aReplenishment->sapresponsestatus != 1 || empty($aReplenishment->sapresponsestatus)){
                            $aReplenishment->sapresponsestatus = 1;
                            $aReplenishment->sapresponseon = $now->format('Y-m-d H:i:s');
                        }
                        $update = $this->app->replenishmentStore()->save($aReplenishment);
                        if (!$update){
                            $return['unableToSave'][] = $item;
                            //throw new \Exception("Unable to update replenishment - ".$item['serialNum']);
                        } 
                        $return['statusAvailable'][] = $item;
                    } 
                } else {
                    /*$vaultitem = $this->app->vaultitemStore()
                        ->searchTable()
                        ->select()
                        ->where('partnerid',$partner['id'])
                        ->andWhere('serialno',$item['detail_serialno'])
                        ->andWhere('status',Vaultitem::STATUS_ACTIVE)
                        ->execute();
                    if($vaultitem > 0){
                        foreach($vaultitem as $aItem){
                            $aItem->movetovaultlocationid = $mbbLocation->id;
                            $aItem->moverequestedon = $now->format('Y-m-d H:i:s');
                            $aItem->status = VaultItem::STATUS_TRANSFERRING;
                            $update = $this->app->vaultitemStore()->save($aItem);
                            if(!$update){
                                $return['unableToSave'][] = $aItem;
                            } else {
                                $return['physicalmove'][] = $item;
                            }
                        }
                    } else {
                        $return['invalidSN'][] = $item; 
                    }*/
                    $return['invalidSN'][] = $item; 
                }
            }
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to update PHYINV to GTP replenishment table.", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
        return $return;
    }

    public function transferVaultItem($items,$partner, VaultLocation $mbblocation, VaultLocation $acelocation, $method){
        //$method = request/direct
        //$type = MBB/ACE
        try{
            $now = common::convertUTCToUserDatetime(new \DateTime());
            foreach ($items as $key=>$item){
                $type = $key; //ACE/MBB
                foreach($item as $key2=>$vaultItem){
                    $vaultitem = $this->app->vaultitemStore()
                        ->searchTable()
                        ->select()
                        ->where('partnerid',$partner['id'])
                        ->andWhere('serialno',$vaultItem['detail_serialno'])
                        //->andWhere('status',Vaultitem::STATUS_ACTIVE)
                        ->execute();
                    if($vaultitem > 0){
                        foreach($vaultitem as $aItem){
                            /*if need to put it to moverequest location first*/
                            if($method == 'request'){
                                $aItem->movetovaultlocationid = ($type == 'MBB') ? $mbblocation->id : $acelocation->id;
                                $aItem->moverequestedon = $now->format('Y-m-d H:i:s');
                                $aItem->status = VaultItem::STATUS_TRANSFERRING;
                            } 
                            /**/
                            /*if direct move*/
                            else if($method == 'direct'){
                                $aItem->vaultlocationid = ($type == 'MBB') ? $mbblocation->id : $acelocation->id;
                                $aItem->movecompletedon = $now->format('Y-m-d H:i:s');
                                $aItem->status = VaultItem::STATUS_INACTIVE;
                            }
                            /**/

                            $update = $this->app->vaultitemStore()->save($aItem);
                            if(!$update){
                                /**/
                                $vaultItemArray = array(
                                    'id' => $aItem->id,
                                    'serialno' => $aItem->serialno,
                                    'moveto' => $type,
                                );
                                /**/
                                $unUpdate[] = $vaultItemArray;
                            } else {
                                /**/
                                /*status name*/
                                if($aItem->status == 0) $statusName = 'PENDING';
                                if($aItem->status == 1) $statusName = 'ACTIVE';
                                if($aItem->status == 2) $statusName = 'TRANSFERRING';
                                if($aItem->status == 3) $statusName = 'INACTIVE';
                                if($aItem->status == 4) $statusName = 'REMOVED';
                                /**/
                                $vaultItemArray = array(
                                    'serialno' => $aItem->serialno,
                                    'moveto' => $type,
                                    'status' => $statusName,
                                );
                                /**/
                                $physicalToMove[] = $vaultItemArray;
                            }
                        }
                    } else {
                        $invalid[] = $vaultItem; 
                    }
                }
                $return['unableToSave'] = $unUpdate;
                $return['physicalmove'] = $physicalToMove;
                $return['invalidSN'] = $invalid;
            }
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to update PHYINV to GTP replenishment table.", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
        return $return;
    }

    public function processBackup($src, $backup,$dateToHeader) {  
        $dateFile           = date('dmY',$dateToHeader);
        foreach ($src as $aSrc){
            // open the source directory 
            $dir = opendir($aSrc);  
           
            // Make the destination directory if not exist 
            @mkdir($backup);  
           
            // Loop through the files in source directory 
            foreach (scandir($aSrc) as $file) {  
                //get filename only
                $pieces = explode(".", $file);
                $combineBackup = $pieces[0].'_'.$dateFile.'.'.$pieces[1];
                if (( $file != '.' ) && ( $file != '..' )) {  
                    if ( is_dir($aSrc . $file) )  
                    {  
                        // Recursively calling custom copy function 
                        // for sub directory  
                        custom_copy($aSrc . $file, $backup . $combineBackup);  
                    }  
                    else {  
                        copy($aSrc . $file, $backup . $combineBackup);  
                    }  
                }  
            }  
            closedir($dir);
        }
        return true;
    }
}
?>