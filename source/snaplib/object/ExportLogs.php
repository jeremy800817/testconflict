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
 * ApiLogs Class
 *
 *
 * Data members:
 * Name			  	Type			Description
 * @property-read 	int             $id			        ID of the table
 * @property       	enum            $type               Api type
 * @property       	string          $fromip             FROM which IP
 * @property       	int        	    $systeminitiate     System initiates
 * @property        string          $refobject          Requested data
 * @property        int             $refobjectid        Requested data
 * @property        string          $requestdata        Requested data
 * @property        string          $responsedata        Requested data
 * @property       	string          $text               Requested data
 * @property        \DateTime      	$createdon    	   	Time this record created
 * @property       	int       		$createdby     	   	User ID
 * @property       	\DateTime  		$modifiedon    	   	Time this record is last modified
 * @property       	int 		 	$modifiedby		       User ID
 * @property       	int     	    $status              Api status
 *
 * @author  Dianah <dianah@silverstream.my>
 * @version 1.0
 * @package Snap\object
 */
class ExportLogs extends SnapObject
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
            'table' => null,
            'select' => null,
            'where' => null,
            'datestart' => null, 
            'dateend' => null,
            'outputcount' => null,
            'sendtoemail' => null,
            'remarks' => null,
            'actionby' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null,
        ];
    }

    private function validateMandatoryField($value, ?string $message, ?string $key): void
    {
        if (empty($value)) {
            throw new InputException(gettext($message), InputException::FIELD_ERROR, $key);
        }
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
