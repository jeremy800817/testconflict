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
 * @property       	int 				$partnerid        	tag category
 * @property       	int       			$partnersapgroup    		code used for this record
 * @property       	int       			$productid       	description of this record
 * @property        int       			$pricesourcetypeid   		The status for this staff.  (Active / Inactive)
 * @property       	float      			$refineryfee    		Time this record is created
 * @property       	float     			$premiumfee   		Time this record is last modified
 * @property       	int       			$includefeeinprice    		Time this record is created
 * @property       	int        			$canbuy   		Time this record is last modified
 * @property       	int       			$cansell   		Time this record is created
 * @property       	int        			$canqueue   		Time this record is last modified
 * @property       	float       		$buyclickminxau    		    Buy Click Min XAU
 * @property       	float       		$buyclickmaxxau    		    Buy Click Max XAU
 * @property       	float       		$sellclickminxau    		Sell Click Min XAU
 * @property       	float       		$sellclickmaxxau    		Sell Click Max XAU
 * @property       	float      			$dailybuylimitxau   		Time this record is created
 * @property       	float      			$dailyselllimitxau   		Time this record is last modified
 * @property        float               $redemptionpremiumfee  Premium fee for redemption
 * @property        float               $redemptioncommission  Commission fee for redemption
 * @property        float               $redemptioninsurancefee  Commission fee for redemption
 * @property        float               $redemptionhandlingfee  Handling fee for redemption
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
class PartnerService extends SnapObject {

	const OPTION_YES = 1;
	const OPTION_NO = 0;

	const SPECIALTYPE_NONE = 'NONE';
    const SPECIALTYPE_AMOUNT = 'AMOUNT';
    const SPECIALTYPE_GRAM = 'GRAM';
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
			'partnerid' => null,
      		'productid' => null,
      		'partnersapgroup' => null,
			'refineryfee' => null,
			'premiumfee' => null,
			'includefeeinprice' => null,
      		'canbuy' => null,
			'cansell' => null,
			'canqueue' => null,
			'canredeem' => null,
			'buyclickminxau' => null,
			'buyclickmaxxau' => null,
			'sellclickminxau' => null,
			'sellclickmaxxau' => null,
			'dailybuylimitxau' => null,
            'dailyselllimitxau' => null,
            'redemptionpremiumfee' => null,
            'redemptioncommission' => null,
            'redemptioninsurancefee' => null,
            'redemptionhandlingfee' => null,
			'specialpricetype' => null,
            'specialpricecondition' => null,
            'specialpricecompanybuyoffset' => null,
            'specialpricecompanyselloffset' => null,
			'createdon' => null,
			'createdby' => null,
			'modifiedon' => null,
			'modifiedby' => null,
			'status' => null
		);

		$this->viewMembers = [
            'partnername' => null,
            'partnercode' => null,
            'productname' => null,
			'productcode' => null,
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
		if (0 == strlen($value)) {
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
		//print_r($this->members);
		$product = $this->getStore()
							->getRelatedStore('product')
							->searchTable()
							->select()
							->where('id', $this->members['productid'])
							->one();
		if(! $product->status) {
			throw new InputException(sprintf(gettext('The product (%s) register for service is not active.'), $product->code), 'productid');
		}
        //Make sure that the code is unique.
        $res = $this->getStore()
	        			->searchTable()
	        			->select()
	        			->where('productid', '=', $this->members['productid'])
	        			->andWhere('partnerid', $this->members['partnerid']);
        if($this->members['id']) {
            $res = $res->andWhere('id', '!=', $this->members['id']);
        }
        $data = $res->count();
        if ($data) {
            throw new InputException(sprintf(gettext('Unable to add 2 identifical product service to the same merchant'), $this->members['productid']), InputException::FIELD_ERROR, 'code');
        }

		$this->validateMandatoryField($this->members['partnerid'], 'Partner Id is mandatory', 'partnerid');
        $this->validateMandatoryField($this->members['partnersapgroup'], 'Partner SAP group is mandatory', 'partnersapgroup');

		return true;
	}

	public function canBuy()
	{
		return 1 == $this->members['canbuy'];
	}

	public function canSell()
	{
		return 1 == $this->members['cansell'];
	}

	public function canQueue()
	{
		return 1 == $this->members['canqueue'];
	}

	public function canRedeem()
	{
		return 1 == $this->members['canredeem'];
	}

	public function includeFeeInPrice()
	{
		return 1 == $this->members['includefeeinprice'];
	}
}
?>
