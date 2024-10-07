<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;
Use Snap\object\SnapObject;

/**
 * Encapsulates the tag table on the database
 *
 * This class encapsulates the tag table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$ID 				ID of the system
 * @property       	string 				$code        	tag category
 * @property       	string     			$name     		code used for this record
 * @property       	int       			$pricesourceid       	description of this record
 * @property        int       			$pullmode   		The status for this staff.  (Active / Inactive)
 * @property       	int       			$currencyid    		Time this record is created
 * @property       	string     			$whitelistip   		Time this record is last modified
 * @property       	string     			$url    		Time this record is created
 * @property       	string    			$connectinfo   		Time this record is last modified
 * @property       	int       			$lapsetimeallowance   		Time this record is created
 * @property       	int       			$providergroupid    		Price provider group ID
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
class PriceProvider extends SnapObject {

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
			'code' => null,
			'name' => null,
			'pricesourceid' => null,
			'productcategoryid' => null,
			'pullmode' => null,
			'currencyid' => null,
			'whitelistip' => null,
			'url' => null,
			'connectinfo' => null,
			'lapsetimeallowance' => null,
			'futureorderstrategy' => null,
			'futureorderparams' => null,
			'index' => null,
			'providergroupid' => null,
			'createdon' => null,
			'createdby' => null,
			'modifiedon' => null,
			'modifiedby' => null,
			'status' => null
		);

		$this->viewMembers = [
			'providergroupcode' => null,
			'currencycode' => null,
			'productcategoryname' => null,
			'pricesourcecode' => null,
			'currencycode' => null,
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

	private function validateIndexField($value, ?string $message, ?string $key): void
	{
		if (empty($value)) {
			$this->members[$key] = $this->members['id'];
		}
	}

	/**
	 * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
	 * valid state, the method will return false. Otherwise it will return true.
	 *
	 * @return boolean True if it is a valid object.  False otherwise.
	 */
	public function isValid() {
		$this->validateIndexField($this->members['index'], 'Price Provider index is mandatory', 'index');
		$this->validateMandatoryField($this->members['name'], 'Name is mandatory', 'name');
		$this->validateMandatoryField($this->members['code'], 'Code is mandatory', 'code');
		$this->validateMandatoryField($this->members['pricesourceid'], 'Price source id is mandatory', 'pricesourceid');
		$this->validateMandatoryField($this->members['productcategoryid'], 'Product Category id is mandatory', 'productcategoryid');
		$this->validateMandatoryField($this->members['currencyid'], 'Currency id is mandatory', 'currencyid');
		$this->validateMandatoryField($this->members['connectinfo'], 'Connect Info is mandatory', 'connectinfo');
        if( ! class_exists($this->members['futureorderstrategy'])) {
        	throw new InputException(gettext('The class does not exists'), InputException::FIELD_ERROR, 'futureorderstrategy');
        }
        $matchingStrategy = new $this->members['futureorderstrategy'];
        if(! $matchingStrategy instanceof \Snap\IFutureOrderMatchStrategy) {
            $this->log("The strategy matching class set in price provider {$provider->id} is not valid", SNAP_LOG_ERROR);
        	throw new InputException(gettext('The future order matching strategy provided is invalid.'), InputException::FIELD_ERROR, 'futureorderstrategy');
        }
		return true;
	}

	public function isPriceDataFresh(\Snap\object\PriceStream $data)
	{
		$now = time();
		if($this->members['lapsetimeallowance'] < (time() - $data->createdon->format('U'))) {
			return false;
		}
		return true;
	}

	public function orderMatchingStrategy()
	{
		$matchingStrategy = null;
        if( class_exists($this->members['futureorderstrategy'])) {
            $matchingStrategy = new $this->members['futureorderstrategy'];
	        if(! $matchingStrategy instanceof \Snap\IFutureOrderMatchStrategy) {
	            $this->log("The strategy matching class set in price provider {$provider->id} is not valid", SNAP_LOG_ERROR);
	        }
        }
        return $matchingStrategy;
	}
}
?>
