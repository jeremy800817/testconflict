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
 * Encapsulates the goodsreceivenote table on the database
 *
 * This class encapsulates the goodsreceivenote table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$ID 				   ID of the system
 * @property       	int 			    $partnerid        	   Partner id
 * @property       	int       			$salespersonid         User id
 * @property        string     			$comments   		   Comments
 * @property       	string     			$jsonpostpayload       Json
 * @property       	float       		$totalxauexpected      Total xau Expected
 * @property       	float      			$totalgrossweight      Total gross weight
 * @property       	float     			$totalxaucollected     Total xau collected
 * @property       	float      			$vatsum   		       Vatsum
 * @property       	DateTime   			$createdon     		   Time this record is created
 * @property       	int        			$createdby     		   User id
 * @property       	DateTime 	   		$modifiedon    		   Time this record is last modified
 * @property       	int         		$modifiedby    		   User id
 * @property       	int       			$status    		       Status.  (Active / Inactive)
 *
 * @author Ang
 * @version 1.0
 * @created 2019/1/17 9:30 AM
 */
class GoodsReceiveNote extends SnapObject {

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
      		'salespersonid' => null,
			'comments' => null,
      		'jsonpostpayload' => null,
			'totalxauexpected' => null,
			'totalgrossweight' => null,
			'totalxaucollected' => null,
      		'vatsum' => null,
      		'createdon' => null,
      		'createdby' => null,
      		'modifiedon' => null,
      		'modifiedby' => null,
      		'status' => null
		);

        $this->viewMembers = [
          
            'partnername' => null,
            'partnercode' => null,
            'salespersonname' => null,
            'salespersonemail' => null,
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
            $result = $select->execute();
        }
        return $result;
    }

    /**
     * get Sales person
     *
     * @return array    result[]    get sales person
     */
    public function geSalesPerson() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('user')->searchTable()->select()
                ->where('id', $this->members['salespersonid']);
            $result = $select->execute();
        }
        return $result;
    }

    /**
     * get good receive note order
     *
     * @return array    result[]    get good receive note order
     */
    public function getGoodReceiveNoteOrder() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('goodsreceivenoteorder')->searchTable()->select()
                ->where('goodsreceivenoteid', $this->members['id']);
            $result = $select->execute();
        }
        return $result;
    }
}
?>
