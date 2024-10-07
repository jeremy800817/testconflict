<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\object;


/**
 * Calendar Class
 *
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 	    $id 			  ID of the table
 * @property       	string     			$title            Title of the date
 * @property       	\DateTime 			$holiday          The date of the holiday
 * @property       	int       			$status        	  Active / Inactive
 * @property       	\DateTime    		$createdon     	  Time this record is created
 * @property       	\DateTime    		$modifiedon       Time this record is last modified
 * @property       	int       			$createdby     	  User ID
 * @property       	int       			$modifiedby    	  User ID
 *
 * @author  Ang <ang@silverstream.my>
 * @version 1.0
 * @package Snap\object
 */
class Calendar extends SnapObject
{

    /**
     * This method will initialise the 2 array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's contractor.
     * @return void
     */
    protected function reset(): void
    {
        $this->members = [
            'id' => null,
            'title' => null,
            'holidayon' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null,
        ];
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid(): bool
    {
        return true;
    }
}
?>