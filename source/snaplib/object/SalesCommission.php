<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;

/**
 * Encapsulates the tag table on the database
 *
 * This class encapsulates the tag table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$ID 					ID of the table
 * @property       	int    				$salespersonid      	User ID
 * @property       	DateTime    		$startdate       		Start date
 * @property       	DateTime   			$enddate       			End date
 * @property        decimal     		$totalcompanybuy     	Total amount of company buy
 * @property       	decimal   			$totalcompanysell     	Total amount of company sell
 * @property       	decimal   			$totalxau    			Total grams
 * @property       	decimal  			$totalfee     			Total commission fee
 * @property       	DateTime   			$createdon     			Time this record is created
 * @property       	int        			$createdby     			User ID
 * @property       	DateTime 	   		$modifiedon    			Time this record is last modified
 * @property       	int         		$modifiedby    			User ID
 * @property       	int       			$status    			    Status
 *
 * @author Ang
 * @version 1.0
 * @created 2019/1/17 9:30 AM
 */
class SalesComission extends SnapObject {

	/**
	 * Keeps a temporary store of the vendors.
	 * @var array
	 */
	// private $inventorycat = array();

	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
	protected function reset() {
		$this->members = array(
			'id' => null,
			'salespersonid' => null,
			'startdate' => null,
			'enddate' => null,
			'totalcompanybuy' => null,
			'totalcompanysell' => null,
			'totalxau' => null,
			'totalfee' => null,
      		'createdon' => null,
      		'createdby' => null,
      		'modifiedon' => null,
      		'modifiedby' => null,
      		'status' => null
		);

	}

	/**
	 * A validation function where to check mandatory fields
	 *
	 * @internal
	 * @param $value
	 * @param null|string $message
	 * @param null|string $key
	 * @throws InputException
	 */
	private function validateMandatoryField($value, ?string $message, ?string $key): void
	{
		if (empty($value)) {
			throw new InputException(gettext($message), InputException::FIELD_ERROR, $key);
		}
	}

	/**
	 * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
	 * valid state, the method will return false. Otherwise it will return true.
	 *
	 * @return boolean True if it is a valid object.  False otherwise.
	 */
	public function isValid() {

		$this->validateMandatoryField($this->members['salespersonid'], 'Salesperson id is mandatory', 'salespersonid');

		return true;
	}
}
?>
