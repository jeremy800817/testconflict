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
 * @property-read 	int 		 		$ID 				ID of the table
 * @property       	int   				$categoryid        	tag category
 * @property       	string    			$code      			Product code for API
 * @property       	string     			$name      			Name
 * @property        int        			$companycansell		Buy Limit
 * @property       	int       			$companycanbuy		Sell Limit
 * @property       	int       			$trxbyweight		Transaction by weigh
 * @property       	int        			$trxbycurrency    	Transaction by Currency/RM
 * @property       	int       			$deliverable    	Product Deliverable
 * @property       	string    			$sapitemcode   		SAP code
 * @property       	DateTime   			$createdon     		Time this record is created
 * @property       	int        			$createdby     		ID of the user created
 * @property       	DateTime 	   		$modifiedon    		Time this record is last modified
 * @property       	int         		$modifiedby    		ID of the user modified
 * @property       	int       			$status    		    Status
 *
 * @author Ang
 * @version 1.0
 * @created 2019/1/17 9:30 AM
 */
class Product extends SnapObject {
	const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    
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
			'categoryid' => null,
			'code' => null,
			'name' => null,
			'weight' => null,
			'companycansell' => null,
			'companycanbuy' => null,
			'trxbyweight' => null,
			'trxbycurrency' => null,
			'deliverable' => null,
			'sapitemcode' => null,
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

		$this->validateMandatoryField($this->members['categoryid'], 'categoryid is mandatory', 'categoryid');
		$this->validateMandatoryField($this->members['code'], 'code is mandatory', 'code');
		$this->validateMandatoryField($this->members['sapitemcode'], 'sapitemcode is mandatory', 'sapitemcode');

		return true;
	}

	public function denominationOrderChecking($orderWeight){
		$orderWeight = floatval($orderWeight);
		if ($orderWeight <= 0){
			return false;
		}
		if (($this->members['code'] == 'DG-999-9' && $this->members['weight'] == 0) || ($this->members['weight'] == 0)){
			return true;
		}
        return !boolval(fmod($orderWeight, $this->members['weight']));
    }
}
?>
