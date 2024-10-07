<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\object;

Use Snap\InputException;

/**
 * Encapsulates the iprestriction table on the database
 *
 * This class encapsulates the iprestriction table data
 * information
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $ID                 ID of the system
 * @property        string              $restricttype       Restriction type (LOGIN)
 * @property        string              $partnertype        Type of partner (HQ, BRANCH)
 * @property        int                 $partnerid          Partner ID
 * @property        string              $ip                 Allowed IP
 * @property        string              $remark             Remark
 * @property        int                 $status             The status for this record.  (Active / Inactive)
 * @property        mxDate              $createdon          Time this record is created
 * @property        mxDate              $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Megat
 * @version 1.0
 * @created 2017/4/14 11:58 AM
 */

class IPRestriction extends SnapObject {

    // Restrict Type
    const RESTRICT_LOGIN = 'LOGIN';

    const PARTNER_HQ = 'HQ';
    const PARTNER_BRANCH = 'BRANCH';

    const STATUS_INACTIVE = "0";
    const STATUS_ACTIVE = "1";

    /**
     * This method will initialise the 2 array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's contractor.
     * 
     * @return None
     */
    protected function reset() {
        $this->members = array(
            'id' => null,
            'restricttype' => null,
            'partnertype' => null,
            'partnerid' => null,
            'ip' => null,
            'remark' => '',
            'status' => null,
            'createdby' => null,
            'createdon' => null,
            'modifiedby' => null,
            'modifiedon' => null
        );
    }

    /**
     * Check if all values in $this->members array is valid  this is where the object
     * member variables get validated for legal values inherited class should
     * implement this abstract function
     * @abstract
     * @access public
     * @return  true if all member data has valid values. Otherwise false.
     */
    function isValid() {
        if ($this->members['restricttype'] == '') {
            throw new InputException( gettext('Restrict type is required'), InputException::FIELD_ERROR, 'restricttype');
        }

        if ($this->members['ip'] == '') {
            throw new InputException( gettext('IP Address is required'), InputException::FIELD_ERROR, 'ip');
        } 
        else if ($this->members['ip'] != '') {
            //if (!filter_var($this->members['ip'], FILTER_VALIDATE_IP)) {
            if (!preg_match('/^(\d{1,3}|\*)\.(\d{1,3}|\*)\.(\d{1,3}|\*)\.(\d{1,3}|\*)$/', $this->members['ip'])) {
                throw new InputException( gettext('Invalid IP Address'), InputException::FIELD_ERROR, 'ip');
            }
        }

        $object = $this->getStore()->searchTable()->select()
            ->where('ip', $this->members['ip'])
            ->andWhere('restricttype', $this->members['restricttype'])
            ->andWhere('partnerid',  $this->members['partnerid'])
            ->andWhere('partnertype',  $this->members['partnertype'])
            ->execute();
        
        // if ($object && $object->id != $this->members['id']) {
        if ($object) {
            foreach ($object as $key => $value) {
                if($value->id != $this->members['id']) {
                    throw new InputException( gettext('Similar record already exists'), InputException::FIELD_ERROR, 'ip');
                }
            }
        }

        return true;
    }
}
?>