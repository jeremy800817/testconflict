<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\InputException;
use Snap\IEntity;

/**
 * ApiLogs Class
 *
 *
 * Data members:
 * Name			  	    Type				Description
 * @property-read 	int 		 	      $id			        ID of the table
 * @property       	enum            $type               Api type
 * @property       	string       		$fromip             FROM which IP
 * @property       	int        			$systeminitiate     System initiates
 * @property       	string       		$requestdata        Requested data
 * @property       	string    		  $responsedata     	Response date
 * @property        \DateTime      	$createdon    	   	Time this record created
 * @property       	int       			$createdby     	   	User ID
 * @property       	\DateTime  			$modifiedon    	   	Time this record is last modified
 * @property       	int 		 	      $modifiedby		       User ID
 * @property       	int     			  $status              Api status
 *
 * @author  Calvin <calvin.thien@ace2u.com>
 * @version 1.0
 * @package Snap\object
 */
class ApiLogs extends SnapObject
{
    const TYPE_GTP = 'gtp';
    const TYPE_SAP = 'sap';
    const TYPE_PRICESTREAM = 'NewPriceStream';
    const TYPE_PRICEVALIDATION = 'NewPriceValidation';
    const TYPE_SAPORDER = 'SapOrder';
    const TYPE_SAPCANCELORDER = 'SapCancelOrder';
    const TYPE_SAPGENERATEGRN = 'SapGenerateGrn';
    const TYPE_SAPGOLDSERIALREQUEST = 'SapGoldSerialRequest';
    const TYPE_APIALLOCATEXAU = 'ApiAllocateXau';
    const TYPE_APIGETPRICE = 'ApiGetPrice';
    const TYPE_APINEWBOOKING = 'ApiNewBooking';
    const TYPE_APICONFIRMBOOKING = 'ApiConfirmBooking';
    const TYPE_APICANCELBOOKING = 'ApiCancelBooking';
    const TYPE_APIREDEMPTION = 'ApiRedemption';

    const TYPE_MYGTP = 'MYGTP';
    const TYPE_MYGTP_EKYC = 'MYGTP_EKYC';
    const TYPE_MYGTP_FPX    = 'MYGTP_FPX';
    const TYPE_MYGTP_WALLET = 'MYGTP_WALLET';
    const TYPE_MYGTP_CASA = 'MYGTP_CASA';

    /**
     * This method will initialise the 2 array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's contractor.
     * @return void
     */
    protected function reset(): void
    {
        $this->members = [
            'id' => null,
            'type' => null,
            'fromip' => null,
            'refobject' => null,
            'refobjectid' => null,
            'systeminitiate' => null,
            'requestdata' => null,
            'responsedata' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null,
        ];

        $this->viewMembers = [
            'createdbyname' => null,
            'modifiedbyname' => null,

        ];
        //test git
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
        //type is mandatory
        $myClass = new \ReflectionClass(__CLASS__);
        $myConstants = $myClass->getConstants();

        if (0 == strlen($this->members['id'])) {
            if (empty($this->members['type']) || !in_array($this->members['type'], $myConstants)) {
                throw new InputException(gettext('The type field is mandatory'), InputException::FIELD_ERROR, 'type');
            }

            $this->validateMandatoryField($this->members['fromip'], 'The IP field is mandatory', 'fromip');
            $this->validateMandatoryField($this->members['requestdata'], 'The requestdata field is mandatory', 'requestdata');
        } else {
            $this->validateMandatoryField($this->members['responsedata'], 'The responsedata field is mandatory', 'responsedata');
        }
        return true;
    }
}
