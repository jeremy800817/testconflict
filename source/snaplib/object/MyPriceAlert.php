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
 * @property        enum                $type               The type of pricealert
 * @property        float               $amount             The amount in RM of price per gram
 * @property        float               $lastprice          The last price triggered
 * @property        int                 $triggered          Price alert triggered or not
 * @property        string              $remarks            Account holder remarks for price alerts
 * @property        int                 $accountholderid    Account holder id
 * @property        int                 $status             The status of this price alert
 * @property        DateTime            $lasttriggeredon    Lastest date and time this alert was triggered
 * @property        DateTime            $senton             Lastest date and time this alert notification was sent
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyPriceAlert extends SnapObject
{

    const TYPE_BUY  = 'CompanySell';
    const TYPE_SELL = 'CompanyBuy';

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
            'amount' => null,
            'remarks' => null,
            'accountholderid' => null,
            'priceproviderid' => null,
            'status' => null,
            'lasttriggeredon' => null,
            'triggered' => null,
            'lastprice' => null,
            'senton' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->viewMembers = array(
            'accountholdercode' => null,
            'accountholdermykadno' => null,
            'accountholderfullname' => null,
            'accountholderpartnerid' => null,
            'priceprovidercode' => null,
            'priceprovidername' => null,
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
        $this->validateRequiredField($this->members['amount'], 'amount');
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        $this->validateRequiredField($this->members['priceproviderid'], 'priceproviderid');

        return true;
    }

    public function getAccountHolder()
    {
        return $this->getStore()
            ->getRelatedStore('myaccountholder')
            ->searchTable()
            ->select()
            ->where('id', $this->members['accountholderid'])
            ->one();
    }

    /**
     * Check if the type of price alert is Buy
     *
     * @return boolean
     */
    public function isBuyAlert()
    {
        return self::TYPE_BUY === $this->members['type'];
    }

    /**
     * Check if the type of price alert is Sell
     *
     * @return boolean
     */
    public function isSellAlert()
    {
        // If not buy alert then it is sell alert
        return !$this->isBuyAlert();
    }


}
