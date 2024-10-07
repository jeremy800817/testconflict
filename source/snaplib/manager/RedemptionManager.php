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
Use \Snap\object\Redemption;
Use \Snap\object\Logistic;
Use \Snap\common;
use \Snap\api\casa\BaseCasa;

class RedemptionManager implements IObservable, IObserver
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
            if (Logistic::TYPE_REDEMPTION == $state->target->type){
                // no checking here, firmed data from logisticManager;
                // courier tracking/update will be on logisticManager;
                $logistic = $state->target;

                $redemption = $this->app->redemptionStore()->getById($logistic->typeid);

                // _UPDATE/_CHANGE status value
                if (Logistic::STATUS_FAILED != $state->target->status){
                    $this->updateRedemptionLogisticStatus($redemption, $logistic);
                }else{
                    $this->failedLogistic($redemption, $logistic);
                }
            }

        }
    }

    /**
     * @param obj       $partner            order data to proceed redemption
     * @param str       $apiVersion         api version of request
     * @param str       $refid              partner refid for redemption
     * @param str       type                total weight of redemption, can be less than order->amount
     * @param str       $totalWeight        total weight of redemption
     * @param obj       branchId            current gold price to calculate redemption gold price during process
     * @param arrobj    $itemArray          item(s) to redeem
     * @param arr       $deliveryDetails    redemption details, information of user, use for logistic and etc\
     * @param arr       $scheduleInfo       schedule details, information use for appointment and pre-order
     * @param str       $timestamp          timestamp
     * @param obj       $priceStream        current gold price to calculate redemption gold price during process
     * 
     * RAW itemArray [item_obj] item_obj[
     *  'serialno' -> optional
     *  denomination (gram)
     *  quantity
     * ] 
     * RAW deliveryDetails obj[
     *  contactname1
     *  contactname2
     *  contact_mobile1
     *  contact_mobile2
     *  address1
     *  address2
     *  city
     *  postcode
     *  state
     *  country
     * ]
     */
    public function createRedemption($partner, $apiVersion, $refid, $branchId, 
                                 $type, $totalWeight, $totalQuantity, $itemArray, 
                                 $deliveryDetails = [], $scheduleInfo = [], $remarks, $timestamp, $priceStream = null,
                                 $redemptionFee = null, $insuranceFee = null, $handlingFee = null, $pickupbranch = null)
    {
        $startedTransaction = $this->app->getDBHandle()->inTransaction();
        if (!$startedTransaction) {
            $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
        }
        try{
            $skipExceptionSave = false;
            $cacher = $this->app->getCacher();
            $this->log("Redemption - createRedemption - data{$itemArray} - start redemption process now is:" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);

            //Ensure no duplicated refid for partner
            $count = $this->app->redemptionStore()
                        ->searchTable()
                        ->select(['id'])
                        ->where('partnerid', $partner->id)
                        ->andWhere('partnerrefno', $refid)
                        ->count();
            if($count) {
                $skipExceptionSave = true;
                throw \Snap\api\exception\RefDuplicatedException::fromTransaction($partner, ['partnerrefno' => $refid, 'field' => 'refid', 'action' => 'redemption']);
            }

            // raw when passing in
            $items = json_encode($itemArray);
            $redemption = $this->app->redemptionStore()->create([
                "partnerid" => $partner->id,
                "branchid" => $branchId,
                "partnerrefno" => $refid,
                "apiversion" => $apiVersion,
                "redemptionno" => $this->generateRedemptionNo($cacher, $partner, $refid),
                "type" => $type,
                "totalweight" => $totalWeight,
                "totalquantity" => $totalQuantity, 
                "redemptionfee" => $redemptionFee,
                "insurancefee" => $insuranceFee,
                "handlingfee" => $handlingFee,
                "items" => $items, // initial raw redemption, will replace on_TYPE_PROCESS()
                "remarks" => $remarks,
                "appointmentbranchid" => $pickupbranch, //branch for pickup. currently nusagold use it
                "status" => Redemption::STATUS_PENDING, // init status pending
            ]);
            $redemption = $this->app->redemptionStore()->save($redemption);

            if ($ownsTransaction) {
                $this->app->getDbHandle()->commit();
            }
        }catch(\Exception $e){
            if ($ownsTransaction) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
        
        return $redemption;
    }

    // process ----------------------
    /**
     * @param Redemption $redemption        Redemption
     * @param obj       $partner            order data to proceed redemption
     * @param obj       branchId            current gold price to calculate redemption gold price during process
     * @param arrobj    $itemArray          item(s) to redeem
     * @param arr       $deliveryDetails    redemption details, information of user, use for logistic and etc\
     * @param arr       $scheduleInfo       schedule details, information use for appointment and pre-order
     * 
     * RAW itemArray [item_obj] item_obj[
     *  'serialno' -> optional
     *  denomination (gram)
     *  quantity
     * ] 
     * RAW deliveryDetails obj[
     *  contactname1
     *  contactname2
     *  contact_mobile1
     *  contact_mobile2
     *  address1
     *  address2
     *  city
     *  postcode
     *  state
     *  country
     * ]
     */
    public function confirmRedemption($redemption, $partner, $branchId, $deliveryDetails = [], $scheduleInfo = [], $submitSAP = true){
        // init action
        // redemption type (branch, delivery, special_delivery, pre-appointment)

        $skipExceptionSave = false;
        $startedTransaction = $this->app->getDBHandle()->inTransaction();
        if (!$startedTransaction) {
            $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
        }

        try{
            //Ensure no duplicated refid for partner
            $count = $this->app->redemptionStore()
                        ->searchTable()
                        ->select(['id'])
                        ->where('partnerid', $partner->id)
                        ->andWhere('partnerrefno', $redemption->partnerrefno)
                        ->andWhere('status', Redemption::STATUS_CONFIRMED)
                        ->count();
            if($count) {
                $skipExceptionSave = true;
                throw \Snap\api\exception\RedemptionError::fromTransaction($redemption, ['message' => "Redemption {$redemption->partnerrefno} is already confirmed."]);
            }
            $type = $redemption->type;
            if (Redemption::TYPE_BRANCH == $type){
                
                $this->log("Redemption - confirmRedemption - data{$redemption->id} - processing `Branch` sub redemption process now is:" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);
                $redemption = $this->onBranchRedeem($redemption, $partner, $branchId);
                

            }else if (in_array($type, [Redemption::TYPE_DELIVERY, Redemption::TYPE_SPECIALDELIVERY, Redemption::TYPE_APPOINTMENT])){

                $this->log("Redemption - confirmRedemption - data{$redemption->id} - processing `Delivery` sub redemption process now is:" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);
                $redemption = $this->onDeliveryRedeem($redemption, $partner, $deliveryDetails, $scheduleInfo, $submitSAP);

            }else{

                $this->log("Redemption - confirmRedemption - data{$redemption->id} - failed on invalid TYPE redemption process now is:" . gmdate('Y-m-d H:i:s') , SNAP_LOG_ERROR);
                throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => 'Unable to proceed redemption.']);
            
            }

            if (Redemption::STATUS_PENDING == $redemption->status){
                $redemption->status = Redemption::STATUS_CONFIRMED;
            }
            $saveRedemption = $this->app->redemptionStore()->save($redemption);
            $this->app->getDbHandle()->commit();

            $this->log("Redemption - confirmRedemption - data{$redemption->id} - redemption data create successful now is:" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);

            $observation = new \Snap\IObservation(
                $saveRedemption, 
                \Snap\IObservation::ACTION_NEW, 
                Redemption::STATUS_CONFIRMED, 
                ['redemptionType' => $type]);
            $this->notify($observation);

            /**
             * Start transaction again if transaction was started prior to this function
             * because we commit without checking if we own the transaction
             */
            if ($startedTransaction && !$ownsTransaction) {
                $this->app->getDbHandle()->beginTransaction();
            }
        }catch(\Exception $e){
            if (!$skipExceptionSave){
                // save all incoming raw data and failed it
                $redemption->status = Redemption::STATUS_FAILED;
                $this->app->redemptionStore()->save($redemption);
                $this->app->getDbHandle()->commit();
                if ($startedTransaction && !$ownsTransaction) {
                    $this->app->getDbHandle()->beginTransaction();
                }
            }else{
                if($ownsTransaction) {
                    $this->app->getDbHandle()->rollback();
                }
            }
            throw $e;
        }

        return $saveRedemption;
    }

    // process ----------------------
    /** 
     * change details on redemption db, available for (deliveryInfo, scheduleInfo), if items will strictly for cancel and create new only
     * @param obj   $partner            partner data
     * @param obj   $refid              redemption ref id
     * @param obj   $deliveryInfo       delivery data
     * @param obj   $scheduleInfo       schedule data
     * 
     **/
    public function updateRedemption($partner, $refid, $deliveryInfo = null, $scheduleInfo = null, $redemption = null, $params = null){
        try{

            $this->app->getDbHandle()->beginTransaction();

            if (!empty($deliveryInfo) && !empty($scheduleInfo)){
                // only either one
                throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => 'Invalid data input.']);;
            }
            
            // only can edit with not completed, failed, process_delivery ( with failed attempts )
            if ($redemption){
                if (in_array($redemption->status, [Redemption::STATUS_COMPLETED, Redemption::STATUS_FAILED])){
                    throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => 'Invalid Redemption Record.']);
                }
            }else{
                $redemption = $this->app->redemptionStore()->searchTable()->select()
                ->where('partnerrefno', $refid)
                ->andWhere('partnerid', $partner->id)
                ->andWhere('status', "NOT IN", [Redemption::STATUS_COMPLETED, Redemption::STATUS_FAILED])
                ->one();
            }

            if (!$redemption){
                throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => 'Invalid Redemption Record.']);
            }

            // check logistic failed and apply new delivery address
            $allowEdit = true;
            // if ($redemption->status == Redemption::STATUS_PROCESSDELIVERY){
            //     $searchLogistic = $this->app->logisticTrackerStore()->select()
            //         ->where('redemptionid', $redemption->id);
            //     foreach ($searchLogistic as $tracker){
            //         if ($tracker->status != 'failed'){
            //             $allowEdit = false;
            //         }
            //         multiple attempts created
            //         if ($tracker->status == 'active'){
            //             $allowEdit = false;
            //         }
            //     }
            // }

            if (!empty($deliveryInfo) && $allowEdit){
                $redemption->deliverycontactname1 = $deliveryInfo->contactname1;
                $redemption->deliverycontactname2 = $deliveryInfo->contactname2;
                $redemption->deliverycontactno1 = $deliveryInfo->contact_mobile1;
                $redemption->deliverycontactno2 = $deliveryInfo->contact_mobile2;
                $redemption->deliveryaddress1 = $deliveryInfo->address1;
                $redemption->deliveryaddress2 = $deliveryInfo->address2;
                $redemption->deliverycity = $deliveryInfo->city;
                $redemption->deliverypostcode = $deliveryInfo->postcode;
                $redemption->deliverystate = $deliveryInfo->state;
                $redemption->deliverycountry = $deliveryInfo->country;
            }
            if (!empty($scheduleInfo) && $allowEdit){

                // call sap update branchid
                $sap_response = $this->app->apiManager()->sapRedemptionUpdate($redemption, $scheduleInfo);
                if ($sap_response['error']){
                    throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => 'Unable to proceed redemption.']);
                }

                $scheduleDateTime = new \DateTime($scheduleInfo->datetime);
                $scheduleDateTime = common::convertUserDatetimeToUTC($scheduleDateTime);
                $redemption->appointmentbranchid = $scheduleInfo->branch_id;
                $redemption->appointmentdatetime = $scheduleDateTime->format('Y-m-d H:i:s');
            }

            // editing from BO 
            if ($params){

                foreach ($params as $key => $paramValue){
                    if ($redemption->$key){
                        $redemption->$key = $paramValue;
                    }
                }

                $redemption->modifiedon = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
                $redemption->modifiedby = defined('SNAPAPP_DBACTION_USERID') ? SNAPAPP_DBACTION_USERID : $this->app->getUsersession()->getUser()->id;
            }

            $updateRedemption = $this->app->redemptionStore()->save($redemption);
            $this->app->getDbHandle()->commit();

            $observation = new \Snap\IObservation(
                $updateRedemption, 
                \Snap\IObservation::ACTION_EDIT, 
                $updateRedemption->status, 
                ['redemptionType' => $redemption->type]);
            $this->notify($observation);

        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }

        return $updateRedemption;
    }

    /**
     * cancel redemption
     * @param obj   $partner         partner data
     * @param str   $refid           redemption ref id
     * 
     **/
    public function cancelRedemption($partner, $refid){
        try{

            $this->app->getDbHandle()->beginTransaction();

            $redemption = $this->app->redemptionStore()->searchTable()->select()
                ->where('partnerrefno', $refid)
                ->andWhere('partnerid', $partner->id)
                ->andWhere('status', "NOT IN", [Redemption::STATUS_FAILED, Redemption::STATUS_PROCESSDELIVERY, Redemption::STATUS_CANCELLED])
                ->one();
            // only can edit with not completed, failed, process_delivery ( with failed attempts )

            if (!$redemption){
                throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => 'Invalid Redemption Record.']);;
            }else{
                // check branch process, branch can completed
                if ($redemption->type != Redemption::TYPE_BRANCH && $redemption->status == Redemption::STATUS_COMPLETED){
                    throw error;
                }

                // check logistic
                $redemptionLogistic = $this->app->logisticStore()->searchTable()->select()
                    ->where('type', Logistic::TYPE_REDEMPTION)
                    ->andWhere('typeid', $redemption->id)
                    ->one();
                if ($redemptionLogistic){
                    throw error;
                }
            }

            // check logistic failed and apply new delivery address
            $allowEdit = true;
            // if ($redemption->status == Redemption::STATUS_PROCESSDELIVERY){
            //     $searchLogistic = $this->app->logisticTrackerStore()->select()
            //         ->where('redemptionid', $redemption->id);
            //     foreach ($searchLogistic as $tracker){
            //         if ($tracker->status != 'failed'){
            //             $allowEdit = false;
            //         }
            //         multiple attempts created
            //         if ($tracker->status == 'active'){
            //             $allowEdit = false;
            //         }
            //     }
            // }

            $sap_response = $this->app->apiManager()->sapRedemptionCancel($redemption);
            if ($sap_response['error']){
                throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => 'Unable to proceed redemption.']);;
            }

            $redemption->status = Redemption::STATUS_CANCELLED;

            $updateRedemption = $this->app->redemptionStore()->save($redemption);
            $this->app->getDbHandle()->commit();

            $observation = new \Snap\IObservation(
                $updateRedemption, 
                \Snap\IObservation::ACTION_CANCEL, 
                $updateRedemption->status, 
                ['redemptionType' => $redemption->type]);
            $this->notify($observation);

        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }

        return $updateRedemption;
    }

    /**
     * reverse redemption
     * @param obj   $partner         partner data
     * @param str   $refid           redemption ref id
     * 
     **/
     public function reverseRedemption($partner, $refid){
        try{
            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }

            $redemption = $this->app->redemptionStore()->searchTable()->select()
                ->where('partnerrefno', $refid)
                ->andWhere('partnerid', $partner->id)
                ->andWhere('status', "NOT IN", [Redemption::STATUS_FAILED, Redemption::STATUS_PROCESSDELIVERY, Redemption::STATUS_CANCELLED])
                ->one();
            // only can edit with not completed, failed, process_delivery ( with failed attempts )

            if (!$redemption){
                throw \Snap\api\exception\RedemptionReversalError::fromTransaction([], ['message' => 'Invalid Redemption Record.']);;
            }else{
                // check branch process, branch can completed
                if ($redemption->type != Redemption::TYPE_BRANCH && $redemption->status == Redemption::STATUS_COMPLETED){
                    throw \Snap\api\exception\RedemptionReversalError::fromTransaction([], ['message' => 'Unable to proceed redemption reversal. Status is not applicable to perform this action.']);
                }

                // check logistic
                $redemptionLogistic = $this->app->logisticStore()->searchTable()->select()
                    ->where('type', Logistic::TYPE_REDEMPTION)
                    ->andWhere('typeid', $redemption->id)
                    ->one();
                if ($redemptionLogistic){
                    throw \Snap\api\exception\RedemptionReversalError::fromTransaction([], ['message' => 'Unable to proceed redemption reversal. Status is not applicable to perform this action.']);
                }

                $now = new \DateTime(gmdate('Y-m-d\TH:i:s'));
                $recordTime = new \DateTime($redemption->createdon->format('Y-m-d H:i:s'));
                $recordTime->modify("+ 1 hours");
                if ($now >= $recordTime){
                    throw \Snap\api\exception\RedemptionReversalError::fromTransaction([], ['message' => 'Unable to proceed redemption reversal. Transaction time exceed to perform this action.']);
                }
            }

            $sap_response = $this->app->apiManager()->sapReverseRedemption($redemption);
            if (!$this->sapReturnVerify($sap_response)){
                $this->log("Reverse Redemption - data{$redemption->id}, sap{$sap_response} - error on sap_response :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_ERROR);
                throw \Snap\api\exception\RedemptionReversalError::fromTransaction([], ['message' => 'Unable to proceed redemption reversal. SAP error.']);
            }

            $redemption->status = Redemption::STATUS_REVERSED;

            $updateRedemption = $this->app->redemptionStore()->save($redemption);
            $this->app->getDbHandle()->commit();

            $observation = new \Snap\IObservation(
                $updateRedemption, 
                \Snap\IObservation::ACTION_REVERSE, 
                $updateRedemption->status, 
                ['redemptionType' => $redemption->type]);
            $this->notify($observation);

            if (!$ownsTransaction) {
                $this->app->getDBHandle()->beginTransaction();
            }

        }catch(\Exception $e){
            if($ownsTransaction) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }

        return $updateRedemption;
    }

    /**
     * @param   obj       $redemption         redemption data
     * @param   string    $branchId           branchId data
     * @param   obj       $partner            partner data
     * @return  data
     **/
    // extension of doRemdemption() based on redemption type -- start
    private function onBranchRedeem($redemption, $partner, $branchId){
        // onBranch-redeem -- ONLY
        
        //$sap_response = $this->app->apiManager()->sapRedemptionBranch($redemption); // pending on request format
        $sap_response = $this->app->apiManager()->sapReserveSerialNumber($redemption); // pending on request format
        if (!$this->sapReturnVerify($sap_response)){  
            $this->log("Redemption - onBranchRedeem - data{$redemption}, sap{$sap_response} - error on sap_response :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_ERROR);
            throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => 'Unable to proceed redemption.']);
        }
        $serialItems = json_encode($this->formatSapItemToGtpItem($sap_response));  // entire response from SAP, itemized from SAP

        // formatted data from SAP 
        $redemption->items = $serialItems;
        $redemption->status = Redemption::STATUS_SUCCESS;

        $redemption = $this->app->redemptionStore()->save($redemption);

        return $redemption;
    }
    /**
     * @param   obj     $redemption             redemption data
     * @param   obj     $deliveryInfo           delivery data
     * @param   obj     $partner                partner data
     * @return  data    
     **/
    private function onDeliveryRedeem($redemption, $partner, $deliveryInfo = null, $scheduleInfo = null, $submitSAP = true){
        // delivery , special delivery
        // scheduleinfo for appointment

        // raw object fro gtp_api_processor
        if (in_array($redemption->type, [Redemption::TYPE_DELIVERY, Redemption::TYPE_SPECIALDELIVERY])){

            if ($redemption->type == Redemption::TYPE_DELIVERY){
                $maxinumGramageForDelivery = 500;
                if ($redemption->totalweight > $maxinumGramageForDelivery){
                    throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => "Delivery Redemption weight more than 500 Gram."]);
                }
            }

            $redemption->deliverycontactname1 = $deliveryInfo->contactname1;
            $redemption->deliverycontactname2 = $deliveryInfo->contactname2;
            $redemption->deliverycontactno1 = $deliveryInfo->contact_mobile1;
            $redemption->deliverycontactno2 = $deliveryInfo->contact_mobile2;
            $redemption->deliveryaddress1 = $deliveryInfo->address1;
            $redemption->deliveryaddress2 = $deliveryInfo->address2;
            $redemption->deliverycity = $deliveryInfo->city;
            $redemption->deliverypostcode = $deliveryInfo->postcode;
            $redemption->deliverystate = $deliveryInfo->state;
            $redemption->deliverycountry = $deliveryInfo->country;

        }else if (Redemption::TYPE_APPOINTMENT == $redemption->type){

            $scheduleBranchInfo = $partner->getBranch($scheduleInfo->branch_id);
            if (!$scheduleBranchInfo){
                throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => 'Unable to proceed redemption. Invalid Branch ID']);
            }
            $redemption->deliveryaddress1 = $scheduleBranchInfo->name;
            $redemption->deliveryaddress2 = $scheduleBranchInfo->address;
            $redemption->deliverypostcode = $scheduleBranchInfo->postcode;

            $scheduleDateTime = new \DateTime($scheduleInfo->datetime);
            $scheduleDateTime = common::convertUserDatetimeToUTC($scheduleDateTime);
            $redemption->appointmentbranchid = $scheduleInfo->branch_id;
            $redemption->appointmentdatetime = $scheduleDateTime->format('Y-m-d H:i:s');

            $redemption->appointmenton = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'), $this->app->getUserTimeZone());
            $redemption->appointmentby = defined('SNAPAPP_DBACTION_USERID') ? SNAPAPP_DBACTION_USERID : $this->app->getUsersession()->getUser()->id;

        }else{

            $this->log("Redemption - onDeliveryRedeem - data{$redemption} - invalid TYPE :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_ERROR);
            throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => 'Unable to proceed redemption. Invalid Type']);

        }

        if ($submitSAP) {
            $sap_response = $this->app->apiManager()->sapRedemptionDelivery($redemption); // pending on request format
            if (!$this->sapReturnVerify($sap_response)){  
                $this->log("Redemption - onDeliveryRedeem - data{$redemption}, sap{$sap_response} - error on sap_response :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_ERROR);
                throw \Snap\api\exception\RedemptionError::fromTransaction([], ['message' => 'Unable to proceed redemption.']);;
            }
            $serialItems = json_encode($this->formatSapItemToGtpItem($sap_response)); // entire response from SAP, itemized from SAP

            // formatted data from SAP 
            $redemption->items = $serialItems; 
        }


        $redemption = $this->app->redemptionStore()->save($redemption);
        
        return $redemption;
    }
    // entension of do Redemption() -- end
    
    // bg ----------------------

    // func ----------------------
    /**
     * @param  arrobj      $items                   request item data _raw
     * @param  string      $redemptionType          type of redemption
     **/
    private function getRedemptionFee($items, $redemptionType){
        // will update on reporting, placing order will not know fees from partner(MBB), but can get the fee list

        $redemptionFee = '';
        $insuranceFee = '';
        $handlingFee = '';

        if ($redemptionType == ""){
            
        }
        
        return $fee;
    }



    // logistic event
    private function updateRedemptionLogisticStatus($redemption, $logistic){
        try{
            $this->app->getDbHandle()->beginTransaction();

            // if delivered . call sap 
            if (Logistic::STATUS_DELIVERED == $logistic->status){
                $redemption->status = Redemption::STATUS_COMPLETED;
                $this->app->redemptionStore()->save($redemption);
            }
            if (Logistic::STATUS_PROCESSING == $logistic->status || Logistic::STATUS_PENDING == $logistic->status){
                $redemption->status = Redemption::STATUS_PROCESSDELIVERY;
                $this->app->redemptionStore()->save($redemption);
            }
            if (Logistic::STATUS_COMPLETED == $logistic->status){
                $redemption->status = Redemption::STATUS_COMPLETED;
                $this->app->redemptionStore()->save($redemption);
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
    private function failedLogistic($redemption, $logistic){
        try{
            $this->app->getDbHandle()->beginTransaction();

            if (Logistic::STATUS_FAILED == $logistic->status){

                // pending sapRdemptionData format
                $sapRedemptionData = (object) [ 
                    "redemption_code" => $redemption->sapredemptioncode,
                    "items" => $redemption->items
                ]; 
                $sap_response = $this->app->apiManager()->sapRedemption($sapRedemptionData);
                if (!$sap_response['error']){
                    $redemption->status = Redemption::STATUS_FAILEDDELIVERY;
                    $this->app->redemptionStore()->save($redemption);
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

    // NOTE -- 16-06-2020 -- SAP redemption number is by item, GTP is by (request), SAP item redemption_ref will be `generateRedemptionNo+{'-'}+{item}`
    public function generateRedemptionNo($cacher, $partner, $refid)
    {
        $bsn_partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
        $moduleString = 'Redemption';
        $generateStore = $this->app->redemptionStore();
        if(!$bsn_partnerid){
            $generateNoPrefix = 'E';
        }else{
            $generateNoPrefix = 'BSN';
        }
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

    private function sapReturnVerify($sap_response){
        // very array data;
        $this->log("Redemption - sapReturnVerify - verify sap return on item status :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);
        if ($sap_response && 'N' == $sap_response[0]['success']){
            return false;
        }
        if (!$sap_response){
            return false;
        }
        foreach ($sap_response as $sap_data){
            if ('N' == $sap_data['success']){
                return false;
            }
        }
        return true;
    }

    private function formatSapItemToGtpItem($sap_response){
        // itemCode
        // serialNum
        // quantity
        $this->log("Redemption - formatSapItemToGtpItem - format sap_response to GTP data :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);
        $items = [];
        foreach ($sap_response as $sap_item){
            $item['sapreturnid'] = $sap_item['id'];
            $item['code'] = $this->getFieldFromSapItemCode($sap_item['itemCode'], 'code');
            $item['serialnumber'] = $sap_item['serialNum'];
            $item['weight'] = $this->getFieldFromSapItemCode($sap_item['itemCode'], 'weight');
            $item['sapreverseno'] = $sap_item['data1'];
            array_push($items, $item);
        }
        return $items;
    }
    private function getFieldFromSapItemCode($sapItemCode, $column){
        $product = $this->app->productStore()->searchTable()->select()->where('sapitemcode', $sapItemCode)->one();
        return $product->$column;
    }


    //tochecksuccessredeematGTPlevel
    public function checkRedemptionMBB($date,$partner){
        $redemption = $this->app->redemptionStore()->searchTable()->select()
                ->where('type', 'IN' ,[Redemption::TYPE_SPECIALDELIVERY,Redemption::TYPE_DELIVERY])
                ->andWhere('partnerid', $partner->id)
                ->andWhere('status', 1)
                ->execute();
        if(count($redemption) > 0){
            foreach($redemption as $aRedemption){
                $items = json_decode($aRedemption->items,true);
                foreach($items as $aItem){
                    $redemptionArray[$aItem['code']][] = array(
                        'weight' => $aItem['weight'],
                        'serialnumber' => $aItem['serialnumber']
                    );
                }

            }
        }

        foreach($redemptionArray as $key=>$aRedeem){
            foreach($aRedeem as $value){
                $totalWeight[$key]+=$value['weight'];
                $serialnumber[$key][] = $value['serialnumber'];
            }
        }

        $allRedeem = array(
            'totalWeight' => $totalWeight,
            'serialnumber' => $serialnumber
        );
        
        return $allRedeem;
    }

    public function processReservedRedemptions($partnerId, $start = null, $end = null){
        if (!$partnerId){
            return false;
        }
        // $start $end MUST be string in UTC time
        if ($start && $end){
            $redemptions = $this->app->redemptionStore()->searchTable()->select()
                ->where('status', Redemption::STATUS_SUCCESS)
                ->andWhere('type', Redemption::TYPE_BRANCH)
                ->andWhere('partnerid', $partnerId)
                ->andWhere('createdon', '>=', $start)
                ->andWhere('createdon', '<=', $end)
                ->execute();
        }else{
            $redemptions = $this->app->redemptionStore()->searchTable()->select()
                ->where('status', Redemption::STATUS_SUCCESS)
                ->andWhere('type', Redemption::TYPE_BRANCH)
                ->andWhere('partnerid', $partnerId)
                ->execute();
        }

        if ($redemptions){
            foreach($redemptions as $redemption) {
                try {

                    $return = $this->app->apiManager()->sapRedemptionBranch($redemption);
                    if (!$this->sapReturnVerify($return)){  
                        $this->log("Redemption - data{$redemption}, sap{$return} - error on sap_response :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_ERROR);
                        throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Unable to proceed order.']);
                    }else{
                        $redemption->status = Redemption::STATUS_COMPLETED;
                        $updateRedemption = $this->app->redemptionStore()->save($redemption);
                        //debug redemption status
                        $message = "update redemption : {$updateRedemption->id} | {$updateRedemption->partnerrefno} | {$updateRedemption->redemptionno}, status: {$updateRedemption->status}";
                        $this->log($message, SNAP_LOG_DEBUG);
                        //debug zzredemption record
                        $dbHandle = $this->app->getDbHandle();
                        $sql = "SELECT rdm_auditkey, rdm_actiontimestamp, rdm_status FROM zzredemption WHERE rdm_id = '{$updateRedemption->id}'";
                        $statement = $dbHandle->query($sql);
                        $row = $statement->fetchAll(\PDO::FETCH_ASSOC);
                        $message = "zzredemption record  : {$updateRedemption->id} | {$updateRedemption->partnerrefno} | {$updateRedemption->redemptionno}, data: " . json_encode($row);
                        $this->log($message, SNAP_LOG_DEBUG);
                    }

                } catch(\Exception $e) {
                    $this->log("Error automatic processing of pending redemption {$redemption->redemptionno} with error " . $e->getMessage(), SNAP_LOG_ERROR);
                }
            }
        }
        return true;
    }

    public function processUnreservedMinted($partnerId, $redemptionno){
        if (!$partnerId){
            return false;
        }

        $redemption = $this->app->redemptionStore()->searchTable()->select()
                    ->where('partnerid', $partnerId)
                    ->andWhere('redemptionno', $redemptionno)
                    ->andWhere('status', Redemption::STATUS_SUCCESS)
                    ->one();

        /*start*/
        if($redemption){
            try{
                $return = $this->app->apiManager()->sapUnreserveSerialNumber($redemption);
                if (!$this->sapReturnVerify($return)){  
                    $this->log("Redemption - data{$redemption->redemptionno}, sap{$return} - error on sap_response :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_ERROR);
                    throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Unable to proceed with unreserve minted.']);
                }else{
                    $redemption->status = Redemption::STATUS_CANCELLED;
                    $redemption->remarks .= '.Unreserve Minted';
                    $updateRedemption = $this->app->redemptionStore()->save($redemption);
                }
            } catch(\Exception $e) {
                $this->log("Error automatic processing of unreserve minted {$redemption->redemptionno} with error " . $e->getMessage(), SNAP_LOG_ERROR);
            }
        } else {
            $this->log("No data for {$redemptionno} was found to proceed with unreserve." . $e->getMessage(), SNAP_LOG_ERROR);
        }

        return true;
    }
    
    public function registerRedemptionFromDb ($partner, $arrRedemption, $partnerName)
    {
        $count = $this->app->redemptionStore()
                    ->searchTable()
                    ->select(['id'])
                    ->where('partnerid', $partner->id)
                    ->andWhere('partnerrefno', $arrRedemption['partnerrefno'])
                    ->count();
        if($count) {
            throw \Snap\api\exception\RefDuplicatedException::fromTransaction($arrRedemption, ['partnerrefno' => $arrRedemption['partnerrefno'], 'code' => $partner->code, 'action' => 'redemption']);
        }
        
        $arrRedemption['partnerrefno'] .= '_' . $partnerName;
        $arrRedemption['redemptionno'] .= '_' . $partnerName;
        $arrRedemption['partnerid'] = $partner->id;
        $arrRedemption = $this->app->redemptionStore()->create($arrRedemption);
        $redemptionSaved = $this->app->redemptionStore()->save($arrRedemption);
        
        return $redemptionSaved;
    }

    //print convert receipt
    public function printConversionOTCBSN($refno, $customerId = null)
    {
        
        try {
            
            if ($this->app->getUsersession()->getUser()->type != 'Customer'){
                $redemption = $this->app->redemptionStore()->getByField('partnerrefno', $refno);
            }else{
                $partnerId = $this->app->getUsersession()->getUser()->partnerid;
                $redemption = $this->app->redemptionStore()->searchTable()->select()
                    ->where('partnerrefno', $refno)
                    ->andWhere('partnerid', $partnerId)
                    ->one();
            }
            if (!$redemption){
                return false;
            }


            $finalAcePriceTitle = "REDEMPTION";

            $weight = number_format($redemption->totalweight,3);
            $totalEstValue = number_format(($redemption->redemptionfee + $redemption->insurancefee + $redemption->handlingfee),2);
            $insuransfee = number_format($redemption->insurancefee,2);
            $redemptionfee = number_format($redemption->redemptionfee,2);
            $totalredemptionfee = number_format(($redemption->redemptionfee + $redemption->insurancefee),2);;
            $handlingfee = number_format($redemption->handlingfee,2);
            $orderFee = number_format($redemption->specialdeliveryfee,2);

            // Get customer name
            $customerId = $redemption->partnerid;
            $userobj = $this->app->partnerStore()->getById($customerId);
            $customername = $userobj->name;

            // Get customer info and bank account info 
            
            $goldTx = $this->app->myconversionStore()->searchView()->select()
            ->where('refno', $redemption->partnerrefno)
            ->one();

            if(ctype_alpha(substr($refno,-1))){
                $refnoAmmend = substr($refno,0,-1);
            }

            $arrproduct = array();
            $redemptionQuantity = $this->app->myconversionStore()->searchView()->select() //3
                    ->where('refno', 'like' ,"%".$refnoAmmend."%")
                    ->execute();

            $product = $this->app->productStore()->searchTable()->select()
                    ->execute();

            foreach($redemptionQuantity as $redemptionQty){
                foreach($product as $item){
                    if($redemptionQty->productid == $item->id){
                        $arrproduct[$item->code] += $redemptionQty->rdmtotalquantity;
                    }
                }
            }

            $totalredemptionfee = number_format((($totalredemptionfee + $goldTx->handlingfee) * count($redemptionQuantity)),2);
            $handlingfee = number_format(($goldTx->courierfee * count($redemptionQuantity)) , 2);

            if($goldTx){
                $accountholdercode = $goldTx -> accountholdercode;
                $fullname = $goldTx -> accountholdername;
            }

            $address = $goldTx->rdmdeliveryaddress.' '.$goldTx->rdmdeliverypostcode.' '.$goldTx->rdmdeliverystate;

            $accountholder = $this->app->myaccountholderStore()->searchView()->select()
            ->where('accountholdercode', $accountholdercode)
            ->one();

            // $product = $this->app->productStore()->getById($order->productid);

            // Get salesperson name
            if ($redemption->salespersonid && $redemption->salespersonid != 0){
                $salesperson = $this->app->userStore()->getById($redemption->salespersonid);
                $salespersonname = $salesperson->name;
            }else{
                $salespersonname = '-';
            }

            $data = [
                'date'                 => $redemption->createdon->format('Y-m-d H:i:s'),
                'partner_name'         => $customername,
                'fullname'             => $fullname,
                'finalAcePriceTitle'   => $finalAcePriceTitle,
                'accountholdercode'    => $accountholdercode,
                'xau'   	 	       => $weight,
                'transactionid'        => $redemption->partnerrefno,
                'teller'               => $salespersonname,
                'final_total'          => $totalEstValue,
                'receipt_no'           => $redemption->partnerrefno,
                'casa_bankaccount'     => $accountholder->accountnumber,
                'making_charges'       => $insuransfee,
                'delivery_fee'         => $handlingfee,
                'redemption_fee'       => $redemptionfee,
                'total_redemption_fee' => $totalredemptionfee,
                'address'              => $address,
                'mykadno'              => $accountholder->mykadno,
                'contact_no'           => $redemption->deliverycontactno1,
                'arrProduct'           => $arrproduct,
                'totalTransaction'     => count($redemptionQuantity),
            ];

            return($data);
        
        } catch (\Exception $e) {
           
            throw $e;
        }
        //return $returnPdf;
    }

    public function printConversionOTC($refno, $customerId = null)
    {
        
        try {
            
            if ($this->app->getUsersession()->getUser()->type != 'Customer'){
                $redemption = $this->app->redemptionStore()->getByField('partnerrefno', $refno);
            }else{
                $partnerId = $this->app->getUsersession()->getUser()->partnerid;

                // $partnerId = $partnerId ? $partnerId : $this->app->getConfig()->{'otc.alrajhi.partner.id'};

                $redemption = $this->app->redemptionStore()->searchTable()->select()
                    ->where('partnerrefno', $refno)
                    ->andWhere('partnerid', $partnerId)
                    ->one();
            }
            if (!$redemption){
                return false;
            }

            $redemptionView = $this->app->redemptionStore()->searchView()->select()
                    ->where('id', $redemption->id)
                    ->one();


            $finalAcePriceTitle = "REDEMPTION";

            $weight = number_format($redemption->totalweight,6,'.','');
            $totalEstValue = number_format(($redemption->redemptionfee + $redemption->insurancefee + $redemption->handlingfee),2,'.','');
            $insuransfee = number_format($redemption->insurancefee,2,'.','');
            $redemptionfee = number_format($redemption->redemptionfee,2,'.','');
            $handlingfee = number_format($redemption->handlingfee,2,'.','');
            $orderFee = number_format($redemption->specialdeliveryfee,2);
            
            // Get customer name
            $customerId = $redemption->partnerid;
            $userobj = $this->app->partnerStore()->getById($customerId);
            $customername = $userobj->name;
            
            // Get customer info and bank account info 
            
            $goldTx = $this->app->myconversionStore()->searchView()->select()
            ->where('refno', $redemption->partnerrefno)
            ->one();
            
            $refnoAmmend = $refno;
            if(ctype_alpha(substr($refno,-1))){
                $refnoAmmend = substr($refno,0,-1);
                
                $redemptionQuantity = $this->app->myconversionStore()->searchView()->select() //3
                ->where('refno', 'like' ,"%".$refnoAmmend."%")
                ->andWhere('rdmpartnerid', $redemption->partnerid)
                ->execute();
            }else{
                $redemptionQuantity = $this->app->myconversionStore()->searchView()->select() //3
                ->where('refno', 'like' ,"%".$refnoAmmend."%")
                ->andWhere('rdmpartnerid', $redemption->partnerid)
                ->one();
                
                
            }

            $totalredemptionfee = number_format(($redemption->redemptionfee + $redemption->insurancefee + $goldTx->handlingfee),2,'.','');
            
            $totalEstValue += number_format($goldTx->servicefee);

            $arrproduct = array();

            $product = $this->app->productStore()->searchTable()->select()
                    ->execute();

            if(is_array($redemptionQuantity)){
                $productStore = $this->app->productStore()->searchTable()->select()
                    ->where('id', $redemptionQuantity[0]->productid)
                    ->one();
            }
            else{
                $productStore = $this->app->productStore()->searchTable()->select()
                    ->where('id', $redemptionQuantity->productid)
                    ->one();
            } 

            $productCode = $productStore->code;

            // for alrajhi only
            

            if(ctype_alpha(substr($refno,-1))){

                foreach($redemptionQuantity as $redemptionQty){
                    foreach($product as $item){
                        if($redemptionQty->productid == $item->id){
                            $arrproduct[$item->code] += $redemptionQty->rdmtotalquantity;
                        }
                    }
                }

            }else{

                $arrproduct[$productCode] = $redemptionQuantity->rdmtotalquantity;
                
            }

            if($goldTx){
                $accountholdercode = $goldTx -> accountholdercode;
                $fullname = $goldTx -> accountholdername;
                $nok_mykadno = $goldTx -> achnokmykadno;
            }

            $address = $goldTx->rdmdeliveryaddress1.' '.$goldTx->rdmdeliveryaddress2.' '.$goldTx->rdmdeliverycity.' '.$goldTx->rdmdeliverypostcode.' '.$goldTx->rdmdeliverystate;

            $accountholder = $this->app->myaccountholderStore()->searchView()->select()
                ->where('accountholdercode', $accountholdercode)
                ->one();

            // checker detail
            $goldTxTable = $this->app->myconversionStore()->searchTable()->select()
                ->where('refno', $redemption->partnerrefno)
                ->one();

            $user = $this->app->userStore()->searchTable()->select()
            ->where('username', $goldTxTable->checker)
            ->one();

            $checkername = $goldTxTable->checker;
            $checkerId = $user->id;

            // $product = $this->app->productStore()->getById($order->productid);

            // Get salesperson name
            if ($redemption->salespersonid && $redemption->salespersonid != 0){
                $salesperson = $this->app->userStore()->getById($redemption->salespersonid);
                $salespersonname = $salesperson->name;
            }else{
                $salespersonname = '-';
            }

            $casa = BaseCasa::getInstance($this->app->getConfig()->{'otc.casa.api'});

			if($accountholder->accounttype){
				if($accountholder->accounttype == 22){
					$params['searchFlag'] = 1;
					$params['keyword'] = $accountholder->partnercusid;
					$jointAccount = $casa->getCustomerInfo($params);
				}
			}

			if($jointAccount){
				$primaryfullname = $jointAccount['data']['joinaccount'][0]['Heading1'];
			}

            $data = [
                'date'                 => $redemption->createdon->format('Y-m-d H:i:s'),
                'partner_name'         => $customername,
                'fullname'             => $fullname,
                'finalAcePriceTitle'   => $finalAcePriceTitle,
                'accountholdercode'    => $accountholdercode,
                'xau'   	 	       => $weight,
                'transactionid'        => $redemption->partnerrefno,
                'teller'               => $salespersonname,
                'final_total'          => $totalEstValue,
                'receipt_no'           => $redemption->partnerrefno,
                'casa_bankaccount'     => $accountholder->accountnumber,
                'making_charges'       => $insuransfee,
                'delivery_fee'         => $redemptionQuantity[0]->courierfee,
                'redemption_fee'       => $redemptionfee,
                'total_redemption_fee' => $totalredemptionfee,
                'address'              => $address,
                'mykadno'              => $accountholder->mykadno,
                'contact_no'           => $redemption->deliverycontactno1,
                'arrProduct'           => $arrproduct,
                'totalTransaction'     => is_array($redemptionQuantity)? count((array)$redemptionQuantity) : 1,
                'nok_mykadno'          => $nok_mykadno,
                'nok_fullname'         => $accountholder->nokfullname,
                'productCode'          => $productStore->weight,
                'accounttype'          => $accountholder->getAccountTypeString(),
                'checkername'          => $checkername,
                'checkerId'            => $checkerId,
                'tellerId'             => $redemptionView->createdby,
                'tellername'           => $redemptionView->createdbyname,
                'primaryfullname'      => $primaryfullname,
                'realproductcode'      => $productCode
            ];

            return($data);
        
        } catch (\Exception $e) {
           
            throw $e;
        }
        //return $returnPdf;
    }

    public function registerRedemptionFromSvr_ ($partner, $arrRedemption, $partnerName)	
    {	
        $count = $this->app->redemptionStore()	
                    ->searchTable()	
                    ->select(['id'])	
                    ->where('partnerid', $partner->id)	
                    ->andWhere('partnerrefno', $arrRedemption['partnerrefno'])	
                    ->count();	
        if($count) {	
            throw \Snap\api\exception\RefDuplicatedException::fromTransaction($arrRedemption, ['partnerrefno' => $arrRedemption['partnerrefno'], 'code' => $partner->code, 'action' => 'redemption']);	
        }	
        	
        $schedule_date = new \DateTime("now", new \DateTimeZone("UTC") );	
        $schedule_date->setTimeZone(new \DateTimeZone('Asia/Kuala_Lumpur'));	
        $triggerOn =  $schedule_date->format('Y-m-d H:i:s');	
        $arrRedemption['partnerrefno'] .= '_' . $partnerName;	
        //$arrRedemption['redemptionno'] .= '_' . $partnerName; // as projects placed outside gtp server will change the prefix, this can be omitted. also can be used to refer back data as reference to the data in other server	
        $arrRedemption['partnerid'] = $partner->id;	
        // $arrRedemption['modifiedon'] = $triggerOn;	
        // $arrRedemption['createdon'] = $triggerOn;	
        // unset($arrRedemption['modifiedon']);	
        // unset($arrRedemption['createdon']);	
        $arrRedemption = $this->app->redemptionStore()->create($arrRedemption);	
        	
        $redemptionSaved = $this->app->redemptionStore()->save($arrRedemption);	
        	
        return $redemptionSaved;	
    }

    public function registerRedemptionFromSvr ($partner, $arrRedemption, $partnerName)
    {
        $count = $this->app->redemptionStore()
                    ->searchTable()
                    ->select(['id'])
                    ->where('partnerid', $partner->id)
                    ->andWhere('redemptionno', $arrRedemption['redemptionno'])
                    ->count();
        if($count) {
            throw \Snap\api\exception\RefDuplicatedException::fromTransaction($arrRedemption, ['partnerrefno' => $arrRedemption['partnerrefno'], 'code' => $partner->code, 'action' => 'redemption']);
        }
        
        $schedule_date = new \DateTime("now", new \DateTimeZone("UTC") );
        $schedule_date->setTimeZone(new \DateTimeZone('Asia/Kuala_Lumpur'));
        $triggerOn =  $schedule_date->format('Y-m-d H:i:s');
        $arrRedemption['partnerrefno'] .= '_' . $partnerName;
        // $arrRedemption['redemptionno'] .= '_' . $partnerName; // as projects placed outside gtp server will change the prefix, this can be omitted. also can be used to refer back data as reference to the data in other server
        $arrRedemption['partnerid'] = $partner->id;
        $arrRedemption['modifiedon'] = $triggerOn;
        $arrRedemption['createdon'] = $triggerOn;
        
        $arrRedemption = $this->app->redemptionStore()->create($arrRedemption);
        
        $redemptionSaved = $this->app->redemptionStore()->save($arrRedemption);
        
        return $redemptionSaved;
    }
}
?>
