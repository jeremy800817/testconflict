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
 * LogisticTracker Class
 *
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                 ID of the table
 * @property        int                 $partnerid          ID of the partner table
 * @property        string              $apiversion         Response date
 * @property        enum                $itemtype           Type of redemption
 * @property        int                 $itemid             ID of the product
 * @property        int                 $senderid           ID of Sender
 * @property        string              $senderref          Reference of sender
 * @property        \DateTime           $sendon             Time of item sent
 * @property        int                 $sendby             ID of courier
 * @property        \DateTime           $receivedon         Time of item received
 * @property        string              $receiveperson      Name of person receive
 * @property        \DateTime           $createdon          Time this record created
 * @property        int                 $createdby          User ID
 * @property        \DateTime           $modifiedon         Time this record is last modified
 * @property        int                 $modifiedby         User ID
 * @property        int                 $status             Api status
 *
 * @author  Calvin <calvin.thien@ace2u.com>
 * @version 1.0
 * @package Snap\object
 */
class LogisticTracker extends SnapObject
{
    const TYPE_ORDER = 'order';
    const TYPE_REDEMPTION = 'Redemption';

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
            'apiversion' => null,
            'itemtype' => null,
            'itemid' => null,
            'senderid' => null,
            'senderref' => null,
            'sendon' => null,
            'sendby' => null,
            'receivedon' => null,
            'receiveperson' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null,

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
        if(empty($this->members['itemtype']) || !in_array($this->members['itemtype'], [self::TYPE_ORDER, self::TYPE_REDEMPTION])
            ){ throw new InputException(gettext('The type field is mandatory'), InputException::FIELD_ERROR, 'type');
        }

        $this->validateMandatoryField($this->members['partnerid'], 'The inventory field is mandatory', 'partnerid');
        //$this->validateMandatoryField($this->members['apiversion'], 'The inventory field is mandatory', 'apiversion');
        $this->validateMandatoryField($this->members['itemid'], 'The inventory field is mandatory', 'itemid');
        $this->validateMandatoryField($this->members['partnerid'], 'The inventory field is mandatory', 'partnerid');
        $this->validateMandatoryField($this->members['senderref'], 'The inventory field is mandatory', 'senderref');
        $this->validateMandatoryField($this->members['sendby'], 'The inventory field is mandatory', 'sendby');
        $this->validateMandatoryField($this->members['receiveperson'], 'The inventory field is mandatory', 'receiveperson');

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


}
?>