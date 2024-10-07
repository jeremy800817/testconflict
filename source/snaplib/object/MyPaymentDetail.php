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
 * @property        int                 $accountholderid    ID of the account holder making this payment
 * @property        float               $amount             Amount of payment
 * @property        string              $paymentrefno       Payment detail paymenrefno
 * @property        string              $gatewayrefno       Payment detail gatewayrefno
 * @property        string              $sourcerefno        The source ref number this payment belongs to
 * @property        string              $signeddata         The data signed sent to payment gateway
 * @property        string              $location           URL returned by the payment gateway
 * @property        float               $gatewayfee         The gateway fee charged for this payment transaction
 * @property        float               $customerfee        The fee charged to customer for this transaction
 * @property        string              $token              The token returned by the payment gateway
 * @property        string              $productdesc        The description line of the product
 * @property        string              $remarks            The remarks for this payment
 * @property        string              $gatewaystatus      The status of this payment in gateway
 * @property        int                 $status             Payment detail status
 * @property        DateTime            $transactiondate    Time the payment transaction happen
 * @property        DateTime            $requestedon        Time the transaction initialized
 * @property        DateTime            $successon          Time from payment gateway for successful transaction
 * @property        DateTime            $failedon           Time from payment gateway for failed transaction
 * @property        DateTime            $refundedon         Time from payment gateway for refunded transaction
 * @property        float               $verifiedamount     Amount from duitnow
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyPaymentDetail extends SnapObject
{
    
    const STATUS_PENDING            = 0;        // Payment created in DB, not created in FPX yet
    const STATUS_SUCCESS            = 1;        // User made payment
    const STATUS_PENDING_PAYMENT    = 2;        // User not yet made payment 
    const STATUS_CANCELLED          = 3;        // User cancelled / did not pay
    const STATUS_FAILED             = 4;        // User tried to pay but was rejected
    const STATUS_REFUNDED           = 5;        // Payment was refunded

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
            'amount' => null,
            'paymentrefno' => null,
            'gatewayrefno' => null,
            'sourcerefno' => null,
            'signeddata' => null,
            'location' => null,
            'gatewayfee' => null,
            'customerfee' => null,
            'token' => null,
            'productdesc' => null,
            'remarks'   => null,
            'gatewaystatus' => null,
            'status' => null,
            'transactiondate' => null,
            'requestedon' => null,
            'successon' => null,
            'failedon' => null,
            'refundedon' => null,
            'verifiedamount' => null,
            'priceuuid' => null,
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
        $this->validateRequiredField($this->members['amount'], 'amount');
        $this->validateRequiredField($this->members['paymentrefno'], 'paymentrefno');
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        // $this->validateRequiredField($this->members['gatewayrefno'], 'gatewayrefno');
        $this->validateRequiredField($this->members['sourcerefno'], 'sourcerefno');
        // $this->validateRequiredField($this->members['signeddata'], 'signeddata');
        // $this->validateRequiredField($this->members['location'], 'location');
        // $this->validateRequiredField($this->members['gatewayfee'], 'gatewayfee');
        // $this->validateRequiredField($this->members['customerfee'], 'customerfee');
        // $this->validateRequiredField($this->members['token'], 'token');
        $this->validateRequiredField($this->members['transactiondate'], 'transactiondate');
        $this->validateRequiredField($this->members['status'], 'status');
        
        return true;
    }

    /**
     * Get status in readable format
     */
    public function getStatusString()
    {
        switch ($this->members['status']) {
            case self::STATUS_PENDING_PAYMENT:
                return "Pending";
            case self::STATUS_SUCCESS:
                return "Successful";
            case self::STATUS_CANCELLED:
            case self::STATUS_FAILED:
                return "Failed";
            case self::STATUS_REFUNDED:
                return "Refunded";
            default:
                return "Unknown";
        }
    }
}
