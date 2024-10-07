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
 * @property        DateTime            $dateorderedon                Date order is sent from MBB API
 * @property        int                 $orderid                      ID of order from API
 * @property        enum                $ordertype                    Type of Order
 * @property        string              $deliveryaddress              Address to be delivered to
 * @property        string              $postcode                     Post Code
 * @property        string              $city                         City
 * @property        enum                $deliverymethod               Method of delivery ( courier/ ace salesman )
 * @property        int                 $deliveryattempt              Number of attemps of the delivery
 * @property        string              $contactnumber                Primary Phone Number of Customer
 * @property        string              $alternatecontactnumber       Alternate Contact Number of Customer
 * @property        string              $contactpersonname            Name of customer of primary contact number
 * @property        string              $alternatcontactpersonname    Name of customer of secondary contact number
 * @property        DateTime            $deliveredon                  Date of delivery from courier
 * @property        DateTime            $orderreceivedon              Date of order received
 * @property        DateTime            $processedon                  Date order is processed
 * @property        DateTime            $intransiton                  Date package is in transit
 * @property        DateTime            $completedon                  Date package is successfully sent
 * @property        DateTime            $undeliveredon                Date package is unsuccessfully sent
 * @property        DateTime            $missingon                    Date package went missing
 * @property        DateTime            $rejectedon                   Date package was rejected
 * @property        DateTime            $awbnumber                    Away bill number
 * @property        string              $deliveredby                  Id of Deliverer
 * @property        string              $receiver                     Name of Receiver
 * @property        DateTime            $deliverystatus               Status of delivery from courier
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
class Logistics extends SnapObject
{
    /*
    const STATUS_DELIVEREDON = 0;
    const STATUS_ORDERRECEIVEDON = 1;
    const STATUS_PROCESSEDON = 2;
    const STATUS_INTRANSITION = 3;
    const STATUS_COMPLETEDON = 4;
    const STATUS_UNDELIVEREDON = 5;
    const STATUS_MISSINGON = 6;
    const STATUS_REJECTEDON = 7;
    */

    const ORDERTYPE_REDEMPTION = 'Redemption';
    const ORDERTYPE_BUYBACK = 'Buyback';
    const ORDERTYPE_REPLENISHMENT = 'Replenishment';


    const DELIVERYMETHOD_COURIER = 'Courier';
    const DELIVERYMETHOD_ACESALESMAN = 'Ace Salesman';
    const DELIVERYMETHOD_PREAPPOINTMENT = 'Pre Appointment';

    /**
     * This method will initialise the 2 array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's contractor.
     * @return void
     */
    protected function reset(): void
    {
        $this->members = [
            'id' => null,
            'dateorderedon' => null,
            'orderid' => null,
            'ordertype' => null,
            'deliveryaddress' => null,
            'postcode' => null,
            'city' => null,
            'deliverymethod' => null,
            'deliveryattempt' => null,
            'contactnumber' => null,
            'alternatecontactnumber' => null,
            'contactpersonname' => null,
            'alternatecontactpersonname' => null,
            'deliveredon' => null,
            'orderreceivedon' => null,
            'processedon' => null,
            'intransiton' => null,
            'completedon' => null,
            'undeliveredon' => null,
            'missingon' => null,
            'rejectedon' => null,
            'awbnumber' => null,
            'deliveredby' => null,
            'receiver' => null,
            'deliverystatus' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null,

        ];

        $this->viewMembers = [


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

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid(): bool
    {
        $this->validateMandatoryField($this->members['orderid'], 'Order id is mandatory', 'orderid');
        $this->validateMandatoryField($this->members['deliveryaddress'], 'Delivery Address is mandatory', 'deliveryaddress');
        $this->validateMandatoryField($this->members['ordertype'], 'Order type is mandatory', 'ordertype');
        $this->validateMandatoryField($this->members['deliverymethod'], 'Delivery Method is mandatory', 'deliverymethod');
        $this->validateMandatoryField($this->members['contactnumber'], 'Contact Number is mandatory', 'contactnumber');
        $this->validateMandatoryField($this->members['contactpersonname'], 'Contact person name is mandatory', 'contactpersonname');

        return true;
    }

    /*
        This method to get the listing of Logistic's OrderType
    */
    public function getOrdertype() {

        $typeArr = [];
        $lists = [
            self::ORDERTYPE_REDEMPTION,
            self::ORDERTYPE_BUYBACK,
            self::ORDERTYPE_REPLENISHMENT,
        ];

        foreach ($lists as $key => $value) {
            $typeArr[] = (object)array("id" => $value, "code" => ucfirst(strtolower($value)));
        }

        return $typeArr;
    }


    /*
        This method to get the listing of Logistic's delivery methods
    */
    public function getDeliveryMethod() {

        $typeArr = [];
        $lists = [
            self::DELIVERYMETHOD_COURIER,
            self::DELIVERYMETHOD_ACESALESMAN,
            self::DELIVERYMETHOD_PREAPPOINTMENT,
        ];

        foreach ($lists as $key => $value) {
            $typeArr[] = (object)array("id" => $value, "code" => ucfirst(strtolower($value)));
        }

        return $typeArr;
    }


}
?>
