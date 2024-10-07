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
Use \Snap\object\Redemption;
Use \Snap\object\Replenishment;
Use \Snap\common;
use Snap\object\Buyback;
use Snap\object\LogisticLog;

Use \GuzzleHttp\Client;
Use \GuzzleHttp\Request;

// TODO -- 16-06-2020 -- > more func supp ui
class LogisticManager implements IObservable
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

    }

    // replenishment(items) -> logistic(s) -> item(s)

    // logistic object file{
    //     Logistic::TYPE_REDEMPTION;
    //     Logistic::TYPE_REPLENISHMENT;
    // }

    // process ----------------------
    /**
    * @param redemption      Obj     $redemption object data
    * @param vendorId        Int     $vendorId = $vendor->id = TagStore['category'] => 'LogisticVendor'
    * @param awbNo           String  awb number
    * @param senderId        Int     user->id of Ace_Delivery salesperson, different naming
    * @param deliveryDate    String  Logistic Delivery Date (scheduled)
     **/
    public function createLogisticRedemption(Redemption $redemption, $vendorId, $awbNo = null, $vendorRefNo = null, $senderId = null, $deliveryDate = null){
        try{
            $cacher = $this->app->getCacher();
            $this->app->getDbHandle()->beginTransaction();

            $type = Logistic::TYPE_REDEMPTION;

            // vendorId in tagStore()

            if (!$vendorId){
                if ($redemption->type == Redemption::TYPE_APPOINTMENT || $redemption->type == Redemption::TYPE_SPECIALDELIVERY){
                    $vendor = $this->app->tagStore()->getByField('value', logistic::VENDOR_ACEDELIVERY_VALUE);
                    $vendorId = $vendor->id;
                }
            }

            $createLogistic = $this->app->logisticStore()->create([
                'type' => $type,
                'awbno' => $awbNo,
                'vendorid' => $vendorId, // logistic vendor ID
                'senderid' => $senderId,
            ]);
            
            $vendor = $this->app->tagStore()->getById($vendorId);
            if (Logistic::VENDOR_ACEDELIVERY_VALUE == $vendor->value){
                $vendorRefNo = $this->generateAceCourierVendorRefNo($cacher, $vendor->id);
            }else{
                // if (!$vendorRefNo){
                //     throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($createLogistic, ['action' => 'invalid logistic reference no.', 'message' => '']);
                // }
            }

            $createLogistic->partnerid = $redemption->partnerid;
            $createLogistic->typeid = $redemption->id;
            
            $createLogistic->contactname1 = $redemption->deliverycontactname1;
            $createLogistic->contactname2 = $redemption->deliverycontactname2;
            $createLogistic->contactno1 = $redemption->deliverycontactno1;
            $createLogistic->contactno2 = $redemption->deliverycontactno2;
            $createLogistic->address1 = $redemption->deliveryaddress1;
            $createLogistic->address2 = $redemption->deliveryaddress2;
            $createLogistic->address3 = $redemption->deliveryaddress3;
            $createLogistic->city = $redemption->deliverycity;
            $createLogistic->postcode = $redemption->deliverypostcode;
            $createLogistic->state = $redemption->deliverystate;
            if (!$redemption->deliverycountry){
                // CAUTION
                $createLogistic->country = 'Malaysia';
            }else{
                $createLogistic->country = $redemption->deliverycountry;
            }

            $deliveryDateTime = new \DateTime($deliveryDate);
            $deliveryDateTime = common::convertUserDatetimeToUTC($deliveryDateTime);
            $createLogistic->deliverydate = $deliveryDateTime->format('Y-m-d H:i:s');

            $createLogistic->status = Logistic::STATUS_PROCESSING;
            
            if ($vendor->value == logistic::VENDOR_GDEX_VALUE){
                $updateAwb = $this->gdexApiCreateConsignment($createLogistic);
                $createLogistic->awbno = $updateAwb;
            }else if ($vendor->value == logistic::VENDOR_LINCLEAR_VALUE){
                $updateAwb = $this->lineclearApiCreateShipment($createLogistic);
                $createLogistic->awbno = $updateAwb;
            }
            
            $saveLogistic = $this->app->logisticStore()->save($createLogistic);

            $this->createLogisticLog($saveLogistic->id, $saveLogistic->status);
            
            $this->app->getDbHandle()->commit();
            
            $observation = new \Snap\IObservation(
                $saveLogistic,
                \Snap\IObservation::ACTION_NEW,
                $saveLogistic->status,
                ['redemptionType' => $redemption->type]);
            $this->notify($observation);

        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
        return $saveLogistic;
    }

    /**
    * @param replenishment   ArrObj  $replenishment list, list of selected replenishment `ITEM` from BO/UI ---> MUST same branchId
    * @param vendorId        Int     $vendorId = $vendor->id = TagStore['category'] => 'LogisticVendor'
    * @param awbNo           String  awb number
    * @param senderId        Int     user->id of Ace_Delivery salesperson, different naming
    * @param deliveryDate    String  Logistic Delivery Date (scheduled)
     **/
    public function createLogisticReplenishment($partnerId, $replenishmentList, $vendorId, $deliveryDate, $awbNo = null, $vendorRefNo = null, $senderId = null){
        try{
            $cacher = $this->app->getCacher();
            $this->app->getDbHandle()->beginTransaction();

            $type = Logistic::TYPE_REPLENISHMENT;

            if (!$vendorId){
                // default
                $Courier = $this->app->tagStore()->getByField('value', 'CourGDEX');
                $vendorId = $Courier->id;
            }

            // Logistic::VENDOR_GDEX;
            $createLogistic = $this->app->logisticStore()->create([
                'type' => $type,
                'awbno' => $awbNo,
                'vendorid' => $vendorId, // logistic vendor ID
                'senderid' => $senderId,
            ]);

            $createLogistic->typeid = 0;

            // create vendor ref no
            $vendor = $this->app->tagStore()->getById($vendorId);
            if (Logistic::VENDOR_ACEDELIVERY_VALUE == $vendor->value){
                $vendorRefNo = $this->generateAceCourierVendorRefNo($cacher, $vendor->id);
            }else{
                // if (!$vendorRefNo){
                //     throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($createLogistic, ['action' => 'invalid logistic reference no.', 'message' => '']);
                // }
                $vendorRefNo = time();
            }
            $createLogistic->partnerid = $partnerId;
            $createLogistic->vendorrefno = $vendorRefNo;

            $createLogistic = $this->app->logisticStore()->save($createLogistic);

            $branchid = '';
            foreach ($replenishmentList as $x => $replenishment){
                if ($x <= 0){
                    $branchid = $replenishment->branchid;
                }else{
                    if ($branchid != $replenishment->branchid){
                        throw new \Exception("Branch must be same.");
                    }
                }
                $check = $this->app->replenishmentLogisticStore()->getByField('replenishmentid', $replenishment->id);
                if ($check){
                    throw new \Exception("Unable to process replenishment logistic. Replenishment Data already inserted {ID: ".$replenishment->id."}");
                }
                $createReplenishmentLogistic = $this->app->replenishmentLogisticStore()->create([
                    "replenishmentid" => $replenishment->id, // CHECK id
                    "logisticid" => $createLogistic->id,
                ]);
                if (!$this->app->replenishmentLogisticStore()->save($createReplenishmentLogistic)){
                    throw new \Exception("Unable to process replenishment logistic");
                }
            }
            $createLogistic->tobranchid  = $branchid;
            $createLogistic->status = Logistic::STATUS_PROCESSING;

            $deliveryDateTime = new \DateTime($deliveryDate);
            $deliveryDateTime = common::convertUserDatetimeToUTC($deliveryDateTime);
            $createLogistic->deliverydate = $deliveryDateTime->format('Y-m-d H:i:s');

            $this->createLogisticLog($createLogistic->id, $createLogistic->status);

            $saveLogistic = $this->app->logisticStore()->save($createLogistic);
            $this->app->getDbHandle()->commit();
            $observation = new \Snap\IObservation(
                $saveLogistic,
                \Snap\IObservation::ACTION_NEW,
                $saveLogistic->status,
                ['replenishmentType' => $replenishment->type]);
            $this->notify($observation);

        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }

        return $saveLogistic;
    }

    // colelction logistic for buyback
    public function createLogisticBuyback($partnerId, $buybackList, $vendorId, $deliveryDate, $awbNo = null, $vendorRefNo = null, $senderId = null){
        try{
            $cacher = $this->app->getCacher();
            $this->app->getDbHandle()->beginTransaction();

            $type = Logistic::TYPE_BUYBACK;

            if (!$vendorId){
                // default
                $aceCourier = $this->app->tagStore()->getByField('value', 'CourAce');
                $vendorId = $aceCourier->id;
            }

            // Logistic::VENDOR_GDEX;
            $createLogistic = $this->app->logisticStore()->create([
                'type' => $type,
                'awbno' => $awbNo,
                'vendorid' => $vendorId, // logistic vendor ID
                'senderid' => $senderId,
            ]);

            $createLogistic->partnerid = $partnerId;
            $createLogistic->typeid = 0;

            // create vendor ref no
            $vendor = $this->app->tagStore()->getById($vendorId);
            if (Logistic::VENDOR_ACEDELIVERY_VALUE == $vendor->value){
                $vendorRefNo = $this->generateAceCourierVendorRefNo($cacher, $vendor->id);
            }else{
                // if (!$vendorRefNo){
                //     throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($createLogistic, ['action' => 'invalid logistic reference no.', 'message' => '']);
                // }
            }
            $createLogistic->vendorrefno = $vendorRefNo;
            $createLogistic = $this->app->logisticStore()->save($createLogistic);

            $branchid = '';
            foreach ($buybackList as $x => $buyback){
                $createBuybackLogistic = $this->app->buybackLogisticStore()->create([
                    "buybackid" => $buyback->id, // CHECK id
                    "logisticid" => $createLogistic->id,
                ]);
                if (!$this->app->buybackLogisticStore()->save($createBuybackLogistic)){
                    throw new \Exception("Unable to process buyback logistic");
                }
            }
            $createLogistic->frombranchid  = $buyback->branchid;

            $branch = $this->app->partnerStore()->getRelatedStore('branches');
            $getBranch = $branch->getByField('code', '47620');
            $aceHQ = $getBranch->id;
            $createLogistic->tobranchid  = $aceHQ;

            $deliveryDateTime = new \DateTime($deliveryDate);
            $deliveryDateTime = common::convertUserDatetimeToUTC($deliveryDateTime);
            $createLogistic->deliverydate = $deliveryDateTime->format('Y-m-d H:i:s');

            $createLogistic->status = Logistic::STATUS_PROCESSING;

            $this->createLogisticLog($createLogistic->id, $createLogistic->status);

            $saveLogistic = $this->app->logisticStore()->save($createLogistic);
            $this->app->getDbHandle()->commit();
            $observation = new \Snap\IObservation(
                $saveLogistic,
                \Snap\IObservation::ACTION_NEW,
                $saveLogistic->status,
                []);
            $this->notify($observation);

        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }

        return $saveLogistic;
    }

    /**
    * @param replenishment   Arr     $replenishment list, list of selected replenishment `ITEM` from BO/UI ---> MUST same branchId
    * @param vendorId        Int     $vendorId = $vendor->id = TagStore['category'] => 'LogisticVendor'
    * @param awbNo           String  awb number
    * @param senderId        Int     user->id of Ace_Delivery salesperson, different naming
    * @param deliveryDate    String  Logistic Delivery Date (scheduled)
     **/
    public function depre_createLogisticReplenishment(Replenishment $replenishment, $vendorId, $awbNo = null, $senderId = null, $deliveryDate){
        try{
            $this->app->getDbHandle()->beginTransaction();

            $type = Logistic::TYPE_REPLENISHMENT;

            // Logistic::VENDOR_GDEX;
            $createLogistic = $this->app->logisticStore()->create([
                'type' => $type,
                'awbno' => $awbNo,
                'vendorid' => $vendorId, // logistic vendor ID
                'senderid' => $senderId,
            ]);

            // OPT1 from branchA to branchB (transfer)
            // OPT1 from Ace to branchA (replenish)
            // no address will be insert on both OPT
            // BO will need to show address on this special case = TYPE_REPLENISHMENT
            // replenishment no longer have type(TRANSFER) with fromBranch toBranch -- 11-06-2020
            // !! pending on logistic for replenishment operation -- 11-06-2020
            // fromBranchId must always ACE_`32` or remove it -- 11-06-2020

            $createLogistic->typeid = $replenishment->id;

            $createLogistic->frombranchid = $replenishment->frombranchid;
            $createLogistic->tobranchid  = $replenishment->tobranchid;

            $deliveryDateTime = new \DateTime($deliveryDate);
            $deliveryDateTime = common::convertUserDatetimeToUTC($deliveryDateTime);
            $createLogistic->deliverydate = $deliveryDateTime->format('Y-m-d H:i:s');

            $saveLogistic = $this->app->logisticStore()->save($createLogistic);
            $this->app->getDbHandle()->commit();
            $observation = new \Snap\IObservation(
                $saveLogistic,
                \Snap\IObservation::ACTION_NEW,
                $saveLogistic->status,
                ['redemptionType' => $replenishment->type]);
            $this->notify($observation);

        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }

        return $saveLogistic;
    }

    // MANUAL only
    public function completeLogistic($logistic){
        // checking for complete and update replenishment/redemption data

        $status = Logistic::STATUS_DELIVERED;
        $logistic = $this->logisticStatus($logistic, $status);

        // update logistic type module data
        $type = $logistic->type;
        if (Logistic::TYPE_REDEMPTION == $type){
            $logsiticCreationModule = $this->app->redemptionStore()->getById($logistic->typeid);
            $logsiticCreationModule->status = Redemption::STATUS_COMPLETED; // use const to prevent different status code from object file
            $this->app->redemptionStore()->save($logsiticCreationModule);
        }elseif (Logistic::TYPE_REPLENISHMENT == $type){
            $logsiticCreationModule = $this->app->replenishmentLogisticStore()->searchTable()->select()->where("logisticid", $logistic->id)->execute();
            foreach ($logsiticCreationModule as $replenishmentLogistic){
                $update = $this->app->replenishmentStore()->getById($replenishmentLogistic->id);
                $update->status = Replenishment::STATUS_COMPLETED; // use const to prevent different status code from object file
                $this->app->replenishmentStore()->save($update);
            }
        }elseif (Logistic::TYPE_BUYBACK == $type){
            $logsiticCreationModule = $this->app->buybackLogisticStore()->searchTable()->select()->where("logisticid", $logistic->id)->execute();
            foreach ($logsiticCreationModule as $buybackLogistic){
                $update = $this->app->buybackStore()->getById($buybackLogistic->id);
                $update->status = Buyback::STATUS_COMPLETED; // use const to prevent different status code from object file
                $this->app->replenishmentStore()->save($update);
            }
        }else{
            $message = 'Invalid logistic type.';
            throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($logistic, ['action' => 'complete logistic.', 'message' => $message]);
        }

        return $logistic;
    }


    /*
    mapping for courier status to GTP logistic status
    for auto/api/crawl ONLY, manual will use logsiticStatus()
     */
    public function courierStatusMapping($logistic, $courier, $rawStatus){
        $courier;
        $rawStatus;
        // pending mapping process HERE
        $status = $rawStatus[$map];
        $this->logisticStatus($logistic, $status);
    }

    /*
    update logistic status and logging
     */
    /**
    * @param logistic           Obj     $logistic object data
    * @param status             Int     status to be change
    * @param senderId           Int     use for sentBy, deliveredBy
    * @param recievedPerson     String  sign proof of delivery
    * @param remarks            String  remarks, on log for more info on status
    * @param time               String  string format as -- ('Y-m-d H:i:s') , the time of logistic status, it can be earlier than update time. status time != update time
     **/
    public function logisticStatus(Logistic $logistic, $status, $senderId = null, $recievedPerson = null, $remarks = null, $time = null){
        // processing
        // packing
        // send/pickup

        // REFER TO OBJECT FILE `STATUS`

        // 10-08-2020 
        // modifiedstatusexport -> 0 = before export, 1 = already export, whenever have modifiedstatus will turn to 0

        if (is_null($time)){
            $time = new \DateTime();
        }else{
            $time = common::convertUserDatetimeToUTC(new \DateTime($time));
        }

        $state = $this->getStateMachine($logistic);

        try{
            $this->app->getDbHandle()->beginTransaction();

            $oldStatus = $logistic->status;

            $message = null;
            $logType = 'Public';
            switch ($status){
                case Logistic::STATUS_PROCESSING:
                    if (!$state->can(Logistic::STATUS_PROCESSING)){
                        $this->log(__METHOD__."({$logistic->id}) - fromStatus {$logistic->status} toStatus {$status}", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($logistic, ['action' => 'change status.', 'message' => $message,]);
                    }
                    break;
                case Logistic::STATUS_PACKING; // new status -- 16-06-2020
                    if (!$state->can(Logistic::STATUS_PACKING)){
                        $this->log(__METHOD__."({$logistic->id}) - fromStatus {$logistic->status} toStatus {$status}", SNAP_LOG_ERROR);
                        $message = 'Remarks cannot be empty.';
                        $logType = 'Private';
                        throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($logistic, ['action' => 'change status.', 'message' => $message,]);
                    }
                    break;
                case Logistic::STATUS_PACKED; // new status -- 16-06-2020
                    if (!$state->can(Logistic::STATUS_PACKED) && !$remarks){
                        $this->log(__METHOD__."({$logistic->id}) - fromStatus {$logistic->status} toStatus {$status}", SNAP_LOG_ERROR);
                        $message = 'Remarks cannot be empty.';
                        $logType = 'Private';
                        throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($logistic, ['action' => 'change status.', 'message' => $message,]);
                    }

                    break;
                case Logistic::STATUS_SENDING:
                    if (!$state->can(Logistic::STATUS_SENDING)){
                        $this->log(__METHOD__."({$logistic->id}) - fromStatus {$logistic->status} toStatus {$status}", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($logistic, ['action' => 'change status.', 'message' => $message,]);
                    }

                    $logistic->senton = $time->format('Y-m-d H:i:s');
                    $logistic->sentby = $senderId;
                    break;
                case Logistic::STATUS_DELIVERED:
                    if (!$state->can(Logistic::STATUS_DELIVERED)){
                        $this->log(__METHOD__."({$logistic->id}) - fromStatus {$logistic->status} toStatus {$status}", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($logistic, ['action' => 'change status.', 'message' => $message,]);
                    }

                    $logistic->deliveredon = $time->format('Y-m-d H:i:s');
                    $logistic->deliveredby = $senderId;
                    $logistic->recievedperson = $recievedPerson;
                    break;
                case Logistic::STATUS_FAILED:
                    if (!$state->can(Logistic::STATUS_FAILED)){
                        $this->log(__METHOD__."({$logistic->id}) - fromStatus {$logistic->status} toStatus {$status}", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($logistic, ['action' => 'change status.', 'message' => $message,]);
                    }
                    break;
                case Logistic::STATUS_COMPLETED:
                    if (!$state->can(Logistic::STATUS_COMPLETED)){
                        $this->log(__METHOD__."({$logistic->id}) - fromStatus {$logistic->status} toStatus {$status}", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($logistic, ['action' => 'change status.', 'message' => $message,]);
                    }
                    break;
                case Logistic::STATUS_COLLECTED:
                    if (!$state->can(Logistic::STATUS_COLLECTED)){
                        $this->log(__METHOD__."({$logistic->id}) - fromStatus {$logistic->status} toStatus {$status}", SNAP_LOG_ERROR);
                        $message = 'Remarks cannot be empty.';
                        throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($logistic, ['action' => 'change status.', 'message' => $message,]);
                    }
                    break;
                default:
                    throw new \Exception("Invalid Action");
            }

            $logistic->status = $status;
            $logistic->modifiedstatusexport = 0; // reset everytime whenever status changed.

            $this->createLogisticLog($logistic->id, $status, $logType, $remarks, $time);
            $updateLogistic = $this->app->logisticStore()->save($logistic);

            // if Status_DELIVERED
            // event will pickup on Redemption/Replnishment Manager

            $this->app->getDbHandle()->commit();

            // redemption/replenishment pickup event to update their status;
            $observation = new \Snap\IObservation($updateLogistic, \Snap\IObservation::ACTION_EDIT, $oldStatus, []);
            $this->notify($observation);
        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }

        return $updateLogistic;
    }

    public function attemps($logistic){
        // check max attemps
        $max_attemps = Logistic::MAX_ATTEMPS;
        try{
            $now = common::convertUTCToUserDatetime(new \DateTime());

            if (!in_array($logistic->status, [logistic::STATUS_SENDING,logistic::STATUS_FAILED])){
                $message = 'Status is not applicable for this action. ';
                throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($logistic, ['action' => 'attemps.', 'message' => $message,]);
                // throw new \Exception($message);
            }

            if ($max_attemps <= $logistic->attemps){
                $message = 'Max attemps exceeded.';
                throw \Snap\api\exception\LogisticInvalidAction::fromTransaction($logistic, ['action' => 'attemps.', 'message' => $message,]);
                // throw new \Exception($message);
            }

            $this->app->getDbHandle()->beginTransaction();
            $logistic->attemps = $logistic->attemps + 1;
            $status = $logistic->attemps.' attemps delivery';

            $this->createLogisticLog($logistic->id, $status);

            $updateLogistic = $this->app->logisticStore()->save($logistic);

            $this->app->getDbHandle()->commit();
            
        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
        return $updateLogistic;
    }

    /**
    * @param logisticId         String  $logistic ID
    * @param value              String  status to be change, info to capture
    * @param logType            String  public private, log type, display dependency
    * @param remarks            String  remarks, on log for more info on status/value
    * @param time               String  string format as -- ('Y-m-d H:i:s') , the time of logistic status, it can be earlier than update time. status time != update time
     **/
    public function createLogisticLog($logisticId, $value, $logType = 'Public', $remarks = null, $time = null){
        if (is_null($time)){
            $time = new \DateTime();
            $time = $time->format('Y-m-d H:i:s');
        }
        $time = common::convertUTCToUserDatetime(new \DateTime());
        $createLogsiticLog = $this->app->logisticLogStore()->create([
            "logisticid" => $logisticId,
            "type" => $logType, // private and public -- important for packing info
            // "value" => $status->toReadable(),
            "value" => $value,
            "remarks" => $remarks,
            "timeon" => $time,
        ]);
        $this->app->logisticLogStore()->save($createLogsiticLog);
    }

    // NOTE -- 10-07-2020 -- only ACE_DELIVERY WILL GENERATE REF NO, ELSE WILL FROM 3rd party vendor (INCOMING DATA)
    public function generateAceCourierVendorRefNo($cacher, $aceVendorId, $partner = null, $refid = null){
        $moduleString = 'Logistic';
        $generateStore = $this->app->logisticStore();
        $generateNoPrefix = 'G';
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
            $totalDayOrders = $generateStore->searchTable()->select()->where('createdon', '>=', $utcStartOfDay->format('Y-m-d H:i:s'))->andWhere('typeid', $aceVendorId)->count();
            $this->log("generate_".$moduleString."No($refid,{$partner->code}) - total ".$moduleString." from DB = " . $totalDayOrders, SNAP_LOG_DEBUG);
            $cacher->set($generateNoKey, $totalDayOrders + 1, 86400);
            $nextGenerateSequence = $totalDayOrders + 1;
        }
        $nextGenerateSequence = strtoupper(sprintf($format, $generateNoPrefix, $now->format('ymd'), $nextGenerateSequence, $envPrefix));
        $this->log("generate_".$moduleString."No() - Generated sequence $nextGenerateSequence for ".$moduleString." $refid for partner {$partner->code}", SNAP_LOG_DEBUG);
        return $nextGenerateSequence;
    }

    /**
     * This method will return the order state machine to manage the different states of the order process.
     *
     * @return Finite/StateMachine/StateMachine
     */
     public function getStateMachine($order) {
        $stateMachine = new \Finite\StateMachine\StateMachine;
        $config       = [
            'property_path' => 'status',
            'states' => [
                Logistic::STATUS_PENDING            => [ 'type' => 'initial', 'properties' => []],
                Logistic::STATUS_PROCESSING         => [ 'type' => 'normal', 'properties' => []],
                // Logistic::STATUS_PACKING            => [ 'type' => 'normal', 'properties' => []],
                Logistic::STATUS_PACKED             => [ 'type' => 'normal', 'properties' => []],
                Logistic::STATUS_COLLECTED          => [ 'type' => 'normal', 'properties' => []],
                Logistic::STATUS_SENDING            => [ 'type' => 'normal', 'properties' => []],
                Logistic::STATUS_DELIVERED          => [ 'type' => 'normal', 'properties' => []],
                Logistic::STATUS_FAILED             => [ 'type' => 'final', 'properties' => []],
                Logistic::STATUS_MISSING            => [ 'type' => 'final', 'properties' => []],
                Logistic::STATUS_COMPLETED          => [ 'type' => 'final', 'properties' => []],
            ],
            'transitions' => [
                Logistic::STATUS_DELIVERED       => [ 'from' => [ Logistic::STATUS_SENDING ], 'to' => Logistic::STATUS_DELIVERED ],
                Logistic::STATUS_FAILED          => [ 'from' => [ Logistic::STATUS_SENDING ], 'to' => Logistic::STATUS_FAILED ],
                Logistic::STATUS_MISSING         => [ 'from' => [ Logistic::STATUS_SENDING ], 'to' => Logistic::STATUS_MISSING ],
                //Logistic::STATUS_CANCELLED       => [ 'from' => [ Logistic::STATUS_PENDING, Logistic::STATUS_PROCESSING, Logistic::STATUS_PACKING, Logistic::STATUS_PACKED ], 'to' => Logistic::STATUS_CANCELLED ],
                Logistic::STATUS_COMPLETED         => [ 'from' => [ Logistic::STATUS_PROCESSING, Logistic::STATUS_SENDING, Logistic::STATUS_DELIVERED ], 'to' => Logistic::STATUS_COMPLETED ],

                Logistic::STATUS_PROCESSING     => [ 'from' => [Logistic::STATUS_PENDING], 'to' => Logistic::STATUS_PROCESSING ],
                // Logistic::STATUS_PACKING        => [ 'from' => [Logistic::STATUS_PROCESSING], 'to' => Logistic::STATUS_PACKING ],
                // Logistic::STATUS_PACKED         => [ 'from' => [Logistic::STATUS_PACKING ], 'to' => Logistic::STATUS_PACKED ],
                Logistic::STATUS_PACKED         => [ 'from' => [Logistic::STATUS_PROCESSING ], 'to' => Logistic::STATUS_PACKED ],
                // Logistic::STATUS_SENDING        => [ 'from' => [Logistic::STATUS_PACKED ], 'to' => Logistic::STATUS_SENDING ],
                Logistic::STATUS_COLLECTED        => [ 'from' => [Logistic::STATUS_PROCESSING, Logistic::STATUS_PACKED ], 'to' => Logistic::STATUS_COLLECTED ],
                Logistic::STATUS_SENDING        => [ 'from' => [Logistic::STATUS_PACKED, Logistic::STATUS_PROCESSING, Logistic::STATUS_COLLECTED ], 'to' => Logistic::STATUS_SENDING ],
            ]
        ];
        $loader       = new \Finite\Loader\ArrayLoader($config);
        $loader->load($stateMachine);
        $stateMachine->setStateAccessor(new \Finite\State\Accessor\PropertyPathStateAccessor($config['property_path']));
        $stateMachine->setObject($order);
        $stateMachine->initialize();
        return $stateMachine;
    }

    // GDEX Prime API - START
    public function gdexApiCreateConsignment($logistic){
        $url = $this->app->getConfig()->{'gtp.gdex.url.create_consignment'};

        if ($logistic->type != Logistic::TYPE_REDEMPTION){
            throw error;
        }

        $redemption = $this->app->redemptionStore()->getById($logistic->typeid);

        $query = ['accountNo' => $this->app->getConfig()->{'gtp.gdex.accountno'}];

        $reqData = 
            [
                "shipmentType" => "Parcel",
                "totalPiece" => 1,
                "shipmentContent" => "Goods",
                "shipmentValue" => 200,
                "shipmentWeight" => number_format((($redemption->totalweight) / 1000), 3, '.', ''), // api unit in KG
                "shipmentLength" => 0,
                "shipmentWidth" => 0,
                "shipmentHeight" => 0,
                "isDangerousGoods" => false,
                "companyName" => "",
                "receiverName" => $logistic->contactname1,
                "receiverMobile" => $logistic->contactno1,
                "receiverMobile2" => $logistic->contactno2 ? $logistic->contactno2 : "",
                "receiverEmail" => "",
                "receiverAddress1" => $logistic->address1,
                "receiverAddress2" => $logistic->address2 ? $logistic->address2 : "",
                "receiverAddress3" => $logistic->address3 ? $logistic->address3 : "",
                "receiverPostcode" => $logistic->postcode,
                "receiverCity" => $logistic->city,
                "receiverState" => $logistic->state,
                "receiverCountry" => $logistic->country,
                "IsInsurance" => true,  
                "note1" => "",
                "note2" => "",
                "orderID" => "",
                "isCod" => false,
                "codAmount" => 0,
                "doNumber1" => "",
                "doNumber2" => "",
                "doNumber3" => "",
                "doNumber4" => "",
                "doNumber5" => "",
                "doNumber6" => "",
                "doNumber7" => "",
                "doNumber8" => "",
                "doNumber9" => ""
            ];

        $reqData = array($reqData);

        $response = $this->gdexApiRequest($url, $reqData, $query);
        // print_r($response);exit;

        $response = json_decode($response);

        $check = $this->checkResGdex($response);
        if ($check['status'] != 'success'){
            throw new \Exception($check['errorMsg']);
        }

        $consignmentNoteNo = $response->r[0];

        // $logistic->awbno = $consignmentNoteNo;
        // $logistic = $this->app->logisticStore()->save($logistic);

        return $consignmentNoteNo;
    }

    public function gdexApiGetConsignmentImage($logistic){
        // file_put_contents('../asd.txt', 'asda');exit;
        $url = $this->app->getConfig()->{'gtp.gdex.url.get_consignment_image'};

        $consignmentNoteNo = $logistic->awbno;

        if (!$consignmentNoteNo){
            throw new \Exception("Invalid AWB No.");
        }

        $query = ['accountNo' => $this->app->getConfig()->{'gtp.gdex.accountno'}];

        $reqData = array($consignmentNoteNo);

        $response = $this->gdexApiRequest($url, $reqData, $query);

        if (!$response){
            throw new \Exception("Error or Invalid AWB No. on GDEX response. Please manually check on GDEX panel.");
        }

        // .zip source, save as .zip on front end extjs
        // https://docs.sencha.com/extjs/6.2.1/modern/Ext.exporter.File.html

        $path = $this->app->getConfig()->{'gtp.gdex.path.files'};
        $filename = $logistic->awbno.'.zip';
        // $file = $path.$filename;
        // $new_file = file_put_contents($file, $response);
        // // unzip($new_file);
        // $zipArchive = new \ZipArchive();
        // $result = $zipArchive->open($file);
        // if ($result === TRUE) {
        //     $zipArchive ->extractTo($path);
        //     $zipArchive ->close();
        //     // Do something else on success
        // } else {
        //     // Do something on error
        // }

        // ob_end_clean();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        return $response;
    }
    
    public function gdexApiGetShipmentStatusDetail($logistic, $autoCrawler = false){
        // {
        //     "s": "success",
        //     "r": {
        //       "consignmentNote": "MY37022088421",
        //       "latestConsignmentNoteStatus": "Delivered",
        //       "latestEnumStatus": 4,
        //       "cnDetailStatusList": [{
        //         "enumStatus": 4,
        //         "consignmentNoteStatus": "Delivered",
        //         "dateScan": "2020-12-19T11:13:15Z",
        //         "statusDetail": "Delivered",
        //         "location": "Kajang"
        //       }, {
        //         "enumStatus": 3,
        //         "consignmentNoteStatus": "Out For Delivery",
        //         "dateScan": "2020-12-19T08:30:13Z",
        //         "statusDetail": "Out for delivery",
        //         "location": "Kajang"
        //       }, {
        //         "enumStatus": 2,
        //         "consignmentNoteStatus": "In Transit",
        //         "dateScan": "2020-12-18T15:01:00Z",
        //         "statusDetail": "Inbound to Kajang",
        //         "location": "Kajang"
        //       }, {
        //         "enumStatus": 2,
        //         "consignmentNoteStatus": "In Transit",
        //         "dateScan": "2020-12-18T08:59:08Z",
        //         "statusDetail": "Outbound from Bangi",
        //         "location": "Bangi"
        //       }, {
        //         "enumStatus": 2,
        //         "consignmentNoteStatus": "In Transit",
        //         "dateScan": "2020-12-18T08:01:50Z",
        //         "statusDetail": "Inbound to Bangi",
        //         "location": "Bangi"
        //       }, {
        //         "enumStatus": 2,
        //         "consignmentNoteStatus": "In Transit",
        //         "dateScan": "2020-12-17T21:21:00Z",
        //         "statusDetail": "In transit",
        //         "location": "Petaling Jaya"
        //       }, {
        //         "enumStatus": 2,
        //         "consignmentNoteStatus": "In Transit",
        //         "dateScan": "2020-12-17T18:53:13Z",
        //         "statusDetail": "Outbound from Subang Jaya",
        //         "location": "Subang Jaya"
        //       }, {
        //         "enumStatus": 1,
        //         "consignmentNoteStatus": "Pickup",
        //         "dateScan": "2020-12-17T14:23:41Z",
        //         "statusDetail": "Picked up by courier",
        //         "location": "Subang Jaya"
        //       }]
        //     },
        //     "e": null
        // }

        $url = $this->app->getConfig()->{'gtp.gdex.url.get_shipment_status_detail'};

        if (!$logistic->awbno){
            throw error;
        }

        $reqData = ['consignmentNumber' => $logistic->awbno];

        $response = $this->gdexApiRequest($url, null, $reqData, "GET");

        $response = json_decode($response);

        // print_r($response);exit;

        $check = $this->checkResGdex($response);
        if ($check['status'] != 'success'){
            throw $check['errorMsg'];
        }
        $statusList = $response->r->cnDetailStatusList;
        $latestConsignmentNoteStatus = $response->r->latestConsignmentNoteStatus;
        $latestEnumStatus = $response->r->latestEnumStatus;

        if ($autoCrawler){
            return $latestConsignmentNoteStatus;
        }

        $vendorValue = Logistic::VENDOR_GDEX_VALUE;
        $aceStatus = $this->mapCourierStatus($logistic, $vendorValue, $statusList, $latestEnumStatus);

        // if ($aceStatus['enumStatus'] > 0){
        //     $checkTime = common::convertUserDatetimeToUTC(new \DateTime($aceStatus['dateScan']));
        //     $checkLog = $this->app->logisticLogStore()->searchTable()->select()->where('logisticid', $logisitic->id)->andWhere('time', $checkTime->format('Y-m-d H:i:s'))->count('id');
        //     if ($checkLog == 0){
        //         if ($logistic->status != $aceStatus['aceStatus']){
        //             $this->logisticStatus($logistic, $aceStatus['aceStatus'], null, null, null, $aceStatus['dateScan']);
        //         }
        //     }
        //     // (Logistic $logistic, $status, $senderId = null, $recievedPerson = null, $remarks = null, $time = null)
        // }

        if (!$statusList){
            $return[] = [
                'time' => '-',
                'api_status' => '-',
                'api_status_text' => 'Success API - Pending on GDEX',
                'ace_status' => '-',
            ];
        }else{
            foreach ($statusList as $x => $list){
                $return[] = [
                    'time' => date($list->dateScan),
                    'api_status' => $list->consignmentNoteStatus,
                    'api_status_text' => $list->statusDetail.' - '.$list->location,
                    'ace_status' => '',
                ];
            }
        }

        return $return; // display on BO
        
        // "consignmentNote": "TCN1004458",
        // "latestConsignmentNoteStatus": null,
        // "latestEnumStatus": 0,
        // "cnDetailStatusList": []
    }


    // $datetime => string
    public function gdexApiCreatePickup($logistic, $datetime){
        // {
        //     "contactName": "Michael",
        //     "contactNumber": "0122001000",
        //     "pickupDate": "2019-12-01T00:00:00",
        //     "officeCloseTime": "17:00:00",
        //     "parcelReadyTime": "10:00:00",
        //     "transportation": "Motorbike",
        //     "isTrolley": true,
        //     "remarks": "Testing",
        //     "shipmentType": "Parcel",
        //     "pieces": 1,
        //     "weight": 10
        // }
        $url = $this->app->getConfig()->{'gtp.gdex.url.create_pickup'};

        if (!$logistic->awbno){
            throw error;
        }
        
        $time = new \DateTime($datetime);

        $reqData = [
            "contactName" => "Nurul Nadirah",
            "contactNumber" => "0380807198",
            "pickupDate" => $time->format('Y-m-d')."T00:00:00",
            "officeCloseTime" => "17:00:00",
            "parcelReadyTime" => $time->format('H:i:s'),
            "transportation" => "Van",
            "isTrolley" => false,
            "remarks" => "Contact before pickup",
            "shipmentType" => "Parcel",
            "pieces" => 1,
            "weight" => 1
        ];
        
        $reqData = array($reqData);

        $response = $this->gdexApiRequest($url, $reqData, null);

        $response = json_decode($response);
        // {
        //     "s": "success",
        //     "r": "CPAA000651",
        //     "e": null
        // }

        $check = $this->checkResGdex($response);
        if ($check['status'] != 'success'){
            throw $check['errorMsg'];
        }
        $responseRef = $response->r;

        $logistic->pickupref = $responseRef;
        $logistic->pickupdatetime = $time->format('Y-m-d H:i:s');

        $return = false;
        $updateLogistic = $this->app->logisticStore()->save($logistic);
        if ($updateLogistic){
            $return = true;
        }

        return $return; 
    }

    private function gdexApiRequest($url, $json = null, $query = null, $reqType = 'POST', $options = null){
        // .. AUTH, HEADER, configs here
        
        $fullUrl = $url;

        $client = new \GuzzleHttp\Client(['verify' => false]);
        // Send an asynchronous request.
        try {

            $sentTime = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));

            $preOption = [
                'headers' => [
                    'ApiToken' => $this->app->getConfig()->{'gtp.gdex.token'},
                    'Content-Type' => 'application/json',
                    'Subscription-Key' => $this->app->getConfig()->{'gtp.gdex.subscriptionkey.primary'}
                ]
            ];
            if (!is_null($query)){
                $preOption += [
                    'query' => $query
                ];
            }
            if (!is_null($json)){
                $preOption += [
                    'body' => json_encode($json)
                ];
            }

            $response = $client->request($reqType, $fullUrl, $preOption); 
            $responseBody = (string)$response->getBody();

            if('200' == $response->getStatusCode()) {
                $this->log("SapApiSender sending to $destination with data $body -- received response (".$responseBody.")", SNAP_LOG_DEBUG);

            } else {
                $responseBody = "Unexpected response from server with status code: ".$response->getStatusCode();
                $errorMsg = $e->getMessage();
            }

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to GDEX {$fullUrl} with error " . $e->getMessage() . "\nData: $body\nResponse:".$responseBody, SNAP_LOG_ERROR);
        }

        return $responseBody;
    }

    private function checkResGdex($res){
        $return = [
            'status' => 'error',
            'errorMsg' => 'unknown',
        ];
        if ($res->e){
            return $return = [
                'status' => 'error',
                'errorMsg' => $res->e,
            ];
        }
        if ($res->s == 'success'){
            return $return = [
                'status' => 'success',
                'errorMsg' => '',
            ];
        }
        if ($res->r){
            return $return = [
                'status' => 'error',
                'errorMsg' => $res->r,
            ];
        }
        if (is_string($res)){
            return $return = [
                'status' => 'error',
                'errorMsg' => $res,
            ];
        }
        return $return;
    }
    // GDEX Prime API - END



    public function lineclearApiCreateShipment($logistic){
        $url = $this->app->getConfig()->{'gtp.lineclear.url.create_shipment'};

        if ($logistic->type != Logistic::TYPE_REDEMPTION){
            throw error;
        }

        $redemption = $this->app->redemptionStore()->getById($logistic->typeid);

        // PENDING
        // shipment ref
        // sender_email => ace email
        // sender_phone => ace phone
        $reqData = [
            "Shipment" => [
                [
                    "ShipmentServiceType" => "Standard Delivery",
                    "SenderName" => "ACE GROUP",
                    "RecipientName" => $logistic->contactname1,
                    "ShipmentAddressFrom" => [
                        "CompanyName" => "ACE GROUP",
                        "UnitNumber" => "No. 19-1",
                        "Address" => "Jalan USJ 10/1D",
                        "Address2" => "Taipan Business Centre",
                        "PostalCode" => "47620",
                        "City" => "Subang Jaya",
                        "State" => "Selangor",
                        "Email" => "customer-service@ace2u.com",
                        "PhoneNumber" => "012315566561",
                        "ICNumber" => ""
                        // "CompanyName" => "asdasd",
                        // "UnitNumber" => "",
                        // "Address" => " No. 1231",
                        // "Address2" => "asdad",
                        // "PostalCode" => "41620",
                        // "City" => "Subang Jaya",
                        // "State" => "Johor",
                        // "Email" => "asd@gmail.com",
                        // "PhoneNumber" => "ascascas",
                        // "ICNumber" => ""
                    ],
                    "ShipmentAddressTo" => [
                        "CompanyName" => "",
                        "UnitNumber" => $logistic->address1,
                        "Address" => $logistic->address2 ? $logistic->address2 : "-", // required field
                        "Address2" => $logistic->address3 ? $logistic->address3 : "",
                        "PostalCode" => $logistic->postcode,
                        "City" => $logistic->city,
                        "State" => $logistic->state,
                        "Email" => "",
                        "PhoneNumber" => $logistic->contactno1,
                        "ICNumber" => ""
                    ],
                    "RecipientPhone" => $logistic->contactno1,
                    "ParcelType" => "Package",
                    "ShipmentRef" => null,
                    "ShipmentDescription" => 'Contact2:'.$logistic->contactno1.' Phone2:'.$logistic->contactno2, // 07/12/2020 --add in 2nd person name and phone.
                    "ShipmentType" => "Pickup",
                    "CODAmount" => "",
                    "WayBill" => [
                        [
                            // "WayBillNo" => "TEST159862027777",
                            "Weight" => number_format((($redemption->totalweight) / 1000), 3, '.', ''), // api unit in KG,
                            "VolumeWidth" => "25",
                            "VolumeHeight" => "25",
                            "VolumeLength" => "25"
                        ],
                        // can be multiple shipment at once
                    ],
                    "DONumber" => ""
                ]
            ]
        ];

        $response = $this->lineclearApiRequest($url, $reqData);
        $response = json_decode($response);
        $check = $this->checkResLineClear($response);
        if ($check['status'] != 'success'){
            throw new \Exception($check['errorMsg']);
        }

        $awbno = $response->ResponseData->WayBill[0];
        $reponseData = $response->ResponseData;

        return $awbno;

    }


    // LineClear API - START
    private function lineclearApiRequest($url, $json = null, $query = null, $reqType = 'POST', $options = null){
        
        $fullUrl = $url;

        $client = new \GuzzleHttp\Client(['verify' => false]);
        // Send an asynchronous request.
        try {

            $sentTime = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));

            $preOption = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic '.$this->app->getConfig()->{'gtp.lineclear.token'},
                ]
            ];
            if (!is_null($query)){
                $preOption += [
                    'query' => $query
                ];
            }
            if (!is_null($json)){
                $preOption += [
                    'body' => json_encode($json)
                ];
            }

            $response = $client->request($reqType, $fullUrl, $preOption); 
            $responseBody = (string)$response->getBody();

            if('200' == $response->getStatusCode()) {
                $this->log("SapApiSender sending to $destination with data $body -- received response (".$responseBody.")", SNAP_LOG_DEBUG);

            } else {
                $responseBody = "Unexpected response from server with status code: ".$response->getStatusCode();
                $errorMsg = $e->getMessage();
            }

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to GDEX {$fullUrl} with error " . $e->getMessage() . "\nData: $body\nResponse:".$responseBody, SNAP_LOG_ERROR);
        }

        return $responseBody;
    }

    private function checkResLineClear($res){

        // sample response
        // {
        //     "Status":true,
        //     "Message":"Shipment creation successful.",
        //     "ResponseData":[
        //         {
        //             "PickupBranchCode":"DUSJ",
        //             "DeliveryRouteCode":"",
        //             "WayBill":[
        //                 "ZND160562974444",
        //                 "CND160562974444001"
        //             ],
        //             "DeliveryHubCode":"ZSHM",
        //             "DeliveryBranchCode":"XKSL",
        //             "PickupRouteCode":"",
        //             "PickupHubCode":"ZSHM"
        //         }
        //     ]
        // }
        // {"Status":true,"Message":"Shipment creation Failed.","Reason":"WayBill Exist"}
        
        $return = [
            'status' => 'error',
            'errorMsg' => 'unknown',
        ];
        if ($res->Status == true){
            if (strpos($res->Message, 'successful') !== false){
                $return['status'] = 'success';
            }else{
                $return['errorMsg'] = $res->Message;
            }
        }
        return $return;
    }
    // LineClear API - END


    private function mapCourierStatus($logistic, $vendorValue, $statusList = null, $latestEnumStatus = null){
        // list here

        // list here end

        // "cnDetailStatusList": [{
        //         "enumStatus": 4,
        //         "consignmentNoteStatus": "Delivered",
        //         "dateScan": "2020-12-19T11:13:15Z",
        //         "statusDetail": "Delivered",
        //         "location": "Kajang"
        //       }, {
        //         "enumStatus": 3,
        //         "consignmentNoteStatus": "Out For Delivery",
        //         "dateScan": "2020-12-19T08:30:13Z",
        //         "statusDetail": "Out for delivery",
        //         "location": "Kajang"
        //       }, {
        //         "enumStatus": 2,
        //         "consignmentNoteStatus": "In Transit",
        //         "dateScan": "2020-12-18T15:01:00Z",
        //         "statusDetail": "Inbound to Kajang",
        //         "location": "Kajang"
        //       }, {
        //         "enumStatus": 2,
        //         "consignmentNoteStatus": "In Transit",
        //         "dateScan": "2020-12-18T08:59:08Z",
        //         "statusDetail": "Outbound from Bangi",
        //         "location": "Bangi"
        //       }, {
        //         "enumStatus": 2,
        //         "consignmentNoteStatus": "In Transit",
        //         "dateScan": "2020-12-18T08:01:50Z",
        //         "statusDetail": "Inbound to Bangi",
        //         "location": "Bangi"
        //       }, {
        //         "enumStatus": 2,
        //         "consignmentNoteStatus": "In Transit",
        //         "dateScan": "2020-12-17T21:21:00Z",
        //         "statusDetail": "In transit",
        //         "location": "Petaling Jaya"
        //       }, {
        //         "enumStatus": 2,
        //         "consignmentNoteStatus": "In Transit",
        //         "dateScan": "2020-12-17T18:53:13Z",
        //         "statusDetail": "Outbound from Subang Jaya",
        //         "location": "Subang Jaya"
        //       }, {
        //         "enumStatus": 1,
        //         "consignmentNoteStatus": "Pickup",
        //         "dateScan": "2020-12-17T14:23:41Z",
        //         "statusDetail": "Picked up by courier",
        //         "location": "Subang Jaya"
        //       }]

        if ($vendorValue == Logistic::VENDOR_GDEX_VALUE){
            $gdexList = [
                [
                    "enumStatus" => 0,
                    "consignmentNoteStatus" => null,
                    "aceStatus" => Logistic::STATUS_PROCESSING,
                ],
                [
                    "enumStatus" => 1,
                    "consignmentNoteStatus" => "Pickup",
                    "aceStatus" => Logistic::STATUS_COLLECTED,
                ],
                [
                    "enumStatus" => 2,
                    "consignmentNoteStatus" => "In Transit",
                    "aceStatus" => Logistic::STATUS_SENDING,
                ],
                [
                    "enumStatus" => 3,
                    "consignmentNoteStatus" => "Out For Delivery",
                    "aceStatus" => Logistic::STATUS_SENDING,
                ],
                [
                    "enumStatus" => 4,
                    "consignmentNoteStatus" => "Delivered",
                    "aceStatus" => Logistic::STATUS_COMPLETED,
                ]
            ];

            $aceStatus = [];
            if ($latestEnumStatus){
                foreach ($gdexList as $status){
                    if ($status["enumStatus"] == $latestEnumStatus){
                        $aceStatus = $status;
                    }
                }
            }

            // $logisticLogs = $this->app->LogisticLogStore()->select()->where('logisticid', $logistic->id)->execute();
            // if (!$logisticLogs){
            //     foreach ($statusList as $status){
            //         $this->app->logisticLogStore()->create([
            //             'logisticid' => $logistic->id,
            //             'type' => LogisticLog::TYPE_PUBLIC,
            //             'value' => 'gdex'.$status['enumStatus'],
            //             'time' => $status
            //         ]);
            //     }
            // }else{
            //     foreach ($statusList as $status){
                    
            //     }
            // }
            return $aceStatus;
        }

        
        if ($vendorValue == Logistic::VENDOR_LINCLEAR_VALUE){
            $linieClearList = [
                
            ];

            return $aceStatus;
        }
        return false;
    }

    public function aceDeliveryGetShipmentStatusDetail($logistic){
        //$logs = $this->app->logisticLogStore()->searchTable()->select()->where('logisticid', $logistic->id)->andWhere('type', LogisticLog::TYPE_PUBLIC)->orderBy('id', desc)->execute();
        $logs = $this->app->logisticLogStore()->searchTable()->select()->where('logisticid', $logistic->id)->orderBy('id', desc)->execute();
        foreach ($logs as $log){
            if (gettype(intval($log->value)) == 'integer'){
                // map integer value to readable
                $log->value = $log->readablestatus;
            }
            if ($log->remarks){
                $log->value = $log->value.'<br>REMARKS:'.$log->remarks;
            }
            $return[] = [
                'time' => $log->timeon->format('Y-m-d H:i:s'),
                'api_status' => '-',
                'api_status_text' => '-',
                'ace_status' => $log->value
            ];
        }
        return $return;
    }


    // 30min interval, get the logstic->status != complete, and courier partner'
    public function courierStatusCrawler(){
        $autoCrawler = true;

        $logistics = $this->app->logisticStore()->searchView()->select()
            ->whereNotNull('awbno')
            ->where('status', 'NOT IN', [Logistic::STATUS_DELIVERED, Logistic::STATUS_COMPLETED, Logistic::STATUS_FAILED, Logistic::STATUS_MISSING])
            ->execute();

        if (!$logistics){
            return true;
        }

        foreach ($logistics as $logistic){
            // differentiate the courier partner api function, can add in on future 
            if ($logistic->vendorvalue == Logistic::VENDOR_GDEX_VALUE){
                $return = $this->gdexApiGetShipmentStatusDetail($logistic, $autoCrawler); // return GDEX ENUM;
                if (!$return || !empty($return)){
                    if ($return == "Delivered"){
                        $GTP_status = Logistic::STATUS_COMPLETED;
                    }else{
                        $checkLog = $this->app->logisticLogStore()->searchTable()->select()->where('logisticid', $logisitic->id)->andWhere('value', Logistic::STATUS_PROCESSING)->count('id');
                        if ($checkLog == 0){
                            $GTP_status = Logistic::STATUS_PROCESSING;
                        }else{
                            return;
                        }
                    }
                }
                if ($GTP_status){
                    $this->logisticStatus($logistic, $GTP_status, null, null, $remarks = null, $time = null);
                }
            }
        }
        return true;
    }
}
?>