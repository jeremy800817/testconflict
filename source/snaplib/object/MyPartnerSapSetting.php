<?php

namespace Snap\object;

use Snap\InputException;

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

 /**
 * Sap Fee settings
 *
 * This class encapsulates the tag table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$id 				ID of the system
 * @property       	int 				$partnerid        	Partner ID
 * @property       	int 				$transactiontype  	Transaction/Fee type (Storage/Admin/Processing/Conversion)
 * @property       	int 				$itemcode        	Item code
 * @property        string              $tradebpvendor      Trade BP Code for Vendor
 * @property        string              $tradebpcus         Trade BP Code for Customer
 * @property        string              $nontradebpvendor      Non-Trade BP Code for Vendor
 * @property        string              $nontradebpcus         Non-Trade BP Code for Customer
 * @property        string              $action             SAP action
 * @property        string              $gtprefno           GTP Ref no
 * @property       	DateTime   			$createdon          Time this record is created
 * @property       	int        			$createdby          Time this record is created
 * @property       	DateTime 	   		$modifiedon         Time this record is last modified
 * @property       	int         		$modifiedby         Time this record is last modified
 * @property       	int       			$status             The status for this record.  (Active / Inactive)
 *
 * @author  Cheok <cheok@silverstream.my>
 */
class MyPartnerSapSetting extends SnapObject {

    public const TYPE_STORAGE_FEE = "STORAGE_FEE";
    public const TYPE_CONVERSION_FEE = "CONVERSION_FEE";
    public const TYPE_PROCESSING_FEE = "PROCESSING_FEE";
    public const TYPE_ADMIN_FEE = "ADMIN_FEE";

    protected function reset() {
        $this->members = [
            'id'    => null,
            'partnerid' => null,
            'transactiontype' => null,
            'itemcode'  => null,
            'tradebpvendor' => null,
            'tradebpcus' => null,
            'nontradebpvendor' => null,
            'nontradebpcus' => null,
            'action'        => null,
            'gtprefno'      => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null
        ];
    }

    public function isValid()
    {
        $this->validateRequiredField($this->members['transactiontype'], 'transactiontype');
        $this->validateRequiredField($this->members['itemcode'], 'itemcode');
        $this->validateRequiredField($this->members['action'], 'action');
        if (!strlen($this->members['tradebpvendor']) && !strlen($this->members['tradebpcus'])
            && !strlen($this->members['nontradebpvendor']) && !strlen($this->members['nontradebpcus']) ) {
                throw new InputException("At least one BP code must be selected", InputException::FIELD_ERROR);
        }

        return true;
    }

}