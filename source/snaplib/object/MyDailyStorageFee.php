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
 * @property            float               $xau                The combined fee value of xau in gram calculated
 * @property            float               $storagefeexau      The storage fee value of xau in gram calculated
 * @property            float               $adminfeexau        The admin fee value of xau in gram calculated
 * @property            float               $balancexau         The balance value of xau in gram calculated
 * @property            int                 $accountholderid    The account holder id
 * @property            int                 $status             The status of this object
 * @property            DateTime            $calculatedon       The date this storage fee is calculated
 * @property            DateTime            $createdon          Time this record is created
 * @property            DateTime            $modifiedon         Time this record is last modified
 * @property            int                 $createdby          User ID
 * @property            int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyDailyStorageFee extends SnapObject
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
            'xau' => null,
            'adminfeexau' => null,
            'storagefeexau' => null,
            'balancexau' => null,
            'accountholderid' => null,
            'status' => null,
            'calculatedon' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->viewMembers = array(
            'achfullname' => null,
            'achaccountholdercode' => null,
            'achmykadno' => null,
            'partnerid' => null,
            'partnercode' => null,
            'partnername' => null,
            'ledcurrentxau' => null
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
        $this->validateRequiredField($this->members['xau'], 'xau');
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        $this->validateRequiredField($this->members['calculatedon'], 'calculatedon');
        $this->validateRequiredField($this->members['status'], 'status');

        return true;
    }
}
