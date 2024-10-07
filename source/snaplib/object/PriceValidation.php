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
 * @property       	int   				$partnerid        	partnerid
 * @property       	int        			$pricestreamid      price stream id
 * @property       	string     			$apiversion         api version
 * @property       	string     			$validityref     	validity reference
 * @property       	string     			$requestedtype     	Request Type
 * @property       	Decimal    			$premiumfee     	Premium fee
 * @property       	Decimal    			$refineryfee     	Refinary fee
 * @property        Decimal             $price              Refinary fee
 * @property       	DateTime  			$validtill    		Date of valid till
 * @property       	int        			$orderid    		Order ID
 * @property       	string     			$reference    		Reference
 * @property       	DateTime   			$timestamp    		Date of timestamp
 * @property       	DateTime   			$createdon     		Time this record is created
 * @property       	int        			$createdby     		User id
 * @property       	DateTime 	   		$modifiedon    		Time this record is last modified
 * @property       	int         		$modifiedby    		User id
 * @property       	int       			$status    		    The status for this price validation.  (Active / Inactive)
 *
 * @author Ang
 * @version 1.0
 * @created 2019/1/17 9:30 AM
 */
class PriceValidation extends SnapObject {
	const REQUEST_COMPANYBUY = 'CompanyBuy';
    const REQUEST_COMPANYSELL = 'CompanySell';
	const REQUEST_REDEMPTION = 'Redemption';
	
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
			'partnerid' => null,
			'pricestreamid' => null,
			'apiversion' => null,
			'uuid' => null,
			'requestedtype' => null,
			'premiumfee' => null,
			'refineryfee' => null,
            'price' => null,
			'validtill' => null,
			'orderid' => null,
			'reference' => null,
			'timestamp' => null,
			'createdon' => null,
			'createdby' => null,
			'modifiedon' => null,
			'modifiedby' => null,
			'status' => null,
		];
		$this->viewMembers = [
			'partnername' => null,
			'partnercode' => null,
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

		$this->validateMandatoryField($this->members['partnerid'], 'Partner id is mandatory', 'partnerid');
		$this->validateMandatoryField($this->members['pricestreamid'], 'Price Stream id is mandatory', 'pricestreamid');

		return true;
	}

    /**
     * We will generate the uuid after obtaining the ID for the pricevalidation object.
     *
     * @param  IEntity $latestCopy The last copy of the object
     * @return void
     */
    public function onCompletedUpdate(IEntity $latestCopy)
    {
        if (get_class($this) == get_class($latestCopy) &&
            0 == $this->members['id'] && 0 != $latestCopy->id) {
            parent::onCompletedUpdate($latestCopy);
            //Generate a validity ref id
            $partnerCode = $this->getStore()->getRelatedStore('partner')->getById($this->members['partnerid'])->code;
            $partnerCode = $partnerCode[0] . $partnerCode[strlen($partnerCode)-1];
            $this->members['uuid'] = 'PV' . $partnerCode . strtoupper(str_pad(dechex($latestCopy->id), 10, "0", STR_PAD_LEFT));
            $this->getStore()->save($this, ['uuid']);
        }
        return true;
    }

    public function __set($nm, $val) {
        if('uuid' == $nm && 0 < $this->members['id']) {
            return false;
        }
        return parent::__set($nm, $val);
    }
}
?>
