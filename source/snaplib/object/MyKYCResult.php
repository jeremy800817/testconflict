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
 * Name                 Type            Description
 * @property-read       int             $id                 ID of the system
 * @property            string          $provider           The KYC provider
 * @property            string          $remarks            Admin remarks?
 * @property            string          $data               JSON data, result from provider
 * @property            string          $result             Pass or fail
 * @property            int             $submissionid       KYCSubmission Id
 * @property            int             $status             Status of this kycresult
 * @property            DateTime        $createdon          Time this record is created
 * @property            DateTime        $modifiedon         Time this record is last modified
 * @property            int             $createdby          User ID
 * @property            int             $modifiedby         User ID
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyKYCResult extends SnapObject
{
    const RESULT_PASSED = 'P';
    const RESULT_FAILED = 'F';
    const RESULT_CAUTIOUS = 'C';

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
            'provider' => null,
            'remarks' => null,
            'data' => null,
            'result' => null,
            'submissionid' => null,
            'status' => null,
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
        $this->validateRequiredField($this->members['provider'], 'provider');
        $this->validateRequiredField($this->members['data'], 'data');
        $this->validateRequiredField($this->members['result'], 'result');
        $this->validateRequiredField($this->members['submissionid'], 'submissionid');

        return true;
    }

    public function getResultString()
    {
        switch ($this->members['result']) {
            case self::RESULT_CAUTIOUS:
                return 'Cautios';
                break;
            case self::RESULT_PASSED:
                return 'Passed';
                break;
            case self::RESULT_FAILED:
                return 'Failed';
                break;
            default:
                return 'Unknown';
                break;
        }
    }
}
