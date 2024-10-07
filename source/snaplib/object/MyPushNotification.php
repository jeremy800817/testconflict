<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use DateTime;
use Snap\InputException;

/**
 * Encapsulates the service table on the database
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name                 Type                Description
 * @property-read       int                 $id                 ID of the system
 * @property            string              $code               The code to represent this pushnotifcation
 * @property            string              $icon               The icon of the pushnotifcation
 * @property            string              $sound               The sound file of the pushnotifcation
 * @property            string              $eventtype          The type of event of this notification
 * @property            int                 $rank               The rank of this notification of the same event type
 * @property            string              $title              The title of this pushnotifcation
 * @property            string              $body               The content of the pushnotifcation
 * @property            string              $language           The language of the pushnotification
 * @property            int                 $status             The status for this PushNotifcation.  (Active / Inactive)
 * @property            Datetime            $validfrom          Time this notification is valid from
 * @property            Datetime            $validto            Time this notification is valid until
 * @property            DateTime            $createdon          Time this record is created
 * @property            DateTime            $modifiedon         Time this record is last modified
 * @property            int                 $createdby          User ID
 * @property            int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyPushNotification extends MyLocalizedObject
{
    const TYPE_EKYC_FAIL = 'EKYC_FAIL';
    const TYPE_EKYC_PASS = 'EKYC_PASS';
    const TYPE_EKYC_INCOMPLETE  = 'EKYC_INCOMPLETE';
    const TYPE_PRICE_MATCH_BUY  = 'PRICE_MATCH_BUY';
    const TYPE_PRICE_MATCH_SELL = 'PRICE_MATCH_SELL';
    const TYPE_INCOMPLETE_PROFILE = 'INCOMPLETE_PROFILE';
    const TYPE_GOLDTRANSACTION_CONFIRMED = "GOLDTRANSACTION_CONFIRMED";
    const TYPE_CONVERSION_CREATE = 'CONVERSION_CREATE';
    const TYPE_PEP_PASSED = 'PEP_PASSED';
    const TYPE_PEP_FAILED = 'PEP_FAILED';
    const TYPE_DORMANT_ACCOUNT = 'DORMANT_ACCOUNT';

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
            'eventtype' => null,
            'code' => null,
            'icon' => null,
            'sound' => null,
            'rank' => null,
            'validfrom' => null,
            'validto'   => null,
            'status' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );
        
        $this->localizableMembers = array(
            'title'     => null,
            'body'      => null
        );
    }

    function getContentType()
    {
        return MyLocalizedContent::TYPE_PUSHNOTIFICATION;
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid()
    {
        $this->validateRequiredField($this->members['code'], 'code');
        $this->validateRequiredField($this->members['eventtype'], 'eventtype');
        $this->validateRequiredField($this->members['validfrom'], 'validfrom');
        $this->validateRequiredField($this->members['validto'], 'validto');
        // $this->validateRequiredField($this->localizableMembers['title'], 'title');
        // $this->validateRequiredField($this->localizableMembers['body'], 'body');

        if ($this->members['validfrom'] instanceof DateTime) {
            $dateFrom = $this->members['validfrom'];
        } else {
            $dateFrom = new \DateTime($this->members['validfrom']);
        }

        if ($this->members['validfrom'] instanceof DateTime) {
            $dateTo = $this->members['validto'];
        } else {
            $dateTo = new \DateTime($this->members['validto']);
        }

        if ($dateFrom > $dateTo) {
            throw new InputException("Start date cannot be later than end date", InputException::FIELD_ERROR);
        }

        return true;
    }

    public static function getType() {
        $rClass = new \ReflectionClass(__CLASS__);
        $constants = $rClass->getConstants();
        $lists = [];
        foreach ($constants as $key => $constant) {
            if (false !== strstr($key, "TYPE_")) {
                $lists[] = $rClass->getConstant($key);
            }
        }

        $categoryArr = [];
		foreach ($lists as $key => $value) {
			$categoryArr[] = (object)array("id" => $value, "code" => ucfirst(strtolower($value)));
		}

		return $categoryArr;
    }
}
