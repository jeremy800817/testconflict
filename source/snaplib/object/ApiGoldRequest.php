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
 * ApiGoldRequest Class
 *
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                 ID of the table
 * @property        int                 $partnerid          ID of the partner
 * @property        string              $partnerrefid       ID of the partner ref
 * @property        string              $apiversion         API version
 * @property        int                 $quantity           Number of item request
 * @property        string              $reference          Reference
 * @property        \DateTime           $timestamp          Time of request
 * @property        \DateTime           $createdon          TIme this record recreated
 * @property        int                 $createdby          User ID
 * @property        \DateTime           $modifiedon         Time this record is last modified
 * @property        int                 $modifiedby         User ID
 *
 * @author  Calvin <calvin.thien@ace2u.com>
 * @version 1.0
 * @package Snap\object
 */
class ApiGoldRequest extends SnapObject
{

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
            'partnerrefid' => null,
            'apiversion' => null,
            'quantity' => null,
            'reference' => null,
            'timestamp' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,

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
        //partner id is mandatory
        if(empty($this->members['partnerid']) || 0 == strlen($this->members['partnerid'] == 0)) {
            throw new InputException(gettext('The partner ID field is mandatory'), InputException::FIELD_ERROR, 'partnerid');
        }
        //api version is mandatory
        if(empty($this->members['apiversion']) || 0 == strlen($this->members['apiversion'] == 0)) {
            throw new InputException(gettext('The API version field is mandatory'), InputException::FIELD_ERROR, 'apiversion');
        }
        //quantity is mandatory
        if(empty($this->members['quantity']) || 0 == strlen($this->members['quantity'] == 0)) {
            throw new InputException(gettext('The quantity field is mandatory'), InputException::FIELD_ERROR, 'quantity');
        }

        //reference is mandatory
        /*
        if(empty($this->members['reference']) || $this->members['reference'] == 0) {
            throw new InputException(gettext('The IP field is mandatory'), InputException::FIELD_ERROR, 'reference');
        */

        return true;
    }


    /**
     * get partner
     *
     * @return array    result[]    get partner
     */
    public function getPartnerID() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('partnerservice')->searchTable()->select()
                ->where('id', $this->members['partnerid']);
            $result = $select->execute();
        }
        return $result;
    }


}
?>
