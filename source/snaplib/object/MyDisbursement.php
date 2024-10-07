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
 * @property            float               $amount             Amount of disbursement
 * @property            int                 $bankid             Bank id requested for this disbursement
 * @property            string              $bankrefno          Bank reference number for disbursement
 * @property            string              $accountname        Bank account holder name for disbursement
 * @property            string              $accountnumber      Bank account number for disbursement
 * @property            string              $refno              Reference number of disbursement
 * @property            string              $acebankcode        The bank code used to pay
 * @property            float               $fee                Fee charged for the disbursement
 * @property            int                 $accountholderid    The id of account holder requesting for this
 * @property            int                 $status             Status for this disbursement.  (In Progress / Completed / Cancelled)
 * @property            string              $gatewayrefno       Gateway reference number from m2e
 * @property            string              $transactionrefno   Ref number of transaction
 * @property            string              $location           URL for the payment
 * @property            string              $productdesc        The description line of the product
 * @property            string              $token              Token returned by the payment gateway
 * @property            string              $signeddata         The token returned by the payment gateway
 * @property            string              $remarks            The remarks for this disbursement
 * @property            DateTime            $requestedon        Time disbursement was requested
 * @property            DateTime            $disbursedon        Time disbursement was fulfilled
 * @property            DateTime            $cancelledon        Time disbursement was cancelled
 * @property            float               $verifiedamount     Check amount from duitnow
 * @property            DateTime            $createdon          Time this record is created
 * @property            DateTime            $modifiedon         Time this record is last modified
 * @property            int                 $createdby          User ID
 * @property            int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyDisbursement extends SnapObject
{

    const STATUS_IN_PROGRESS = 0;
    const STATUS_COMPLETED   = 1;
    const STATUS_CANCELLED   = 2;

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
            'amount' => null,
            'bankid' => null,
            'bankrefno' => null,
            'accountname' => null,
            'accountnumber' => null,
            'acebankcode' => null,
            'fee' => null,
            'refno' => null,
            'accountholderid' => null,
            'status' => null,
            'gatewayrefno' => null,
            'transactionrefno' => null, 
            'location' => null,
            'productdesc' => null,
            'token' => null,
            'signeddata' => null,
            'remarks' => null,
            'requestedon' => null,
            'disbursedon' => null,
            'cancelledon' => null,
            'verifiedamount' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->viewMembers = array(
            'accpartnerid' => null,
            'accmykadno' => null,
            'accemail' => null,
            'bankswiftcode' => null,
            'accountholdercode' => null,
            'ordorderno' => null /*no need to add to view table*/
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
        $this->validateRequiredField($this->members['amount'], 'amount');
        $this->validateRequiredField($this->members['bankid'], 'bankid');
        $this->validateRequiredField($this->members['accountname'], 'accountname');
        $this->validateRequiredField($this->members['accountnumber'], 'accountnumber');
        $this->validateRequiredField($this->members['transactionrefno'], 'transactionrefno');
        $this->validateRequiredField($this->members['refno'], 'refno');
        // $this->validateRequiredField($this->members['fee'], 'fee');
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        // $this->validateRequiredField($this->members['requestedon'], 'requestedon');

        return true;
    }

    /**
     * Get the status text of this disbursement object
     *
     * @return string
     */
    public function getStatusString()
    {
        switch ($this->members['status']) {
            case self::STATUS_IN_PROGRESS:
                return 'In Progress';
                break;
            case self::STATUS_COMPLETED:
                return 'Completed';
                break;
            case self::STATUS_CANCELLED:
                return 'Cancelled';
                break;
            default:
                return 'In Progress';
                break;
        }
    }
}
