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
 * Encapsulates the eventlog table on the database
 *
 *  @property-read 	int  		$id				Primary Key
 *  @property 		int			$triggerid		The trigger that this log event belongs to
 *  @property 		int			$groupid		The group id representing a particular group subscription
 *  @property 		int			$objectid		The object id to be notified
 *  @property 		string		$reference		The reference number of the object
 *  @property       string      $subject        The event subject
 *  @property 		text		$log 			The event information
 *  @property 		string		$sendto			255)	Recipients information
 *  @property 		datetime	$sendon			Date / time event was sent
 *  @property 		\Datetime	$createdon		Datetime)		Time this record is created
 *  @property 		\Datetime	$modifiedon		Datetime)		Time this record is last modified
 *  @property 		int			$status			Status active(1), suspended(2)
 *  @property 		int			$createdby		User ID
 *  @property 		int			$modifiedby		User ID
 *  @property  		double 		$moduleid		Module ID from the IEventConfig interface
 *  @property 		int			$actionid		Available action id from the IEventConfig interface
 *
 * @author Devon
 * @version 1.0
 * @created 2017/8/25 9:33 AM
 * @package  snap.base
 */
class EventLog extends SnapObject
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
            'objectid' => null,
            'reference' => null,
            'subject' => null,
            'log' => null,
            'sendto' => null,
            'sendon' => null,
            'createdon' => null,
            'modifiedon' => null,
            'status' => null,
            'createdby' => null,
            'modifiedby' => null,
        );
        $this->viewMembers = array(
            'grouptypeid' => null,
            'moduleid' => null,
            'actionid' => null,
            'groupcode' => null,
            'groupname' => null,
        );
    }

    /**
     * Check if all values in $this->members array is valid  this is where the object
     * member variables get validated for legal values inherited class should
     * implement this abstract function
     * 
     * @return	boolean    true if all member data has valid values. Otherwise false.
     */
    public function isValid()
    {
        return true;
    }
}
?>