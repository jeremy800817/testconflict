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
 * Encapsulates the eventjob table on the database
 *
 *  @property-read 	int  		$id					 Primary Key
 *  @property 		string		$processorclass		 class name of the event processor
 *  @property 		string		$processordataclass	 class name of the event data processor (Implementing IEventProcessorData)
 *  @property 		string		$processordata		 The data that has been cached and can be retrieved to continue background processing
 *  @property 		\Datetime	$createdon			 Time this record is created
 *  @property 		\Datetime	$modifiedon			 Time this record is last modified
 *  @property 		int			$status				 Status active(1), suspended(2)
 *  @property 		int			$createdby			 User ID
 *  @property 		int			$modifiedby			 User ID
 *
 * @author Devon
 * @version 1.0
 * @created 2017/8/25 10:24 AM
 * @package  snap.base
 */
class EventJob extends SnapObject
{
    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;

    /**
     * Reset all values in $this->members array  this is where the object member
     * variables get initialized to its default values inherited class should
     * implement this abstract function
     */
    public function reset()
    {
        $this->members = array(
            'id' => null,
            'processorclass' => null,
            'processordataclass' =>null,
            'processordata' => null,
            'createdon' => null,
            'modifiedon' => null,
            'status' => null,
            'createdby' => null,
            'modifiedby' => null
        );
        $this->viewMembers = array(
            'partnercode' => null,
            'partnertype' => null,
            'partnername' => null,
        );
    }

    /**
     * Check if all values in $this->members array is valid  this is where the object
     * member variables get validated for legal values inherited class should
     * implement this abstract function
     * 
     * @return	boolean   true if all member data has valid values. Otherwise false.
     */
    public function isValid()
    {
        return true;
    }
}
?>
