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
 * @property       	int   			    $providerid        	tag category
 * @property       	int        			$productcategoryid     		code used for this record
 * @property       	int       			$currencyid      	description of this record
 * @property        float      			$pricepergram    		The status for this staff.  (Active / Inactive)
 * @property       	int       			$pricesourceid     		Time this record is created
 * @property       	DateTime  			$pricesourceon   		Time this record is last modified
 * @property       	DateTime   			$createdon     		Time this record is created
 * @property       	int        			$createdby     		Time this record is created
 * @property       	DateTime 	   		$modifiedon    		Time this record is last modified
 * @property       	int         		$modifiedby    		Time this record is last modified
 * @property       	int       			$status    		    The status for this vendor.  (Active / Inactive)
 *
 * @author Ang
 * @version 1.0
 * @created 2019/1/17 9:30 AM
 */
class PriceStream extends SnapObject {

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
		$this->members = [
			'id' => null,
			'providerid' => null,
			'providerpriceid' => null,
			'priceadjusterid' => null,
			'uuid' => '',
			//'productcategoryid' => null,
			'currencyid' => null,
			'companybuyppg' => null,
			'companysellppg' => null,
			'rawfxusdbuy' => null,
			'rawfxusdsell' => null,
			'rawfxsource' => null,
			//'pricepergram' => null,
			'pricesourceid' => null,
			'pricesourceon' => null,
			'createdon' => null,
			'createdby' => null,
			'modifiedon' => null,
			'modifiedby' => null,
			'status' => null
		];
		$this->viewMembers = [
			'categoryname' => null,
			'pricesourcename' => null,
			'providername' => null,
			'providercode' => null,
            'createdbyname' => null,
            'modifiedbyname' => null,
		];

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

		$this->validateMandatoryField($this->members['providerid'], 'Provider id is mandatory', 'providerid');
		$this->validateMandatoryField($this->members['currencyid'], 'Currency id is mandatory', 'currencyid');
		$this->validateMandatoryField($this->members['pricesourceid'], 'Price Source id is mandatory', 'pricesourceid');


		return true;
	}

    /**
     * We will generate the validityref after obtaining the ID for the pricevalidation object.
     *
     * @param  IEntity $latestCopy The last copy of the object
     * @return void
     */
    public function onCompletedUpdate(IEntity $latestCopy)
    {
        if (get_class($this) == get_class($latestCopy) &&
            0 == $this->members['id'] && 0 != $latestCopy->id) {
            parent::onCompletedUpdate($latestCopy);
            // $this->members['id'] = $latestCopy->id;
			$this->members['uuid'] = 'PS' . strtoupper(str_pad(dechex($this->members['providerid']))) . strtoupper(str_pad(dechex($latestCopy->id), 16, "0", STR_PAD_LEFT));
			$this->getStore()->save($this, ['uuid']);
        }
        return true;
    }

    public function __set($nm, $val) {
        if('uuid' == $nm) {
            return false;
        }
        return parent::__set($nm, $val);
    }
}
?>
