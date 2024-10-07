<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;

Use Snap\object\user;

/**
 * Implementation of usersession class for the Snap Application framework
 *
 * @author  Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.base
 */
class usersession implements IUserSession
{
    Use TLogging;

    /* session string key used in mxae session */
    const SESSION_STRING = 'snsess';

    /**
    * The user object
    *
    * This is the user object that is associated with this user session
    *
    * @var      user
    */
    private $user = null;

    /**
    * The user object
    *
    * This is the user object that is associated with this virtual user session
    *
    * @var      user
    */
    private $virtualUser = null;

    /**
    * The userLog object
    *
    * This is the userlog object that is associated with this user session
    *
    * @var      userLog
    */
    private $userlog = null;

    /**
    * The username used to login to this system
    *
    * @var      string
    */
    private $username = '';

    /**
    * The Crypt object
    *
    * This is crypto engine to encrypt/decrypt session data
    *
    * @var      \DateTime
    */
    private $cryto = null;

    /**
    * The session id
    *
    * @var      string
    */
    private $sessId = '';

    /**
    * The \DateTime object
    *
    * This is the current login time in \DateTime object that is associated with this user session
    *
    * @var      \DateTime
    */
    private $loginTime = null;

    /**
    * The \DateTime object
    *
    * This is the last login time in \DateTime object of the previous session
    *
    * @var      \DateTime
    */
    private $lastLoginTime = null;

    /**
    * The \DateTime object
    *
    * This is the last access time in \DateTime object of the current session
    *
    * @var      \DateTime
    */
    private $lastAccessTime = null;

    /**
    * The expiration time in seconds after N seconds of inactive time
    *
    * Default: Session will expire after 5 minutes or 300 seconds of inactive time. 0 means never expire.
    *
    * @var      integer
    */
    private $expiredTime = 300;

    /**
    * The clear text password of the current user session
    *
    * Default: Session will expire after 5 minutes or 300 seconds of inactive time. 0 means never expire.
    *
    * @var      integer
    */
    private $password = '';

    /**
    * The timezone offset in minutes based on UTC
    *
    * Default: 0 minutes in timezone offset
    *
    * @var      integer
    */
    private $timezoneOffset = 0;

    /**
    * The DateTimezone object representing the current user session
    *
    * Default: null
    *
    * @var      DateTimezone
    */
    private $timezone = null;

    /**
    * Current URL query string
    *
    * Default: empty
    *
    * @var      string
    */
    private $queryString = '';

    private $app = null;

    /*
    * Indicates if the session is for the SmartClient based GUI.  It has different requirements in password length
    * and password verification processes.
    *
    * @var boolean
    * @access private
    */
    //private $smartAMS = false;

    /*
    * Constructor function
    *
    * @access public
    */
    public function __construct(App $app)
    {
        $this->app = $app;
        //$this->log(__CLASS__ . ': Usersession::__constructor('.print_r($smartAMS, true).')', SNAP_LOG_ERROR);
        $this->crypto = new crypt;
        $this->crypto->setCipher('twofish'); 	// set the cipher
        $this->crypto->setMode('cfb'); 			// set encryption mode
        $serverKey = '';
        if (isset($_SERVER['SERVER_ADDR'])) {
            $serverKey = $_SERVER['SERVER_ADDR'];
        }
        $this->crypto->setKey('Db39K6Qz'.$serverKey); 	// set key
        $this->loginTime = new \DateTime("0000-00-00 00:00:00");
        $this->lastLoginTime = new \DateTime("0000-00-00 00:00:00");
        $this->lastAccessTime = new \DateTime("0000-00-00 00:00:00");
        $this->sessId = session_id();
    }

    /**
     * This method is meant to be overridden by derived class to check post validation of any other checks before
     * allow the session to pass through.
     *
     * @return boolean True means the session can pass through and login the user.  False will mean the user session not successful.
     */
    public function onPostValidationCheck()
    {
        /*		// partner specific checking
                if ($this->user->partnerid > 0) {
                    // check if partner is active
                    $partner = new mxPartner;
                    $res = $partner->query("SELECT #p#status FROM {$partner->getTableName()} WHERE #p#id={$partner->quote($this->user->partnerid)}");
                    $partner = $this->app->getController()->partnerFactory()->getById($this->user->partnerid, ['status']);
                    if (!$partner || !$partner->isActive()) {
                        $this->log(__CLASS__ . ": Partner account '{$this->user->partnerid}:{$this->user->code}' is not active!", SNAP_LOG_WARNING);
                        //$this->setLastError(MX_ERR_USER_ACCOUNT_EXPIRED, array(), __FUNCTION__, __LINE__);
                        return false;
                    }
                    // check if for ip restriction
                    if (!$this->app->getController()->iprestrictionFactory()->canPartnerLogin($this->user->partnerid, $this->app->getRemoteIP())) {
                        return false;
                    }
                }
        */
        return true;
    }

    /**
    * Initialize current user session if it exists
    *
    * @return boolean True if valid user session exists. Otherwise, it returns False.
    */
    public function initSession()
    {
        //$this->log(__CLASS__ . ': initSession(): '.$_SERVER['QUERY_STRING'], SNAP_LOG_ERROR);
        $this->log('Invoking UserSession::initSession()...');
        $userid = 0;
        //$type = 'xpay';
        if (null === $this->user) {
            $this->user = $this->app->getController()->getStore('user')->create();
        }
        // check whether there is an existing user session
        if (isset($_SESSION[self::SESSION_STRING])) {
            $sessdat = $_SESSION[self::SESSION_STRING];
        }
        if (isset($sessdat) && 0 < strlen($sessdat)) {		// existing user session available
            $this->log(__CLASS__ . ': Decoding existing sessdat ('.$_SESSION[self::SESSION_STRING].')...', SNAP_LOG_DEBUG);
            // decrypt the encrypted session data
            //$sessdat = $this->crypto->decrypt($sessdat);
            if (0 < strlen($sessdat)) {		// able to decrypt successfully?
                $regs = explode('|', $sessdat);
                $userid = intval($regs[0]);
                $userlogid = $regs[1];
                //$this->smartAMS = $regs[7];
                $this->log("Existing session with userid=$userid found!");
            }
        }
        // $userid and $type must be valid
        if (0 < $userid) {
            $this->user = $this->app->getController()->getStore('user')->getById($userid);
            $this->log("User with id $userid has username {$this->user->username}");
        }
        if ($this->user && 0 < $this->user->id) {
            // check if $this->user contains a valid user record
            if (0 < $this->user->id) {
                $this->log("Validating existing session with username={$this->user->username}...");
                if (0 == $userlogid) {
                    $this->log(__CLASS__ . ': Userlog ID is empty!', SNAP_LOG_ERROR);
                //$this->setLastError(MX_ERR_INVALID_SESSION, array(), __FUNCTION__, __LINE__);
                } else {
                    $this->userlog = $this->app->getController()->getStore('userlog')->getById($userlogid, ['id', 'usrid', 'logouttime']);
                    if (! $this->userlog) {
                        $this->log(__CLASS__ . ': Unable to initialize userlog object!', SNAP_LOG_ERROR);
                    //$this->setLastError(MX_ERR_INVALID_SESSION, array(), __FUNCTION__, __LINE__);
                    } else {
                        $this->queryString = $_SERVER['QUERY_STRING'];
                        $this->loginTime = new \DateTime($regs[2]);
                        $this->lastLoginTime = new \DateTime($regs[3]);
                        $this->lastAccessTime = new \DateTime($regs[4]);
                        $this->username = base64_decode($regs[6]);
                        $this->timezoneOffset = $regs[7];
                        $GLOBALS['SNAP_SESSION_USERNAME'] = $this->username;
                        if ($this->userlog->usrid != $this->user->id) {
                            $this->log(__CLASS__ . ': Invalid session! Something is corrupted!!!!', SNAP_LOG_ERROR);
                        //$this->setLastError(MX_ERR_INVALID_SESSION, array(), __FUNCTION__, __LINE__);
                        } elseif (0 < $this->userlog->logouttime) {
                            $this->log(__CLASS__ . ': Session got kicked out by newer session!', SNAP_LOG_WARNING);
                        //$this->setLastError(MX_ERR_SESSION_TERMINATED, array(), __FUNCTION__, __LINE__);
                        } elseif ($this->isExpired()) {
                            // has expired... logout the user
                            $this->log(__CLASS__ . ': User ('.$this->user->username.') session has expired naturally based on last accessed time '.$this->lastAccessTime->format('Y-m-d H:i:s').' and expiration time of '.$this->expiredTime.' seconds', SNAP_LOG_WARNING);
                        //$this->setLastError(MX_ERR_SESSION_EXPIRED, array(), __FUNCTION__, __LINE__);
                        } elseif (! $this->user->isActive() || $this->user->isExpired()) {
                            $this->log(__CLASS__ . ': User ('.$this->user->username.') account is no longer active!', SNAP_LOG_WARNING);
                        //$this->setLastError(MX_ERR_USER_ACCOUNT_EXPIRED, array(), __FUNCTION__, __LINE__);
                        } else {
                            if ($this->onPostValidationCheck()) {
                                // finally, valid session
                                $this->log(__CLASS__ . ": Valid user session found for '{$this->user->username}' (tzOffset = {$this->timezoneOffset}) ...", SNAP_LOG_DEBUG);
                                $this->password = base64_decode($regs[5]);
                                return true;
                            }
                        }
                    }
                }
            } else {
                $this->log(__CLASS__ . ': Failed to get user object!', SNAP_LOG_ERROR);
                //$this->setLastError(MX_ERR_INVALID_SESSION, array(), __FUNCTION__, __LINE__);
            }
        } else {
            $this->log(__CLASS__ . ': Unable to initialize user object!', SNAP_LOG_ERROR);
            //$this->setLastError(MX_ERR_INVALID_SESSION, array(), __FUNCTION__, __LINE__);
        }
        return false;
    }

    /**
    * Attempts to validate and login a user
    *
    * @param string $username Name of the user, can be empty
    * @param string $password The password to be used
    * @param boolean $smartAMS Indicate whether this session is in AJAX / Smart mode
    * @param array $extraParams Extra parameters to be passed in validating/authenticating the user session
    *
    * @return boolean True if username and password is correct or session is validated already. Otherwise, it returns False.
    */
    public function login($username = '', $password = '', $extraParams = array())
    {
        $this->log('Invoking UserSession::login()...');
        if (null === $this->user) {
            $this->user = $this->app->getController()->getStore('user')->create();
        }
        // attempt to check for existing session. if exist, the initialize user session based on it
        if ($this->initSession()) {
            $this->updateSession();
            // valid user session already exists
            return true;
        } else {
            // not valid
            $this->logout();
        }
        //if ($this->getLastError() == MX_ERR_SESSION_EXPIRED) return false;
        //End Add 2009/5/4
        //$this->smartAMS = $smartAMS;

        //Added by Devon on 2012/8/5 to make the checking of username (email) case insensitive.
        $username = strtolower(trim($username));
        //End Add by Devon on 2012/8/5

        //$type = 'xpay';
        //if ($this->user->setSignature()) {
        if (0 < strlen($username) && 0 < strlen($password)) {
            // check suspended
            if($this->app->getConfig()->{'projectBase'} == 'BSN'){
                $isSuspend = false;
                $check_user = $this->app->userStore()->searchTable()->select()->where('username',$username)->orderBy('id', 'desc')->one();
                if($check_user){
                    if($check_user->status == User::STATUS_SUSPENDED){
                        $isSuspend = true;
                    }
                }
                if($isSuspend){
                    header('location: /index.php?action=invalid_signon');
                    return false;
                }
                else{
                    if(isset($_SESSION['failed_attempt'][$username])){
                        if($_SESSION['failed_attempt'][$username] == 3){
                            $usr = $this->app->userStore()->searchTable()->select()->where('username',$username)->orderBy('id', 'desc')->one();
                            if($usr){
                                $usr->status = User::STATUS_SUSPENDED;
                                $usr = $this->app->userStore()->save($usr);
                            }
                            $_SESSION['failed_attempt'][$username] = 0;
                            header('location: /index.php?action=invalid_signon');
                            return false;
                        }
                    }
                }
            }

            // decrypt encrypted password
            $password = base64_encode(pack('H*', $password));
            $crypto = new crypt;
            $crypto->setCipher('tripledes'); 	// set the cipher
            $crypto->setMode('ecb'); 			// set encryption mode
            $crypto->setKey($this->getSalt()); 	// set key
            $password = trim($crypto->decrypt($password, 1));
            //$extraParams['code'] = strtoupper(trim($extraParams['code']));
            //$this->log(__CLASS__ . ': Username = '.$username.', Password = '.$password.', Salt = '.$_SESSION['sessionSalt'], SNAP_LOG_WARNING);
            $this->log(__CLASS__ . ": Validating user '$username' (code = '{$extraParams['code']}')...", SNAP_LOG_DEBUG);
            if ($this->user->validateUser($username, $password, strtoupper($extraParams['code']))) {
                if (! $this->onPostValidationCheck()) {
                    return false;
                }

                if (isset($extraParams['tzoffset'])) {
                    $this->timezoneOffset = $extraParams['tzoffset'];
                }
                //print_r($extraParams);
                $this->lastLoginTime = $this->user->lastlogin;
                $this->loginTime = new \DateTime;
                if (! $this->user->updateLastLogin($this->loginTime, $this->app->getRemoteIP())) {
                    $this->log(__CLASS__ . '::' .  __METHOD__ . ": Unable to update the last login for user  " . $this->user->username, SNAP_LOG_ERROR);
                    // $this->lastError = $this->user->getLastError();
                    // $this->lastErrorMessage = $this->user->getLastErrorMessage();
                    return false;
                }
                $this->lastAccessTime = \DateTime::createFromFormat('U', date('U'));
                $this->password = $password;

                $this->userlog = $this->app->getController()->getStore('userlog')->create();
                if (! $this->userlog->login($this->user)) {
                    $this->log(__CLASS__ . '::' . __METHOD__ . ": Unable to add in a userlog for  " . $this->user->username, SNAP_LOG_ERROR);
                    // $this->lastError = $this->userlog->getLastError();
                    // $this->lastErrorMessage = $this->userlog->getLastErrorMessage();
                    return false;
                }
                // new gtp2 migration new login reset password -- start
                $user_resetrequestedon = $this->user->resetrequestedon;
                $cut_off_date = new \DateTime('2021-11-01'); // db will put same date as reference for migration new login reset 
                if (($this->user->passwordmodifiedon <= $cut_off_date) && $this->user->type == 'Customer'){
                    $length = 10;
                    $temp_reset_token = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
                    $_SESSION['newgtp2login'] = $temp_reset_token;
                    $this->user->resettoken = $temp_reset_token;
                    $this->app->userStore()->save($this->user);
                    header('location: /?action=migration_new_login');
                    return false;
                    // return 'migration_new_login';
                }
                // new gtp2 migration new login reset password -- end
                $this->updateSession();
                $this->resetSalt();

                //Added here to log user's first login...
                //$this->addTrail( $this->user, MX_AUDIT_LOGIN);
                $this->username = strtolower($username);
                $GLOBALS['SNAP_SESSION_USERNAME'] = $this->username;
                $this->log(__CLASS__ . ": Registered a userlog ID #{$this->userlog->id} for this '{$this->username}' successful session (tzOffset = {$this->timezoneOffset})...", SNAP_LOG_DEBUG);
                return true;
            } else {	
                $this->app->getStore('userlog')->addInvalidLogin($username);
                if($this->app->getConfig()->{'projectBase'} == 'BSN'){
                    if(isset($_SESSION['failed_attempt'][$username])){
                        $_SESSION['failed_attempt'][$username] +=1;
                        if($_SESSION['failed_attempt'][$username] == 3){
                            $usr = $this->app->userStore()->searchTable()->select()->where('username',$username)->orderBy('id', 'desc')->one();
                            if($usr){
                                $usr->status = User::STATUS_SUSPENDED;
                                $usr = $this->app->userStore()->save($usr);
                            }
                            $_SESSION['failed_attempt'][$username] = 0;
                            header('location: /index.php?action=invalid_signon');
                            return false;
                        }
    
                    }
                    else{
                        $_SESSION['failed_attempt'][$username] = 1;
                    }
                }
                // invalid login
                $this->log(__CLASS__ . ": Invalid login attempt by '{$username}'!", SNAP_LOG_WARNING);
            }
        } else {
        }
        return false;
    }

    /**
    * Logout the user from this current session.
    *
    * This method will logout the user from its session.  It will also clear the session data.
    *
    * @return void
    */
    public function logout($bDestroySession = false)
    {
        $this->log('Invoking UserSession::logout()...');
        if ($this->user instanceof user) {
            if ($this->userlog instanceof userLog) {
                $this->userlog->logout();
            }
            if (0 < strlen($this->user->username)) {
                $this->log(__CLASS__ . ': Clearing a session from "'.$this->user->username.'"!', SNAP_LOG_WARNING);
            }
        }
        $this->updateSession(true);
        if ($bDestroySession) {
            $this->log(__CLASS__ . ': Destroying sesssion...', SNAP_LOG_DEBUG);
            session_unset();
            session_destroy();
        }
        return true;
    }

    /**
    * Update the session data
    *
    * This function will update the session with the appropriate data accordingly. However, if the session has expired, the session data will be cleared.
    *
    * @param  boolean $bClear If set to true, the session data will be cleared
    *
    * @return void
    */
    private function updateSession($bClear = false)
    {
        $this->log(__CLASS__ . ': Invoking updateSession...', SNAP_LOG_DEBUG);
        if ($this->isExpired() || $bClear == true) {
            //If it is not using the smartclient based ams, we clear the string.  Otherwise, we need the salt key to continue.
            if ($bClear) {
                $this->log(__CLASS__ . ' : Session forced to be cleaered.... '.$_SESSION[self::SESSION_STRING], SNAP_LOG_DEBUG);
            } else {
                $this->log(__CLASS__ . ': Session has expired.... '.$_SESSION[self::SESSION_STRING], SNAP_LOG_DEBUG);
            }
            $_SESSION[self::SESSION_STRING] = '';
            //$this->log(__CLASS__ . ': 2 sessdat = '.$_SESSION[self::SESSION_STRING], SNAP_LOG_DEBUG);
            unset($_SESSION[self::SESSION_STRING]);
        //We will add that the user has logged out here...
        } else {
            if (! ($this->loginTime instanceof \DateTime)) {
                $this->loginTime = new \DateTime();
            }
            if (! ($this->lastLoginTime instanceof \DateTime)) {
                $this->lastLoginTime = new \DateTime();
            }

            $sessdat = $this->user->id.'|'.$this->userlog->id.'|'.$this->loginTime->format('Y-m-d H:i:s').'|'.$this->lastLoginTime->format('Y-m-d H:i:s').'|'.date('Y-m-d H:i:s').'|'.base64_encode($this->password).'|'.base64_encode($this->user->username).'|'.$this->timezoneOffset.'|'.base64_encode($_SERVER['QUERY_STRING']);
            $this->log("Setting session data $sessdat", SNAP_LOG_DEBUG);
            //$_SESSION[self::SESSION_STRING] = $this->crypto->encrypt($sessdat);
            $_SESSION[self::SESSION_STRING] = $sessdat;
        }
    }

    /**
    * Store personal data for this user session with a name
    *
    * @param  string $name Name of data to store under
    * @param  mixed $object Can be an object or any data type variable (including array)
    *
    * @return void
    * @see UserSession::retrieve()
    */
    public function store($name, $object)
    {
        //return $this->app->store($name, $object);
        $_SESSION[$name] = $object;
    }

    /**
    * Retrieve personal data for this user session by name
    *
    * @param  string $name Name of data to retrieve from
    *
    * @return mixed The stored data
    * @see UserSession::store()
    */
    public function retrieve($name)
    {
        //return $this->app->retrieve($name);
        return $_SESSION[$name];
    }

    /**
    *  Get the current session's salt to be used for encrypting and decrypting data
    *
    *  @return string
    */
    public function getSalt()
    {
        $salt = $this->retrieve('sessionSalt');
        if (null == $salt || empty($salt)) {
            $salt = Common::getRandomPassword(24);
            $this->store('sessionSalt', $salt);
        }
        return $salt;
    }

    /**
    *  Reset and clear the current session's salt
    *
    *  @return string
    */
    public function resetSalt()
    {
        $this->store('sessionSalt', '');
    }

    /**
    * Get username of the current user session
    *
    * @param 	boolean $bFull Indicate whether to get the full username used to sign in to the system
    *
    * @return   string Username of the current user session
    */
    public function getUsername()
    {
        if ($this->isValid()) {
            return $this->getUser()->username;
        }
        return '';
    }

    /**
    * Get user id of the current user session
    *
    * @return   integer User ID of the current user session
    */
    public function getUserId()
    {
        if ($this->isValid()) {
            return $this->getUser()->id;
        }
        return 0;
    }

    /**
    * Get user type of the current user session
    *
    * @return   integer User Type of the current user session
    */
    public function getUserType()
    {
        if ($this->isValid()) {
            return $this->getUser()->type;
        }
        return 0;
    }

    /**
    * Get user object of the current user session
    *
    * @return   user
    */
    public function getUser()
    {
        if ($this->isVirtual()) {
            return $this->virtualUser;
        }
        return $this->user;
    }

    /**
    * Get password of the current user session
    *
    * @return   string Password of the current user session
    */
    public function getPassword()
    {
        return $this->password;
    }

    /**
    * Get last login time of the previous user session
    *
    * @return   \DateTime
    */
    public function getLastLogin()
    {
        return $this->lastLoginTime;
    }

    /**
    * Get last access time of the current user session
    *
    * @return   \DateTime
    */
    public function getLastAccess()
    {
        return $this->lastAccessTime;
    }

    /**
    * Get login time of the current user session
    *
    * @return   \DateTime
    */
    public function getLoginTime()
    {
        return $this->loginTime;
    }

    /**
    * Get the current session id
    *
    * @return   string
    */
    public function getSessionId()
    {
        return $this->sessId;
    }

    /**
    * Get the current session timezone offset value in minutes for this user from where he/she is accessing
    *
    * @return   integer Timezone offset in minutes
    */
    public function getTimezoneOffset()
    {
        return $this->timezoneOffset;
    }

    /**
    * Get the current session DateTimezone object for this user from where he/she is accessing
    *
    * @return   DateTimezone
    */
    public function getTimezone()
    {
        if (null == $this->timezone) {
            $tzOffset = -($this->getTimezoneOffset());
            $tzDecimal = 0;
            if (0 < $tzOffset%60) {
                $tzDecimal = 1;
            }
            $tzName = 'GMT'.((0 <= $tzOffset)?'+':'-').number_format($tzOffset/60, $tzDecimal);
            $this->timezone = new \DateTimezone($tzName);
        }
        return $this->timezone;
    }

    /**
    * Get the current session URL query string
    *
    * @return   string
    */
    public function getQueryString()
    {
        if (0 == strlen($this->queryString) && 0 < strlen($_SERVER['QUERY_STRING'])) {
            $this->queryString = $_SERVER['QUERY_STRING'];
        }
        return $this->queryString;
    }

    /**
    * Check if the current user session has expired
    *
    * @return   boolean True if it has. Otherwise false.
    */
    public function isExpired()
    {
        // added by ivan @ 9:30pm Jun 17, 2013 GMT+8
        //return false;
        // end of added
        if (0 == $this->expiredTime) {
            // session does not expire
            return false;
        }
        //if ($this->user === null) return true;
        //if ($this->user->id == 0) return true;
        $lastAccessedTime = 0;
        if ($this->lastAccessTime instanceof \DateTime) {
            if ('0000-00-00 00:00:00' == $this->lastAccessTime->format('Y-m-d H:i:s')) {
                $this->log(__CLASS__ . ': Last Access Time is not set yet???', SNAP_LOG_DEBUG);
                return false;
            } else {
                $lastAccessedTime = $this->lastAccessTime->format('U');
                $this->log(__CLASS__ . ': Last Access Time was '.$this->lastAccessTime->format('Y-m-d H:i:s'), SNAP_LOG_DEBUG);
            }
        }
        $expiryInt = $lastAccessedTime + $this->expiredTime;
        //$tmpDate = new \DateTime(date('Y-m-d H:i:s'));fion
        //$currentInt = $tmpDate->format('U');
        $currentInt = time();
        //$dbg = debug_backtrace();
        //$this->log(__CLASS__ . p: rint_r($dbg, true), SNAP_LOG_ERROR);
        //ob_start();
        //debug_print_backtrace();
        //$dbginfo = ob_get_contents();
        //ob_end_clean();
        //file_put_contents('/tmp/mxdebug.log', print_r($dbg, true)."\n\n".$dbginfo);
        //$this->log(__CLASS__ . ': Session Last Accessed Time: '.$this->lastAccessTime->format('U').', Expiry Time: '.$this->expiredTime.', Current Time: '.$currentInt, SNAP_LOG_ERROR);
        $this->log(__CLASS__ . ': Current Time = '.$currentInt.', Last accessed time = '.$lastAccessedTime.', ExpiryTime = '.$expiryInt, SNAP_LOG_DEBUG);

        if ($currentInt <= $expiryInt) {
            // has not expired yet.
            return false;
        }
        $this->log(__CLASS__ . '::'.__FUNCTION__.'() - Session for "'.$this->username.'" has expired!', SNAP_LOG_WARNING);
        // session has expired
        return true;
    }

    /**
    * Set the expired time for the user session
    *
    * @param    integer $time Number of seconds of inactive time before expiring the user session. 0 means never expire.
    *
    * @return   void
    */
    public function setExpiredTime($time)
    {
        $this->expiredTime = intval($time);
        $this->log(__CLASS__ . ': Session expiration time set at '.$this->expiredTime.' seconds', SNAP_LOG_DEBUG);
    }

    /**
    * Get the expired time for the user session
    *
    * @return   integer Number of seconds of inactive time before expiring the user session. 0 means never expire.
    */
    public function getExpiredTime()
    {
        return $this->expiredTime;
    }

    public function isValid()
    {
        if ($this->getUser() && ! $this->isExpired()) {
            return true;
        }
        return false;
    }

    public function isVirtual()
    {
        return ($this->virtualUser instanceof user && 0 < $this->virtualUser->id);
    }

    public function getRealUser()
    {
        return $this->user;
    }

    // function setVirtual($username, $code, $password = null) {
    // 	if ($this->user instanceof user && ($this->user->hasRight(MX_OBJECT_USERSHADOW, MX_RIGHT_VIEW) || $this->user->isInternalAgent())) {
    // 		// must be operator then only can do this
    // 		if (!$this->user->isOperator() && !$this->user->isInternalAgent()) return false;
    // 		if ($password !== null) {
    // 			$this->log('Virtual shadowing - validating secondary password...');
    // 			if ($this->user->isPassword2Empty()) {
    // 				//$this->setLastError(MX_ERR_GENERAL, array(gettext('Your secondary password is currently not set. Please set it first before using this feature.'), -1), __FUNCTION__, __LINE__);
    // 				return false;
    // 			}
    // 			if (!$this->user->validatePassword2($password)) {
    // 				$this->lastError = $this->user->getLastError();
    // 				$this->lastErrorMessage = $this->user->getLastErrorMessage();
    // 				return false;
    // 			}
    // 			$this->log('Virtual shadowing - password validated!');
    // 		}
    // 		$code = preg_replace('/[^0-9A-Z]/', '', strtoupper($code));
    // 		$username = preg_replace('/[^0-9a-z_]/', '', strtolower($username));
    // 		$vuser = new user;
    // 		if ($vuser->getBy(array('code' => $code, 'username' => $username))) {
    // 			if ($this->user->isInternalAgent()) {
    // 				$agentmanagerid = 0;
    // 				$gateway = new mxGateway;
    // 				if ($gateway->getBy(array('partnerid' => $vuser->partnerid, 'status' => mxGateway::STATUS_ACTIVE), array('id', 'agentmanagerid'))) {
    // 					$agentmanagerid = $gateway->agentmanagerid;
    // 				}
    // 				if ($this->user->partnerid != $gateway->agentmanagerid) {
    // 					$this-__CLASS__ . >: log("Failed to set iagent mode for {$code}::{$username} with gateway parter_id#{$vuser->partnerid}!", SNAP_LOG_ERROR);
    // 				}
    // 			}
    // 			$this->virtualUser = $vuser;
    // 			$this-__CLASS__ . >: log("Virtual shadowing succeeded: {$code}::{$username}!", SNAP_LOG_INFO);
    // 			return true;
    // 		} else {
    // 			$this-__CLASS__ . >: log("Failed to set virtual user: {$code}::{$username} does not exists!", SNAP_LOG_ERROR);
    // 		}
    // 	} else {
    // 		$this-__CLASS__ . >: log("Failed to set virtual user: no rights!", SNAP_LOG_ERROR);
    // 	}
    // 	return false;
    // }
}
?>
