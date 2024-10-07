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
 * @property        int                 $documentno             Generated Document Number
 * @property        int                 $documentdateon         Generated Document Date, can be vary from transaction date, to create previous date
 * @property        int                 $tolocationid           ID of vault location - from - previous 
 * @property        int                 $fromlocationid         ID of vault location - to - current 
 * @property        int                 $status                 Transaction status
 * @property        \DateTime           $createdon              Time this record created
 * @property        int                 $createdby              User ID
 * @property        \DateTime           $modifiedon             Time this record is last modified
 * @property        int                 $modifiedby             User ID
 *
 * @version 1.0
 * @package Snap\object
 */
class VaultItemTrans extends SnapObject
{

    const TYPE_TRANSFER = 'TRANSFER';
    const TYPE_RETURN = 'RETURN';
    const TYPE_TRANSFER_CONFIRMATION = 'TRANSFERCONFIRMATION';

    const STATUS_CANCELLED = 0; 
    const STATUS_ACTIVE = 1;

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
            'type' => null,
            'documentno' => null,
            'documentdateon' => null,
            'fromlocationid' => null,
            'tolocationid' => null,
            'status' => null,
            'cancelby' => null,
            'cancelon' => null,

            'transferrequestby' => null,
            'confirmrequestby' => null, // approval
            'completerequestby' => null,
            'requestprocessing' => null, // after confirm, once confirmed this will be true else null
            'transferrequeston' => null,
            'confirmtransferon' => null,
            'complatereqeueston' => null,

            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
        ];
        $this->viewMembers = [
            'fromlocationname' => null,
            'tolocationname' => null,
            'createdbyname' => null,
            'modifiedbyname' => null,
            'cancelbyname' => null,
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
        
        if(empty($this->members['type']) || !is_numeric(strlen($this->members['type']))) {
            throw new InputException(gettext('The type field is mandatory'), InputException::FIELD_ERROR, 'type');
        }
        if(empty($this->members['fromlocationid']) || !is_numeric(strlen($this->members['fromlocationid']))) {
            throw new InputException(gettext('The fromlocationid field is mandatory'), InputException::FIELD_ERROR, 'fromlocationid');
        }
        if(empty($this->members['tolocationid']) || !is_numeric(strlen($this->members['tolocationid']))) {
            throw new InputException(gettext('The tolocationid field is mandatory'), InputException::FIELD_ERROR, 'tolocationid');
        }
        if(empty($this->members['documentno']) || !$this->members['documentno']) {
            $this->members['documentno'] = $this->setDocumentNo();
            // throw new InputException(gettext('The documentno field is mandatory'), InputException::FIELD_ERROR, 'documentno');
        }
        return true;
    }

    /**
     * Get Transferring Status
     * @return int   
     */
    public function setDocumentNo()
    {
        $latestDocuments = $this->getStore()->searchTable()->select()->orderby('id', 'DESC')->one();
        if (!$latestDocuments){
            $documentNo = 'TN00004';
            // initial seq number FROM old documentManager number
        }else{
            $documentNo = $latestDocuments->documentno;
        }
        $number = filter_var($documentNo, FILTER_SANITIZE_NUMBER_INT);
        $number = intval($number) + 1; 
        $type = 'TransferNote';
        $documentNo = $this->formatTypeNumber($number, $type);
        return $documentNo;
    }

    public function formatTypeNumber($number, $type){
        
        if ($type == 'ConsignmentNote'){
            $prefex = 'CN';
        }
        if ($type == 'TransferNote'){
            $prefex = 'TN';
        }
        if ($type == 'DeliveryNote'){
            $prefex = 'DN';
        }
        
        $nextSequence = strtoupper(sprintf("%s%05d", $prefex, $number));
        return $nextSequence;
    }

    public function getChild(){
        $res = $this->getStore()->getRelatedStore('vaultitemtransitem')->searchView()->select()->where('vaultitemtransid', $this->members['id'])->execute();
        return $res;
    }

    public function getReqeustedOwnership(){
       
        $fromLocationOwner = \Snap\App::getInstance()->vaultlocationStore()->getById($this->members['fromlocationid']);
        // $toLocationOwner = \Snap\App::getInstance()->vaultlocationStore()->getById($this->members['tolocationid']);
        $requestby = \Snap\App::getInstance()->userStore()->getById($this->members['requestby']);
        if ($fromLocationOwner->partnerid == $requestby->partnerid){
            $return_actions = [
                'self_request',
                'opponent_confirm',
                'opponent_complete'
            ];
        }
        if ($fromLocationOwner->partnerid != $requestby->partnerid){
            $return_actions = [
                'self_request',
                'opponent_confirm',
                'self_complete'
            ];
        }
    }

    public function getTransActions($checkPreset = true){
        if ($this->members['transferrequestby'] && $checkPreset){
            $fromLocationOwner = \Snap\App::getInstance()->vaultlocationStore()->getById($this->members['fromlocationid']);
            // $toLocationOwner = \Snap\App::getInstance()->vaultlocationStore()->getById($this->members['tolocationid']);
            $requestby = \Snap\App::getInstance()->userStore()->getById($this->members['requestby']);
            if ($fromLocationOwner->partnerid == $requestby->partnerid){
                $return_actions = [
                    'self_request',
                    'opponent_confirm',
                    'opponent_complete'
                ];
            }
            if ($fromLocationOwner->partnerid != $requestby->partnerid){
                $return_actions = [
                    'self_request',
                    'opponent_confirm',
                    'self_complete'
                ];
            }
            if ($return_actions){
                $this->members[''];
            }
            return $return_actions;
        }
        return false;
    }
    public function __get($nm) {

        if( ('requeststate' != $nm) ) return parent::__get($nm);
        
        if ('requeststate' == $nm){
            $actions = $this->getTransActions();
            if ($actions){
                
                

            }else{
                return false;
            }
        }
    }
}
?>
