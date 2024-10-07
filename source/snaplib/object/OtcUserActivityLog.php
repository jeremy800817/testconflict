<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2023
 * @copyright Silverstream Technology Sdn Bhd. 2023
 */
Namespace Snap\object;

/**
* Encapsulates the userlog table on the database
*
* @property-read     int        $id                 Primary key
* @property          int        $usrid              Foreign key to table 'user'
* @property          string     $module             Module name
* @property          string     $action             User action
* @property          string     $activitydetail     User activity detail
* @property          string     $ip                 IP address from where the user access the server
* @property          string     $browser            Browser string or User Agent used to browse for this session
* @property          int        $activitytime       Activity time of this log (unix epoch time in seconds)
*
* @author   Jeremy <jeremy@silverstream.my>
* @version  1.0
* @package  data object
*/
class OtcUserActivityLog extends SnapObject {

    /**
    * Initialisation of the class.  Overwritten the base class method.
    *
    * @access   public
    * @return   void
    */
    function reset() {
        $this->members = array(
            'id' => 0,
            'usrid' => 0,
            'module' => '',
            'action' => '',
            'activitydetail' => '',
            'ip' => '',
            'browser' => '',
            'activitytime' => 0
        );

        $this->viewMembers = array(
            'username' => null,
            'name' => null,
            'email' => null,
            'type' => null,
            'status' => null,
            'lastlogin' => null,
            'lastloginip' => null,
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