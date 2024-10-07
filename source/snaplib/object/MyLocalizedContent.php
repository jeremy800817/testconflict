<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\object\SnapObject;

/**
 * This class encapsulates the localizedcontent table.
 * Contains the localized data of its source.
 * 
 * Data members:
 * Name             Type            Description
 * @property-read   int             $id                 ID of the localized content
 * @property        int             $sourcetype         Type of content
 * @property        int             $sourceid           Id of source type
 * @property        string          $data               JSON encoded data
 * @property        string          $language           Language of content
 * @property        int             $status             Status of the localized content
 * @property        DateTime        $createdon          Time this record is created
 * @property        int             $createdby          User who created this record
 * @property        DateTime        $modifiedon         Time this record is last modified
 * @property        int             $modifiedby         User who last modified this record
 * 
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 01-Oct-2020
 */
class MyLocalizedContent extends SnapObject
{

    const LANG_ENGLISH = "EN";
    const LANG_BAHASA  = "MS";
    const LANG_CHINESE = "ZH";

    const TYPE_ANNOUNCEMENT             = 'ANNOUNCEMENT';
    const TYPE_PUSHNOTIFICATION         = 'PUSH_NOTI';
    const TYPE_DOCUMENTATION            = 'DOCUMENTATION';
    const TYPE_OCCUPATIONCATEGORY       = 'OCCUPATION_CAT';
    const TYPE_OCCUPATIONSUBCATEGORY    = 'OCCUPATION_SUBCAT';
    const TYPE_CLOSREASON               = 'CLOSE_REASON';

    protected function reset()
    {
        $this->members = [
            'id'        => null,
            'sourcetype'=> null,
            'sourceid'  => null,
            'data'      => null,
            'language'  => null,
            'status'    => null,
            'createdon' => null,
            'modifiedon'=> null,
            'createdby' => null,
            'modifiedby'=> null,
        ];
    }

    public function isValid()
    {
        $this->validateRequiredField($this->members['sourcetype'], 'Source type field is mandatory', 'sourcetype');
        $this->validateRequiredField($this->members['sourceid'], 'Source id field is mandatory', 'sourceid');
        $this->validateRequiredField($this->members['data'], 'Data field is mandatory', 'data');
        $this->validateRequiredField($this->members['language'], 'Language field is mandatory', 'language');

        return true;
    }
}

?>