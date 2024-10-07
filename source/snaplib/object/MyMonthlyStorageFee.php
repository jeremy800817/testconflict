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
 * @property            int                 $pricestreamid      Pricestream Id
 * @property            int                 $productid          Product Id
 * @property            float               $xau                The value of xau in gram calculated
 * @property            float               $price              The value of gold price per gram 
 * @property            float               $amount             The value of amount calculated
 * @property            float               $adminfeexau        The XAU value of admin fee
 * @property            float               $storagefeexau      The XAU value of storage fee
 * @property            int                 $accountholderid    The account holder id
 * @property            string              $refno              Running number for storage fee
 * @property            int                 $status             The status of this object
 * @property            DateTime            $chargedon          The date this storage fee is charged
 * @property            DateTime            $createdon          Time this record is created
 * @property            DateTime            $modifiedon         Time this record is last modified
 * @property            int                 $createdby          User ID
 * @property            int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2021/03/31 1:15 PM
 */
class MyMonthlyStorageFee extends SnapObject
{
    public const TYPE_ADMIN_AND_STORAGE_FEE = 'StorageFee'; 
    
    const STATUS_PENDING = 0;  // Charged but not sent to SAP
    const STATUS_COMPLETE = 1; // SAP success
    const STATUS_FAILED = 2; // SAP Failed
    const STATUS_PAID = 3;

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
            'pricestreamid' => null,
            'xau' => null,
            'price' => null,
            'amount' => null,
            'adminfeexau' => null,
            'storagefeexau' => null,
            'accountholderid' => null,
			'accruedmonthlyfee' => null,
            'status' => null,
            'refno' => null,
            'chargedon' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->viewMembers = array(
            'achfullname' => null,
            'achaccountholdercode' => null,
            'achmykadno' => null,
            'partnerid' => null,
            'partnercode' => null,
            'partnername' => null, 
            'ledcurrentxau' => null,
            'adminfeeamount' => null,
            'storagefeeamount' => null,
            'achpartnercusid' => null,
            'achaccountnumber' => null,
            'achaccountholderid' => null,
            'achaccountholderid' => null,
            'apjcreatedon' => null,
            'apjmodifiedon' => null,
            'apjstatus' => null,
            'pdtamount' => null
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
        $this->validateRequiredField($this->members['xau'], 'xau');
        $this->validateRequiredField($this->members['price'], 'price');
        $this->validateRequiredField($this->members['amount'], 'amount');
        $this->validateRequiredField($this->members['adminfeexau'], 'adminfeexau');
        $this->validateRequiredField($this->members['storagefeexau'], 'storagefeexau');
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        $this->validateRequiredField($this->members['pricestreamid'], 'pricestreamid');
        $this->validateRequiredField($this->members['chargedon'], 'chargedon');

        return true;
    }
}
