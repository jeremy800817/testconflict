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
 * Encapsulates the order table on the database
 *
 * This class encapsulates the order table data
 * information
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                           ID of the table
 * @property        int                 $partnerid                    Partner ID
 * @property        int                 $buyerid                      User ID
 * @property        int                 $partnerrefid                 Partner Reference Id
 * @property        int                 $orderno                      Order No
 * @property        int                 $pricestreamid                Price stream id
 * @property        int                 $salespersonid                user id
 * @property        string              $apiversion                   api version
 * @property        enum                $type                         Type (CompanyBuy,CompanySell,CompanyBuyBack)
 * @property        int                 $productid                    Product id
 * @property        int                 $isspot                       Is spot (1/0)
 * @property        float               $price                        amount of gold price
 * @property        int                 $byweight                     By weight
 * @property        float               $xau                          amount of weight
 * @property        float               $amount                       amount of order_amount EXCLUDING fees
 * @property        float               $fee                          amount of fees , can be refinery / premium
 * @property        float               $totalamount                  'no need' amount of final_order_amount including fees = from view['amount+fee']
 * @property        string              $remarks                      Remarks
 * @property        DateTime            $bookingon                    Booking date
 * @property        float               $bookingprice                 Booking price
 * @property        int                 $bookingpricestreamid         Booking Price stream id
 * @property        DateTime            $confirmon                    Confirm date
 * @property        int                 $confirmby                    User ID
 * @property        float               $confirmpricestreamid         Confirm Pricestreamud
 * @property        float               $confirmprice                 Confirm price
 * @property        DateTime            $cancelon                    cancel date
 * @property        int                 $cancelby                    User ID
 * @property        float               $cancelpricestreamid         cancel Pricestreamud
 * @property        float               $cancelprice                 cancel price
 * @property        boolean             $reconciled                   Reconciliation status (if applicable)
 * @property        DateTime            $reconciledon                 Reconciliation date
 * @property        int                 $reconciledby                 Reconciliation person
 * @property        DateTime            $createdon                    DATE
 * @property        int                 $createdby                    User ID
 * @property        DateTime            $modifiedon                   DATE
 * @property        int                 $modifiedby                   User ID
 * @property        int                 $status                       Status
 *
 * @author  Ang <ang@silverstream.my>
 * @version 1.0
 * @package Snap\object
 */
class Order extends SnapObject
{
    const STATUS_PENDING = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_PENDINGPAYMENT = 2;
    const STATUS_PENDINGCANCEL = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_COMPLETED = 5;
    const STATUS_EXPIRED = 6;

    const TYPE_COMPANYBUY = 'CompanyBuy';
    const TYPE_COMPANYSELL = 'CompanySell';
    const TYPE_COMPANYBUYBACK = 'CompanyBuyBack';

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
            'buyerid' => null,
            'partnerrefid' => null,
            'orderno' => null,
            'pricestreamid' => null,
            'salespersonid' => null,
            'apiversion' => null,
            'type' => null,
            'productid' => null,
            'isspot' => null,
			'partnerprice' => null,
            'price' => null,
            'byweight' => null,
            'xau' => null,
            'amount' => null,
            'fee' => null,
            'discountprice' => null,
            'discountinfo' => null,
            'remarks' => null,
            'bookingon' => null,
            'bookingprice' => null,
            'bookingpricestreamid' => null,
            'confirmon' => null,
            'confirmby' => null,
            'confirmpricestreamid' => null,
            'confirmprice' => null,
            'confirmreference' => null,
            'cancelon' => null,
            'cancelby' => null,
            'cancelpricestreamid' => null,
            'cancelprice' => null,
            'notifyurl' => null,
            'reconciled' => null,
            'reconciledon' => null,
            'reconciledby' => null,
            'reconciledsaprefno' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null,

        ];

        $this->viewMembers = [
            'uuid' => null,
            'fpprice' => null,
            'feetypename' => null,
            'salespersonname' => null,
            'buyername' => null,
            'productname' => null,
            'confirmbyname' => null,
            'cancelbyname' => null,
            'reconciledbyname' => null,
            'partnername' => null,
            'productcode' => null,
            'partnercode' => null,
            'priceprovidercode' => null,
            'partnerbuycode1' => null,
            'partnersellcode1' => null,
            'statusname' => null,
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
    
    private function validateMandatoryField_Amount($value, ?string $message, ?string $key): void
    {
        if ($value <= 0) {
            throw new InputException(gettext($message), InputException::FIELD_ERROR, $key);
        }
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid(): bool
    {
        $this->validateMandatoryField($this->members['partnerid'], 'Partner id is mandatory', 'inventorycatid');
        $this->validateMandatoryField($this->members['partnerrefid'], 'Partner reference id is mandatory', 'partnerrefid');
        $this->validateMandatoryField($this->members['orderno'], 'Order number is mandatory', 'orderno');
        $this->validateMandatoryField($this->members['pricestreamid'], 'Product stream is mandatory', 'pricestreamid');
        $this->validateMandatoryField($this->members['productid'], 'Product is mandatory', 'productid');
        $this->validateMandatoryField($this->members['type'], 'Type is mandatory', 'type');
        $this->validateMandatoryField_Amount($this->members['amount'], 'Amount cannot be 0, please contact administrative.', 'amount'); // cannot less than 0

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
     * get list of price source
     *
     * @return array    result[]    get list of of price source
     */
    public function getPriceSource() {

        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('pricestream')->searchTable()->select()
                ->where('id', $this->members['pricesourceid']);
            $result = $select->first();
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
            $result = $select->one();
        }
        return $result;
    }

    /**
     * get product
     *
     * @return array    result[]    get product
     */
    public function getOrderQueue() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('orderqueue')->searchTable()->select()
                ->where('orderid', $this->members['id']);
            $result = $select->execute();
        }
        return $result;
    }

    public function isCompanyBuy()
    {
        return self::TYPE_COMPANYBUY == $this->members['type'];
    }

    public function isCompanySell()
    {
        return self::TYPE_COMPANYSELL == $this->members['type'];
    }

    public function isCompanyBuyBack()
    {
        return self::TYPE_COMPANYBUYBACK == $this->members['type'];
    }
}
?>
