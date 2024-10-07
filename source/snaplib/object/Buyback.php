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
 * @property       	int   			    $partnerid    	    partner ID
 * @property       	string   			$partnerrefid    	partner reference
 * @property       	int   			    $branchid    	    branch ID, where item to be collect
 * @property       	int   			    $order    	        order ID, which the buyback process order ID
 * @property       	int   			    $productid    	    product ID, which identify the item
 * @property       	int   			    $serialno    	    minted bar serial number
 * @property       	DateTime   			$collectedon    	logistic collected time
 * @property       	int        			$collectedby    	logistic collected by (system refid)
 * @property       	int       			$status    		    The status for this price validation.  (Active / Inactive)
 * @property       	DateTime   			$createdon     		Time this record is created
 * @property       	int        			$createdby     		User id
 * @property       	DateTime 	   		$modifiedon    		Time this record is last modified
 * @property       	int         		$modifiedby    		User id
 *
 * @version 1.0
 */
class Buyback extends SnapObject {

    const STATUS_PENDING = 0; // init data
    const STATUS_CONFIRMED = 1; // after SAP
    const STATUS_PROCESSCOLLECT = 2; 
    const STATUS_COMPLETED = 3; 
    const STATUS_FAILED = 4; // mostly from SAP FAILED
    const STATUS_REVERSED = 5; 

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
			'partnerrefno' => null,
			'apiversion' => null,
			'branchid' => null,
			'buybackno' => null,
			'productid' => null,
			'pricestreamid' => null,
			'price' => null,
			'totalweight' => null,
			'totalamount' => null,
			'totalquantity' => null,
			'fee' => null,
			'items' => null,
			'remarks' => null,
			'bookingon' => null,
			'bookingprice' => null,
			'bookingpricestreamid' => null,
			'confirmpricestreamid' => null,
			'confirmprice' => null,
			'confirmon' => null,
			'collectedon' => null,
			'collectedby' => null,
			'confirmon' => null,
			'collectedon' => null,
			'collectedby' => null,
			'reconciled' => null,
            'reconciledon' => null,
            'reconciledby' => null,
            'reconciledsaprefno' => null,
			'status' => null,
			'createdon' => null,
			'createdby' => null,
			'modifiedon' => null,
			'modifiedby' => null,
		];

		$this->viewMembers = [
			'branchname' => null,
			'branchcode' => null,
			'branchsapcode' => null,
			'partnername' => null,
			'partnercode' => null,
			'statusname' => null,
            'createdbyname' => null,
            'modifiedbyname' => null,
		];
	}
	
	public function isValid(){
		return true;
	}

	/**
     * get partner
     *
     * @return array    result[]    get partner
     */
    public function getPartner() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('partner')->searchTable()->select()
                ->where('id', $this->members['partnerid']);
            $result = $select->one();
        }
        return $result;
    }

    /**
     * get product
     *
     * @return array    result[]    get product
     */
    public function getProduct() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('product')->searchTable()->select()
                ->where('id', $this->members['productid']);
            $result = $select->execute();
        }
        return $result;
    }

	// to submit SAP => different endpoint
    public function fromMbb($app){
        $mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};
        if ($this->members['partnerid'] == $mbbpartnerid){
            return true;
        }
        return false;
    }
}
