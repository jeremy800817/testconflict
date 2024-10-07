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
 * Encapsulates the service table on the database
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name		     	Type				Description
 * @property-read 	int 		     	$ID 			       	ID of the record
 * @property       	enum   				$sourcetype 			source type constants
 * @property       	int    	  			$sourceid			    ID of the source that this attachment is referenced by
 * @property       	string   			$description 	    	description of the attachment
 * @property       	string    			$filename       		Uploaded file name
 * @property       	int       			$filesize       		Size of attachment
 * @property       	string    			$mimetype      			MIME type
 * @property      	string   			$data       	    	data
 * @property       	DateTime   			$createdon     			Time this record is created
 * @property       	int        			$createdby     			User id
 * @property       	int   	     		$modifiedon    			Time this record is last modified
 * @property       	int         		$modifiedby    			user id
 * @property       	int       			$status    		    	The status for this vendor.  (Active / Inactive)
 *
 * @author Ang <ang@silverstream.my>
 * @version 1.0
 * @created 2017/4/14 11:16 AM
 */
class Attachment extends SnapObject {

	const ANNOUNCEMENT_SOURCE = 'ANNOUNCEMENT';

	/**
	 * Keeps a temporary store of the vendors.
	 * @var array
	 */
	// private $inventorycat = array();

	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
	protected function reset() {
		$this->members = array(
			'id' => null,
			'sourcetype' => self::ANNOUNCEMENT_SOURCE,
			'sourceid' => null,
			'description' => null,
      		'filename' => null,
			'filesize' => null,
			'mimetype' => null,
			'data' => null,
      		'createdon' => null,
      		'createdby' => null,
			'modifiedon' => null,
			'modifiedby' => null,
			'status' => null
		);
	}

	/**
	 * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
	 * valid state, the method will return false. Otherwise it will return true.
	 *
	 * @return boolean True if it is a valid object.  False otherwise.
	 */
	//public function isValid() {
	//	return true;
	//}
    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a 
     * valid state, the method will return false. Otherwise it will return true.
     * 
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid() {
        if(self::ANNOUNCEMENT_SOURCE != $this->members['sourcetype']) {
            throw new InputException(gettext('Invalid source type defined for the attachment'), InputException::FIELD_ERROR, 'sourcetype');         
        }
        return true;
    }

	/**
	 * This method will implement serializing the object into a cacheable string for optimum storage.
	 * @return String
	 */
	function toCache() {
		return $this->members;
	}
	

}
?>
