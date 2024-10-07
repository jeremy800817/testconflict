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
 * @property-read   int                 $ID                 ID of the system
 * @property        enum                $sourcetype         The source of this record extracted from
 * @property        enum                $type               Type of entity
 * @property        string              $name               Name from source
 * @property        string              $icno               IC number from source
 * @property        string              $dob                Dateofbirth from source
 * @property        string              $alias              Alias name from source
 * @property        string              $businessregno      Business registration number from source
 * @property        string              $address            Address from source
 * @property        string              $remarks            Remarks / note from source
 * @property        DateTime            $listedon           Date the entity was listed on
 * @property        DateTime            $importedon         Date the entity was imported
 * @property        int                 $status             The status of entry
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyScreeningList extends SnapObject
{

    const SOURCE_UN = 'UN';
    const SOURCE_BNM = 'BNM';
    const SOURCE_MOHA = 'MOHA';

    const TYPE_INDIVIDUAL = 'INDIVIDUAL';
    const TYPE_BUSINESS = 'BUSINESS';

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
            'type' => null,
            'name' => null,
            'icno' => null,
            'dob' => null,
            'alias' => null,
            'businessregno' => null,
            'address' => null,
            'remarks' => null,
            'status' => null,
            'listedon' => null,
            'importedon' => null,
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
        $this->validateRequiredField($this->members['sourcetype'], 'sourcetype');
        $this->validateRequiredField($this->members['name'], 'name');
        $this->validateRequiredField($this->members['type'], 'type');

        return true;
    }

     /**
     * This method will get all the sourcetypes available
     *
     * @return string 
     */
    public static function getSourceType() {
        $rClass = new \ReflectionClass(__CLASS__);
        $constants = $rClass->getConstants();
        $lists = [];
        foreach ($constants as $key => $constant) {
            if (false !== strstr($key, "SOURCE_")) {
                $lists[] = $rClass->getConstant($key);
            }
        }

        $sourceArr = [];
        foreach ($lists as $key => $value) {
			$sourceArr[] = (object)array("id" => $value, "code" => ucfirst(strtoupper($value)));
		}
		return $sourceArr;
    }

}
