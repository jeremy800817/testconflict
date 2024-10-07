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
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$ID 				ID of the system
 * @property       	int   				$replenishmentid     	ID of replenishment
 * @property       	int   				$logisticid     		ID of logistic
 * @property       	DateTime   			$createdon     		Time this record is created
 * @property       	int        			$createdby     		User id
 * @property       	DateTime 	   		$modifiedon    		Time this record is last modified
 * @property       	int         		$modifiedby    		User id
 *
 * @version 1.0
 */
class ReplenishmentLogistic extends SnapObject {
    
	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
	protected function reset() {
		$this->members = [
			'id' => null,
			'replenishmentid' => null,
			'logisticid' => null,
			'createdon' => null,
			'createdby' => null,
			'modifiedon' => null,
			'modifiedby' => null,
		];
	}
	
	public function isValid(){
		return true;
	}
}
