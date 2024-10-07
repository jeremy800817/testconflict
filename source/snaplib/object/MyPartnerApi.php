<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

/**
 * Maps partners to API classes
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                 ID 
 * @property        enum                $type               The type of this mapping
 * @property        string              $name               Name for this mapping
 * @property        int                 $classtype          The string of the API class
 * @property        string              $partnerid          Partner ID
 * @property        int                 $status             The status of this mapping
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Cheok
 * @version 1.0
 * @created 2020/10/20
 */
class MyPartnerApi extends SnapObject
{

    const TYPE_FPX = 'FPX';
    const TYPE_EKYC = 'EKYC';

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
            'type' => null,
            'name' => null,
            'classtype' => null,
            'partnerid' => null,
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
        $this->validateRequiredField($this->members['type'], 'type');
        $this->validateRequiredField($this->members['classtype'], 'classtype');
        $this->validateRequiredField($this->members['partnerid'], 'partnerid');
        $this->validateRequiredField($this->members['status'], 'status');

        return true;
    }
}
