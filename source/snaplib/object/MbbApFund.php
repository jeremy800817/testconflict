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
 * ApFund Class
 *
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                 ID of the table
 * @property        int                 $partnerid          ID of partner
 * @property        enum                $operationtype      Type of operation
 * @property        int                 $orderid            ID of order
 * @property        decimal             $beginprice         Start price
 * @property        int                 $beginpriceid       ID start price
 * @property        decimal             $endprice           End price
 * @property        int                 $endpriceid         End price ID
 * @property        decimal             $amountppg          Amount
 * @property        decimal             $amount             Amount
 *
 * @author  Calvin <calvin.thien@ace2u.com>
 * @version 1.0
 * @package Snap\object
 */
class MbbApFund extends SnapObject
{

    const TYPE_ORDERCONFIRM = 'OrderConfirm';
    const TYPE_ORDERREVERSE = 'OrderReverse';

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * This method will initialise the 2 array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's contractor.
     * @return void
     */
    protected function reset(): void
    {
        $this->members = [
            'id' => null,
            'partnerid' => null,
            'operationtype' => null,
            'orderid' => null,
            'p1buyprice' => null,
            'p1sellprice' => null,
            'p2buyprice' => null,
            'p2sellprice' => null,
            'p3buyprice' => null,
            'p3sellprice' => null,
            'p1pricestreamid' => null,
            'p2pricestreamid' => null,
            'p3pricestreamid' => null,
            'p1priceon' => null,
            'p2priceon' => null,
            'p3priceon' => null,
            'beginprice' => null,
            'beginpriceid' => null,
            'endprice' => null,
            'endpriceid' => null,
            'amountppg' => null, //  diff of anp gold price
            'amount' => null, //  diff of anp order amount => (order_weight * anp_gold_price)
            'status' => null,
            'remarks' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null
        ];

        $this->viewMembers = [
            'ordercreatedon' => null,
            'orderxau' => null,
            'orderno' => null,
            'ordertype' => null,
            // 'createdbyname' => null,
            // 'modifiedbyname' => null,

        ];
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid(): bool
    {
        //Operation type is mandatory
        /*
        if(empty($this->members['operationtype']) || $this->members['operationtype'] == 0) {
            throw new InputException(gettext('The IP field is mandatory'), InputException::FIELD_ERROR, 'operationtype');}
        */
        //order id is mandatory
        if(empty($this->members['orderid']) || 0 == strlen($this->members['orderid'])) {
            throw new InputException(gettext('The orderid field is mandatory'), InputException::FIELD_ERROR, 'orderid');}
        //amount is mandatory
        // if(empty($this->members['amount']) || 0 == strlen($this->members['amount'])) {
        //     throw new InputException(gettext('The amount field is mandatory'), InputException::FIELD_ERROR, 'amount');}

        return true;
    }


    /**
     * get partner
     *
     * @return array    result[]    get partner
     */
    public function getPartnerID() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('partnerservice')->searchTable()->select()
                ->where('id', $this->members['partnerid']);
            $result = $select->execute();
        }
        return $result;
    }

    /**
     * get order ID
     *
     * @return array    result[]    get order ID
     */
    public function getOrderID() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('order')->searchTable()->select()
                ->where('id', $this->members['orderid']);
            $result = $select->execute();
        }
        return $result;
    }


}
?>
