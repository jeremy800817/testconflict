<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

/**
 * This class encapsulates the image table data
 * Contains image file stored that is referenced by other object
 *
 * Data members:
 * Name             Type            Description
 * @property-read   int             $id                 ID of the system
 * @property        enum            $type               Type of image
 * @property        int             $image              Binary representation of the image
 * @property        int             $status             The status of this Image
 * @property        DateTime        $createdon          Time this record is created
 * @property        DateTime        $modifiedon         Time this record is last modified
 * @property        int             $createdby          User ID
 * @property        int             $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/11/05 5:15 PM
 */
class MyImage extends SnapObject
{
    const TYPE_BASE64 = 'BASE64';

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
            'image' => null,
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
        $this->validateRequiredField($this->members['image'], 'image');
        $this->validateRequiredField($this->members['status'], 'status');

        return true;
    }
}
