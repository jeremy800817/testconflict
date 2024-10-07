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
 * Encapsulates the tag link table on the database
 *
 * This class encapsulates the tag link table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$ID 				ID of the system
 * @property       	int    				$tagid       		tag id
 * @property       	enum    			$sourcetype       	source type for ASSET, INVENTORY, PATIENT, SERVICE
 * @property       	int    				$sourceid       	source if for ASSET, INVENTORY, PATIENT, SERVICE
 *
 * @author Megat
 * @version 1.0
 * @created 2017/4/17 2:33 PM
 */

class TagLink extends SnapObject {

	const ASSET_SOURCE = 'ASSET';
	const INVENTORY_SOURCE = 'INVENTORY';
	const PATIENT_SOURCE = 'PATIENT';
	const SERVICE_SOURCE = 'SERVICE';

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
			'tagid' => null,
			'sourcetype' => null,
			'sourceid' => null
		);
		$this->viewMembers = array(
			'tagcode' => null
		);
	}

	/**
	 * This method will return the tag object that is stored
	 * @return Tag 
	 */
	public function getTag() {
		return $this->getStore()->getRelatedStore('tag')->getById($this->members['tagid']);
	}

	/**
	 * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a 
	 * valid state, the method will return false. Otherwise it will return true.
	 * 
	 * @return boolean True if it is a valid object.  False otherwise.
	 */
	public function isValid() {
		switch($this->members['sourcetype']) {
			case self::ASSET_SOURCE:
			case self::INVENTORY_SOURCE:
			case self::PATIENT_SOURCE:
			case self::SERVICE_SOURCE:
				break;
			default:
				throw new InputException(gettext('Invalid source type defined for the taglink object'), InputException::FIELD_ERROR, 'sourcetype');
		}
		return true;
	}
}
?>