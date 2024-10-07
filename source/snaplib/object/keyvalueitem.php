<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;

/**
 * Encapsulates the a key-value pair in object format.  This is to facilitate use in cachestore type
 *
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$id 				ID of the system
 * @property       	string    			$value       		Value to store
 *
 * @author Devon
 * @version 1.0
 * @created 2017/9/2 8:32 PM
 * @package  snap.base
 */
class keyvalueitem extends snapObject
{

    /**
     * This method will initialise the 2 array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's contractor.
     *
     * @return void
     */
    protected function reset()
    {
        $this->members = array(
            'id' => null,
            'value' => null
        );
        
        $this->viewMembers = array(
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
        return true;
    }
}
?>