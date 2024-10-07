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
Use \Snap\object\VaultItem;
Use \Snap\object\VaultLocation;
Use \Snap\object\VaultItemTrans;
Use \Snap\object\VaultItemTransItem;
Use \Snap\object\Partner;
Use \Snap\object\Product;
Use \Snap\object\Order;
Use \Snap\object\Redemption;
Use \Snap\object\MyLedger;


// partnerid is assigned by SAP
// partnerid always (true)
// allocated (boolean)
// allocated 0 = unallocate
// allocated 1 = allocated
// move_to_vault_item_location_id = vault_location_id
// move_request_on = request to move
// move_complated_on = complete movement of item 
// returnedon = after return and/or complate movement of item TO ace
// --bank return = deallocated
// --return to sap = return
// --bank return physical = deallocated and move to ACE
// ->deallocated = _CHANGE allocated (0), without movement
// ->movement = _CHANGE allocated (0/1), _CHANGE move_requested_on _PENDING_row
// ->return = _HAS allocated (0), movement

class BankVaultManager implements IObservable, IObserver
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;

    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
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

    /**
     * Method to be called by SAP when indicating new serial numbers has been imported
     * @param  Partner $partner         Partner object
     * @param  Product $product         Related product
     * @param  Array   $serialItemArray List of serial numbers to be added. serial number object
     */
    public function onSapNotifyReceiveNewSerial(Partner $partner, $serialItemArray)
    {
        // note: only for initial serial import, no other data involved according to up-to-date flow on 12-MAY-2020

        // sap spec: [
        //     {
        //         "id": 0,
        //         "itemCode": "GS-999-9-1000g",
        //         "serialNum": "GEX-ASSAYRF-290224",
        //         "whsCode": "MIB_RSV",
        //         "bankId": null,
        //         "customerId": "MIB",
        //         "createdDate": "2020-05-07 00:00:00"
        //     }
        // ]
        
        try{
            $this->log("into BankVaultManager::onSapNotifyReceiveNewSerial({$partner->code}, {$serialItemArray}) method", SNAP_LOG_DEBUG);
            $dgvShareKilobar = $this->app->getConfig()->{"gtp.go.partner.id"}; //get GOGOLD/GOPAYZ id as it use for common warehouse sharedgv
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();
            $returnObj = [];
            foreach ($serialItemArray as $x => $serialItem){

                $productCodeSAP = $this->app->productStore()->searchTable()->select()
                    ->where('sapitemcode', $serialItem->itemCode)
                    ->one();
                if (!$productCodeSAP){
                    $this->log("VaultItems onSapNotifyReceiveNewSerial item {$serialItem} , product does not exist, on onSapNotifyReceiveNewSerial()", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=> "Item code $productCodeSAP invalid."]);
                }
                
                $getSerial = $this->app->vaultItemStore()->searchTable()->select()
                    ->where('serialno', $serialItem->serialNum)
                    ->andWhere('productid', $productCodeSAP->id)
                    // ->andWhere('status', '<>', VaultItem::STATUS_ACTIVE)
                    ->one();
                if ($getSerial){
                    if (in_array($getSerial->status, [VaultItem::STATUS_ACTIVE, VaultItem::STATUS_TRANSFERRING,  VaultItem::STATUS_PENDING_ALLOCATION])){
                        $this->log("VaultItems onSapNotifyReceiveNewSerial item {$serialItem} , Serial Number exists, status is not applicable, on onSapNotifyReceiveNewSerial()", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=> "Serial no. {$getSerial->serialno} already in system."]);
                    }
                }

                if ($getSerial){
                    $item = $getSerial;
                }else{
                    $item = $this->app->vaultItemStore()->create();
                }
                
                // $warehouseSAP = $this->app->vaultlocationStore()->searchTable()->select()
                //     ->where('sapwhscode', $getSerial->whsCode)
                //     ->one();
                // if (!$warehouseSAP){
                //     $this->log("VaultItems onSapNotifyReceiveNewSerial item {$serialItem} , Warehouse Code does not exist, on onSapNotifyReceiveNewSerial()", SNAP_LOG_ERROR);
                //     throw new \Exception('SAP error, warehouse code undefined');
                // }
                // $item->locationid = $warehouseSAP->id;

                $item->productid = $productCodeSAP->id;
                $item->weight = $productCodeSAP->weight;
                $item->partnerid = ($dgvShareKilobar != $partner->id ? $partner->id : 0); //partnerid = 0 when received customerId = GOGOLD/GOPAYZ as it is for common warehouse 
                $item->serialno = $serialItem->serialNum; 
                // $item->vaultlocationid = $serialItem->locationid;  
                $item->allocated = 0;
                $item->allocatedon = null;
                $item->status = VaultItem::STATUS_ACTIVE;

                if($dgvShareKilobar == $partner->id) $shareIsTrue = 1;
                $item->sharedgv = $shareIsTrue; //sharedgv is TRUE/1 if received customerId = GOGOLD/GOPAYZ as it is for common warehouse

                $update = $this->app->vaultitemStore()->save($item);
                if(!$update){
                    throw new \Exception('Internal Error. Unable to proceed update.');
                }else{
                    $returnObj[$x] = $item;
                }
            }

            foreach ($returnObj as $x => $notifyItem){
                $observation = new \Snap\IObservation($notifyItem, \Snap\IObservation::ACTION_NEW, [], []);
                $this->notify($observation);
            }
            $this->app->getDbHandle()->commit();
            $return = $returnObj;
            $this->log("VaultItems onSapNotifyReceiveNewSerial items {$returnObj} , has been successfully created. on onSapNotifyReceiveNewSerial()", SNAP_LOG_DEBUG);

            
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem {$serialItemArray} not successful , {$e->getMessage()} on onSapNotifyReceiveNewSerial()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
        return $return;
    }

    /**
     * Method to be called by SAP when the physical item is made available already.
     * @param  Partner $partner      Partner object
     * @param  Array   $serialsArray List of serial numbers to be added.
     */
    public function onSapNotifyItemAvailable(Partner $partner, $serialItemArray)
    {
        $this->log("into BankVaultManager::onSapNotifyItemAvailable({$partner->code}, {$serialItemArray}) method", SNAP_LOG_DEBUG);

        // _ON item recieved GRN from SAP
        // SAP_ structure - action: success/failed based on return object item
        try{
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();

            $returnObj = [];
            $oldItems = [];
            foreach ($serialItemArray as $x => $serialItem){
                $dgvShareKilobar = $this->app->getConfig()->{"gtp.go.partner.id"}; //get GOGOLD/GOPAYZ id as it use for common warehouse sharedgv
                $partnerId = ($dgvShareKilobar != $partner->id ? $partner->id : 0); 

                $item = $this->app->vaultItemStore()->searchTable()->select()
                    ->where('serialno', $serialItem->serialNum)
                    ->andWhere('partnerid', $partnerId)
                    // ->andWhere('status', VaultItem::STATUS_ACTIVE)
                    ->one();
                if (!$item){
                    $this->log("VaultItem serialno {$serialItem->serialNum} , Serial Number does not exist. on onSapNotifyItemAvailable()", SNAP_LOG_ERROR);
                    throw new \Exception('Serial number does not exist');
                }
                // -- new for UPDATE DO_Number
                // if (!in_array($item->status, [VaultItem::STATUS_PENDING, VaultItem::STATUS_ACTIVE, VaultItem::STATUS_PENDING_ALLOCATION])){
                //     $this->log("VaultItem serialno {$serialItem->serialNum} , Serial Number exists, status is not applicable, on onSapNotifyItemAvailable()", SNAP_LOG_ERROR);
                //     throw new \Exception('Serial number status is not applicable');
                // }
                // -- new for UPDATE DO_Number

                $oldItems[$x] = $item;

                $defaultLocation = $this->getDefaultLocation();
                if (!$defaultLocation){
                    $this->log("onSapNotifyItemAvailable({$item->id}):  Unable to proceed to update due to default location", SNAP_LOG_ERROR);
                    // throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Unable to proceed updates.']);
                    throw new \Exception('Internal Error. Unable to proceed update.');
                }

                // -- new for UPDATE DO_Number
                if (!$item->status){
                    $item->status = vaultItem::STATUS_ACTIVE;
                }
                if (!$item->vaultlocationid){
                    $item->vaultlocationid = $defaultLocation->id;
                }
                $item->deliveryordernumber = $serialItem->DoDocNum;
                // -- new for UPDATE DO_Number

                $update = $this->app->vaultItemStore()->save($item);
                if(!$update){
                    throw new \Exception('Internal Error. Unable to proceed updates.');
                }else{
                    $returnObj[$x] = $item;
                }
            }

            foreach ($returnObj as $x => $notifyItem){
                $observation = new \Snap\IObservation($notifyItem, \Snap\IObservation::ACTION_EDIT, $oldItems[$x]->status, []);
                $this->notify($observation);
            }
            $this->app->getDbHandle()->commit();
            $return = $returnObj;
            $this->log("VaultItems onSapNotifyItemAvailable items {$returnObj} , has been successfully updated. on onSapNotifyItemAvailable()", SNAP_LOG_DEBUG);
            
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem {$serialItemArray} not successful , {$e->getMessage()} on requestItemSerial()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
        return $return;
    }

    /**
     * This is the function that will be used to request for one or many serial for bars to return
     * @param  Partner   $partner    Partner object
     * @param  Product   $product    Product to get serials for
     * @param  number    $quantity   number of serials to request
     * @param  string    $reference  Client provided reference 
     * @param  datetime  $timeStamp  timsestamp from client
     * @param  string    $apiVersion 
     * @return Array of vaultItems representing the serials to return.
     */
    public function requestItemSerial(Partner $partner, Product $product, $quantity, $reference, $timeStamp, $apiVersion = '')
    {
        $this->log("into BankVaultManager::requestItemSerial({$partner->code}, {$product->code}, $quantity, $reference, $timeStamp, $apiVersion) method", SNAP_LOG_DEBUG);
        
        $lockKey = '{requestItemSerial}:' . $partner->code;
        $cacher = $this->app->getCacher();
        $cacher->waitForLock($lockKey, 1, 60, 60);
        
        try{
            
            if (!$this->hasPermission($apiVersion)){
                $this->log("Error in vaultitem invalid action , unathorised , requestItemSerial()", SNAP_LOG_ERROR);
                throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=>'Invalid action.']);
            }

            // $this->mbbSerialRequestRiskAccessment($partner);

            $vaultItems = $this->getUnallocatedVaultItems($partner, $product, $quantity);
            if (!$vaultItems){
                $this->log("Error in vaultitem items requested not available , requestItemSerial()", SNAP_LOG_ERROR);
                throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=>'Item not available.']);
            }

            if ($vaultItems && $vaultItems->available < $quantity){
                // return serial based on current availability? or refuse
                $this->log("Requested quantity less than available quantity, action none, requestItemSerial()", SNAP_LOG_DEBUG);
                throw \Snap\api\exception\VaultItemAvailability::fromTransaction($vaultItems, ['available' => $vaultItems->available]);
            }

            $registerAllocated = $this->allocateVaultItems($vaultItems->items, $partner->id);
            if (!$registerAllocated['success']){
                $this->log("Error in vaultitem {$vaultItems} not successful , {$registerAllocated['error']['message']} on requestItemSerial()", SNAP_LOG_ERROR);
                throw new \Exception($registerAllocated['error']['message']);
            }
            $return = $registerAllocated['items'];

            foreach ($return as $x => $notifyItem){
                $observation = new \Snap\IObservation($notifyItem, \Snap\IObservation::ACTION_ASSIGN, [], []);
                $this->notify($observation);
            }
            $cacher->unlock($lockKey);

        }catch(\Exception $e){
            $cacher->unlock($lockKey);
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , requestItemSerial()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }

        return $return;
        
    }

    /**
     * Request a move of item to a new location
     * @param  VaultItem[]        $items 
     * @param  VaultLocation      $newLocation
     */
    public function requestMoveItemToLocation($items, VaultLocation $newLocation, $direct_confirm = false, $confirmation = false, $doc_date = null)
    {
        $this->log("into BankVaultManager::requestMoveItemToLocation({$items}, {$newLocation->id}) method", SNAP_LOG_DEBUG);

        try{

            
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();
            
            $returnObj = [];
            foreach ($items as $x => $item){
                
                $state = $this->getStateMachine($item);
                // if (!$state->can('transfer') || ! $item->canRequestMoveToLocation($newLocation)){
                //     throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=> "Invalid action for {$item->serialno}."]);
                // }
                /* if ($item->partnerid != $newLocation->partnerid){
                    $this->log("Error in vaultitem {$item->id} vaultitem_partnerid {$item->partnerid} is not same with vaultlocation_partnerid {$newLocation->partnerid} , requestMoveItemToLocation()", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=>'Invalid merchant id on both item and location.']);
                } */

                $this->log("Requesting vaultitemid = {$item->id} from= {$item->movetovaultlocationid} to= {$newLocation->id} , on requestMoveItemToLocation()", SNAP_LOG_DEBUG);

                $item->movetovaultlocationid = $newLocation->id;
                $item->moverequestedon = $now->format('Y-m-d H:i:s');
                $item->status = VaultItem::STATUS_TRANSFERRING;

                $update = $this->app->vaultitemStore()->save($item);
                if(!$update){
                    $this->log("Error in vaultitemid {$item->id} hit error , can not update on requestMoveItemToLocation()", SNAP_LOG_ERROR);
                    throw new \Exception('Unable to update');
                }else{
                    $returnObj[$x] = $item;
                }

                // transaction variable
                $fromlocationid = $item->vaultlocationid;
                $tolocationid = $newLocation->id;
                $partnerid = $item->partnerid;
            }

            // if not direct confirm will add transaction here because theres a add transaction in markitemarried()
            if ($confirmation && !$direct_confirm){
                $type = 'TRANSFER';
                $documentDate = $doc_date;
                // if (!$doc_date){
                //     throw new \Exception('Invalid request. No doc date.');
                // }
                $trans = $this->addTransaction($items, $fromlocationid, $tolocationid, $partnerid, $type, $documentDate);
                if ($trans){
                    $action = 'request';
                    // $currentUser = $this->app->getUserSession()->getUser()->id;
                    // $trans->transferrequestby = $currentUser;
                    $this->requestConfirmationTransfer($trans, $action, $checkPreset = false);
                }
            }

            foreach ($returnObj as $x => $notifyItem){
                $observation = new \Snap\IObservation($notifyItem, \Snap\IObservation::ACTION_ASSIGN, [], []);
                $this->notify($observation);
            }
            $this->app->getDbHandle()->commit();
            $return = $returnObj;
            $this->log("VaultItems requestMoveItemToLocation(items) {$returnObj} , has been successfully updated.  Items = {$returnObj}", SNAP_LOG_DEBUG);

        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , requestMoveItemToLocation()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
        return $return;
    }

     /**
     * Request a move of item to a new location for bmmb
     * @param  VaultItem[]        $items 
     * @param  VaultLocation      $newLocation
     */
    public function requestMoveItemToLocationBmmb($items, VaultLocation $newLocation)
    {
        $this->log("into BankVaultManager::requestMoveItemToLocation({$items}, {$newLocation->id}) method", SNAP_LOG_DEBUG);

        try{

            
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();
            
            $returnObj = [];
            $oldStatusses = [];
            $counter = 0;
            $prev_status = null;
            $prev_oldLocation = null;

            // Serial No Init
            $serialNoLine = [];

            foreach ($items as $item){
                
                $state = $this->getStateMachine($item);
                /*if (!$state->can('transfer') || ! $item->canRequestMoveToLocation($newLocation)){
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=> "Invalid action for {$item->serialno}."]);
                }*/
                /* if ($item->partnerid != $newLocation->partnerid){
                    $this->log("Error in vaultitem {$item->id} vaultitem_partnerid {$item->partnerid} is not same with vaultlocation_partnerid {$newLocation->partnerid} , requestMoveItemToLocation()", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=>'Invalid merchant id on both item and location.']);
                } */
                $oldLocation = $this->app->vaultlocationStore()->getById($item->vaultlocationid);

                $newLocation = $this->app->vaultlocationStore()->getById($newLocation->id);

                $this->log("Requesting sp = {$item->id} from= {$item->movetovaultlocationid} to= {$newLocation->id} , on requestMoveItemToLocation()", SNAP_LOG_DEBUG);

                $item->movetovaultlocationid = $newLocation->id;
                $item->moverequestedon = $now->format('Y-m-d H:i:s');
                $oldStatusses[$counter] = $item->status;
                $item->status = VaultItem::STATUS_TRANSFERRING;

                $update = $this->app->vaultitemStore()->save($item);
                if(!$update){
                    $this->log("Error in vaultitemid {$item->id} hit error , can not update on requestMoveItemToLocation()", SNAP_LOG_ERROR);
                    throw new \Exception('Unable to update');
                }else{
                    $returnObj[$counter] = $item;
                }

                // Compare old status with current status
                // Check if all status are consistent
                // Skip first check when prev_status is null
                if ($prev_status != null){
                    if ($oldStatusses[$counter] != $prev_status){
                        // Item is different, throw exception here
                        throw new \Exception('Selected serial numbers to transfer are not identical in status');
                    }  
                }

                // Compare previous old location with current old location 
                // Check if all status are consistent
                // Skip first check when prev_status is null
                if ($prev_oldLocation != null){
                    if ($oldLocation->name != $prev_oldLocation){
                        // Item is different, throw exception here
                        throw new \Exception('Selected serial numbers to transfer are not identical in vault location');
                    }  
                }
               
                $prev_status = $oldStatusses[$counter];
                $prev_oldLocation = $oldLocation->name;
                // Create Serial Number Template
                $serialNoLine[] = $item->serialno;
               

                $counter++;
                
                
            }


            // After check if all statusses are identical, send email
            $this->notify(new IObservation($items[0], IObservation::ACTION_OTHER, $prev_status, [
                'serialno'        => implode(", ", $serialNoLine),
                'vaultfrom'       => $prev_oldLocation,
                'vaultto'         => $newLocation->name,
            ]));

            $this->app->getDbHandle()->commit();
            $return = $returnObj;
            $this->log("VaultItems requestMoveItemToLocation(items) {$returnObj} , has been successfully updated.  Items = {$returnObj}", SNAP_LOG_DEBUG);

        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , requestMoveItemToLocation()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
        return $return;
    }

    /**
     * Finalise and indicate that the gold has been transferred to the new location indicated
     * @param  VaultItem[] $items 
     */
    public function markItemsArrivedAtLocation($items, $documentDate = null, $confirmation = false)
    {
        $this->log("into BankVaultManager::markItemsArrivedAtLocation({$items}) method", SNAP_LOG_DEBUG);

        
        try{
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();
            
            $returnObj = [];
            $prevVaultIds = [];
            foreach ($items as $x => $item){
                // new direct confirm, no more pending extra clicking
                $fromVaultLocationId = $item->vaultlocationid; // previous location id
                $toVaultLocationId = $item->movetovaultlocationid; // new location id
                $partnerid = $item->partnerid; // new location id

                $state = $this->getStateMachine($item);
                if (!$state->getCurrentState()->has('transferring')){
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=>'Invalid action.']);
                }
                if(0 == $item->vaultlocationid) {
                    //throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=>'Physical Item for ' . $item->serialno . ' not arrived yet']);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=>'Serial Number  ' . $item->serialno . ' doesn’t have correspond Delivery Order, it will be on Transferring mode status']);
                }
                $prevVaultIds[$x] = $item->vaultlocationid;
                $item->vaultlocationid = $item->movetovaultlocationid;
                $item->movetovaultlocationid = 0;
                $item->movecompletedon = $now->format('Y-m-d H:i:s');
                $item->status = VaultItem::STATUS_ACTIVE;

                $update = $this->app->vaultitemStore()->save($item);

                if(!$update){
                    $this->log("Error in vaultitem $item hit error , can not update on markItemsArrivedAtLocation()", SNAP_LOG_ERROR);
                    throw new \Exception('Unable to update');
                }else{
                    $returnObj[$x] = $item;
                }
            }

            // add in vault item transaction
            if (!$confirmation){
                $this->addTransaction($items, $fromVaultLocationId, $toVaultLocationId, $partnerid, $type = 'TRANSFER', $documentDate, $confirmation);
            }

            $this->log("VaultItems markItemsArrivedAtLocation(items) $returnObj[$x] , has been successfully updated.  Items = {$returnObj[$x]}", SNAP_LOG_DEBUG);
            
            foreach ($returnObj as $x => $notifyItem){
                $observation = new \Snap\IObservation($notifyItem, \Snap\IObservation::ACTION_ASSIGN, [], ['previousVaultLocationId' => $prevVaultIds[$x]]);
                $this->notify($observation);
            }
            
            $this->app->getDbHandle()->commit();
            $return = $returnObj;
            
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , markItemsArrivedAtLocation()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
        return $return;
    }

         /**
     * Finalise and indicate that the gold has been transferred to the new location indicated
     * with additional status change from allocated -> unallocated
     * @param  VaultItem[] $items 
     */
    public function markItemsArrivedAtLocationDeallocated($items)
    {
        $this->log("into BankVaultManager::markItemsArrivedAtLocation({$items}) method", SNAP_LOG_DEBUG);

        
        try{
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();
            
            $returnObj = [];
            $prevVaultIds = [];
            foreach ($items as $x => $item){
                $state = $this->getStateMachine($item);
                if (!$state->getCurrentState()->has('transferring')){
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=>'Invalid action.']);
                }
                if(0 == $item->vaultlocationid) {
                    //throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=>'Physical Item for ' . $item->serialno . ' not arrived yet']);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message'=>'Serial Number  ' . $item->serialno . ' doesn’t have correspond Delivery Order, it will be on Transferring mode status']);
                }
                $prevVaultIds[$x] = $item->vaultlocationid;
                $item->vaultlocationid = $item->movetovaultlocationid;
                $item->movetovaultlocationid = 0;
                $item->allocated = 0;
                $item->allocatedon = '0000-00-00 00:00:00'; // PENDING remove?
                $item->movecompletedon = $now->format('Y-m-d H:i:s');
                $item->status = VaultItem::STATUS_ACTIVE;

                $update = $this->app->vaultitemStore()->save($item);
                if(!$update){
                    $this->log("Error in vaultitem $item hit error , can not update on markItemsArrivedAtLocation()", SNAP_LOG_ERROR);
                    throw new \Exception('Unable to update');
                }else{
                    $returnObj[$x] = $item;
                }
            }

            $this->log("VaultItems markItemsArrivedAtLocation(items) $returnObj[$x] , has been successfully updated.  Items = {$returnObj[$x]}", SNAP_LOG_DEBUG);
            
            foreach ($returnObj as $x => $notifyItem){
                $observation = new \Snap\IObservation($notifyItem, \Snap\IObservation::ACTION_ASSIGN, [], ['previousVaultLocationId' => $prevVaultIds[$x]]);
                $this->notify($observation);
            }
            
            $this->app->getDbHandle()->commit();
            $return = $returnObj;
            
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , markItemsArrivedAtLocation()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
        return $return;
    }

    /**
     * Mark item as deallocated status
     * @param  VaultItem[]        $items 
     * @param  VaultLocation      $newLocation
     */
    public function markItemDeallocated(Partner $partner, $serialNoArray = array())
    {
        $this->log("into BankVaultManager::markItemDeallocated({$partner->code}, {$serialNoArray}) method", SNAP_LOG_DEBUG);

        // mbb_api_ serial_number FORMAT = 'GOLDBRAND-000001'
        // first is brand_name and second is serial

        try{
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();

            $returnObj = [];
            foreach ($serialNoArray as $x => $serialNo){

                $item = $this->getSerialNo($serialNo, $partner);               
                // $state = $this->getStateMachine($item);
                // if (!$state->can('return')){
                //     $this->log("markItemDeallocated({$item->id}):  Unable to proceed to confirm due to status", SNAP_LOG_ERROR);
                //     throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Item Status not available']);
                // }
                if (!$item){
                    $this->log("Error in vaultitem $serialNo hit error , could not find serial no. on markItemDeallocated()", SNAP_LOG_ERROR);
                    throw new \Exception('Could not find serial no. '.$serialNo);
                }
                if (!$item->allocated || $item->allocated == 0){
                    $this->log("Error in vaultitem $item hit error , deallocation mismatch on markItemDeallocated()", SNAP_LOG_ERROR);
                    throw new \Exception('Item is not allocated. '.$item->id);
                }
                if ($item->status == VaultItem::STATUS_INACTIVE) {
                    $this->log("Error in vaultitem $item hit error , item status mismatch on markItemDeallocated()", SNAP_LOG_ERROR);
                    throw new \Exception('Item is not active. '.$item->id);
                }

                $item->partnerid = 0;
                $item->allocated = 0;
                $item->allocatedon = '0000-00-00 00:00:00'; // PENDING remove?

                $update = $this->app->vaultitemStore()->save($item);
                if(!$update){
                    $this->log("Error in vaultitem $serialNo hit error , can not update on markItemDeallocated()", SNAP_LOG_ERROR);
                    throw new \Exception('Unable to update Serial No.');
                }else{
                    $returnObj[$x] = $item;
                }
            }

            $this->log("VaultItems markItemDeallocated(items) $returnObj[$x] , has been successfully updated.  Items = {$returnObj[$x]}", SNAP_LOG_DEBUG);
            
            foreach ($returnObj as $x => $notifyItem){
                $observation = new \Snap\IObservation($notifyItem, \Snap\IObservation::ACTION_ASSIGN, [], []);
                $this->notify($observation);
            }
            
            $this->app->getDbHandle()->commit();
            $return = $returnObj;
            
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , markItemDeallocated()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
        return $return;
    }

    /**
     * Mark the items as to be returned to HQ.
     * @param  VaultItem[]        $items
     */
    public function markItemReturned($vaultItems)
    {
        $this->log("into BankVaultManager::markItemReturned({$vaultItems}) method", SNAP_LOG_DEBUG);

        try{
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();

            $returnObj = [];
            $prevVaultIds = [];
            $prevVaultStatus = [];
            $serialNoLine = [];
            foreach ($vaultItems as $x => $item){
                $state = $this->getStateMachine($item);
                // if (!$state->can("return")){
                if (!$state->getCurrentState()->has('transferring')){
                    $this->log("markItemReturned({$item->id}):  Unable to proceed to confirm due to status", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Item Status not available']);
                }

                $defaultLocation = $this->getDefaultLocation();
                if (!$defaultLocation){
                    $this->log("markItemReturned({$item->id}):  Unable to proceed to update due to default location", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Unable to proceed updates.']);
                }

                if ($item->movetovaultlocationid != $defaultLocation->id){
                    $this->log("markItemReturned({$item->id}):  Unable to proceed to update due to moving location and final location", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Unable to proceed updates.']);
                }

                // If all location same
                $oldLocation = $this->app->vaultlocationStore()->getById($item->vaultlocationid);

                $prevVaultIds[$x] = $item->vaultlocationid;
                $prevVaultStatus[$x] = $item->status;
                $item->allocated = 0;
                // $item->vaultlocationid = $item->movetovaultlocationid;
                $item->vaultlocationid = $defaultLocation->id;
                $item->movetovaultlocationid = 0;
                $item->moverequestedon = '0000-00-00 00:00:00';
                // $item->movecompletedon = $now->format('Y-m-d H:i:s'); // updated on previous process

                $item->status = VaultItem::STATUS_INACTIVE;
                $item->returnedon = $now->format('Y-m-d H:i:s');
                
                $sap_response = $this->app->apiManager()->sapReturnKilobar($item);
                if (!$this->sapReturnVerify($sap_response)){
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'SAP return failed. Unable to proceed with kilobar return.']);
                }

                // Check all returnobj if its matching
                $serialNoLine[] = $item->serialno;

                // Compare old status with current status
                // Check if all status are consistent
                // Skip first check when prev_status is null
                if ($x > 0){
                    if ($prevVaultIds[$x] != $prevVaultIds[$x - 1]){
                        // Item is different, throw exception here
                        throw new \Exception('Selected serial numbers to transfer are not identical in status');
                    }  
                }
                // Compare previous old location with current old location 
                // Check if all status are consistent
                // Skip first check when prev_status is null
                if ($x > 0){
                    if ($prevVaultStatus[$x] != $prevVaultStatus[$x - 1]){
                        // Item is different, throw exception here
                        throw new \Exception('Selected serial numbers to transfer are not identical in vault location');
                    }  
                }
                
                $prev_status = $prevVaultStatus[$x];
                $prev_oldLocation = $oldLocation->name;

                $update = $this->app->vaultitemStore()->save($item);
                if(!$update){
                    $this->log("Error in vaultitem {$item->id} hit error , can not update on markItemReturned()", SNAP_LOG_ERROR);
                    throw new \Exception('Unable to update item.');
                }else{
                    $returnObj[$x] = $item;
                }
            }
                    
            $this->log("VaultItems return items $returnObj , has been successfully updated.  Items = {$returnObj}", SNAP_LOG_DEBUG);
            
            /*
            foreach ($returnObj as $x => $notifyItem){
                $observation = new \Snap\IObservation($notifyItem, \Snap\IObservation::ACTION_ASSIGN, [], ['previousVaultLocationId' => $prevVaultIds[$x]]);
                $this->notify($observation);
            }*/
            
            // After check if all statusses are identical, send email
            $observation = new \Snap\IObservation($vaultItems[0], \Snap\IObservation::ACTION_OTHER, $prev_status, [
                'previousVaultLocationId' => $prevVaultIds,   
                'serialno'        => implode(", ", $serialNoLine),
                'vaultfrom'       => $prev_oldLocation,
            ]);
            $this->notify($observation);
            
            
            $this->app->getDbHandle()->commit();
            $return = $returnObj;
            
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , markItemReturned()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
        return $return;
    }

        /**
     * Mark the items at partner , deallocate and to be returned to HQ in 1 process.
     * ONLY FOR MBB
     * @param  VaultItem[]        $items
     * 
     * please select $vaultItems from HANDLER and CHECK IF ITS SAME PARTNER
     */
    public function mark_Item_Deallocated_And_Returned_Directly($vaultItems, $force = false)
    {
        $this->log("into BankVaultManager::markItemReturned({$vaultItems}) method", SNAP_LOG_DEBUG);

        try{
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();

            $returnObj = [];
            $prevVaultIds = [];
            $prevVaultStatus = [];
            $serialNoLine = [];

            $mbbPartnerId = $this->app->getConfig()->{"gtp.mib.partner.id"};
            foreach ($vaultItems as $item){
                if ($item->partnerid != $mbbPartnerId){
                    if ($force == false){
                        throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Must partner(MBB) vault items only']);
                    }
                }
                $item_location = $this->app->vaultlocationStore()->getById($item->vaultlocationid);
                if ($item_location->type != VaultLocation::TYPE_END){
                    if ($force == false){
                        throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Vault item must be at partner location']);
                    }
                }
            }
            foreach ($vaultItems as $x => $item){

                $defaultLocation = $this->getDefaultLocation();
                if (!$defaultLocation){
                    $this->log("markItemReturned({$item->id}):  Unable to proceed to update due to default location", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Unable to proceed updates.']);
                }

                // transaction variable
                $fromVaultLocationId = $item->vaultlocationid;
                $toVaultLocationId = $defaultLocation->id;
                $partnerid = $item->partnerid;

                // If all location same
                $oldLocation = $this->app->vaultlocationStore()->getById($item->vaultlocationid);

                $item->allocated = 0;
                $item->vaultlocationid = $defaultLocation->id;
                $item->movetovaultlocationid = 0;
                // $item->moverequestedon = '0000-00-00 00:00:00';
                $item->movecompletedon = $now->format('Y-m-d H:i:s');
                $item->status = VaultItem::STATUS_INACTIVE;
                $item->returnedon = $now->format('Y-m-d H:i:s');
                
                $sap_response = $this->app->apiManager()->sapReturnKilobar($item);
                if (!$this->sapReturnVerify($sap_response)){
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'SAP return failed. Unable to proceed with kilobar return.']);
                }

                $update = $this->app->vaultitemStore()->save($item);
                if(!$update){
                    $this->log("Error in vaultitem {$item->id} hit error , can not update on markItemReturned()", SNAP_LOG_ERROR);
                    throw new \Exception('Unable to update item.');
                }else{
                    $returnObj[$x] = $item;
                }
            }

            // add in vault item transaction
            $vaultItemTrans = $this->addTransaction($vaultItems, $fromVaultLocationId, $toVaultLocationId, $partnerid, $type = 'RETURN');
                    
            $this->log("VaultItems return items $returnObj , has been successfully updated.  Items = {$returnObj}", SNAP_LOG_DEBUG);
            
            
            $this->app->getDbHandle()->commit();
            $return = $returnObj;

            
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , markItemReturned()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
        return $return;
    }

    
    /**
     * Mark the items at partner , deallocate and to be returned to HQ in 1 process.
     * ONLY FOR MBB
     * @param  VaultItem[]        $items
     * 
     * please select $vaultItems from HANDLER and CHECK IF ITS SAME PARTNER,
     * EXCEPT WITHOUT ADDTRANSACTION()
     */
    public function mark_Item_Deallocated_And_Returned_Directly_Without_Transaction($vaultItems, $force = false)
    {
        $this->log("into BankVaultManager::markItemReturned({$vaultItems}) method", SNAP_LOG_DEBUG);

        try{
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();

            $returnObj = [];
            $prevVaultIds = [];
            $prevVaultStatus = [];
            $serialNoLine = [];

            $mbbPartnerId = $this->app->getConfig()->{"gtp.mib.partner.id"};
            foreach ($vaultItems as $item){
                if ($item->partnerid != $mbbPartnerId){
                    if ($force == false){
                        throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Must partner(MBB) vault items only']);
                    }
                }
                $item_location = $this->app->vaultlocationStore()->getById($item->vaultlocationid);
                if ($item_location->type != VaultLocation::TYPE_END){
                    if ($force == false){
                        throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Vault item must be at partner location']);
                    }
                }
            }
            foreach ($vaultItems as $x => $item){

                $defaultLocation = $this->getDefaultLocation();
                if (!$defaultLocation){
                    $this->log("markItemReturned({$item->id}):  Unable to proceed to update due to default location", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Unable to proceed updates.']);
                }

                // transaction variable
                $fromVaultLocationId = $item->vaultlocationid;
                $toVaultLocationId = $defaultLocation->id;
                $partnerid = $item->partnerid;

                // If all location same
                $oldLocation = $this->app->vaultlocationStore()->getById($item->vaultlocationid);

                $item->allocated = 0;

                // check if there were location previously, if no, do not assign location
                if($fromVaultLocationId){
                    $item->vaultlocationid = $defaultLocation->id;
                }

                $item->movetovaultlocationid = 0;
                // $item->moverequestedon = '0000-00-00 00:00:00';
                $item->movecompletedon = $now->format('Y-m-d H:i:s');
                $item->status = VaultItem::STATUS_INACTIVE;
                $item->returnedon = $now->format('Y-m-d H:i:s');
                
                $sap_response = $this->app->apiManager()->sapReturnKilobar($item);
                if (!$this->sapReturnVerify($sap_response)){
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'SAP return failed. Unable to proceed with kilobar return.']);
                }

                $update = $this->app->vaultitemStore()->save($item);
                if(!$update){
                    $this->log("Error in vaultitem {$item->id} hit error , can not update on markItemReturned()", SNAP_LOG_ERROR);
                    throw new \Exception('Unable to update item.');
                }else{
                    $returnObj[$x] = $item;
                }
            }

            // add in vault item transaction
            // $vaultItemTrans = $this->addTransaction($vaultItems, $fromVaultLocationId, $toVaultLocationId, $partnerid, $type = 'RETURN');
                    
            $this->log("VaultItems return items $returnObj , has been successfully updated.  Items = {$returnObj}", SNAP_LOG_DEBUG);
            
            
            $this->app->getDbHandle()->commit();
            $return = $returnObj;

            
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , markItemReturned()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
        return $return;
    }

    public function cancelMoveRequest($items) {        
        foreach ($items as $x => $item){
                
            if(VaultItem::STATUS_TRANSFERRING == $item->status) {            
                $item->movetovaultlocationid = 0;
                $item->moverequestedon = '0000-00-00 00:00:00';
                $item->status = VaultItem::STATUS_ACTIVE;
                $this->app->vaultItemStore()->save($item);
                $observation = new \Snap\IObservation($notifyItem, \Snap\IObservation::ACTION_CANCEL, VaultItem::STATUS_TRANSFERRING, []);
                $this->notify($observation);
            }

        }
        
    }

    public function requestActivateItemForTransfer($items) {   
    
        $this->log("ItemList = {$items} on requestActivateItemForTransfer()", SNAP_LOG_DEBUG);

        try{

            $body = "<ul>";
            $this->app->getDbHandle()->beginTransaction();
            
            foreach ($items as $x => $item){
                
                if(VaultItem::STATUS_ACTIVE == $item->status) {            
                    $item->movetovaultlocationid = 0;
                    $item->moverequestedon = '0000-00-00 00:00:00';
                    $item->status = VaultItem::STATUS_PENDING_ALLOCATION;
                    $update = $this->app->vaultItemStore()->save($item);
                    if(!$update){
                        $this->log("Error in vaultitem $item hit error , can not update on requestActivateItemForTransfer()", SNAP_LOG_ERROR);
                        throw new \Exception('Unable to update');
                    }
                    
                    // Initialize header for mail
                    $serialNo = $item->serialno;
                    $body .= "<li> ".$serialNo ."</li>";
                      
                }
            }
            
            $this->app->getDbHandle()->commit();
            $return['success'] = true;
            $body .= "</ul>";
            $observation = new \Snap\IObservation($item, \Snap\IObservation::ACTION_OTHER, VaultItem::STATUS_ACTIVE, [
                'serialno'        => $body,
                'requestedby'     => $this->app->getUserSession()->getUser()->name,
            ]);
            $this->notify($observation);
            $this->log("Selected  {$items} , has been successfully requested for allocation approval.  Items = {$items}", SNAP_LOG_DEBUG);

        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , requestActivateItemForTransfer()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
    }

    public function approvePendingItemForTransfer($items) {   
    
        $this->log("ItemList = {$items} on approvePendingItemForTransfer()", SNAP_LOG_DEBUG);

        try{

            // Do checking before proceeding
            // Permission check if user has admin privileges 
            if (!$this->app->hasPermission('/root/bmmb/vault/approve')){
                $this->log("approvePendingItemForTransfer({$items}):  Unable to proceed to confirm due to not having administrative permission", SNAP_LOG_ERROR);
                throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => "Unable to process request due to not having the necessary permissions"]);
            }

            $body = "<ul>";
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();
            
            foreach ($items as $x => $item){
                // If user who requested transfer is same as the user for approval, reject request
                if ($this->app->getUserSession()->getUser()->id == $item->modifiedby){
                    $this->log("approvePendingItemForTransfer({$item->id} {$item->serialno}):  Unable to approve request as it is requested by the same user", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => "{$item->serialno} Item cannot be approved as it is requested by the same user"]);
                }

                if(VaultItem::STATUS_PENDING_ALLOCATION == $item->status) {    
                    $state = $this->getStateMachine($item);
                    if (!$state->can('allocate')){
                        $this->log("approvePendingItemForTransfer({$item->id} {$item->serialno}):  Unable to proceed to confirm due to status", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => "{$item->serialno} Item Status not available"]);
                    }
                    $item->allocated = 1;
                    $item->allocatedon = $now->format('Y-m-d H:i:s');

                    $item->movetovaultlocationid = 0;
                    $item->moverequestedon = '0000-00-00 00:00:00';
                    $item->status = VaultItem::STATUS_ACTIVE;
                    $update = $this->app->vaultItemStore()->save($item);
                    if(!$update){
                        $this->log("Error in vaultitem $item hit error , can not update on approvePendingItemForTransfer()", SNAP_LOG_ERROR);
                        throw new \Exception('Unable to update');
                    }
                    
                // Initialize header for mail
                    $serialNo = $item->serialno;
                    $body .= "<li> ".$serialNo ."</li>";
                      
                }
            }
            
            $this->app->getDbHandle()->commit();
            $return['success'] = true;
            $body .= "</ul>";
            $observation = new \Snap\IObservation($item, \Snap\IObservation::ACTION_OTHER, VaultItem::STATUS_PENDING_ALLOCATION, [
                'serialno'        => $body,
                'approvedby'     => $this->app->getUserSession()->getUser()->name,
            ]);
            $this->notify($observation);
            $this->log("Selected  {$items} , has been successfully approved allocation of items.  Items = {$items}", SNAP_LOG_DEBUG);

        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , approvePendingItemForTransfer()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }
        
    }

    private function hasPermission($apiVersion){
        if (preg_match('/[0-9\.]+m$/', $apiVersion) || preg_match('/[0-9\.]+b$/', $apiVersion)){
            return true;
        }
        return false;
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

    // get mbb gold volume used -- consignment
    private function mbbXauVolumeUtilization($partnerid){
        $getAllocatedItems = $this->app->vaultItemStore()->searchTable()->select()
            ->addFieldSum('weight', 'total_amount')
            ->where('partnerid', $partnerid)
            ->where('weight', 1000)
            ->andWhere('allocated', 1)
            ->first();

        $getConsignedVolumeToDate = $this->app->store()->end();

        $usage = $getAllocatedItems['total_amount'] / $getConsignedVolumeToDate;
        
        $usage -= 1;
        $usage *= 100;
        
        $return['consinged'] = $getAllocatedItems['total_amount'];
        $return['usage'] = $usage;

        return $return;
        // return volume based analytic;
    }

    // risk accessment of consignment  
    private function mbbSerialRequestRiskAccessment($partnerid, $req_quantity_in_weight){

        $volume = $this->mbbXauVolumeUtilization($partnerid);

        $current_ = $volume['consigned'] * (round( $volume['usage'] * 100 ));
        
        // return analysis;
    }

    // checking status - decision flag
    private function vaultItemRiskAccessment(){

        // return item status decision
        // etc locked, returnning some status
    }

    private function getUnallocatedVaultItems($partner, $product, $quantity = false){
        
        $items = $this->app->vaultItemStore()->searchTable()->select()
            ->where('allocated', 0)
            ->andWhere('partnerid', $partner->id)
            ->andWhere('productid', $product->id)
            // ->andWhere('sharedgv', false)
            ->andWhere('status', VaultItem::STATUS_ACTIVE)
            ->limit($quantity)
            ->execute();
       
        if (!$items){
            return false;
        }
        
        $x = 0;
        foreach ($items as $item) {
            $x++;
        }

        $return = (object) [
            'available' => $x,
            'items' => $items
        ];
        return $return;
    }

    private function allocateVaultItems($itemList, $partnerid){
        $this->log("ItemList = {$itemList} on allocatedVaultItems()", SNAP_LOG_DEBUG);

        try{
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();
            
            foreach ($itemList as $x=> $item){
                $state = $this->getStateMachine($item);
                if (!$state->can('allocate')){
                    $this->log("allocateVaultItems({$item->id} {$item->serialno}):  Unable to proceed to confirm due to status", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => "{$item->serialno} Item Status not available"]);
                }

                $item->partnerid = $partnerid;
                $item->allocated = 1;
                $item->allocatedon = $now->format('Y-m-d H:i:s');

                $update = $this->app->vaultItemStore()->save($item);
                if(!$update){
                    $this->log("Error in vaultitem {$item->id} hit error , can not update on allocateVaultItems()", SNAP_LOG_ERROR);
                    throw new \Exception('Unable to update');
                }
                $returnItems[$x] = $update;
            }
            
            $this->app->getDbHandle()->commit();
            $return['success'] = true;
            $return['items'] = $returnItems;
            $this->log("VaultItems return items {$itemList} , has been successfully updated.  Items = {$itemList}", SNAP_LOG_DEBUG);

        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem hit errors {$e->getMessage()} , allocateVaultItems()", SNAP_LOG_ERROR);
            // $return['error'] = [
            //     'message' => $e->getMessage(),
            // ];
            throw $e;
        }

        return $return;
    }

    private function getSerialNo($serialList, $partner){
        // mbb_api_ serial_number FORMAT = 'GOLDBRAND-000001'
        // first is brand_name and second is serial
        $this->log("Get single item from serial list, requestItemSerial()", SNAP_LOG_DEBUG);

        $serial = $this->app->vaultItemStore()->searchTable()->select()
            ->where('partnerid', $partner->id)
            ->andWhere('serialno', $serialList)
            ->one();

        if (!$serial){
            return false;
        }

        return $serial;
    }

    /* 
    sap query vault item in G4S and mbb racks with [serialnumber, location, status]
     */
    public function sapQueryVaultItems($partnerId, $apiVersion = null){
        $vaultItems = $this->app->vaultItemStore()->searchView()->select()
            ->where('partnerid', $partnerId)
            ->andWhere('status',VaultItem::STATUS_ACTIVE)
            ->orWhere('status',VaultItem::STATUS_TRANSFERRING)
            ->execute();
            // sapoutputstatus

        // return $vaultItems;
        $output = [];
        foreach ($vaultItems as $item){
            $arr['serialnumber'] = $item->serialno;
            $arr['denomination'] = $item->weight;
            $arr['location'] = $item->vaultlocationname; // code?
            $arr['status'] = $item->sapoutputstatus;
            $arr['deliveryordernumber'] = $item->deliveryordernumber;
            $output[] = $arr;
        }
        return $output;
    }

    private function sapReturnVerify($sap_response){
        // very array data;
        $this->log("Return Kilobar - sapReturnVerify - verify sap return on item status :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);
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

    public function addTransaction($vaultItems, $fromlocationid, $tolocationid, $partnerid, $type, $documentDate = null){
        try{

            // $this->app->getDbHandle()->beginTransaction();
            $returnObj = [];


            $create = $this->app->vaultitemtransStore()->create([
                'type' => $type,
                'partnerid' => $partnerid,
                'fromlocationid' => $fromlocationid,
                'tolocationid' => $tolocationid,
                'status' => VaultItemTrans::STATUS_ACTIVE,
            ]);

            if ($documentDate){
                $create->documentdateon = $documentDate;
            }else{
                $create->documentdateon = new \DateTime('now', $this->app->getUserTimezone);
            }

            $saveTrans = $this->app->vaultitemtransStore()->save($create);
            $trans_id = $saveTrans->id;

            foreach ($vaultItems as $vaultItem){
                $create_trans_item = $this->app->vaultitemtransitemStore()->create([
                    'vaultitemtransid' => $trans_id,
                    'vaultitemid' => $vaultItem->id,
                    'status' => VaultItemTransItem::STATUS_ACTIVE,
                ]);
                $this->app->vaultitemtransitemStore()->save($create_trans_item);
            }

            // $this->app->getDbHandle()->commit();
            $return = $saveTrans;
            
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem {$vaultItems} not successful , {$e->getMessage()} on addTransaction()", SNAP_LOG_ERROR);
            throw $e;
        }
        
        return $return;
       
    }

    public function voidTransaction($vaultItemTrans){
        try{

            $this->app->getDbHandle()->beginTransaction();

            if ($vaultItemTrans->status == VaultItemTrans::STATUS_CANCELLED){
                throw new \Exception('invalid status to proceed.');
            }
            // reverse process start
            $fromlocationid = $vaultItemTrans->fromlocationid;
            $tolocationid = $vaultItemTrans->tolocationid;
            $child = $vaultItemTrans->getChild();
            foreach ($child as $item){
                $check = $this->voidTransactionCheck($item, $vaultItemTrans->id);
                if ($check == false){
                    $processItem_id = $item->vaultitemid;
                    $vaultItem = $this->app->vaultitemStore()->getById($processItem_id);
                    throw new \Exception('Reversal failed, Item : '.$vaultItem->serialno.' has active transaction ahead. ');
                }

                $processItem_id = $item->vaultitemid;
                $processItem_vaultlocationid = $fromlocationid;

                // vaultitem data - start
                $vaultItem = $this->app->vaultitemStore()->getById($processItem_id);
                // reset
                $vaultItem->movetovaultlocationid = 0;
                $vaultItem->moverequestedon = '0000-00-00 00:00:00';
                $vaultItem->movecompletedon = '0000-00-00 00:00:00';
                $vaultItem->status = VaultItem::STATUS_ACTIVE;
                // revert
                $vaultItem->vaultlocationid = $processItem_vaultlocationid;
                $this->app->vaultitemStore()->save($vaultItem);
                // vaultitem data - end

                $item->status = VaultItemTransItem::STATUS_INACTIVE;
                $this->app->vaultitemtransitemStore()->save($item);
            }
            $vaultItemTrans->status = VaultItemTrans::STATUS_CANCELLED;
            $vaultItemTrans->cancelby = $this->app->getUserSession()->getUser()->id;
            $vaultItemTrans->cancelon = new \DateTime('now', $this->app->getUserTimezone());
            $return = $this->app->vaultitemtransStore()->save($vaultItemTrans);

            $this->app->getDbHandle()->commit();

        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem {$vaultItems} not successful , {$e->getMessage()} on addTransaction()", SNAP_LOG_ERROR);
            throw $e;
        }

        return $return;
    }

    public function voidTransactionCheck($vaultItemTransItem, $vaultTransId){
        // get latest trans item which included the $vaultitem
        $search = $this->app->vaultitemtransitemStore()->searchTable()->select()->where('vaultitemid', $vaultItemTransItem->vaultitemid)->orderBy('vaultitemtransid', 'desc')->one();
        if ($search->vaultitemtransid != $vaultTransId){
            // if latest vaultitem_transitem not the latest trans that included the item will be reject, only latest trans contained item will be proceed
            return false;
        }
        return true;

        // $searchesCount = $this->app->vaultitemtransitemStore()->searchTable()->select()->where('vaultitemid', $vaultItem->id)->count();
        // if ($searchesCount < 2){
        //     // skip search if no trans
        //     return true;
        // }
        // $searches = $this->app->vaultitemtransitemStore()->searchTable()->select()->where('vaultitemid', $vaultItem->id)->orderBy('id', 'desc')->execute();
        // $hasOldPending = false;
        // foreach ($searches as $search){
        //     $vaultTrans = $this->app->vaultitemtransStore()->searchTable()->select()->where('id', $search->vaultitemtransid);
        //     if ($vaultTrans->status == VaultItemTrans::STATUS_ACTIVE){
  
        //     }
        // }
    }

    public function requestConfirmationTransfer($vaultItemTrans, $action, $checkPreset = true){
        // will share on other options
        try{

            $type = VaultItemTrans::TYPE_TRANSFER_CONFIRMATION;

            $returnObj = [];

            $trans_id = $vaultItemTrans->id;

            // // locked for transfer request confirmation
            // foreach ($vaultItems as $vaultItem){
            //     $vaultItem->locked = 1;
            //     $this->app->vaultitemtransitemStore()->save($vaultItem);
            // }

            // $vaultItemTrans->type = VaultItemTrans::TYPE_TRANSFER_CONFIRMATION;
            $action_preset = $vaultItemTrans->getTransActions($checkPreset);
            if ($action_preset){
                $hasPermission = $this->requestConfirmationTransferController($action_preset, $action);
                if (!$hasPermission){
                    throw new \Exception("No permission on this action. Contact administrative.");
                }
            }

            $currentUser = $this->app->getUserSession()->getUser()->id;
            $time = new \DateTime('now', $this->app->getUserTimezone());
            if ($action == 'request'){
                $vaultItemTrans->type = VaultItemTrans::TYPE_TRANSFER_CONFIRMATION;;
                $vaultItemTrans->transferrequestby = $currentUser;
                $vaultItemTrans->transferrequeston = $time;
            }
            if ($action == 'confirm'){
                $vaultItemTrans->confirmrequestby = $currentUser;
                $vaultItemTrans->confirmrequeston = $time;
            }
            if ($action == 'complete'){
                $vaultItemTrans->type = VaultItemTrans::TYPE_TRANSFER;;
                $vaultItemTrans->completerequestby = $currentUser;
                $vaultItemTrans->completerequeston = $time;
                $completed = $this->markItemsArrivedAtLocation($items, $documentDate = null, $confirmation = true);
            }

            $saveTrans = $this->app->vaultItemTransStore()->save($vaultItemTrans);

            // $this->app->getDbHandle()->commit();
            $return = $saveTrans;
            
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("Error in vaultitem {$vaultItems} not successful , {$e->getMessage()} on addTransaction()", SNAP_LOG_ERROR);
            throw $e;
        }
        
        return $return;
    }

    public function requestConfirmationTransferController($action_preset, $action){
        // $action = [
        //     'request',
        //     'confirm',
        //     'complete'
        // ]
        // $action_preset = [
        //     'self_request',
        //     'opponent_confirm',
        //     'opponent_complete'
        // ];
        // $action_preset = [
        //     'self_request',
        //     'opponent_confirm',
        //     'self_complete'
        // ];

        $me = 'self'; // action owner;
        
        if ($action_preset[$me.'_'.$action]){
            return true;
        }
        
        return false;
    }


    /**
     * This method will return the order state machine to manage the different states of the order process.
     * 
     * @return Finite/StateMachine/StateMachine 
     */
    public function getStateMachine($vaultItem) {
        // $this->log("{$vaultItem->id} STATEMACHINE, VAULTITEM OBJ", SNAP_LOG_DEBUG);
        $stateMachine = new \Finite\StateMachine\StateMachine;
        $config       = [
            'property_path' => 'statestatus',
            'states' => [
                'pending'                   => [ 'type' => 'initial', 'properties' => [] ],
                'allocated_active'          => [ 'type' => 'normal', 'properties' => [] ],
                'allocated_transferring'    => [ 'type' => 'normal', 'properties' => ['transferring'=>true] ],
                'allocated_inactive'        => [ 'type' => 'normal', 'properties' => [] ],
                'unallocated_active'        => [ 'type' => 'normal', 'properties' => [] ],
                'unallocated_pending'       => [ 'type' => 'normal', 'properties' => [] ],
                'unallocated_transferring'  => [ 'type' => 'normal', 'properties' => ['transferring'=>true] ],
                'unallocated_inactive'      => [ 'type' => 'final', 'properties' => [] ],
            ],
            'transitions' => [
                'allocate' => [ 
                    'from' => [ 'pending', 'unallocated_active', 'unallocated_pending' ], 
                    'to' => 'allocated_active',
                ],
                'transfer' => [ 
                    'from' => [ 'pending', 'allocated_active', 'unallocated_active' ], 
                    'to' => 'allocated_inactive',
                ],
                'return' => [ 
                    'from' => [ 'pending', 'allocated_active' ], 
                    'to' => 'unallocated_active',
                ],
                'remove' => [ 
                    'from' => [ 'pending', 'unallocated_active' ], 
                    'to' => 'unallocated_inactive',
                ],
            ]
        ];
        $loader       = new \Finite\Loader\ArrayLoader($config);
        $loader->load($stateMachine);
        $stateMachine->setStateAccessor(new \Finite\State\Accessor\PropertyPathStateAccessor($config['property_path']));
        $stateMachine->setObject($vaultItem);
        $stateMachine->initialize();
        return $stateMachine;
    }

    /**
     * 
     * @param Partner             $partner      The partner
     * @param float               $grams        Xau amount available to sell | buy
     * 
     */
    public function updateSharedDGVPartnerBalance($partner, $grams, $orderType){
        // put on after commit
        $this->log(__METHOD__."(): update sharedgv partner usage start", SNAP_LOG_DEBUG);
        if (!$partner->sharedgv){
            return false;
        }

        $cacher = $this->app->getCacher();
        $shareDgvUsageKey = '{shareDgvUsage}:total';
        $shareDgvUsagePartnerKey = '{shareDgvUsage}:'.$partner->id;

        $shareDgvUsage = $cacher->get($shareDgvUsageKey);
        $shareDgvUsagePartner = $cacher->get($shareDgvUsagePartnerKey);
        
        if ($shareDgvUsage && $shareDgvUsagePartner){
            if ($orderType == Order::TYPE_COMPANYSELL){
                $shareDgvUsage = $cacher->increment($shareDgvUsageKey, $grams); // if conversion minus the shareDgvUsage _PENDING -> coz on other file, do on shared file
                $shareDgvUsagePartner = $cacher->increment($shareDgvUsagePartnerKey, $grams); // partner dgv, own dgv value
            }else if ($orderType == Order::TYPE_COMPANYBUY){
                $shareDgvUsage = $cacher->decrement($shareDgvUsageKey, $grams); // if conversion minus the shareDgvUsage _PENDING -> coz on other file, do on shared file
                $shareDgvUsagePartner = $cacher->decrement($shareDgvUsagePartnerKey, $grams); // partner dgv, own dgv value
            }else{
                throw new \Exception('Invalid Order Type to update Shared DGV Usage.');
            }
        }else{
            if(! $shareDgvUsage) {
                $this->log(__METHOD__."(): no cache found - shareDgvUsage", SNAP_LOG_DEBUG);
                // no cache found , set new one based on new calculation
                // $totalShareDgvUsage = all partner share dgv usage
    
                $partners = $this->app->partnerStore()->searchTable()->select()->where('sharedgv', true)->execute();
                foreach ($partners as $partner){
                    $sharedgvPartnerIds[] = $partner->id;
                }
    
                $partnersShareDgvUsage = $this->getCurrentDgvFromDb($sharedgvPartnerIds); //  including pending 
    
                $totalShareDgvAmount = $this->getSharedDgvAmount();
                if ($orderType == Order::TYPE_COMPANYBUY){
                    $shareDgvUsage = $partnersShareDgvUsage - $grams ;
                }else{
                    $shareDgvUsage = $partnersShareDgvUsage + $grams;
                }
    
                $cacher->set($shareDgvUsageKey, $shareDgvUsage);
            }
            if(! $shareDgvUsagePartner) {
                $this->log(__METHOD__."(): no cache found - shareDgvUsagePartner", SNAP_LOG_DEBUG);
                // no cache found , set new one based on new calculation
                // $totalShareDgvUsage = all partner share dgv usage
    
                $sharedgvPartnerId = [$partner->id]; // getCurrentDgvFunction
    
                $partnersShareDgvUsage = $this->getCurrentDgvFromDb($sharedgvPartnerId); //  including pending 
    
                $totalShareDgvAmount = $this->getSharedDgvAmount();
                if ($orderType == Order::TYPE_COMPANYBUY){
                    $totalShareDgvUsagePartner = $partnersShareDgvUsage - $grams;
                }else{
                    $totalShareDgvUsagePartner = $partnersShareDgvUsage + $grams;
                }
    
                $cacher->set($shareDgvUsagePartnerKey, $totalShareDgvUsagePartner);
            }
        }

        return $shareDgvUsage;
    }

    function getCurrentDgvFromDb($partnerIds = array(), $percentage = false){
        // get all from DB same like daily dgv report
        $this->log(__METHOD__."(): get sharedgv partner usage from db start", SNAP_LOG_DEBUG);
        try {

            $getLastDayOfPrevMonth = date("Y-n-j 15:59:59", strtotime("last day of previous month"));

            $totalSharedDgvAmount = $this->getSharedDgvAmount();

            $ledgerStore = $this->app->myledgerStore();
            $ledgerHdl = $ledgerStore->searchTable(false);
            $p = $ledgerStore->getColumnPrefix();

            $totalBuyTransaction = $ledgerHdl->select()
                                ->addFieldSum('credit', 'credit_xau')
                                ->where('accountholderid', 0)
                                ->where('type', MyLedger::TYPE_ACEBUY)
                                ->andWhere('partnerid', 'IN', $partnerIds)
                                ->andWhere('status', MyLedger::STATUS_ACTIVE)
                                ->first();
            $totalBuy = $totalBuyTransaction['credit_xau'];

            $totalSellTransaction = $ledgerHdl->select()
                                ->addFieldSum('debit', 'debit_xau')
                                ->where('accountholderid', 0)
                                ->where('type', MyLedger::TYPE_ACESELL)
                                ->andWhere('partnerid', 'IN', $partnerIds)
                                ->andWhere('status', MyLedger::STATUS_ACTIVE)
                                ->first();
            $totalSell = $totalSellTransaction['debit_xau'];

            $totalSellSPTransaction = $this->app->orderStore()->searchTable(false)->select()
                                    ->addFieldSum('xau', 'total_xau')
                                    ->where('partnerid', 'IN', $partnerIds)
                                    ->andWhere('type', Order::TYPE_COMPANYSELL)
                                    ->andWhere('remarks', 'LIKE', 'Special order%')
                                    ->andWhere('status','IN', [Order::STATUS_CONFIRMED,Order::STATUS_COMPLETED])
                                    ->first();
            $totalSellSP = $totalSellSPTransaction['total_xau'];

            $totalBuySPTransaction = $this->app->orderStore()->searchTable(false)->select()
                                    ->addFieldSum('xau', 'total_xau')
                                    ->where('partnerid', 'IN', $partnerIds)
                                    ->andWhere('type', Order::TYPE_COMPANYBUY)
                                    ->andWhere('remarks', 'LIKE', 'Special order%')
                                    ->andWhere('status','IN', [Order::STATUS_CONFIRMED,Order::STATUS_COMPLETED])
                                    ->first();
            $totalBuySP = $totalBuySPTransaction['total_xau'];

            $totalRedemptionTransaction = $ledgerHdl->select()
                                ->addFieldSum('credit', 'credit_xau')
                                ->where('accountholderid', 0)
                                ->where('type', MyLedger::TYPE_ACEREDEEM)
                                ->andWhere('partnerid', 'IN', $partnerIds)
                                ->andWhere('status', MyLedger::STATUS_ACTIVE)
                                ->first();
            $totalRedemption = $totalRedemptionTransaction['credit_xau'];

            $totalStorageFee = $this->app->mymonthlystoragefeeStore()->searchView(false)->select()
                                ->addFieldSum('storagefeexau', 'storagexau')
                                ->where('partnerid', 'IN', $partnerIds)
                                //->andWhere('status', 1)
                                ->andWhere('chargedon', '<=',$getLastDayOfPrevMonth)
                                ->first();
            $totalStorageBuy = $totalStorageFee['storagexau'];

            $totalAdminFee = $this->app->mymonthlystoragefeeStore()->searchView(false)->select()
                                ->addFieldSum('adminfeexau', 'adminsxau')
                                ->where('partnerid', 'IN', $partnerIds)
                                //->andWhere('status', 1)
                                ->andWhere('chargedon', '<=',$getLastDayOfPrevMonth)
                                ->first();
            $totalAdminBuy = $totalAdminFee['adminsxau'];


            $gtpUtilization = ($totalSell + $totalSellSP) - ($totalBuy + $totalBuySP) - $totalRedemption - ($totalStorageBuy + $totalAdminBuy);
            $utilisedTotalPercentage = number_format($gtpUtilization/$weightKilobar*100,2);

            if ($percentage){
                return $utilisedTotalPercentage;
            }

            return $gtpUtilization;

        }catch(\Exception $e){

        }
    }

    function getCurrentDgvUsage($all = true, $partnerId = false){
        // $partnerId for BO, specific user, how many they used
        $this->log(__METHOD__."(): get sharedgv partner usage", SNAP_LOG_DEBUG);

        $shareDgvUsage = false;
        if ($all == true && !$partnerId){
            // get total share DgvUsage
            $shareDgvUsageKey = '{shareDgvUsage}:total';
        }
        if ($all == false && $partnerId){
            $shareDgvUsagePartnerKey = '{shareDgvUsage}:'.$partnerId;
            $shareDgvUsageKey = $shareDgvUsagePartnerKey;
        }
        $cacher = $this->app->getCacher();
        $shareDgvUsage = $cacher->get($shareDgvUsageKey);
        if (!isset($shareDgvUsage)){
            $key = false;
            if ($all){
                $partners = $this->app->partnerStore()->searchTable()->select()->where('sharedgv', true)->execute();
                foreach ($partners as $partner){
                    $sharedgvPartnerIds[] = $partner->id;
                }
                $selectedPartnerId = $sharedgvPartnerIds;

                // $key = $shareDgvUsageKey;
            }
            if ($partnerId){
                $selectedPartnerId = [$partnerId];
                $key = $shareDgvUsagePartnerKey;
            }
            $shareDgvUsage = $this->getCurrentDgvFromDb($selectedPartnerId); // IN condition

            if ($key){
                $cacher->set($key, $shareDgvUsage);
            }
        }
        return $shareDgvUsage;
    }

    function getSharedDgvAmount(){
        $this->log(__METHOD__."(): get total allocated sharedgv amount", SNAP_LOG_DEBUG);
        $totalDgvAmount = $this->app->vaultitemStore()->searchView()->select()
                    ->where('partnerid', 0)
                    ->andWhere('allocated', 0)
                    ->andWhere('status', VaultItem::STATUS_ACTIVE)
                    ->andWhere('sharedgv', true)
                    ->orderby(['serialno'])
                    ->sum('weight');
        return floatval($totalDgvAmount);
    }

    function getVaultData($partner) {
        $this->log(__METHOD__."(): get gold list for partner", SNAP_LOG_DEBUG);
        
        $partner_Gold = $this->app->vaultitemStore()
            ->searchView()
            ->select()
            ->where('partnerid', $partner->id)
            ->andWhere('status', VaultItem::STATUS_ACTIVE)
            ->orderby(['serialno'])
            ->get();
    
        $vaultLocationEnd = $this->app->vaultlocationStore()
            ->searchView()
            ->select()
            ->where('partnerid',  $partner->id)
            ->one();
    
        $items = $this->app->vaultitemStore()
            ->searchTable()
            ->select()
            ->where('partnerid',  $partner->id)
            ->where('weight', 1000)
            ->execute();

        $vaultLocationStart = $this->app->vaultlocationStore()
            ->searchView()
            ->select()
            ->where('type', 'Start')
            ->one();

        $vaultLocationEnd2 = $this->app->vaultlocationStore()
            ->searchView()
            ->select()
            ->where('type', 'Intermediate')
            ->one();

        $items2 = $this->app->vaultitemStore()
        ->searchTable()
        ->select()
        ->where('partnerid','IN',$partnerArray)
        ->andWhere('sharedgv', true)
        ->andWhere('status', VaultItem::STATUS_ACTIVE)
        ->andWhere('weight', 1000)
        ->execute();

        $items3 = $this->app->vaultitemStore()
        ->searchTable()
        ->select()
        ->where('sharedgv', true)
        ->andWhere('status', VaultItem::STATUS_ACTIVE)
        ->andWhere('weight', 1000)
        ->execute();

        return [
            'partner_Gold' => $partner_Gold,
            'vaultLocationEnd' => $vaultLocationEnd,
            'vaultLocationEnd2' => $vaultLocationEnd2,
            'vaultLocationStart' => $vaultLocationStart,
            'items' => $items,
            'items2' => $items2,
            'items3' => $items3,
        ];
    }

    function checkSharedDgvBalanceToProceed($grams, $orderType){
        // checking on booking/transaction/order
        $this->log(__METHOD__."(): check sharedgv partner balance start", SNAP_LOG_DEBUG);
        $totalSharedDgvAmount = $this->getSharedDgvAmount();
        $currentTotalSharedUsage = $this->getCurrentDgvUsage();
        if ($orderType == Order::TYPE_COMPANYSELL){
            $balance = $totalSharedDgvAmount - $currentTotalSharedUsage - $grams;
        }
        if ($orderType == Order::TYPE_COMPANYBUY){
            $balance = $totalSharedDgvAmount - $currentTotalSharedUsage + $grams;
        }
        if ($balance > 0){
            return true;
        }
        return false;
    }

    /**
     * This method will return the order state machine to manage the different states of the order process.
     * 
     * @return Finite/StateMachine/StateMachine 
     */
    function getUtilizationValues($partner){
        // get total utiliation 
        try{
            // $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            // $currentdate        = $now->format('Y-m-d H:i:s');
            // // $date               = date('Y-m-d h:i:s',$transaction['currentdate']);
            // // $date               = date('Y-m-d h:i:s',$currentdate);
            // $createDateEnd      = date_create($currentdate);
            // $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
            // $dateOfData         = date_format($modifyDateEnd,"d-m-Y");
            // $endDate            = date_format($modifyDateEnd,"Y-m-d 15:59:59");

            $totalBuyTransaction = $this->app->orderStore()->searchTable(false)->select()
                    ->addFieldSum('xau', 'total_xau')
                    ->where('partnerid', $partner->id)
                    ->andWhere('type', Order::TYPE_COMPANYBUY)
                    // ->andWhere('createdon', '<=',$endDate)
                    ->andWhere('status','IN', [Order::STATUS_PENDING,Order::STATUS_CONFIRMED])
                    ->first();
            $totalBuy = $totalBuyTransaction['total_xau'];

            $totalSellTransaction = $this->app->orderStore()->searchTable(false)->select()
                    ->addFieldSum('xau', 'total_xau')
                    ->where('partnerid', $partner->id)
                    ->andWhere('type', Order::TYPE_COMPANYSELL)
                    // ->andWhere('createdon', '<=',$endDate)
                    ->andWhere('status','IN', [Order::STATUS_PENDING,Order::STATUS_CONFIRMED])
                    ->first();
            $totalSell = $totalSellTransaction['total_xau'];

            $totalRedemptionTransaction = $this->app->redemptionStore()->searchTable(false)->select()
                    ->addFieldSum('totalweight', 'total_xau')
                    ->where('partnerid',  $partner->id)
                    // ->andWhere('createdon', '<=',$endDate)
                    ->andWhere('status','IN',[Redemption::STATUS_CONFIRMED,Redemption::STATUS_COMPLETED])
                    ->first();
            $totalRedemption = $totalRedemptionTransaction['total_xau'];

            $gtpUtilization = $totalSell - ($totalBuy + $totalRedemption);

            // Get vault xau
            // Get Vault Location
            $vaultLocation = $this->app->vaultlocationStore()->searchView()->select()->where('partnerid', $partner->id)->one();

            // Get all vault amount
            // Vaultlocation based on bmmb
            $items = $this->app->vaultitemStore()->searchView()->select()->where('partnerid', $partner->id)->andWhere('vaultlocationid', $vaultLocation->id)->execute();

            $xau = $ordxau = 0;

            foreach ($items as $item){
                $xau = $item->weight + $xau;
                $xau = $partner->calculator(false)->round($xau);
            }

            $withoutDO = $gtpUtilization - $xau;

            $return['vaultamount'] = $xau;
            $return['totalutilization'] = $gtpUtilization;
            $return['withoutdoxau'] = $withoutDO;

        }catch(\Exception $e) {
            $this->log(__METHOD__."Error to acquire total utilization data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
     
        return $return;
    }
}
?>