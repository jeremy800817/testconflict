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
 * @property        int                 $partnerid          Original amount for gold
 * @property        int                 $senderid           Settelement method for buy
 * @property        int                 $receiverid         Salespersoncode entered by account holder
 * @property        string              $receiveremail      The campaigncode entered by account hoder
 * @property        string              $receivername       The transaction reference number
 * @property        decimal             $xau                The order id this gold transaction belongs to
 * @property        string              $contact            Status of gold transaction
 * @property        DateTime            $expireon           Time when the transaction completed
 * @property        int                 $status             Time when the transaction cancelled
 * @property        string              $message            Time when the transaction failed
 * @property        string              $sendercode         Time when the transaction failed
 * @property        string              $receivercode       Time when the transaction failed
 * @property        DateTime            $transferon         Time when the transaction failed
 * @property        DateTime            $cancelon           Time when the transaction failed
 * @property        string              $type               Time when the transaction failed
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID

 *
 * @author Dianah
 * @version 1.0
 * @created 2022/04/07 2:56 PM
 */
class MyGoldInviteTransfer extends SnapObject
{

    const TYPE_INVITE   = 'INVITE';
    const TYPE_TRANSFER = 'TRANSFER';

    const STATUS_PENDING   = 0;   
    const STATUS_ACTIVE    = 1;   
    const STATUS_INACTIVE  = 2;   

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
            'senderid' => null,
            'receiverid' => null,
            'receiveremail' => null,
            'receivername' => null,
            'xau' => null,
            'price' => null,
            'amount' => null,
            'contact' => null,
            'expireon' => null,
            'status' => null,
            'message' => null,
            'sendercode' => null,
            'receivercode' => null,
            'transferon' => null,
            'cancelon' => null,
            'type' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->viewMembers = array(
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
        $this->validateRequiredField($this->members['partnerid'], 'partnerid');
        $this->validateRequiredField($this->members['senderid'], 'senderid');
        $this->validateRequiredField($this->members['receivername'], 'receivername');
        $this->validateRequiredField($this->members['receiveremail'], 'receiveremail');

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
            case self::STATUS_ACTIVE:
                return gettext("Active");
                break;
            case self::STATUS_INACTIVE:
                return gettext("Inactive");
                break;
            default:
                return "";
        }
    }

}
