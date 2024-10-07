<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\object;
Use Snap\InputException;
Use Snap\IEntity;


/**
 * VaultItem Class
 *
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                     ID of the table
 * @property        int                 $partnerid              Partner that item belongs to
 * @property        int                 $vaultlocationid        ID of the vault location
 * @property        int                 $productid              ID of the product
 * @property        decimal             $weight                 Weight of the product
 * @property        string              $brand                  Product source
 * @property        string              $serialno               Serial number of item
 * @property        int                 $newvaultlocationid     ID of new vault location
 * @property        int                 $goldrequestid          ID of request
 * @property        \DateTime           $createdon              Time this record created
 * @property        int                 $createdby              User ID
 * @property        \DateTime           $modifiedon             Time this record is last modified
 * @property        int                 $modifiedby             User ID
 * @property        int                 $status                 Api status
 *
 * @author  Calvin <calvin.thien@ace2u.com>
 * @version 1.0
 * @package Snap\object
 */
class VaultItem extends SnapObject
{

    const STATUS_PENDING = 0; 
    const STATUS_ACTIVE = 1; // 
    const STATUS_TRANSFERRING = 2; // transferring to other vault location
    const STATUS_INACTIVE = 3; 
    const STATUS_REMOVED = 4; // 
    const STATUS_PENDING_ALLOCATION = 5; // 
    // const transferring = 4;
    // 0 - pending, 1 - available, 2 - allocated, 3 - transferring, 4 - returned   // ori status
    // serial_no != '' && vaultlocationid == '' is virtual bar
    // serial_no != '' && vaultlocationid != '' is physical bar
    // transferring = (movetovaultlocationid != '')

    /**
     * This method will initialise the 2 array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's contractor.
     * @return void
     */
    protected function reset(): void
    {
        $this->members = [
            'id' => null,
            'partnerid' => null,
            'vaultlocationid' => null,
            'productid' => null,
            'weight' => null,
            'brand' => null,
            'serialno' => null,
            'allocated' => false,
            'allocatedon' => false,
            'utilised' => null,
            'movetovaultlocationid' => null,
            'moverequestedon' => null,
            'movecompletedon' => null,
            'newvaultlocationid' => null,
            'deliveryordernumber' => null,
            'sharedgv' => null,
            //'goldrequestid' => null,
            'returnedon' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null,
            'locked' => null, // active confirmation
        ];
        $this->viewMembers = [
            'vaultlocationname' => null,
            'vaultlocationtype' => null,
            'vaultlocationdefault' => null,
            'movetolocationpartnerid' => null,
            'movetovaultlocationname' => null,
            'newvaultlocationname' => null,
            'partnername' => null,
            'partnercode' => null,
            'productcode' => null,
            'productname' => null,
            'createdbyname' => null,
            'modifiedbyname' => null,
        ];

    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid(): bool
    {
        //weight is mandatory
        if(empty($this->members['weight']) || !is_numeric(strlen($this->members['weight']))) {
            throw new InputException(gettext('The weight field is mandatory'), InputException::FIELD_ERROR, 'weight');
        }
        //Serial number must be unique and mandatory
        if (0 == strlen($this->members['serialno'])) {
            throw new InputException(gettext('serialno is required'), InputException::FIELD_ERROR, 'serialno');
        } elseif (36 < strlen($this->members['serialno'])) {
            throw new InputException(gettext('Length of the serialno field can not be more than 36 characters'), InputException::FIELD_ERROR, 'serialno');
        } else {
            //Make sure that the serialno is unique.
            $res = $this->getStore()->searchTable()->select()->where('serialno', '=', $this->members['serialno']);
            if($this->members['id']) {
                $res = $res->andWhere('id', '!=', $this->members['id']);
            }
            $data = $res->count();
            if ($data) {
                throw new InputException(sprintf(gettext('The serialno %s is already in use by another entry'), $this->members['serialno']), InputException::FIELD_ERROR, 'serialno');
            }
        }
        return true;
    }

    public function __get($nm) {

        if( ('statestatus' != $nm) && ('sapoutputstatus' != $nm) ) return parent::__get($nm);
        
        if ('statestatus' == $nm){
            if($this->status == self::STATUS_ACTIVE && $this->allocated == 1) {
                return 'allocated_active';
            }
            elseif(($this->status == self::STATUS_INACTIVE || $this->status == self::STATUS_REMOVED) && $this->allocated == 1){
                return 'allocated_inactive';
            }
            elseif($this->status == self::STATUS_TRANSFERRING && $this->allocated == 1){
                return 'allocated_transferring';
            }
            elseif($this->status == self::STATUS_ACTIVE && $this->allocated == 0){
                return 'unallocated_active';
            }
            elseif(($this->status == self::STATUS_INACTIVE || $this->status == self::STATUS_REMOVED) && $this->allocated == 0){
                return 'unallocated_inactive';
            }
            elseif($this->status == self::STATUS_TRANSFERRING && $this->allocated == 0){
                return 'unallocated_transferring';
            }
            elseif($this->status == self::STATUS_PENDING_ALLOCATION && $this->allocated == 0){
                return 'unallocated_pending';
            }
            
            else{
                return 'none';
            }
        }

        if ('sapoutputstatus' == $nm){
            if ($this->status == self::STATUS_PENDING){
                return "Pending";
            }elseif ($this->status == self::STATUS_ACTIVE){
                return "Active";
            }elseif($this->status == self::STATUS_INACTIVE){
                return "Inactive";
            }elseif($this->status == self::STATUS_TRANSFERRING){
                return "Transferring";
            }elseif($this->status == self::STATUS_REMOVED){
                return "Removed";
            }else{
                return "INVALID_STATUS";
            }
        }
        
    }

    /**
     * Checks if this current item has been allocated to MBB
     * @return boolean   True if allocated.  False otherwise
     */
    public function canAllocate()
    {
        return ! $this->members['allocated'] && $this->members['status'];
    }

    /**
     * Checks if the item is allocated already or not.
     * @return boolean True if already been allocated.  False otherwise.
     */
    public function isAllocated()
    {
        return ! $this->canAllocate();
    }

    /**
     * Checks if can request a move of item to a new location.
     * @param  VaultLocation $newLocation 
     * @return Boolean    True if able to put in a request to move to location.  False otherwise.
     */
    public function canRequestMoveToLocation(VaultLocation $newLocation)
    {
        $currentLocation = $this->getStore()->getRelatedStore('vaultlocation')
                                ->searchTable()
                                ->select()
                                ->where('id', $this->members['vaultlocationid'])
                                ->one();
        if( 0 == $this->members['status'] ||
            (0 < $this->members['movetovaultlocationid']) ||
            ($newLocation->isStartingLocation() && $this->isAllocated()) ||
            ($newLocation->isFinalLocation() && !$this->isAllocated()) ) {
            $this->log("Unable to move vault item ({$this->id}) [allocated = {$this->members['allocated']}] new location {$newLocation->id} ({$newLocation->name}|{$newLocation->type})", SNAP_LOG_DEBUG);
            return false;
        }
        return true;
    }

    /**
     * Checks if can request a move of item to a new location.
     * @param  VaultLocation $newLocation 
     * @return Boolean    True if able to put in a request to move to location.  False otherwise.
     */
    public function requestMoveToLocation(VaultLocation $newLocation)
    {
        if( $this->canRequestMoveToLocation($newLocation)) {
            $this->members['moverequestedon'] = new \Datetime();
            $this->members['movetovaultlocationid'] = $newLocation->id;
            $this->getStore()->save($this);
            $this->log("Vault item {$this->members['id']} requested to move to new location {$newLocation->id} ({$newLocation->name})", SNAP_LOG_DEBUG);
            return true;
        }
        return false;
    }

    /**
     * Checks if can proveed to complete the move to location
     * @return Boolean   True if can perform the operation.  False otherwise
     */
    public function canCompleteMoveToLocation()
    {
        if(0 == $this->members['movetovaultlocationid']) {
            $this->log("Unable to complete move to location for vault item {$this->members['id']} because no new vault location id found", SNAP_LOG_DEBUG);
            return false;
        }
        $newLocation = $this->getStore()
                            ->getRelatedStore('vaultlocation')
                            ->searchTable()
                            ->select()
                            ->where('id', $this->members['movetovaultlocationid'])
                            ->one();
        if(0 == $this->members['status'] ||
           $newLocation->isFinalLocation() && !$this->isAllocated() ||
           ($newLocation->isStartingLocation() && $this->isAllocated())) {
            $this->log("Unable to complete move vault item ({$this->id}) [allocated = {$this->members['allocated']}] new location {$newLocation->id} ({$newLocation->name}|{$newLocation->type})", SNAP_LOG_DEBUG);
            return false;
        }
        return true;
    }

    /**
     * Complete the move operation
     * @return Boolean  True if successful.  False otherwise.
     */
    public function completeMoveToLocation()
    {
        if($this->canCompleteMoveToLocation()) {
            $this->members['vaultlocationid'] = $this->members['movetovaultlocationid'];
            $this->members['movetovaultlocationid'] = 0;
            $this->members['movecompletedon'] = new \Datetime;
            $this->getStore()->save($this);
            return true;
        }
        return false;
    }

    /**
     * Checks if user can activate returning of the gold to its original location
     * @return Boolean  True if able to perform the action  False otherwise.
     */
    public function canReturn()
    {
        $currentLocation = $this->getStore()
                                ->getRelatedStore('vaultlocation')
                                ->searchTable()
                                ->select()
                                ->where('id', $this->members['vaultlocationid'])
                                ->one();
        if(0 < $this->members['status'] && 
           0 == $this->members['allocated'] && 
           0 == $this->members['movetovaultlocationid'] && 
           $currentLocation->isFinalLocation()) {
            return true;
        }
        return false;
    }

    /**
     * Performs the return function.
     * @return Boolean  True if successful.  False otherwise.
     */
    public function completeReturn()
    {
        if($this->canReturn()) {
            $this->members['partnerid'] = 0;
            $this->members['status'] = 0;
            $this->members['vaultlocationid'] = 0;
            $this->members['movetovaultlocationid'] = 0;
            $this->getStore()->save($this);
            return true;
        }
        return false;
    }

    
    /**
     * Get Transferring Status
     * @return int   
     */
    public function getTransferring()
    {
        return  self::STATUS_TRANSFERRING;
    }
}
?>
