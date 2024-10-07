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
 * @property        enum                $type               The type of this token
 * @property        string              $token              The token string
 * @property        int                 $accountholderid    The account holder id owns this token
 * @property        string              $remarks            Extra info for this token, Device name etc
 * @property        int                 $status             The status of this token
 * @property        DateTime            $expireon           Expiry of this token
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyTransferGold extends SnapObject
{
    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;
    const STATUS_REQUIREAPPROVAL = 3;
    const STATUS_TIMEOUTAPPROVAL = 4;
    const STATUS_REJECTAPPROVAL = 5;
    
    const TYPE_TRANSFER = 'TRANSFER';
    const TYPE_INVITE = 'INVITE';
    const TYPE_RENT = 'RENT';

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
            'partnerid' => null,
            'accountholderid' => null,
            'type' => null,
            'fromaccountholderid' => null,
            'toaccountholderid' => null,
            'receiveremail' => null,
            'receivername' => null,
            'contact' => null,
            'refno' => null,
            'xau' => null,
            'price' => null,
            'amount' => null,
            'message' => null,
            'sendercode' => null,
            'receivercode' => null,
            'status' => null,
            'transferon' => null,
            'cancelon' => null,
            'expireon' => null,
            'isnotifyrecipient' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
            'checker' => null,
            'actionon' => null,
            'remarks' => null
        );

        $this->viewMembers = array(
            'frompartnerid' => null,
            'fromfullname' => null,
            'fromaccountholdercode' => null,
            'tofullname' => null,
            'toaccountholdercode' => null,
            'partnercode' => null,
            'partnername' => null,
            'createdbyname' => null,
	    'modifiedbyname' => null,
	    'transpartnername' => null
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
        $this->validateRequiredField($this->members['fromaccountholderid'], 'fromaccountholderid');
        $this->validateRequiredField($this->members['toaccountholderid'], 'toaccountholderid');
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        $this->validateRequiredField($this->members['xau'], 'xau');
        $this->validateRequiredField($this->members['refno'], 'refno');

        return true;
    }

    /**
     * Gets current status in text format
     * 
     * @return string
     */
    public function getStatusString()
    {
        switch ($this->members['status']) {
            case self::STATUS_PENDING:
                return gettext("Pending");
                break;
            case self::STATUS_SUCCESS:
                return gettext("Success");
                break;
            case self::STATUS_FAILED:
                return gettext("Failed");
                break;
            case self::STATUS_REQUIREAPPROVAL:
                return gettext("Require Approval");
                break;
            case self::STATUS_TIMEOUTAPPROVAL:
                return gettext("Timeout Approval");
                break;
            case self::STATUS_REJECTAPPROVAL:
                return gettext("Approval Rejected");
                break;
            default:
                return "";
        }
    }
}
