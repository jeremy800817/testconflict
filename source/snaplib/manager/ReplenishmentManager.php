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
Use \Snap\object\Logistic;
Use \Snap\object\Replenishment;
Use \Snap\common;

// confirmation on sap replenishment data structure
class ReplenishmentManager implements IObservable, IObserver
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;
    
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        if($changed instanceof \Snap\manager\LogisticManager) {
        
            if (Logistic::TYPE_REPLENISHMENT == $state->target->type){
                // no checking here, firmed data from logisticManager;
                // courier tracking/update will be on logisticManager;
                $logistic = $state->target;

                $replenishments = $this->app->replenishmentLogisticStore()->searchTable()->select()->where("logisticid", $logistic->id)->execute();

                // _UPDATE/_CHANGE status value
                if (Logistic::STATUS_FAILED != $state->target->status){
                    $this->updateReplenishmentLogisticStatus($replenishments, $logistic);
                }else{
                    $this->failedLogistic($replenishments, $logistic);
                }
            }

        }
    }

    /** 
    * @param replenishmentList      Arr     data from SAP and API_VIEW
    * @param apiVersion             String  
    **/
    public function doReplenishment($replenishmentList){
        /* 
        replenishmentList = [
            item = [
                "productid" = 1,
                "serialno" = "SERIAL_NO",
                "sapwhscode" = "WhsCode",
                "branchid" = 5,
                "partnerid" = 1,
                "saprefno" = "SAP_REF",
            ]
        ]
         */
        try{
            $cacher = $this->app->getCacher();
            $this->app->getDbHandle()->beginTransaction();
            $this->log("Replenishment - doReplenishment - data{$replenishmentList} start replenishment process now is:" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);
            $replenishmentNo = $this->generateReplenishmentNo($cacher);

            foreach ($replenishmentList as $replenishment){
                // check unique replenishment serial no
                $check = $this->app->replenishmentStore()->searchTable()->select(['id'])->where('serialno', $replenishment['serialno'])->andWhere('status', '<>', replenishment::STATUS_FAILED)->count();
                if ($check){
                    throw new \Exception("Serial Number existed.");
                }

                $createReplenishment = $this->app->replenishmentStore()->create([
                    "partnerid" => $replenishment['partnerid'],
                    "replenishmentno" => $replenishmentNo,
                    "productid" => $replenishment['productid'],
                    "serialno" => $replenishment['serialno'],
                    "sapwhscode" => $replenishment['sapwhscode'],
                    "saprefno" => $replenishment['saprefno'],
                    "branchid" => $replenishment['branchid'],
                    "status" => Replenishment::STATUS_CONFIRMED,
                ]);
                if (!$this->app->replenishmentStore()->save($createReplenishment)){
                    $this->log('Error in replenishment datastore save.' , SNAP_LOG_DEBUG);
                    throw new \Exception("Unable to process replenishment.");
                }
            }

            $this->app->getDbHandle()->commit();
            $this->log("Replenishment - doReplenishment - data{$replenishmentList} - replenishment data create successful now is:" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);

            $return = $this->app->replenishmentStore()->searchTable()->select()->where("replenishmentno", $replenishmentNo)->execute();
            
        }catch(\Exception $e){
            $this->log('Error in replenishment process.', SNAP_LOG_DEBUG);
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }

        return $return;
    }

    /** 
     * send to SAP when replenishment is completed by logistic -- STATUS_RECIEVED OR STATUS_COMPLETED
     * @param replenishmentList      Arr     data from SAP and API_VIEW
     * @param apiVersion             String  
    **/
    public function sapReplenishmentReply($replenishment){

        $sap_response = $this->app->ApiManager()->sapMintedGold();

    }

    // logistic event
    private function updateReplenishmentLogisticStatus($replenishments, $logistic){
        try{
            $this->app->getDbHandle()->beginTransaction();

            foreach ($replenishments as $replenishmentlogistic){

                $replenishment = $this->app->replenishmentStore()->getById($replenishmentlogistic->replenishmentid);
                // if delivered . call sap 
                if (Logistic::STATUS_DELIVERED == $logistic->status){
                    $replenishment->status = Replenishment::STATUS_COMPLETED;
                    $replenishment->replenishedon = $logistic->deliveredon;
                    $this->app->replenishmentStore()->save($replenishment);
                }
                if (Logistic::STATUS_SENDING == $logistic->status || Logistic::STATUS_PROCESSING == $logistic->status){
                    $replenishment->status = Replenishment::STATUS_PROCESSDELIVERY;
                    $this->app->replenishmentStore()->save($replenishment);
                }
            }

            $this->app->getDbHandle()->commit();
        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
    }

    // exception on logistic - delivery failed, or any issues
    private function failedLogistic($replenishment, $logistic){
        try{
            $this->app->getDbHandle()->beginTransaction();

            if (Logistic::STATUS_FAILED == $logistic->status){

                // pending sapRdemptionData format
                $sapReplenishmentData = (object) [ 
                    "replenishment_code" => $replenishment->sapreplenishmentcode,
                    "items" => $replenishment->items
                ]; 
                $sap_response = $this->app->apiManager()->sapReplenishment($sapReplenishmentData);
                if (!$sap_response['error']){
                    $replenishment->status = Replenishment::STATUS_FAILED;
                    $this->app->replenishmentStore()->save($replenishment);
                }else{
                    // logistic failed, sap failed update
                    
                }
            }

            $this->app->getDbHandle()->commit();
        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
    }

    // NOTE -- 16-06-2020 -- SAP replenishment number is by item, GTP is by (request), SAP item replenishment_ref will be `generateReplenishmentNo+{'-'}+{item}`
    public function generateReplenishmentNo($cacher, $partner = null, $refid = null)
    {
        $moduleString = 'Replenishment';
        $generateStore = $this->app->replenishmentStore();
        $generateNoPrefix = 'H';
        $format = '%s%s%05d';
        $envPrefix = '';       

        $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
        if ($developmentEnv){
            $format = '%s%s%04d%s';
            $envPrefix = 'D';
        }
        $this->log("generate_".$moduleString."No($refid, {$partner->code}) - into the method ", SNAP_LOG_DEBUG);
        $now = new \DateTime(gmdate('Y-m-d\TH:i:s'));
        $now = \Snap\common::convertUTCToUserDatetime($now);
        $generateNoKey = $moduleString.'No:' . $now->format('Ymd');
        $nextGenerateSequence = $cacher->increment($generateNoKey, 1, 86400);
        $this->log("generate_".$moduleString."No($refid,{$partner->code}) - The date used is " . $now->format('Y-m-d H:i:s') . " and key = " . $generateNoKey, SNAP_LOG_DEBUG);
        if(! $nextGenerateSequence) {
            $this->log("generate_".$moduleString."No($refid,{$partner->code}) - the redis key not found.  Generating total orders from DB", SNAP_LOG_DEBUG);
            $utcStartOfDay = new \DateTime($now->format('Y-m-d 00:00:00'));
            $utcStartOfDay = \Snap\common::convertUserDatetimeToUTC($utcStartOfDay);
            //Can't find the key.  We will have to rebuild it.
            $totalDayOrders = $generateStore->searchTable()->select()->where('createdon', '>=', $utcStartOfDay->format('Y-m-d H:i:s'))->count();
            $this->log("generate_".$moduleString."No($refid,{$partner->code}) - total ".$moduleString." from DB = " . $totalDayOrders, SNAP_LOG_DEBUG);
            $cacher->set($generateNoKey, $totalDayOrders + 1, 86400);
            $nextGenerateSequence = $totalDayOrders + 1;
        }
        $nextGenerateSequence = strtoupper(sprintf($format, $generateNoPrefix, $now->format('ymd'), $nextGenerateSequence, $envPrefix));
        $this->log("generate_".$moduleString."No() - Generated sequence $nextGenerateSequence for ".$moduleString." $refid for partner {$partner->code}", SNAP_LOG_DEBUG);
        return $nextGenerateSequence;
    }

    
    
    /** 
     * on recieving inventory update from sap
     * @param items      Arr     raw data from SAP
    **/
    // items => [
    //      $item = array(
    //          "serialNum",
    //          "itemCode",
    //      )
    // ];
    public function recievingFromSAP($items){
        try{
            $return = true;
            $this->app->getDbHandle()->beginTransaction();
            foreach ($items as $item){
                // $item['serialNum']; // raw sap data
                $replenishment = $this->app->replenishmentStore()->getByField("serialno", $item['serialNum']);
                if (!$replenishment){
                    throw new \Exception("Invalid Serial Number - ".$item['serialNum']);
                }else{
                    $replenishment->sapresponsestatus = 1;
                    $replenishment->sapresponseon = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
                }
                if (!$this->app->replenishmentStore()->save($replenishment)){
                    throw new \Exception("Unable to update replenishment - ".$item['serialNum']);
                }
            }
            $this->app->getDbHandle()->commit();
        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
        return $return;
    }

}