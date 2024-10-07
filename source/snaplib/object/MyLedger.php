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
 * Name             Type        Description
 * @property-read   int         $id                 ID of the system
 * @property        enum        $type               The type of ledger
 * @property        int         $typeid             ID of the source object
 * @property        int         $partnerid          ID of partner
 * @property        int         $accountholderid    AccountHolder Id
 * @property        float       $debit              The amount debitted to the account holder
 * @property        float       $credit             The amount creditted to the account holder
 * @property        string      $refno              The refno this ledger associated with
 * @property        string      $remarks            Remarks or description of this ledger
 * @property        int         $status             The status of this ledger
 * @property        DateTime    $transactiondate    The date of this transaction
 * @property        DateTime    $createdon          Time this record is created
 * @property        DateTime    $modifiedon         Time this record is last modified
 * @property        int         $createdby          User ID
 * @property        int         $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyLedger extends SnapObject
{

    const TYPE_BUY_FPX = 'BUY_FPX';
    const TYPE_BUY_CONTAINER = 'BUY_CONTAINER';
    const TYPE_SELL = 'SELL';
    const TYPE_CONVERSION = 'CONVERSION';
    const TYPE_CONVERSION_FEE = 'CONVERSION_FEE';
    const TYPE_STORAGE_FEE = 'STORAGE_FEE';
	const TYPE_BUY_CASA = 'BUY_CASA';
	const TYPE_SELL_CASA = 'SELL_CASA';

    const TYPE_REFUND_DG = 'REFUND_DG';
    const TYPE_CREDIT    = 'CREDIT';
    const TYPE_DEBIT     = 'DEBIT';
    const TYPE_TRANSFER  = 'TRANSFER';
    const TYPE_PROMO     = 'PROMO';

    const TYPE_VAULT_IN  = 'VAULT_IN';
    const TYPE_VAULT_OUT = 'VAULT_OUT';
    const TYPE_ACESELL   = 'ACESELL';
    const TYPE_ACEBUY    = 'ACEBUY';
    const TYPE_ACEREDEEM = 'ACEREDEEM';

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
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
            'typeid' => null,
            'partnerid' => null,
            'accountholderid' => null,
            'debit' => null,
            'credit' => null,
            'refno' => null,
            'remarks' => null,
            'status' => null,
            'transactiondate' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->viewMembers = array(
            'achaccountholdercode' => null,
            'achfullname' => null,
            'achmykadno' => null,
            'partnername' => null,
            'partnercode' => null,
            'ordgoldprice' => null,            
            'ordsaprefno' => null,            
            'amountin' => null,
            'amountout' => null,
            'amountbalance' => null,
            'xaubalance' => null,
            // 'createdbyname' => null,
            // 'modifiedbyname' => null,
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
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        $this->validateRequiredField($this->members['partnerid'], 'partnerid');
        $this->validateRequiredField($this->members['debit'], 'debit');
        $this->validateRequiredField($this->members['credit'], 'credit');
        $this->validateRequiredField($this->members['refno'], 'refno');
        $this->validateRequiredField($this->members['transactiondate'], 'transactiondate');
        $this->validateRequiredField($this->members['status'], 'status');

        return true;
    }
}
