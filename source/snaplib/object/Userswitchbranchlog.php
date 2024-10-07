<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\object;

Use Snap\App;
Use Snap\InputException;

/**
 * Encapsulates user table
 *
 * This class encapsulates the user table data and its member data
 * information
 *
 * Data members:
 * Name					Type		Description
 * @property-read         int          $id					      integer		      Primary Key
 * @property              int          $userid		    	      integer		      User Id
 * @property              int          $frompartnerid		  	  integer		      From partner id
 * @property              int          $topartnerid		   	      integer		      To partner id
 * @property              \DateTime    $createdon		    	  (\DateTime) 		  Time this record is created
 * @property              int          $createdby			      integer		      record created by user id 
 * @property              \DateTime    $modifiedon			      (\DateTime)	      Time this record last modified on
 * @property              int          $modifiedby				  integer		      record last modified by user id
 * @property              string       $status				      (\DateTime)		  status for the record

 * @author Chen <chen.teng.siang@silverstream.my>
 * @version 1.0
 * @created 16-Aug-2024 12:02:00 PM
 */
class Userswitchbranchlog extends SnapObject
{
    //Statuses
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * Reset all values in $this->members array  this is where the object member
     * variables get initialized to its default values inherited class should
     * implement this abstract function
     * @abstract
     * @return	none
     */
    public function reset()
    {
        $this->members = array(
            'id' => 0,
            'userid' => 0,
			'frompartnerid' => 0,      
			'topartnerid' => 0,
            'createdon' => 0,
            'createdby' => 0,
            'modifiedon' => 0,
            'modifiedby' => 0,
            'status' => 0,
        );
        $this->viewMembers = array(
            'username' => '',
            'frompartnername' => '',
            'topartnername' => '',
            'createdbyname' => '',
            'modifiedbyname' => ''
        );
    }

    /**
     * A validation function where to check mandatory fields
     *
     * @internal
     * @param $value
     * @param null|string $message
     * @param null|string $key
     * @throws InputException
     */
    private function validateMandatoryField($value, ?string $message, ?string $key): void
    {
        if (empty($value)) {
            throw new InputException(gettext($message), InputException::FIELD_ERROR, $key);
        }
    }


    /**
    * Check if username is in valid and legal by checking for illegal characters and its length
    *
    * @param string $username the username value to test
    *
    * @return   boolean True if valid.  Otherwise false.
    */
    public function isValid(): bool
    {
        $this->validateMandatoryField($this->members['userid'], 'Partner id is mandatory', 'userid');
        $this->validateMandatoryField($this->members['frompartnerid'], 'From partner reference id is mandatory', 'frompartnerid');
        $this->validateMandatoryField($this->members['topartnerid'], 'To partner reference id is mandatory', 'topartnerid');

        return true;
    }

    public function isActive()
    {
        return (self::STATUS_ACTIVE == $this->members['status']);
    }
    
}
?>
