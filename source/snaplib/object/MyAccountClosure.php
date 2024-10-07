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
 * @property        string              $reason             Reason selected by the account holder
 * @property        string              $remarks            Admin remarks for action taken
 * @property        int                 $accountholderid    Account holder id
 * @property        int                 $status             The status of the account closure request
 * @property        DateTime            $requestedon        Time account closure request is made
 * @property        DateTime            $closedon           Time the account is closed
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyAccountClosure extends SnapObject
{

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_IN_PROGRESS = 3;
    const STATUS_REACTIVATED = 4; // for reactivated accounts

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
            'remarks' => null,
            'reasonid' => null,
            'accountholderid' => null,
            'transactionrefno' => null,
            'status' => null,
            'requestedon' => null,
            'closedon' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->viewMembers = array(
            'achfullname' => null,
            'achaccountholdercode' => null,
            'achmykadno' => null,
            'achpartnerid' => null,
            'locreason' => null,
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
        $this->validateRequiredField($this->members['reasonid'], 'reasonid');
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        $this->validateRequiredField($this->members['requestedon'], 'requestedon');

        return true;
    }

    /**
     * Get status in readable format
     */
    public function getStatusString()
    {
        switch ($this->members['status']) {
            case self::STATUS_APPROVED:
                return gettext('Approved');
            case self::STATUS_PENDING:
                return gettext('Pending');
            case self::STATUS_REJECTED:
                return gettext('Rejected');
            case self::STATUS_IN_PROGRESS:
                return gettext('In Progress');
            default:
                return gettext('Unknown');
        }
    }

    public static function convertStatusString($status)
    {
        switch ($status) {
            case self::STATUS_APPROVED:
                return 'Approved';
            case self::STATUS_PENDING:
                return 'Pending';
            case self::STATUS_REJECTED:
                return 'Rejected';
            case self::STATUS_IN_PROGRESS:
                return 'In Progress';
            default:
                return 'Unknown';
        }
    }
}
