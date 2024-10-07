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
 * @property        float               $open               The first price for the date
 * @property        float               $close              The last price for the date
 * @property        float               $high               The highest price for the date
 * @property        float               $low                The lowest price for the date
 * @property        int                 $priceproviderid    The id of price provider used for this price
 * @property        int                 $status             Status of this price record
 * @property        DateTime            $priceon            Datetime of the price recorded
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyHistoricalPrice extends SnapObject
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
            'id'              => null,
            'open'            => null,
            'close'           => null,
            'high'            => null,
            'low'             => null,
            'priceproviderid' => null,
            'status'          => null,
            'priceon'         => null,
            'createdon'       => null,
            'modifiedon'      => null,
            'createdby'       => null,
            'modifiedby'      => null,
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
        $this->validateRequiredField($this->members['open'], 'open');
        $this->validateRequiredField($this->members['close'], 'close');
        $this->validateRequiredField($this->members['high'], 'high');
        $this->validateRequiredField($this->members['low'], 'low');
        $this->validateRequiredField($this->members['priceproviderid'], 'priceproviderid');
        $this->validateRequiredField($this->members['priceon'], 'priceon');


        return true;
    }
}
