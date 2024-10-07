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
 * Name             Type                Description
 * @property-read   int                 $id                 ID of the system
 * @property        float               $originalamount     Original amount for gold
 * @property        enum                $settlementmethod   Settelement method for buy
 * @property        string              $salespersoncode    Salespersoncode entered by account holder
 * @property        string              $campaigncode       The campaigncode entered by account hoder
 * @property        string              $refno              The transaction reference number
 * @property        int                 $orderid            The order id this gold transaction belongs to
 * @property        int                 $status             Status of gold transaction
 * @property        DateTime            $completedon        Time when the transaction completed
 * @property        DateTime            $reversedon         Time when the transaction cancelled
 * @property        DateTime            $failedon           Time when the transaction failed
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 * 
 * Order View
 * @property        int                 $ordpartnerid
 * @property        int                 $ordbuyerid
 * @property        string              $ordorderno
 * @property        string              $ordtype
 * @property        decimal             $ordprice
 * @property        decimal             $ordxau
 * @property        decimal             $ordamount
 * @property        decimal             $ordfee
 * @property        string              $ordremarks
 * @property        DateTime            $ordbookingon
 * @property        DateTime            $ordconfirmon
 * @property        DateTime            $ordcancelon
 * @property        int                 $ordstatus
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyGoldTransaction extends SnapObject
{

    const SETTLEMENT_METHOD_FPX = 'FPX';
    const SETTLEMENT_METHOD_CONTAINER = 'CONTAINER';
    const SETTLEMENT_METHOD_BANKACCOUNT = 'BANK_ACCOUNT';
    const SETTLEMENT_METHOD_WALLET = 'WALLET';
    const SETTLEMENT_METHOD_CASH = 'CASH';
    const SETTLEMENT_METHOD_LOAN = 'LOAN';
    const SETTLEMENT_METHOD_CASA = 'CASA';

    const STATUS_PENDING_PAYMENT = 0;   // Pending payment
    const STATUS_CONFIRMED       = 1;   // SAP success
    const STATUS_PAID            = 2;   // Payment Completed
    const STATUS_FAILED          = 3;   // Payment Failed
    const STATUS_REVERSED        = 4;
    const STATUS_PENDING_REFUND  = 5;
    const STATUS_REFUNDED        = 6;
    const STATUS_PENDING_APPROVAL= 7;   // OTC status for approval
    const STATUS_REJECTED        = 8;   // OTC status for rejection

    const FROMALERT_NO = 1;
    const FROMALERT_YES = 1;

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
            'originalamount' => null,
            'settlementmethod' => null,
            'salespersoncode' => null,
            'campaigncode' => null,
            'extradata' => null,
            'refno' => null,
            'orderid' => null,
            'status' => null,
            'completedon' => null,
            'failedon' => null,
            'reversedon' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
            'skipconfirm' => null, //dont have to add to table.use for wallet purpose.
            'checker' => null,
            'remarks' => null,
            'actionon' => null
        );

        $this->viewMembers = array(

            'achfullname' => null,
            'achcode' => null,
            'achemail' => null,
            'achphoneno' => null,
            'achmykadno' => null,
            'achpartnercusid' => null,
            'achtype' =>null,
            'achtypename' => null,

            'referralbranchcode' => null,
            'referralbranchname' => null,

            'dbmpdtgatewayrefno'         => null,
            'dbmpdtreferenceno'          => null,
            'dbmpdtrequestedon'          => null,
            'dbmpdtaccountholdername'    => null,
            'dbmpdtaccountholdercode'    => null,
            'dbmpdtverifiedamount'       => null,     

            'ordpartnerid'  => null,
            'ordproductname'=> null, 
            'ordbuyerid'    => null,
            'ordorderno'    => null,
            'ordtype'       => null,
            'ordprice'      => null,
            'ordfpprice'      => null,
            'ordxau'        => null,
            'ordamount'     => null,
            'ordfee'        => null,
            'orddiscountprice'=> null,
            'orddiscountinfo' => null,
            'ordfeetypename'=> null,
            'ordremarks'    => null,
            'ordbookingprice' => null,
            'ordisspot'     => null,
            'ordbookingon'  => null,
            'ordconfirmon'  => null,
            'ordcancelon'   => null,
            'ordstatus'     => null,
            'ordpartnername'=> null,
            'ordpartnercode'=> null,

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

            'dbmamount' => null,
            'dbmbankid' => null,
            'dbmbankrefno' => null,
            'dbmaccountname' => null,
            'dbmaccountnumber' => null,
            'dbmacebankcode' => null,
            'dbmfee' => null,
            //'dbmrefno' => null,
            'dbmaccountholderid' => null,
            'dbmstatus' => null,
            //'dbmgatewayrefno' => null,
            'dbmtransactionrefno' => null,
            //'dbmrequestedon' => null,
            'dbmdisbursedon' => null,
            'dbmbankname' => null,
            'dbmbankswiftcode' => null,
            'partnercommissionpergram' => null,
            'partnercommission' => null,
            'acecommissionpergram' => null,
            'acecommission' => null,
            'affiliatecommissionpergram' => null,
            'affiliatecommission' => null,

            'fpxcost' => null, //add for dailytrn report to display fpx cost. not exist in vwtable
            'fpxnetamount' => null, //add for dailytrn report to display fpx net amount (processing fee - fpx cost). not exist in vwtable
            'peakstatus' => null, //add for commission report to display peak or non peak. not exist in vwtable

            'priceuuid' => null, //add for passing uuid in mygoldtransaction object. not exist in vwtable
            'createdbyname' => null, 
            'modifiedbyname' => null,
			'gtpreference' => null
        );
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid()
    {
        $this->validateRequiredField($this->members['originalamount'], 'originalamount');
        $this->validateRequiredField($this->members['settlementmethod'], 'settlementmethod');
        $this->validateRequiredField($this->members['refno'], 'refno');
        $this->validateRequiredField($this->members['orderid'], 'orderid');

        return true;
    }

    /**
     * Gets current status in text format
     * 
     * @return string
     */
    public function getStatusString()
    {
        switch ($this->members['status']) {
            case self::STATUS_PENDING_PAYMENT:
                return gettext("Pending");
                break;
            case self::STATUS_PAID:
                return gettext("Paid");
                break;
            case self::STATUS_CONFIRMED:
                return gettext("Confirmed");
            case self::STATUS_REVERSED:
                return gettext("Reversed");
            case self::STATUS_FAILED:
                return gettext("Failed");
            case self::STATUS_PENDING_REFUND:
                return gettext("Pending Refund");
            case self::STATUS_REFUNDED:
                return gettext("Refunded");
                break;
            case self::STATUS_PENDING_APPROVAL:
                return gettext("Pending Approval");
                break;
            case self::STATUS_REJECTED:
                return gettext("Rejected");
                break;
            // case self::STATUS_CONFIRM_FAILED;
            //     return "Confirmation failed";
            default:
                return "";
        }
    }

    /**
     * Returns the GTP order related to this gold transaction
     * @return Order
     */
    public function getOrder()
    {
        $order = null;
        if (0 < $this->members['orderid']) {
            $order = $this->getStore()->getRelatedStore('order')->getById($this->members['orderid']);
        }

        return $order;
    }
}
