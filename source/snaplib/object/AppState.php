<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;

/**
 * Encapsulates the service table on the database
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$ID 				ID of the system
 * @property       	int    				$patientid 			patient id
 * @property       	int    				$branchid 			branch id
 * @property       	int    				$servicecatid 		service category id
 * @property       	string    			$name       		service name
 * @property       	string    			$description       	more detail on the service
 * @property       	double    			$price       		price of service
 * @property      	DateTime   			$activeon       	this service is effective on
 * @property      	DateTime   			$inactiveon     	this service is terminates on
 * @property       	int       			$status        		The status for this staff.  (Active / Inactive)
 * @property       	mxDate    			$createdon     		Time this record is created
 * @property       	mxDate    			$modifiedon    		Time this record is last modified
 * @property       	int       			$createdby     		User ID
 * @property       	int       			$modifiedby    		User ID
 *
 * @author Megat
 * @version 1.0
 * @created 2017/4/14 11:16 AM
 */
class AppState extends SnapObject {

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
			'userid' => null,
			'key' => null,
			'value' => null
		);
	}

	/**
	 * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a 
	 * valid state, the method will return false. Otherwise it will return true.
	 * 
	 * @return boolean True if it is a valid object.  False otherwise.
	 */
	public function isValid() {
		return true;
	}

	/**
	 * This method will implement serializing the object into a cacheable string for optimum storage.
	 * @return String
	 */
	function toCache() {
		return $this->members;
	}	
}
?>