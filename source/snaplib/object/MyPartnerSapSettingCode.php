<?php

namespace Snap\object;

use Snap\InputException;

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

 /**
 * Sap Fee code
 *
 * This class encapsulates the tag table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$id 				ID of the system
 * @property       	int 				$partnerid        	Partner ID
 * @property        string              $tradebpvendor      Trade BP Code for Vendor
 * @property        string              $tradebpcus         Trade BP Code for Customer
 * @property        string              $nontradebpvendor      Non-Trade BP Code for Vendor
 * @property        string              $nontradebpcus         Non-Trade BP Code for Customer
 * @property       	DateTime   			$createdon          Time this record is created
 * @property       	int        			$createdby          Time this record is created
 * @property       	DateTime 	   		$modifiedon         Time this record is last modified
 * @property       	int         		$modifiedby         Time this record is last modified
 * @property       	int       			$status             The status for this record.  (Active / Inactive)
 *
 * @author  Cheok <cheok@silverstream.my>
 */
class MyPartnerSapSettingCode extends SnapObject {

    protected function reset() {
        $this->members = [
            'id'    => null,
            'partnerid' => null,
            'tradebpvendor' => null,
            'tradebpcus' => null,
            'nontradebpvendor' => null,
            'nontradebpcus' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null
        ];
    }

    public function isValid()
    {
        if (!strlen($this->members['tradebpvendor']) && !strlen($this->members['tradebpcus'])
            && !strlen($this->members['nontradebpvendor']) && !strlen($this->members['nontradebpcus']) ) {
                throw new InputException("At least one BP code must be entered", InputException::FIELD_ERROR);
        }

        return true;
    }

}