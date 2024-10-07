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
 * @property        string              $vaultitemtransid       ID of Vault item transaction 
 * @property        string              $vaultitemid            ID of vault item
 * @property        int                 $tolocationid           ID of vault location - from - previous _PENDING|INCASE_NO_TRANS_ID
 * @property        int                 $fromlocationid         ID of vault location - to - current _PENDING|INCASE_NO_TRANS_ID
 * @property        \DateTime           $movementon             single item movement date time, incase admins dint follow process _PENDING|INCASE_NO_TRANS_ID
 * @property        int                 $status                 Item status, can be cancelled by single item by admins
 * @property        \DateTime           $createdon              Time this record created
 * @property        int                 $createdby              User ID
 * @property        \DateTime           $modifiedon             Time this record is last modified
 * @property        int                 $modifiedby             User ID
 *
 * @version 1.0
 * @package Snap\object
 */
class VaultItemTransItem extends SnapObject
{

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
            'vaultitemtransid' => null, // if o document, 0 -> NO
            'vaultitemid' => null,
            // 'fromlocationid' => null,
            // 'tolocationid' => null,
            // 'movementon' => null,
            'status' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
        ];
        $this->viewMembers = [
            'serialno' => null,
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
        if(empty($this->members['vaultitemtransid']) || !is_numeric(strlen($this->members['vaultitemtransid']))) {
            throw new InputException(gettext('The vaultitemtransid field is mandatory'), InputException::FIELD_ERROR, 'vaultitemtransid');
        }else{
            // $res = App::getInstance()->vaultitemtransStore()->searchTable()->select()->where('id', $this->members['vaultitemtransid']);
            // $data = $res->count();
            // if (!$data) {
            //     throw new InputException(sprintf(gettext('The vaultitemtrans ID %s is not in system'), $this->members['vaultitemtransid']), InputException::FIELD_ERROR, 'vaultitemtransid');
            // }
        }
        if(empty($this->members['vaultitemid']) || !is_numeric(strlen($this->members['vaultitemid']))) {
            throw new InputException(gettext('The vaultitemid field is mandatory'), InputException::FIELD_ERROR, 'vaultitemid');
        }else{
            $res = \Snap\App::getInstance()->vaultitemStore()->searchTable()->select()->where('id', $this->members['vaultitemid']);
            $data = $res->count();
            if (!$data) {
                throw new InputException(sprintf(gettext('The vaultitem ID %s is not in system'), $this->members['vaultitemid']), InputException::FIELD_ERROR, 'vaultitemid');
            }
        }
        
        return true;
    }

    /**
     * Get Document No
     * @return int   
     */
    public function getDocumentNo()
    {
        $res = App::getInstance()->vaultitemtransStore()->searchTable()->select()->where('id', $this->members['vaultitemtransid'])->one();
        return $res->documentno;
    }
}
?>
