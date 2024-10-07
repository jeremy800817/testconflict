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
 * @property        int                 $screeninglistid    The id of the screening list
 * @property        int                 $accountholderid    The id of the account holder
 * @property        int                 $amlascanlogid      The id of the scan if any
 * @property        string              $matcheddata        Name from source
 * @property        string              $remarks            Admin remarks
 * @property        int                 $status             The status of the log
 * @property        DateTime            $matchedon          Date the entity was imported
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyScreeningMatchLog extends SnapObject
{

    const STATUS_PENDING = 0;
    const STATUS_IGNORED = 1;
    const STATUS_BLACKLISTED = 2;
    const STATUS_SUSPENDED = 3;

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
            'screeninglistid' => null,
            'accountholderid' => null,
            'amlascanlogid' => null,
            'matcheddata' => null,
            'remarks' => null,
            'status' => null,
            'matchedon' => null,
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
        $this->validateRequiredField($this->members['screeninglistid'], 'screeninglistid');
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        $this->validateRequiredField($this->members['matcheddata'], 'matcheddata');

        return true;
    }

    /**
     * Get the status string of this SnapObject
     *
     * @return string
     */
    public function getStatusString()
    {
        switch ($this->members['status']) {
            case self::STATUS_PENDING:
                return 'Pending';
                break;
            case self::STATUS_IGNORED:
                return 'Ignored';
                break;
            case self::STATUS_SUSPENDED:
                return 'Suspended';
                break;
            default:
                return 'Blacklisted';
                break;
        }
    }
}
