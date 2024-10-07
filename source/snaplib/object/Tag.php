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
 * @property-read 	int 		 		$ID 				ID of the system
 * @property       	string    			$category       	tag category
 * @property       	string    			$code       		code used for this record
 * @property       	string    			$description      	description of this record
 * @property       	string    			$value           	description of this record
 * @property       	mxDate    			$createdon     		Time this record is created
 * @property       	mxDate    			$modifiedon    		Time this record is last modified
 * @property       	int       			$createdby     		User ID
 * @property       	int       			$modifiedby    		User ID
 * @property       	int       			$status        		The status for this staff.  (Active / Inactive)
 *
 * @author Ang
 * @version 1.0
 * @created 2019/3/17 2:30 PM
 */
class Tag extends SnapObject {

	const PRICESOURCE_CATEGORY = 'PriceSource';
	const PRODUCTCATEGORY_CATEGORY = 'ProductCategory';
	const CURRENCY_CATEGORY = 'Currency';
	const VAULTOWNER_CATEGORY = 'VaultOwner';
	const TRADINGSCHEDULE_CATEGORY = 'TradingSchedule';
	const LOGISTICVENDOR_CATEGORY = 'LogisticVendor';
	const PRICEPROVIDER_CATEGORY = 'PriceProvider';
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
			'category' => null,
			'code' => null,
			'description' => null,
			'value' => null,
			'createdon' => null,
			'createdby' => null,
			'modifiedon' => null,
			'modifiedby' => null,
			'status' => null,
		);

		$this->viewMembers = array(
			//'staffname' => null //from staff
            'createdbyname' => null,
            'modifiedbyname' => null,
		);
	}

	/**
	 * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
	 * valid state, the method will return false. Otherwise it will return true.
	 *
	 * @return boolean True if it is a valid object.  False otherwise.
	 */
	public function isValid() {
		//Make sure only 1 copy of the tag code is available.....
		$myId = ($this->members['id']) ? $this->members['id'] : 0;
		if($this->getStore()->searchTable(['id'])->select()->where('code', $this->members['code'])->andWhere('id', '!=', $myId)->count()) {
			throw new InputException(gettext('The tag code provided is already available in the system'), InputException::FIELD_ERROR, 'code');
		}

		//Tag category is mandatory
		if($this->members['category'] == '') {
			throw new InputException(gettext("Tag category is Mandatory."), InputException::FIELD_ERROR, 'category');
		}

		//Tag code is mandatory
		if($this->members['code'] == '') {
			throw new InputException(gettext("Tag code is Mandatory."), InputException::FIELD_ERROR, 'code');
		}

		//Tag value is mandatory
		if($this->members['value'] == '') {
			throw new InputException(gettext("Tag value is Mandatory."), InputException::FIELD_ERROR, 'value');
		}

		return true;
	}

	/*
        This method to get the listing of Tag Category 
    */
	public function getCategory() {

		$categoryArr = [];
		$lists = [
			self::PRICESOURCE_CATEGORY,
			self::PRODUCTCATEGORY_CATEGORY,
			self::CURRENCY_CATEGORY,
			self::VAULTOWNER_CATEGORY,
			self::TRADINGSCHEDULE_CATEGORY,
			self::LOGISTICVENDOR_CATEGORY,
			self::PRICEPROVIDER_CATEGORY
		];

		foreach ($lists as $key => $value) {
			$categoryArr[] = (object)array("id" => $value, "code" => ucfirst(strtolower($value)));
		}

		return $categoryArr;
	}

}
?>
