<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;
/**
 * Encapsulates the service table on the database
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name                 Type                Description
 * @property-read       int                 $id                     ID of the system
 * @property            string              $refno                  Refno
 * @property            int                 $redemptionid           ID of the redemption
 * @property            int                 $productid              ID of the product
 * @property            float               $commissionfee          Commission Fee of product
 * @property            float               $premiumfee             Premium Fee of product
 * @property            float               $handlingfee            Handling Fee of product
 * @property            float               $courierfee             Courier Fee of conversion
 * @property            enum                $logisticfeepaymentmode Payment mode selected for logistic fee payment
 * @property            string              $campaigncode           Campaign code entered by user
 * @property            int                 $accountholderid        The account holder id that requested this conversion
 * @property            int                 $status                 The status for this conversion.  (In Progress / Completed / Cancelled)
 * @property            DateTime            $createdon              Time this record is created
 * @property            DateTime            $modifiedon             Time this record is last modified
 * @property            int                 $createdby              User ID
 * @property            int                 $modifiedby             User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyConversion extends SnapObject
{

    const LOGISTIC_FEE_PAYMENT_MODE_CONTAINER = 'CONTAINER';
    const LOGISTIC_FEE_PAYMENT_MODE_FPX = 'FPX';
    const LOGISTIC_FEE_PAYMENT_MODE_WALLET = 'WALLET';
    const LOGISTIC_FEE_PAYMENT_MODE_GOLD = 'GOLD';
    const LOGISTIC_FEE_PAYMENT_MODE_CASA = 'CASA';
    
    const STATUS_PAYMENT_PENDING = 0;
    const STATUS_PAYMENT_PAID = 1;
    const STATUS_EXPIRED = 2;
    const STATUS_PAYMENT_CANCELLED = 3;
    const STATUS_REVERSED = 4;

    /**
     * This method will initialise the array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's constructor.
     *
     * @return void
     */
    protected function reset()
    {
        $this->members = array(
            'id' => null,
            'refno' => null,
            'redemptionid' => null,
            'productid'    => null,
            'commissionfee'    => null,
            'campaigncode' => null,
            'premiumfee'    => null,
            'handlingfee'    => null,
            'courierfee'    => null,
            'logisticfeepaymentmode' => null,
            'campaigncode' => null,
            'accountholderid' => null,
            'status' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->viewMembers = array(
            'rdmpartnerid' => null,
            'rdmbranchid' => null,
            'rdmbranchname' => null,
            'rdmsalespersonid' => null,
            'rdmpartnerrefno' => null,
            'rdmredemptionno' => null,
            'rdmapiversion' => null,
            'rdmtype' => null,
            'rdmsapredemptioncode' => null,
            'rdmredemptionfee' => null,
            'rdminsurancefee' => null,
            'rdmhandlingfee' => null,
            'rdmspecialdeliveryfee' => null,
            'rdmtotalweight' => null,
            'rdmtotalquantity' => null,
            'rdmtotalfee' => null,
            'rdmitems' => null,
            'rdmbookingon' => null,
            'rdmbookingprice' => null,
            'rdmbookingpricestreamid' => null,
            'rdmconfirmon' => null,
            'rdmconfirmby' => null,
            'rdmconfirmpricestreamid' => null,
            'rdmconfirmprice' => null,
            'rdmconfirmreference' => null,
            'rdmdeliveryaddress1' => null,
            'rdmdeliveryaddress2' => null,
            'rdmdeliveryaddress3' => null,
            'rdmdeliveryaddress' => null,
            'rdmdeliverycity' => null,
            'rdmdeliverypostcode' => null,
            'rdmdeliverystate' => null,
            'rdmdeliverycountry' => null,
            'rdmdeliverycontactname1' => null,
            'rdmdeliverycontactname2' => null,
            'rdmdeliverycontactno1' => null,
            'rdmdeliverycontactno2' => null,
            'rdmappointmentbranchid' => null,
            'rdmappointmentdatetime' => null,
            'rdmappointmenton' => null,
            'rdmappointmentby' => null,
            'rdmreconciled' => null,
            'rdmreconciledon' => null,
            'rdmreconciledby' => null,
            'rdmstatus' => null,

            'accountholdercode' => null,
            'accountholdername' => null,
            'accounttype' => null,
            'accounttypename' => null,
            'accountholdermykadno' => null,
            'accountholderemail' => null,
            'accountholderphoneno' => null,

            'pdtamount' => null,
            //'pdtpaymentrefno' => null,
            'pdtgatewayrefno' => null,
            'pdtsourcerefno' => null,
            'pdtsigneddata' => null,
            'pdtlocation' => null,
            'pdtgatewayfee' => null,
            'pdtcustomerfee' => null,
            'pdttoken' => null,
            'pdtstatus' => null,
            'pdttransactiondate' => null,
            //'pdtrequestedon' => null,
            'pdtsuccesson' => null,
            'pdtfailedon' => null,
            'pdtrefundedon' => null,

            'partnername' => null,
            'partnercode' => null
        );
    }

    public function getRefNo($forPaymentDetail = false)
    {
        $refno = $this->members['refno'];
        if ($forPaymentDetail) {
            $refno = ctype_alpha($refno[-1]) ? substr($refno, 0, -1) : $refno;
        }

        return $refno;
    }

    /**
     * Returns the redemption object related to this conversion
     * @return Redemption | null
     */
    public function getRedemption() : Redemption
    {
        $redemption = null;
        if (0 < $this->members['redemptionid']) {
            $redemption = $this->getStore()->getRelatedStore('redemption')->getById($this->members['redemptionid']);
        }

        return $redemption;
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid()
    {
        $this->validateRequiredField($this->members['redemptionid'], 'redemptionid');
        $this->validateRequiredField($this->members['logisticfeepaymentmode'], 'logisticfeepaymentmode');
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        $this->validateRequiredField($this->members['productid'], 'productid');

        return true;
    }

    /**
     * Returns a readable status text
     */
    public function getStatusText()
    {
        switch ($this->members['status']) {
            case self::STATUS_PAYMENT_PENDING   :
                return gettext("Pending");
            case self::STATUS_PAYMENT_PAID      :
                return gettext("Paid");
            case self::STATUS_EXPIRED           :
                return gettext("Expired");
            case self::STATUS_PAYMENT_CANCELLED :
                return gettext("Cancelled");
            case self::STATUS_REVERSED          :
                return gettext("Reversed");
            default:
                return "";
        }
    }
}
