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
 * Encapsulates the goodsreceivenote table on the database
 *
 * This class encapsulates the goodsreceivenote table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$ID 				   	ID of the system
 * @property       	string 			    $uuid        	   		unique id
 * @property       	int       			$priceproviderid        Price Provider id
 * @property       	float       		$fxbuypremium      		Fx buy premium 
 * @property       	float      			$fxsellpremium      	Fx sell premium 
 * @property       	float     			$buymargin     			Buy margin
 * @property       	float     			$sellmargin     		Sell margin
 * @property       	float     			$refinefee     			Refine Fee
 * @property       	float     			$supplierpremium		Supplier premium
 * @property       	float      			$buyspread  			Buy Spread
 * @property       	float      			$sellspread				Sell Spread
 * @property       	DateTime   			$createdon 				Time this record is created
 * @property       	int        			$createdby				User id
 *
 * @version 1.0
 */
class PriceAdjuster extends SnapObject {

    /**
     * Represents the peak tier.
     */
    const TIER_PEAK = 0;

    /**
     * Represents the non-peak tier.
     */
    const TIER_NONPEAK = 1;

    /**
     * Indicates that usage percentage is not used for calculating the bill.
     */
    const TYPE_NOUSEPERCENT = 0;

    /**
     * Indicates that usage percentage is used for calculating the bill.
     */
    const TYPE_USEPERCENT = 1;

    
	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
	protected function reset() {
		$this->members = array(
			'id' => null,
			'uuid' => null,
			'priceproviderid' => null,
			'fxbuypremium' => null,
			'fxsellpremium' => null,
			'buymargin' => null,
			'sellmargin' => null,
			'refinefee' => null,
			'supplierpremium' => null,
			'buyspread' => null,
			'sellspread' => null,
			'effectiveon' => null,
			'effectiveendon' => null,
			'tier' => null,
			'usepercent' => null,
			'buypercent' => null,
			'sellpercent' => null,
			'usespreadcopy' => null,
			'buyspreadoriginal' => null,
			'sellspreadoriginal' => null,
			'createdon' => null,
			'createdby' => null,
		);

		$this->viewMembers = [
            'priceprovidername' => null,
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
		$this->validateMandatoryField($this->members['priceproviderid'], 'Price Provider is mandatory', 'priceproviderid');
		// $this->validateMandatoryField($this->members['fxbuypremium'], 'Fx buy premium is mandatory', 'fxbuypremium');
		// $this->validateMandatoryField($this->members['fxsellpremium'], 'Fx sell premium is mandatory', 'fxsellpremium');
		// $this->validateMandatoryField($this->members['buymargin'], 'Buy margin is mandatory', 'buymargin');
		// $this->validateMandatoryField($this->members['sellmargin'], 'Sell margin is mandatory', 'sellmargin');
		// $this->validateMandatoryField($this->members['refinefee'], 'Refine Fee is mandatory', 'refinefee');
		// $this->validateMandatoryField($this->members['supplierpremium'], 'Supplier premium is mandatory', 'supplierpremium');
		// $this->validateMandatoryField($this->members['buyspread'], 'Buy spread', 'buyspread');
		// $this->validateMandatoryField($this->members['sellspread'], 'Sell spread is mandatory', 'sellspread');

		return true;
	}

    /**
     * Returns an array containing the peak and non-peak tiers.
     *
     * @return array An array containing the peak and non-peak tiers.
     */
    public static function getTier()
    {
        return [self::TIER_PEAK, self::TIER_NONPEAK];
    }
}
?>
