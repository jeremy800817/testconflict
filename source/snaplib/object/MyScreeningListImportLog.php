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
 * @property-read       int                 $id                 ID of the system
 * @property            string              $sourcetype         The source of url
 * @property            string              $url                The url source
 * @property            int                 $status             The status for the url.  (Active / Inactive)
 * @property            DateTime            $importedon         Time this record is imported
 * @property            DateTime            $createdon          Time this record is created
 * @property            DateTime            $modifiedon         Time this record is last modified
 * @property            int                 $createdby          User ID
 * @property            int                 $modifiedby         User ID
 *
 * @author Ang
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyScreeningListImportLog extends SnapObject
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
            'sourcetype' => null,
            'url'   => null,
            'status' => null,
            'importedon' => null,
            'importedby' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->viewMembers = [
            'createdbyname' => null,
            'modifiedbyname' => null,

        ];
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid()
    {
        $this->validateRequiredField($this->members['sourcetype'], 'sourcetype');
        $this->validateRequiredField($this->members['url'], 'url');

        return true;
    }
}
