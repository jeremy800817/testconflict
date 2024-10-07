<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\object;

/**
* Encapsulates the userlog table on the database
*
* @property-read     int        $id               Primary key <br>
* @property          int        $usrid            Foreign key to table 'user'<br>
* @property          string     $username         Username of the usrid<br>
* @property          string     $sessid           Session ID<br>
* @property          string     $ip               IP address from where the user access the server<br>
* @property          string     $browser          Browser string or User Agent used to browse for this session<br>
* @property          int        $lastactive       Last accessed time of this user (unix epoch time in seconds)<br>
* @property          int        $logintime        Login time of this user (unix epoch time in seconds)<br>
* @property          int        $logouttime       Logout time of this user (unix epoch time in seconds)<br>
*
* To access the data members, use $[varname]->[data member name] format.<br>
* E.g.
* echo ("The ID is: " . $this->id);<br>
* $this->created = mxDate();
*
*
* @author   Ivan Hoo <ivan@silverstream.my>
* @version  1.0
* @package  data object
*/
class UserLog extends SnapObject {

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
            'username' => '',
            'sessid' => '',
            'ip' => '',
            'browser' => '',
            'lastactive' => 0,
            'logintime' => 0,
            'logouttime' => 0
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

    /**
    * Start / login a userlog session
    *
    * Start / login a userlog session
    *
    * @access public
    * @return boolean True if successful. Otherwise false.
    */
    function login(User $user) {
        if ($this->members['id'] > 0 || $user->id == 0) {
            // already login or invalid user
            return false;
        }
        $oldRecord = $this->getStore()->searchTable()->select()
                                    ->where('usrid', '=', $user->id)
                                    ->andWhere('logouttime', '=', 0)
                                    ->execute();
        if( $oldRecord && count($oldRecord)) {
            $oldRecord = $oldRecord[0];
            $oldRecord->logouttime = time();
            $this->getStore()->save($oldRecord, ['logouttime']);
        }
        $this->members['usrid'] = $user->id;
        $this->members['username'] = $user->username;
        $this->members['sessid'] = session_id();
        $this->members['ip'] = \Snap\App::getInstance()->getRemoteIP();
        $this->members['browser'] = $_SERVER['HTTP_USER_AGENT'];
        $this->members['lastactive'] = $_SERVER['REQUEST_TIME'];
        $this->members['logintime'] = $_SERVER['REQUEST_TIME'];
        $this->members['logouttime'] = 0;
        $obj = $this->getStore()->save($this);
        foreach($this->members as $key => $value) $this->members[$key] = $obj->{$key};
        return $this;
    }

    /**
    * Close / logout a userlog session
    *
    * Close / logout a userlog session
    *
    * @access public
    * @return boolean True if successful. Otherwise false.
    */
    function logout() {
        if ($this->members['id'] == 0) {
            // object has not been initialized with ::login() yet
            return false;
        }
        $this->members['logouttime'] = $_SERVER['REQUEST_TIME'];
        return $this->getStore()->save($this, $this->members['logouttime']);
    }
}
?>