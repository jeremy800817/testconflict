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
 * @author Ang
 * @version 1.0
 * @created 2023-06-08 15:07
 * @package  snap.base
 */
class UsrAdditionalData extends SnapObject
{
    /**
     * Reset all values in $this->members array  this is where the object member
     * variables get initialized to its default values inherited class should
     * implement this abstract function
     */
    public function reset()
    {
        $this->members = array(
            'id' => null,
            'userid' => null,
            'staffid' => null,
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

    // public function addEventSubscriber($objectid, $triggerid, $groupid, $receiver)
    // {
    //     return $this->getStore()->registerSubscriber($objectid, $triggerid, $groupid, $receiver);
    // }

      /**
     * Add address record for the account holder
     *
     * @param  string $line1
     * @param  string $line2
     * @param  string $city
     * @param  string $postcode
     * @param  string $state
     * @return MyAddress
     */
    public function addAddress($line1, $line2, $city, $postcode, $state, $mailingLine1 = null, $mailingLine2 = null, $mailingCity = null, $mailingPostcode = null, $mailingState = null)
    {
        $address = $this->getStore()->getRelatedStore('myaddress')->create([
            'line1'           => $line1,
            'line2'           => $line2,
            'city'            => $city,
            'postcode'        => $postcode,
            'state'           => $state,
            'mailingline1' => $mailingLine1,
            'mailingline2' => $mailingLine2,
            'mailingcity' => $mailingCity,
            'mailingpostcode' => $mailingPostcode,
            'mailingstate' => $mailingState,
            'accountholderid' => $this->members['id'],
            'status'          => MyAddress::STATUS_ACTIVE,
        ]);

        return $this->getStore()->getRelatedStore('myaddress')->save($address);
    }
    
    /**
     * Update address record for the account holder
     *
     * @param  string $line1
     * @param  string $line2
     * @param  string $city
     * @param  string $postcode
     * @param  string $state
     * @return MyAddress
     */
    public function updateAdditionalData(UsrAdditionalData $address, $line1, $line2, $city, $postcode, $state, $mailingLine1 = null, $mailingLine2 = null, $mailingCity = null, $mailingPostcode = null, $mailingState = null)
    {
        $address->line1    = $line1;
        $address->line2    = $line2;
        $address->city     = $city;
        $address->postcode = $postcode;
        $address->state    = $state;
        $address->mailingline1 = $mailingLine1;
        $address->mailingline2 = $mailingLine2;
        $address->mailingcity = $mailingCity;
        $address->mailingpostcode = $mailingPostcode;
        $address->mailingstate = $mailingState;

        return $this->getStore()->getRelatedStore('myaddress')->save($address);
    }
}
?>