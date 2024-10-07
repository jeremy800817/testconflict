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
 * @property       	int       			$partnerid    		ID IN partner 
 * @property       	string       		$replenishmentno    generated number for replenishment
 * @property       	string       		$sapwhscode    		sap warehouse code
 * @property       	string       		$saprefno    		sap reference code
 * @property       	string       		$type    			replenishment type (current 1 only => 'replenish')
 * @property       	int       			$productid    		replenishment product ID
 * @property       	string       		$serialno    		replenishment item serial no (minted bar)
 * @property       	DateTime   			$schedule     		Time of this record for schedule
 * @property       	DateTime   			$replenishedon     	Time of this record is relpenished / acknowledge recieved
 * @property       	int					$sapresponsestatus  Status from sap notify inventory status.
 * @property       	DateTime   			$sapresponseon     	Time of sap notify inventory updates
 * @property       	DateTime   			$createdon     		Time this record is created
 * @property       	int        			$createdby     		User id
 * @property       	DateTime 	   		$modifiedon    		Time this record is last modified
 * @property       	int         		$modifiedby    		User id
 *
 * @version 1.0
 */
class Replenishment extends SnapObject {
	const TYPE_REPLENISH = 'Replenish';

    const STATUS_PENDING = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_FAILED = 3;
    const STATUS_PROCESSDELIVERY = 4; // update when create logistic tracker, only after confirmed
    const STATUS_CANCELLED = 5;
    const STATUS_RETURNED = 6; //20210511 - can be use if status need to change to return. phyrtn/minted return

	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
	protected function reset() {
		$this->members = [
			'id' => null,
			'partnerid' => null,
			'branchid' => null,
			'salesperson' => null,
			'replenishmentno' => null,
			'sapwhscode' => null,
			'saprefno' => null,
			'type' => null,
            'productid' => null,
			'serialno' => null,
			'status' => null,
			'statusexport' => null,
			'statusexporton' => null,
			'schedule' => null,
			'replenishedon' => null,
			'sapresponsestatus' => null,
			'sapresponseon' => null,
			'returntosapon' => null,
			'returnreason' => null,
			'createdon' => null,
			'createdby' => null,
			'modifiedon' => null,
			'modifiedby' => null,
		];
		$this->viewMembers = array(
			'productname' => null,
			'branchname' => null,
			'branchcode' => null,
			'branchsapcode' => null,
			'partnername' => null,
			'partnercode' => null,
			'salesname' => null,
            'createdbyname' => null,
            'modifiedbyname' => null,
		);
	}
	
	public function isValid(){
		return true;
	}

}
