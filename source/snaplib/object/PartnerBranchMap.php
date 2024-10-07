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
 * PartnerBranchMap Class
 *
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                 ID of the table
 * @property-read   int                 $partnerid          ID of the partner table
 * @property        string              $code               branch number
 * @property        string              $name               name of the branch
 * @property        string              $sapcode            SAP code
 * @property        string              $address            SAP code
 * @property        string              $postcode           SAP code
 * @property        string              $city               SAP code
 * @property        string              $contactno          SAP code
 * @property        \DateTime           $createdon          Time this record created
 * @property        int                 $createdby          User ID
 * @property        \DateTime           $modifiedon         Time this record is last modified
 * @property        int                 $modifiedby         User ID
 * @property        int                 $status             Status
 *
 * @author  Calvin <calvin.thien@ace2u.com>
 * @version 1.0
 * @package Snap\object
 */
class PartnerBranchMap extends SnapObject
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
            'code' => null,
            'name' => null,
            'sapcode' => null,
            'address' => null,
            'postcode' => null,
            'city' => null,
            'contactno' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null,

        ];
    }

    /**
     * A validation function where to check mandatory fields
     *
     * @internal
     * @param $value
     * @param null|string $message
     * @param null|string $key
     * @throws InputException
     */
    private function validateMandatoryField($value, ?string $message, ?string $key): void
    {
        if (empty($value)) {
            throw new InputException(gettext($message), InputException::FIELD_ERROR, $key);
        }
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
        if(empty($this->members['partnerid']) || $this->members['partnerid'] == 0) {
            throw new InputException(gettext('The partner field is mandatory'), InputException::FIELD_ERROR, 'partnerid');
        }
        //branch code is mandatory
        if(0 == strlen($this->members['code']) && ! is_string($this->members['code'])) {
            throw new InputException(gettext('The Code field is mandatory'), InputException::FIELD_ERROR, 'code');
        } else {
            //Make sure that the code is unique.
            $res = $this->getStore()->searchTable()->select()->where('code', '=', $this->members['code'])->andWhere('partnerid', $this->members['partnerid']);
            if($this->members['id']) {
                $res = $res->andWhere('id', '!=', $this->members['id']);
            }
            $data = $res->count();
            if ($data) {
                throw new InputException(sprintf(gettext('The code %s is already in use by another entry'), $this->members['code']), InputException::FIELD_ERROR, 'code');
            }
        }
        //name is mandatory
        if(0 == strlen($this->members['name']) && ! is_string($this->members['name'])) {
            throw new InputException(gettext('The field is mandatory'), InputException::FIELD_ERROR, 'name');
        }
        //sap code is mandatory
        if(0 == strlen($this->members['sapcode']) && ! is_string($this->members['sapcode'])) {
            throw new InputException(gettext('The field is mandatory'), InputException::FIELD_ERROR, 'sapcode');
        }

        return true;
    }
}
