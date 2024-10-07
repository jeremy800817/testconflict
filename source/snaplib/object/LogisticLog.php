<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;
Use Snap\object\Logistic;
/**
 * Encapsulates the tag table on the database
 *
 * This class encapsulates the tag table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$ID 				ID of the system
 * @property       	int   				$logisticid        	parent ID (logistic table)
 * @property       	string        		$value              value of the logistic logging, status, etc
 * @property       	DateTime     		$time     	        time of this logistic logging, status, etc [eg: CAN BE updatesTime != requestTime, not same as createdon]
 * @property       	DateTime   			$createdon     		Time this record is created
 * @property       	int        			$createdby     		User id
 * @property       	DateTime 	   		$modifiedon    		Time this record is last modified
 * @property       	int         		$modifiedby    		User id
 *
 * @version 1.0
 */
class LogisticLog extends SnapObject {

	const TYPE_PUBLIC = 'Public';
	const TYPE_PRIVATE = 'Private';

	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
	protected function reset() {
		$this->members = [
			'id' => null,
			'logisticid' => null,
			'type' => null,
			'value' => null,
			'timeon' => null,
			'remarks' => null,
			'createdon' => null,
			'createdby' => null,
			'modifiedon' => null,
			'modifiedby' => null,
		];
    }

	public function isValid(){
		return true;
	}

	public function __get($nm) {

        if( ('readablestatus' != $nm) ) return parent::__get($nm);
        
        if ('readablestatus' == $nm){
            $readable = '';
            switch ($this->value) {
                case Logistic::STATUS_PENDING:
                    $readable = gettext('Pending');
                    break;
                case Logistic::STATUS_PROCESSING:
                    $readable = gettext('Processing');
                    break;
                case Logistic::STATUS_PACKING:
                    $readable = gettext('Packing');
                    break;
                case Logistic::STATUS_PACKED:
                    $readable = gettext('Packed');
                    break;
                case Logistic::STATUS_COLLECTED:
                    $readable = gettext('Collected');
                    break;
                case Logistic::STATUS_SENDING:
                    $readable = gettext('In Transit');
                    break;
                case Logistic::STATUS_DELIVERED:
                    $readable = gettext('Delivered');
                    break;
                case Logistic::STATUS_COMPLETED:
                    $readable = gettext('Completed');
                    break;
                case Logistic::STATUS_FAILED:
                    $readable = gettext('Failed');
                    break;
                case Logistic::STATUS_MISSING:
                    $readable = gettext('Missing');
                    break;
                default:
                   $readable = gettext('unknown');
            }
            return $readable;
        }
    }
}
