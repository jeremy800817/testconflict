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
 * @property-read   int                 $id                 ID of the system
 * @property        string              $line1              Address line 1
 * @property        string              $line2              Address line 2
 * @property        string              $city               Address city
 * @property        int                 $postcode           Address postcode
 * @property        string              $state              Address state
 * @property        int                 $accountholderid    The id of account holder
 * @property        int                 $status             The status of this address
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyAddress extends SnapObject
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
            'line1' => null,
            'line2' => null,
            'city' => null,
            'postcode' => null,
            'state' => null,
			'mailingline1' => null,
            'mailingline2' => null,
            'mailingcity' => null,
            'mailingpostcode' => null,
            'mailingstate' => null,
            'accountholderid' => null,
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
        $this->validateRequiredField($this->members['line1'], 'line');
        $this->validateRequiredField($this->members['city'], 'city');
        $this->validateRequiredField($this->members['postcode'], 'postcode');
        $this->validateRequiredField($this->members['state'], 'state');
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        
        return true;
    }
}
