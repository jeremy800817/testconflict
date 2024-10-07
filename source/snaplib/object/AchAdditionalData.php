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
 *
 * @author Rinston
 * @version 1.0
 * @created 2023-06-08 15:07
 * @package  snap.base
 */
class AchAdditionalData extends SnapObject
{
	
	const CATEGORY_STAFF = 'INDIVIDU - STAF';
	
    /**
     * Reset all values in $this->members array  this is where the object member
     * variables get initialized to its default values inherited class should
     * implement this abstract function
     */
    public function reset()
    {
        $this->members = array(
            'id' => null,
            'accountholderid' => null,
            'title' => null,
            'idtype' => null,
            'category' => null,
            'nationality' => null,
            'dateofbirth' => null,
            'bumiputera' => null,
            'religion' => null,
            'gender' => null,
            'maritalstatus' => null,
            'race' => null,
            'mailingaddress1' => null,
            'mailingaddress2' => null,
            'mailingpostcode' => null,
            'mailingtown' => null,
            'mailingstate' => null,
            'mailingcountry' => null,
            'homephoneno' => null,
            'occupation' => null,
            'employername' => null,
            'officeaddress1' => null,
            'officeaddress2' => null,
            'officepostcode' => null,
            'officetown' => null,
            'officestate' => null,
            'officecountry' => null,
            'dateofincorporation' => null,
            'placeofincorporation' => null,
            'businessdesc' => null,
            'contactpersonname' => null,
            'phonenoincorporation' => null,
            'mobilenoincorporation' => null,
            'jointtypeofid' => null,
            'jointtitle' => null,
            'jointnationality' => null,
            'jointdateofbirth' => null,
            'jointbumiputera' => null,
            'jointgender' => null,
            'jointmaritalstatus' => null,
            'jointreligion' => null,
            'jointrace' => null,
            'jointrelationship' => null,
            'jointoccupation' => null,
            'jointmobileno' => null,
            'status' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null
        );
        // $this->viewMembers = array(
            
        // );
    }

    /**
     * Check if all values in $this->members array is valid  this is where the object
     * member variables get validated for legal values inherited class should
     * implement this abstract function
     * @abstract
     * @return  true if all member data has valid values. Otherwise false.
     */
    public function isValid()
    {
        return true;
    }

    public function addEventSubscriber($objectid, $triggerid, $groupid, $receiver)
    {
        return $this->getStore()->registerSubscriber($objectid, $triggerid, $groupid, $receiver);
    }
}
?>