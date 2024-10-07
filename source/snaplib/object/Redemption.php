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
 * Redemption Class
 *
 *
 * Data members:
 * Name             Type				Description
 * @property-read   int                 $id                     ID of the table
 * @property        int                 $partnerid              ID IN partner 
 * @property        int                 $branchid               ID IN the Partner branch map
 * @property        int                 $salespersonid          ID IN User
 * @property        int                 $partnerrefno           Number of reference partner
 * @property        string              $redemptionno           Number of redemption
 * @property        string              $apiversion     	    API version
 * @property        enum                $type                   Type of redemption
 * @property        int                 $productid              ID IN the product
 * @property        Decimal             $redemptionfee          Redemption fee
 * @property        Decimal             $insurancefee           Insurance fee
 * @property        Decimal             $handlingfee            Product handling fee
 * @property        Decimal             $specialdeliveryfee     Delivery fee
 * @property        String              $xaubrand               Source of product
 * @property        String              $xauserialno            Product serial number
 * @property        Decimal             $xau                    Product category
 * @property        Decimal             $fee                    Redemption fee
 * @property        \DateTime           $bookingon              Time of request received
 * @property        Decimal             bookingprice            Price of gold
 * @property        \DateTime           bookingpricestreamid    ID IN price stream
 * @property        \DateTime           confirmon               Time of request confirmed
 * @property        int                 confirmby               User ID
 * @property        Decimal             confirmpricestreamid    ID IN price stream
 * @property        decimal             confirmprice          Final price
 * @property        string              confirmreference        Reference
 * @property        string              deliveryaddress1        Address of delivery
 * @property        string              deliveryaddress2        Address of delivery
 * @property        string              deliveryaddress3        Address of delivery
 * @property        string              deliverypostcode        Postcode of delivery address
 * @property        string              deliverystate           State of delivery address
 * @property        string              deliverycontactno       Contact of receiver
 * @property        string              inventory               Inventory of item
 * @property        \DateTime           processedon             Time of request process start
 * @property        \DateTime           deliveredon             Time of delivered
 * @property        \DateTime           $createdon              Time of request created
 * @property        int                 $createdby              User ID
 * @property        \DateTime           $modifiedon             Time this record is last modified
 * @property        int                 $modifiedby             User ID
 * @property        int                 $status                 Status
 * @property        int                 $remarks                Remarks
 *
 * @author  Calvin <calvin.thien@ace2u.com>
 * @version 1.0
 * @package Snap\object
 */
class Redemption extends SnapObject
{
    const STATUS_PENDING = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_FAILED = 3;
    const STATUS_PROCESSDELIVERY = 4; // update when create logistic tracker, only after confirmed
    const STATUS_CANCELLED = 5;
    const STATUS_REVERSED = 6;
    const STATUS_FAILEDDELIVERY = 7;
    const STATUS_SUCCESS = 8; // NEW FOR MIB FOR SAP RESERVED - TIMEOUT ISSUE

    const TYPE_BRANCH = 'Branch';
    const TYPE_DELIVERY = 'Delivery';
    const TYPE_SPECIALDELIVERY = 'SpecialDelivery';
    const TYPE_APPOINTMENT = 'Appointment';

    const GTPSTATUS_PENDING = 0;
    const GTPSTATUS_SUCCESS = 1;
    const GTPSTATUS_FAILTRANSFER = 2;
    const GTPSTATUS_PENDINGSAP = 3;

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
            'branchid' => null,
            'salespersonid' => null,
            'partnerrefno' => null,
            'redemptionno' => null,
            'apiversion' => null,
            'type' => null,
            'sapredemptioncode' => null,
            'redemptionfee'=> null,
            'insurancefee' => null,
            'handlingfee' => null,
            'specialdeliveryfee' => null,
            'totalweight' => null,
            'totalquantity' => null,
            'items' => null,
            'bookingon' => null,
            'bookingprice' => null,
            'bookingpricestreamid' => null,
            'confirmon' => null,
            'confirmby' => null,
            'confirmpricestreamid' => null,
            'confirmprice' => null,
            'confirmreference' => null,
            'deliveryaddress1' => null,
            'deliveryaddress2' => null,
            'deliveryaddress3' => null,
            'deliverycity' => null,
            'deliverypostcode' => null,
            'deliverystate' => null,
            'deliverycountry' => null,
            'deliverycontactname1' => null,
            'deliverycontactname2' => null,
            'deliverycontactno1' => null,
            'deliverycontactno2' => null,
            'appointmentbranchid' => null,
            'appointmentdatetime' => null,
            'appointmenton' => null,
            'appointmentby' => null,
            'reconciled' => null,
            'reconciledon' => null,
            'reconciledby' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null,
            'remarks' => null,
            'gtpstatus' => null
        ];

        $this->viewMembers = array(
            'branchname' => null,
            'partnername' => null
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
    public function isValid(): bool
    {
        $this->validateMandatoryField($this->members['partnerid'], 'Partner id is mandatory', 'inventorycatid');
        $this->validateMandatoryField($this->members['partnerrefno'], 'Partner reference no is mandatory', 'partnerrefno');
        $this->validateMandatoryField($this->members['redemptionno'], 'Redemption number is mandatory', 'redemptionno');
        $this->validateMandatoryField($this->members['type'], 'Type is mandatory', 'type');

        return true;
    }

    /**
     * get list of progress status
     *
     * @return array    result[]    get list of progress status
     */
    public function redeemProgressStatus() {

        $arr[] = self::STATUS_PENDING;
        $arr[] = self::STATUS_CONFIRMED;
        $arr[] = self::STATUS_COMPLETED;
        return $arr;
    }

    public function isForRedemotion() {
        return $this->members['type'] == self::TYPE_REDEEM;
    }

    public function isForReplendishment() {
        return $this->members['type'] == self::TYPE_REPLENDISH;
    }

    public function isForReplendishmentPreorder() {
        return $this->members['type'] == self::TYPE_PREORDER;
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

    /**
     * get branch
     *
     * @return array    result[]    get branch
     */
    public function getBranch() {
        $result = array();
        if($this->members['id'] > 0) {
            $select = $this->getStore()->getRelatedStore('partnerBranchMap')->searchTable()->select()
                ->where('id', $this->members['branchid']);
            $result = $select->execute();
        }
        return $result;
    }

    /**
     * get sales person
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
     * Returns a readable status text
     */
    public function getStatusText()
    {
        switch ($this->members['status']) {
            case self::STATUS_PENDING         :
                return "Pending";
            case self::STATUS_CONFIRMED       :
                return "Confirmed";
            case self::STATUS_COMPLETED       :
                return "Completed";
            case self::STATUS_FAILED          :
                return "Failed";
            case self::STATUS_PROCESSDELIVERY : 
                return "Process Delivery";
            case self::STATUS_CANCELLED       :
                return "Cancelled";
            case self::STATUS_REVERSED        :
                return "Reversed";
            default:
                return "";
        }
    }
}
?>