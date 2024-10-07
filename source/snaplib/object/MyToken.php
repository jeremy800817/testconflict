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
 * @property        enum                $type               The type of this token
 * @property        string              $token              The token string
 * @property        int                 $accountholderid    The account holder id owns this token
 * @property        string              $remarks            Extra info for this token, Device name etc
 * @property        int                 $status             The status of this token
 * @property        DateTime            $expireon           Expiry of this token
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyToken extends SnapObject
{

    const TYPE_PUSH = 'PUSH';
    const TYPE_ACCESS = 'ACCESS';
    const TYPE_REFRESH = 'REFRESH';
    const TYPE_PASSWORD_RESET = 'PASSWORD_RESET';
    const TYPE_VERIFICATION = 'VERIFICATION';
    const TYPE_VERIFICATION_PHONE = 'VERIFICATION_PHONE';
    const TYPE_PIN_RESET = 'PIN_RESET';

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
            'type' => null,
            'token' => null,
            'remarks' => null,
            'accountholderid' => null,
            'status' => null,
            'expireon' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
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
        $this->validateRequiredField($this->members['type'], 'type');
        $this->validateRequiredField($this->members['token'], 'token');
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');

        if (in_array($this->members['type'], [self::TYPE_PASSWORD_RESET, self::TYPE_ACCESS])) {
            $this->validateRequiredField($this->members['expireon'], 'expireon');
        }

        // if (self::TYPE_PUSH_TOKEN === $this->members['type']) {
        //     $this->validateRequiredField($this->members['remarks'], 'remarks');
        // }

        return true;
    }
}
