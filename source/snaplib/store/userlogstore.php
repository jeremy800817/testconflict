<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2016 - 2018
 * @copyright Silverstream Technology Sdn Bhd. 2016 - 2018
 */
Namespace Snap\store;

Use Snap\object\vendor;
Use PDO;
use PDOException;
use PDORow;

/**
 * This class implements simple functions to extra relevant data from userlogs
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store
*/

class userlogstore extends dbdatastore
{

    /**
    * Get all of active session record for user referenced by $idorname
    *
    * @param mixed $idorname The user id (integer) or username (string - alphanumeric)
    * @param integer $sessionTimeOut The user session timeout duration in seconds
    *
    * @return	array Array of object mxMemberLog which correspond to all the active session record for user referenced by $idorname
    */
    public function getAllActiveSessions($idorname = '', $sessionTimeOut = 0, $bCountOnly = false)
    {
        $queryObj = $this->getStore()->searchTable()->select();
        if (is_numeric($idorname)) {
            $queryObj = $queryObj->where('usrid', '=', $idorname);
        } else {
            $queryObj = $queryObj->where('usrid', '>', 0);
            if (0 < strlen($idorname)) {
                $queryObj = $queryObj->andWhere('username', '=', $idorname);
            }
        }
        $lastActiveMin = 0;
        if (0 < $sessionTimeOut) {
            $lastActiveMin = $_SERVER['REQUEST_TIME'] - $sessionTimeOut;
        }
        $objects = $queryObj->andWhere('logintime', '>', 0)
                             ->andWhere('logouttime', '=', 0)
                             ->andWhere('lastactive', '>=', $lastActiveMin)
                             ->execute();
        if ($bCountOnly) {
            return count($objects);
        }
        return $objects;
    }

    /**
    * Get the number of active session for user referenced by $idorname
    *
    * @param mixed $idorname The user id (integer) or username (string - alphanumeric)
    * @param integer $sessionTimeOut The user session timeout duration in seconds
    *
    * @return	integer The number of active session for user referenced by $idorname
    */
    public function getActiveSessionCount($idorname, $sessionTimeOut = 0)
    {
        return $this->getAllActiveSessions($idorname, $sessionTimeOut, true);
    }

    /**
    * Deactivate any and all active sessions for user referenced by $idorname
    *
    * @param mixed $idorname The user id (integer) or username (string - alphanumeric)
    * @param integer $sessionTimeOut The user session timeout duration in seconds
    *
    * @return	boolean Return true if successful. Otherwise false.
    */
    public function doDeactiveSessions($idorname, $sessionTimeOut = 0)
    {
        $objects = $this->getAllActiveSessions($idorname, $sessionTimeOut);
        if (is_array($objects)) {
            foreach ($objects as $object) {
                $object->logouttime = time();
                $this->save($object);
            }
        }
    }

    /**
    * Add an invalid login attempt log record
    *
    * @param string $username The user's username
    *
    * @return	boolean True if successful.  Otherwise false.
    */
    public function addInvalidLogin($username)
    {		
        if (0 == strlen($username)) {
            return false;
        }
        $object = $this->create([
            'usrid' => 0,
            'username' => preg_replace('/[^-0-9a-zA-z@._]/', '', $username),
            'sessid' => session_id(),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'browser' => $_SERVER['HTTP_USER_AGENT'],
            'logintime' => $_SERVER['REQUEST_TIME'],
            'lastactive' => 0,
            'logouttime' => $this->members['logintime']
        ]);
        return $this->save($object);
    }
}
?>