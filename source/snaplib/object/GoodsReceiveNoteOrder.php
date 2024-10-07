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
 * @author Ang
 * @version 1.0
 * @created 2019/1/17 9:30 AM
 */
class GoodsReceiveNoteOrder extends SnapObject {

	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
	protected function reset() {
		$this->members = array(
			'id' => null,
			'orderid' => null,
			'buybackid' => null,
      		'goodsreceivenoteid' => null,
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
		// $this->validateMandatoryField($this->members['orderid'], 'Order id is mandatory', 'orderid');
		// $this->validateMandatoryField($this->members['goodsreceivenoteid'], 'Good Receive Note id is mandatory', 'goodsreceivenoteid');

		return true;
	}

	/**
     * get order
     *
     * @return array    result[]    get order
     */
    public function getOrder() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('order')->searchTable()->select()
                ->where('id', $this->members['orderid']);
            $result = $select->execute();
        }
        return $result;
    }

    /**
     * get good receive note id
     *
     * @return array    result[]    get good receive note
     */
    public function getReceiveNote() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('goods_receive_note')->searchTable()->select()
                ->where('id', $this->members['goodsreceivenoteid']);
            $result = $select->execute();
        }
        return $result;
    }

    /**
     * get good receive note DRAFT id
     *
     * @return array    result[]    get good receive note
     */
    public function getReceiveNoteDraft() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('goodsreceivenotedraft')->searchTable()->select()
                ->where('goodsreceivenotedraftid', $this->members['id'])
                ->andWhere('status', GoodsReceivedNoteDraft::STATUS_ACTIVE);
            $result = $select->execute();
        }
        return $result;
    }
}
?>
