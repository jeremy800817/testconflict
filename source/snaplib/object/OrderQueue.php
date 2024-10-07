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
 * Encapsulates the orderqueue table on the database
 *
 * This class encapsulates the orderqueue table data
 * information
 *
 * Data members:
 * Name			  	    Type				Description
 * @property-read 	int 		 		$ID 				      ID of the system
 * @property        int         $orderid          order id
 * @property       	int 			  $partnerid        partner id
 * @property       	int     		$buyerid     		  user id
 * @property        int         $partnerrefid     partner reference id
 * @property        int         $orderqueueno     order queue no
 * @property       	int         $salespersonid    User ID
 * @property        string     	$apiversion   		API version
 * @property        enum        $ordertype        Order Type (Order::CompanyBuy,Order::CompanySell,Order::CompanyBuyBack)
 * @property       	enum       	$queuetype    		Queue type (Day, GoodTillDate, GoodTillCancel)
 * @property       	DateTime    $expireon   		  Expire date
 * @property       	int         $productid    		Product ID
 * @property       	float     	$pricetarget   		Price Target
 * @property       	int       	$byweight   		  By Weight
 * @property       	float     	$xau    		      By price
 * @property       	float      	$amount   		    Amount
 * @property       	string     	$remarks   		    Remarks
 * @property        DateTime    $cancelon         Cancel Date
 * @property        int         $cancelby         User ID
 * @property        int         $matchpriceid     Match Price ID
 * @property        DateTime    $matchon          Match Date
 * @property        string      $notifyurl        Notify URL
 * @property        string      $notifymatchurl   Match Notify Url
 * @property        string      $successnotifyurl Success Notify Url
 * @property        boolean     $reconciled       Reconciliation status (if applicable)
 * @property        DateTime    $reconciledon     Reconciliation date
 * @property        int         $reconciledby     Reconciliation person
 * @property       	DateTime   	$createdon     		date data created
 * @property       	int        	$createdby     		USER id
 * @property       	DateTime 	   $modifiedon    	date data modified
 * @property       	int         $modifiedby    		user id
 * @property       	int       	$status    		    status
 *
 * @author Ang
 * @version 1.0
 * @created 2019/1/17 9:30 AM
 */
class OrderQueue extends SnapObject {

    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_FULLFILLED = 2;
    const STATUS_MATCHED = 3;
    const STATUS_PENDINGCANCEL = 4;
    const STATUS_CANCELLED = 5;
    const STATUS_EXPIRED = 6;

    const OTYPE_COMPANYBUY = 'CompanyBuy';
    const OTYPE_COMPANYSELL = 'CompanySell';
    const OTYPE_COMPANYBUYBACK = 'CompanyBuyBack';
    const OTYPE_REDEMPTION = 'Redemption';

    const QTYPE_DAY = 'Day';
    const QTYPE_GOODTILLDATE = 'GoodTillDate';
    const QTYPE_GOODTILLCANCEL = 'GoodTillCancel';

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
            'partnerid' => null,
            'buyerid' => null,
            'partnerrefid' => null,
            'orderqueueno' => null,
            'salespersonid' => null,
            'apiversion' => null,
            'ordertype' => null,
            'queuetype' => null,
            'effectiveon' => null,
            'expireon' => null,
            'productid' => null,
            'pricetarget' => null,
            'byweight' => null,
            'xau' => null,
            'amount' => null,
            'remarks' => null,
            'cancelon' => null,
            'cancelby' => null,
            'matchpriceid' => null,
            'matchon' => null,
            'notifyurl' => null,
            'notifymatchurl' => null,
            'successnotifyurl' => null,
            'reconciled' => null,
            'reconciledon' => null,
            'reconciledby' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null
		);

        $this->viewMembers = array(
            'orderno' => null,
            'pricesourceid' => 0,
            'salespersonname' => null,
            'buyername' => null,
            'productname' => null,
            //'confirmbyname' => null,
            'cancelbyname' => null,
            'reconciledbyname' => null,
            'partnername' => null,
            'partnercode' => null,
            'companybuyppg' => null,
            'companysellppg' => null,
            //'productcode' => null,
            //'partnercode' => null,
            //'statusname' => null,
            'createdbyname' => null,
            'modifiedbyname' => null,
            //'reconciled' => null,

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
    	$this->validateMandatoryField($this->members['partnerid'], 'Partner id is mandatory', 'partnerid');
    	$this->validateMandatoryField($this->members['partnerrefid'], 'Partner reference id is mandatory', 'partnerrefid');
    	$this->validateMandatoryField($this->members['productid'], 'Partner reference id is mandatory', 'productid');
    	$this->validateMandatoryField($this->members['ordertype'], 'Order Type is mandatory', 'ordertype');
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
    public function getSalesPerson() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('user')->searchTable()->select()
                ->where('id', $this->members['salespersonid']);
            $result = $select->execute();
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
}
?>
