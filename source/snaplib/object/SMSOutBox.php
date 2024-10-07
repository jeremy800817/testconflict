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
 * @property       	string    			$phoneno       		Receiver Contact
 * @property       	string    			$msg       			Received message Content
 * @property       	DateTime   			$senttime       	Time of message
 * @property        string     			$reference       	reference of provided
 * @property       	string    			$msgtype     		Type of message
 * @property       	string    			$operator    		Operator
 * @property       	string     			$errormsg     		Recorded Error
 * @property       	int       			$retrycount    		Number of retry count
 * @property       	string    			$rawresponse   		response
 * @property       	DateTime   			$createdon     		Time this record is created
 * @property       	int        			$createdby     		ID of user
 * @property       	DateTime 	   		$modifiedon    		Time this record is last modified
 * @property       	int         		$modifiedby    		ID of user
 * @property       	int       			$status    		    status
 *
 * @author Ang
 * @version 1.0
 * @created 2019/1/17 9:30 AM
 */
class SMSOutBox extends SnapObject {

	/**
	 * Keeps a temporary store of the vendors.
	 * @var array
	 */
	// private $inventorycat = array();

	const TYPE_SYSTEM = 'System';
	const TYPE_USER = 'User';
	const STATUS_SUCCESS = 1;
	const STATUS_FAILED = 0;

	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
	protected function reset() {
		$this->members = array(
			'id' => null,
			'phoneno' => null,
			'msg' => null,
			'senttime' => null,
			'reference' => null,
			'msgtype' => null,
			'operator' => null,
			'errormsg' => null,
			'retrycount' => null,
	      	'rawresponse' => null,
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

		$this->validateMandatoryField($this->members['phoneno'], 'Phone number is mandatory', 'phoneno');
				$this->validateMandatoryField($this->members['reference'], 'Reference is mandatory', 'reference');

		return true;
	}
}
?>
