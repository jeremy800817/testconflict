<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2023
 * @copyright Silverstream Technology Sdn Bhd. 2023
 */
Namespace Snap\object;

/**
* Encapsulates the RegisterRemarks table on the database
*
* @property-read     int           $id               Primary key
* @property          string        $mykadno          Identity No.
* @property          string        $remarks          Remarks
* @property          int           $status           status
* @property          string        $createdby        USER ID
* @property          DateTime      $createdon        DateTime
* @property          string        $modifiedby       USER ID
* @property          DateTime      $modifiedon       DateTime
*
* @author   AmirNazhan <amirnazhan.nizar@silverstream.my>
* @version  1.0
* @package  data object
*/
class OtcRegisterRemarks extends SnapObject {

	const TYPE_REGISTER = 'Registration';
    const TYPE_BUY = 'Buy';
    const TYPE_SELL = 'Sell';
    const TYPE_CONVERSION = 'Conversion';

    const APPROVED = 1;
    const REJECTED = 2;
    const PENDING = 0;
    
    /**
    * Initialisation of the class.  Overwritten the base class method.
    *
    * @access   public
    * @return   void
    */
    function reset() {
        $this->members = array(
            'id' => 0,
            'type' => '',
            'mykadno' => '',
            'remarks' => '',
            'status' => '',
            'createdby' => '',
            'createdon' => '',
            'modifiedby' => '',
            'modifiedon' => '',
        );
    }

    /**
    * Check if all values in $this->members array is valid
    *
    * Check if all values in $this->members array is valid (eg. integer can only contain numbers)
    *
    * @access   public
    * @return   true if all member data has valid values. Otherwise false.
    */
    function isValid() {
        return true;
    }

}
?>