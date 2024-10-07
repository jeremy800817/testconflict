<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2023
 * @copyright Silverstream Technology Sdn Bhd. 2023
 */
Namespace Snap\object;

/**
* Encapsulates the OtcOutstandingStorageFeeJob table on the database
*
* @property-read     int           $id				Primary key
* @property          string        $accountholderid	Account Holder ID
* @property          string        $refno          	Payment Ref No
* @property          int           $code           	Payment Code
* @property          string        $desc           	Payment Description
* @property          int           $status			status
* @property          string        $createdby		USER ID
* @property          DateTime      $createdon		DateTime
* @property          string        $modifiedby		USER ID
* @property          DateTime      $modifiedon		DateTime
*
* @author   AmirNazhan <amirnazhan.nizar@silverstream.my>
* @version  1.0
* @package  data object
*/
class OtcOutstandingStorageFeeJob extends SnapObject {
    
	const STATUS_FAILED = 2;
	
	const DEDUCTTYPE_Q1 = 'QUARTER 1 DEDUCT';
	const DEDUCTTYPE_Q2 = 'QUARTER 2 DEDUCT';
	const DEDUCTTYPE_MONTHLY = 'MONTHLY DEDUCT';
	const DEDUCTTYPE_CRONJOB = 'AUTO CRONJOB DEDUCT';
	const DEDUCTTYPE_MANUAL = 'MANUAL DEDUCT';
	
	
    /**
    * Initialisation of the class.  Overwritten the base class method.
    *
    * @access   public
    * @return   void
    */
    function reset() {
        $this->members = array(
            'id' => 0,
            'accountholderid' => '',
            'refno' => '',
            'code' => '',
            'desc' => '',
            'amount' => '',
            'xau' => '',
            'deducttype' => '',
            'status' => '',
            'createdby' => '',
            'createdon' => '',
            'modifiedby' => '',
            'modifiedon' => ''
        );
		
		$this->viewMembers = array(
            'achpartnercusid' => null,
            'achfullname' => null,
            'achmykadno' => null,
			'achaccountnumber' => null,
			'achaccountholdercode' => null,
            'achbranch' => null,
            'achtype' => null,
            'achno' => null, 
            'pdtamount' => null,
            'pdtsourcerefno' => null,
            'msfaccruedmonthlyfee' => null
        );
    }

    /**
    * Check if all values in $this->members array is valid
    *
    * Check if all values in $this->members array is valid (eg. integer can only contain numbers)
    *
    * @access   public
    * @return   true if all member data has valid values. Otherwise false.
    */
    function isValid() {
        return true;
    }

}
?>