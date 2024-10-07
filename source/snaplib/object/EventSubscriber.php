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
 * This class is used to keep information about interested parties for a particular event
 *
 *  @property-read  int        $id            Primary Key
 *  @property       int        $triggerid     The trigger object that this log belongs to.
 *  @property       int        $groupid       The group id representing a particular group subscription
 *  @property       string     $receiver      Information to indicate the receiver particulars.  Separate by ; or comma
 *  @property       \Datetime  $createdon     Time this record is created
 *  @property       \Datetime  $modifiedon    Time this record is last modified
 *  @property       int        $status        Status active(1), suspended(2)
 *  @property       int        $createdby     User ID
 *  @property       int        $modifiedby    User ID
 *
 * @author Devon
 * @version 1.0
 * @created 2017/8/25 10:24 AM
 * @package  snap.base
 */
class EventSubscriber extends SnapObject
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
            'triggerid' => null,
            'groupid' => null,
            'receiver' => null,
            'createdon' => null,
            'modifiedon' => null,
            'status' => null,
            'createdby' => null,
            'modifiedby' => null,
        );
        $this->viewMembers = array(
            'grouptypeid' => null,
            'moduleid' => null,
            'moduleid' => null,
            'actionid' => null,
            'moduledesc' => null
        );
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