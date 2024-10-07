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
 * @property        int                 $achid              Account ID 
 * @property        string              $transactiontype    Transaction type
 * @property        string              $gtrrefno           Gtr reference number
 * @property        float               $transactionamount  Transaction Amount
 * @property        float               $xau                Xau
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 * 
 * @author Jeff
 * @version 1.0
 * @created 2021/12/13 6:00 PM
 */
class MyLoanTransaction extends SnapObject
{

    const TYPE_BUY_FPX = 'BUY_FPX';
    const TYPE_BUY_CONTAINER = 'BUY_CONTAINER';
    const TYPE_SELL = 'SELL';
    const TYPE_CONVERSION = 'CONVERSION';
    const TYPE_CONVERSION_FEE = 'CONVERSION_FEE';
    const TYPE_STORAGE_FEE = 'STORAGE_FEE';

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
            'achid' => null,
            'transactiontype' => null,
            'gtrrefno' => null,
            'transactionamount' => null,
            'xau' => null,
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
        $this->validateRequiredField($this->members['achid'], 'achid');
        $this->validateRequiredField($this->members['transactiontype'], 'transactiontype');
        // $this->validateRequiredField($this->members['gtrrefno'], 'gtrrefno');
        $this->validateRequiredField($this->members['transactionamount'], 'transactionamount');
              
        return true;
    }
}
