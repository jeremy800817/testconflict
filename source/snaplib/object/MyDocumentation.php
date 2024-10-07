<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

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
 * @property-read       string              $language           Language of this documentation
 * @property            string              $name               The name of this documentation
 * @property            string              $code               The code to represent this documentation
 * @property            string              $filecontent        The content of the documentation
 * @property            string              $filename           The original filename of the documentation
 * @property            int                 $status             The status for this Documentation.  (Active / Inactive)
 * @property            int                 $partnerid          The partnerid for this Documentation. 
 * @property            DateTime            $createdon          Time this record is created
 * @property            DateTime            $modifiedon         Time this record is last modified
 * @property            int                 $createdby          User ID
 * @property            int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyDocumentation extends MyLocalizedObject
{
    const CODE_TNC        = 'TNC';
    const CODE_PDPA       = 'PDPA';
    const CODE_DISCLAIMER = 'DISCLAIMER';
    const CODE_FAQ = 'FAQ';
    const CODE_PDS = 'PDS';

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
            'name' => null,
            'code' => null,
            'status' => null,
            'partnerid' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->localizableMembers = array(
            'filecontent'   => null,
            'filename'   => null
        );

        $this->viewMembers = array(
            'createdbyname' => null,
            'modifiedbyname' => null,
            'locid' => null,
            'loclanguage' => null,
            'locstatus' => null,
            'loccreatedon' => null,
            'locmodifiedon' => null,
            'loccreatedbyname' => null,
            'locmodifiedbyname' => null,
            'locfilecontent' => null,
            'locfilename' => null,
        );

        parent::reset();
    }

    protected function getContentType() { return MyLocalizedContent::TYPE_DOCUMENTATION; }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid()
    {
        $this->validateRequiredField($this->members['name'], 'name');
        $this->validateRequiredField($this->members['code'], 'code');
        // $this->validateRequiredField($this->localizableMembers['content'], 'content');
        $this->validateUniqueField($this->members['code'], 'code');
        
        return true;
    }

    private function validateUniqueField($value, $key)
    {
        if (null === $this->members['id']) {
            $id = 0;
        } else {
            $id = $this->members['id'];
        }

        $exists = $this->getStore()
            ->searchTable()
            ->select(['code'])
            ->where($key, $value)
            ->andWhere('id', '!=', $id)
            ->exists();

        if ($exists) {
            throw new \Exception(sprintf(gettext('The code %s is already in use by another entry'), $this->members['code']), InputException::FIELD_ERROR);
        }
    }
}
