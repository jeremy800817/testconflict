<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;
/**
 * Encapsulates the service table on the database
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $ID                 ID of the system
 * @property        int                 $postcodefrom       Postcode from range
 * @property        int                 $postcodeto         Postcode to range
 * @property        int                 $vendorid           Logistic vendor id from Tag
 * @property        float               $amount             Fee amount
 * @property        int                 $status             Status of this logistic fee
 * @property        DateTime            $validfrom          Fee Valid from
 * @property        DateTime            $validto            Fee Valid to
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class LogisticFeeMapping extends SnapObject
{

    /**
     * This method will initialise the array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's constructor.
     *
     * @return void
     */
    protected function reset()
    {
        $this->members = array(
            'id' => null,
            'postcodefrom' => null,
            'postcodeto' => null,
            'vendorid' => null,
            'amount' => null,
            'status' => null,
            'validfrom' => null,
            'validto' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid()
    {
        $this->validateRequiredField($this->members['postcodefrom'], 'postcodefrom');
        $this->validateRequiredField($this->members['postcodeto'], 'postcodeto');
        $this->validateRequiredField($this->members['vendorid'], 'vendorid');
        $this->validateRequiredField($this->members['amount'], 'amount');
        $this->validateRequiredField($this->members['validfrom'], 'validfrom');
        $this->validateRequiredField($this->members['validto'], 'validto');

        return true;
    }
}
