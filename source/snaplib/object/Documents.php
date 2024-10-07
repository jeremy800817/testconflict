<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;

class Documents extends SnapObject
{
    const STATUS_INACTIVE = '0';
    const STATUS_PENDING = '1';
    const STATUS_ACTIVE = '2';
    const TYPE_CONSIGNMENTNOTE = 'ConsignmentNote';
    const TYPE_TRANSFERNOTE = 'TransferNote';
    const TYPE_DELIVERYORDER = 'DeliveryNote';

    /**
     * This method will initialise the 2 array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's contractor.
     * @return void
     */
    protected function reset(): void
    {
        
        $this->members = [
            'id' => null,
            'type' => null,
            'typenumber' => null,
            'transactiontype' => null,
            'transactionid' => null,
            'status' => null,
            'scheduledon' => null,
            'printon' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
        ];

        $this->viewMembers = [
            'createdbyname' => null,
            'modifiedbyname' => null,
        ];
        //test git
    }

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
        return true;
    }

    public function increment(){
        $number = $this->members['typenumber'];
        $pattern = "/[^0-9]/";
        $number = preg_replace($pattern, '', $number);
        $number = intval($number);
        return $number + 1;
        
    }


}
?>
