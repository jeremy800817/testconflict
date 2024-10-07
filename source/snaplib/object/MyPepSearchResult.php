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
 * @property            string          $provider           The PEP provider
 * @property            string          $request            The request data 
 * @property            string          $response           The response data, matches persons from provider
 * @property            int             $matchescount       The number of matches found
 * @property            int             $accountholderid    The id of accountholder this search was performed for
 * @property            int             $status             Status of this pep search
 * @property            DateTime        $createdon          Time this record is created
 * @property            DateTime        $modifiedon         Time this record is last modified
 * @property            int             $createdby          User ID
 * @property            int             $modifiedby         User ID
 * @author Azam
 * @version 1.0
 * @created 2020/12/03 5:15 PM
 */
class MyPepSearchResult extends SnapObject
{
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
            'request' => null,
            'response' => null,
            'matchescount' => null,
            'accountholderid' => null,
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
        if (0 == strlen($this->members['id'])) {
            $this->validateRequiredField($this->members['provider'], 'provider');
            $this->validateRequiredField($this->members['request'], 'request');
        } else {
            $this->validateRequiredField($this->members['response'], 'response');
        }

        return true;
    }
}
