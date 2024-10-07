<?php
/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021
*/
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;

/**
* Encapsulates the goodsreceivenoteorder table on the database
*
* This class encapsulates the goodsreceivenoteorder table data
* information
*
* Data members:
* Name				Type				Description
* @property-read 	int 		 		$ID 					ID of the system
* @property       	int 			    $orderid        		Order id
* @property       	int       			$goodsreceivenoteid 	Good Receive Note id
* @property       	DateTime   			$createdon     			Time this record is created
* @property       	int        			$createdby     			User id
* @property       	DateTime 	   		$modifiedon    			Time this record is last modified
* @property       	int         		$modifiedby    			User id
* @property       	int       			$status    		    	Status.  (Active / Inactive)
*
*/
class GoodsReceivedNoteDraft extends SnapObject {
    /**
    * This method will initialise the 2 array members of this class with the definition of fields to be used
    * by the object.  This method will be called in the object's contractor.
    *
    * @return None
    */
    protected function reset() {
        $this->members = array(
            'id' => null,
            'goodreceivednoteorderid' => null,
            'branchid' => null,
            'referenceno' => null,
            'product' => null,
            'purity' => null,
            'weight' => null,
            'details' => null,
            'gtpxauweight' => null, // recalculate with sap purity
            'desc' => null,
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
    public function isValid() {
        $this->validateMandatoryField($this->members['goodreceivednoteorderid'], 'Good Received Note Order id is mandatory', 'goodreceivednoteorderid');
        // $this->validateMandatoryField($this->members['branchid'], 'Branch id is mandatory', 'branchid');
        // $this->validateMandatoryField($this->members['purity'], 'Purity is mandatory', 'purity');

        return true;
    }


}
?>
