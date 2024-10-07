<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\object;

use Snap\InputException;
use Snap\IEntity;

/**
 * Encapsulates the OtcPricingModel table on the database
 *
 * This class encapsulates the OtcPricingModel table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$id 				   	ID of the system
 * @property       	int       			$priceproviderid        Price Provider id
 * @property       	string       		$name      				Name
 * @property       	float      			$sellmarginpercent		Sell Margin Percent
 * @property       	float     			$buymarginpercent     	Buy Margin Percent
 * @property       	float     			$sellmarginamount     	Sell Margin Amount
 * @property       	float     			$buymarginamount     	Buy Margin Amount
 * @property       	float     			$min					Min amount/gram
 * @property       	float      			$max  					Max amount/gram
 * @property       	string      		$code					Campaign/Promotion Code
 * @property       	string   			$type 					Type
 * @property        DateTime            $starton                Start Date
 * @property        DateTime            $endon                  End Date
 * @property        DateTime            $createdon              Created Date
 * @property        int                 $createdby				Created By User ID
 * @property        DateTime            $modifiedon				Modified Date
 * @property        int                 $modifiedby				Modified By User ID
 * @property        int                 $status					Status
 *
 * @version 1.0
 */
class OtcPricingModel extends SnapObject {

    /**
     * Represents the pricing model type
     */
    const TYPE_AMOUNT = 'AMOUNT';
	const TYPE_CODE = 'CODE';
	const TYPE_STAFF = 'STAFF';
	const TYPE_GRAM = 'GRAM';
    
	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
	protected function reset()
	{
		$this->members = array(
			'id' => null,
			'priceproviderid' => null,
			'name' => null,
			'sellmarginpercent' => null,
			'buymarginpercent' => null,
			'sellmarginamount' => null,
			'buymarginamount' => null,
			'min' => null,
			'max' => null,
			'code' => null,
			'type' => null,
			'starton' => null,
			'endon' => null,
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
	public function isValid()
	{
		$this->validateMandatoryField($this->members['priceproviderid'], 'Price Provider is mandatory', 'priceproviderid');
		$this->validateMandatoryField($this->members['name'], 'Name is mandatory', 'name');
		$this->validateMandatoryField($this->members['type'], 'Type is mandatory', 'type');
		$this->validateMandatoryField($this->members['status'], 'Status is mandatory', 'status');

		return true;
	}
}

?>
