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
 * Name                 Type                Description
 * @property-read       int                 $id                 ID of the system
 * @property            int                 $accountholderid    The receiver accountholder id
 * @property            float               $price              The gold price 
 * @property            float               $amount             The amount in RM
 * @property            float               $xau                The gram of xau
 * @property            string              $remarks            The remarks of the transfer
 * @property            int                 $status             Status Success or Failed
 * @property            DateTime            $createdon          Time this record is created
 * @property            DateTime            $modifiedon         Time this record is last modified
 * @property            int                 $createdby          User ID
 * @property            int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2021/08/03 2:15 PM
 */
class MyGoldTransfer extends SnapObject
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
            'accountholderid' => null,
            'price' => null,
            'amount' => null,            
            'xau' => null,
            'remarks' => null,
            'status' => null,
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
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');        
        $this->validateRequiredField($this->members['price'], 'price');
        $this->validateRequiredField($this->members['amount'], 'amount');
        $this->validateRequiredField($this->members['xau'], 'xau');

        return true;
    }
}
