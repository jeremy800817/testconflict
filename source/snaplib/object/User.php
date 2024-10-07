<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\object;

Use Snap\App;
Use Snap\InputException;

/**
 * Encapsulates user table
 *
 * This class encapsulates the user table data and its member data
 * information
 *
 * Data members:
 * Name					Type		Description
 * @property-read         int          $id					      integer		      Primary Key
 * @property              string       $username		    	varchar(20)		  Username
 * @property              string       $password		  	  varchar(200)		Password
 * @property              string       $oldpassword		   	varchar(255)		Old password
 * @property              string       $name		    	   	varchar(100)		Name
 * @property              string       $phoneno			      varchar(15)		  Mobile No
 * @property              string       $email			       	varchar(50)	    Email
 * @property              int          $partnerid				  integer		      Partner ID
 * @property              string       $type				      varchar(10)		  User Type
 * @property              \DateTime    $passwordmodified	(\DateTime)		  When password last modified date
 * @property              \DateTime    $createdon			    (\DateTime)	    Time this record is created
 * @property              int          $createdby			    integer		      User ID
 * @property              \DateTime    $modifiedon			  (\DateTime)	    Time this record is last modified
 * @property              int          $modifiedby			  integer		      User ID
 * @property              int          $status				    integer		      Status active(1), suspended(2)

 * @author Weng
 * @version 1.0
 * @created 16-Oct-2012 09:32:28 AM
 */
class user extends SnapObject
{
    const OBJECT_ID = 7;

    //Statuses
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_NEW = 2;
    const STATUS_EXPIRED = 3;
    const STATUS_DORMANT = 4;
    const STATUS_SUSPENDED = 5;
    const STATUS_DISABLED = 6;
    const STATUS_DISABLED_RELIEF = 7;
    const STATUS_DISABLED_RESIGN = 8;
    const STATUS_DISABLED_TEMPORARY = 9;
    const STATUS_DELETED = 10;

    const TYPE_OPERATOR = 'Operator';
    const TYPE_TRADER = 'Trader';
    const TYPE_CUSTOMER = 'Customer';
    const TYPE_SALE = 'Sale';
    const TYPE_REFERRAL = 'Referral';
    const TYPE_AGENT = 'Agent';

    const TYPE_OTC_HQ = 'HQ';
    const TYPE_OTC_REGIONAL = 'Regional';
    const TYPE_OTC_BRANCH = 'Branch';


    /**
    * maximum length of the user name
    *
    */
    const USERNAME_MAX_LEN = 20;
    /**
    * minimum length of the username
    *
    */
    const USERNAME_MIN_LEN = 2;
    /**
    * maximum length of the password to use
    *
    */
    const PASSWORD_MAX_LEN = 20;
    /**
    * Minimum length of the password used
    *
    */
    const PASSWORD_MIN_LEN = 6;

    const PASSWORD_HASH_BITS = 224;
	// Start Added by Weng on 2012/11/06 for permission
	/**
	* The roles collection array
	*
	* @var      array objects of mxRole
	* @access   private
	*/
	private $roles = array();

	/**
	 * Check if all values in $this->members array is valid  this is where the object
	 * member variables get validated for legal values inherited class should
	 * implement this abstract function
	 * @abstract
	 * @access public
	 * @return	true if all member data has valid values. Otherwise false.
	 */
	function isValid() {
		if ($this->members['expire'] == 'null') {
			$this->members['expire'] = new \DateTime('9999-12-31 00:00:00');
		}
		return true;
	}

	/**
	 * Renice all values in $this->members array (eg. date string to date object)
	 * this is where the object member variables get renice to approriate object or
	 * value inherited class should implement this abstract function
	 * @abstract
	 * @access   public
	 * @return	none
	 */
	protected function mapRawValues( $field, $rawValue) {
		switch($field) {
			case 'expire':
			case 'passwordmodified':
			case 'lastlogin':
				if(is_string($rawValue)) return new \DateTime($rawValue);
		}
		return parent::mapRawValues($field, $rawValue);
	}

    /**
     * Reset all values in $this->members array  this is where the object member
     * variables get initialized to its default values inherited class should
     * implement this abstract function
     * @abstract
     * @return	none
     */
    public function reset()
    {
        $this->members = array(
            'id' => 0,
            'username' => '',
            'password' => '',
            'oldpassword' => '',
            'name' => '',
            'phoneno' => '',
            'email' => '',
            'partnerid' => 0,
            'type' => '',
            'passwordmodifiedon' => 0,  
            // 'failtimes' => 0,
			'expire' => 0,      
			'lastlogin' => 0,
            'lastloginip' => '',
            'resettoken' => '',
            'resetrequestedon' => '',
            'createdon' => 0,
            'createdby' => 0,
            'modifiedon' => 0,
            'modifiedby' => 0,
            'status' => 0,
            'state' => '',
        );
        $this->viewMembers = array(
            'partnercode' => '',
            'partnertype' => 0,
            'partnername' => ''
        );
    }


    /**
    * Check if username is in valid and legal by checking for illegal characters and its length
    *
    * @param string $username the username value to test
    *
    * @return   boolean True if valid.  Otherwise false.
    */
    public function isUsernameValid($username)
    {
        $username = strtolower($username);
        if (strlen($username) < self::USERNAME_MIN_LEN || strlen($username) > self::USERNAME_MAX_LEN) {
            if (! ($this->members['username'] == $username && $this->members['id'] > 0)) {
                throw new InputException(sprintf(gettext('Field (%s) must not be less than %d or more than %d characters'), 'username', self::USERNAME_MIN_LEN, self::USERNAME_MAX_LEN), InputException::FIELD_ERROR, 'username_user');
                //$this->addErrorMessage('username', MX_ERR_INVALID_LENGTH1, array('username', self::USERNAME_MIN_LEN, self::USERNAME_MAX_LEN), __FUNCTION__, __LINE__);
                return false;
            }
        }
        if (! preg_match('/^[a-z][a-z0-9_@.]+$/', $username)) {
            throw new InputException(sprintf(gettext('Field (%s) must begin with an alphabet letter followed by only alphabet, number and/or underscore characters'), 'username'), InputException::FIELD_ERROR, 'username_user');
            //$this->addErrorMessage('username', MX_ERR_INVALID_VARIABLE_FORMAT, array('username'), __FUNCTION__, __LINE__);
            return false;
        }
        return true;
    }

    /**
    * Check if username already exists
    *
    * @param string $username the username value to test
    *
    * @return   boolean True if exists.  Otherwise false.
    */
    public function isUsernameExists($username)
    {
        $username = strtolower($username);
        $count = count($this->getStore()->searchTable()->select(['id'])
                         ->Where('id', '!=', ($this->members['id']) ? $this->members['id'] : 0)
                         ->andWhere('username', '=', $username)
                         ->andWhere('partnerid', '=', $this->members['partnerid'])
                         ->execute());
        if ($count > 0) {
            throw new InputException(sprintf(gettext('The value chosen for field (%s) already exists. Please choose another one'), 'username'), InputException::FIELD_ERROR, 'username_user');
            //$this->addErrorMessage('username', MX_ERR_VALUE_EXISTS, array('username'), __FUNCTION__, __LINE__);
            return true;
        }
        return false;
    }

    /**
    * Check if the password provided by the user is valid.
    *
    * @param string $password The password string for this user
    *
    * @return   boolean True if successful.  Otherwise false.
    */
    public function isPasswordValid($password)
    {
        if (strlen($password) < self::PASSWORD_MIN_LEN || strlen($password) > self::PASSWORD_MAX_LEN) {
            throw new InputException(sprintf(gettext('Field (%s) must not be less than %d or more than %d characters'), gettext('password'), self::PASSWORD_MIN_LEN, self::PASSWORD_MAX_LEN), InputException::FIELD_ERROR, 'password_user');
            //$this->addErrorMessage('password', MX_ERR_INVALID_LENGTH1, array('password', self::PASSWORD_MIN_LEN, self::PASSWORD_MAX_LEN), __FUNCTION__, __LINE__);
            return false;
        }
        if (! preg_match('/[a-zA-Z]/', $password) || ! preg_match('/[0-9]/', $password)) {
            throw new InputException(gettext('Password must contain both letters and numbers only'), InputException::FIELD_ERROR, 'password_user');
            //$this->addErrorMessage('password', MX_ERR_INVALID_PASSWORD, array(), __FUNCTION__, __LINE__);
            return false;
        }
        return true;
    }

    /**
    * Check if the current user account has expired
    *
    * @return   boolean True if it has. Otherwise false.
    */
    public function isExpired()
    {
        if ($this->members['id'] > 0 ) {
            if(0 == strlen($this->members['expire']->format('YmdHis'))) {
                return false;
            }
            $expiryStr = $this->members['expire']->format('YmdHis');
            $tmpDate = new \DateTime();
            $currentStr = $tmpDate->format('YmdHis');

            if ($currentStr <= $expiryStr || $this->members['expire']->format('Y') <= 1970) {
                // user account has not expired
                return false;
            }
        }
        // user account has expired
        return true;
    }

    public function isActive()
    {
        return (self::STATUS_ACTIVE == $this->members['status']);
    }

    /**
    * This method is used to update the password on the database side.
    *
    * This method will update password for a particular user.  The password should meet the minimum
    * length as specified in the constants above.  If the function returns false, it can be due to
    * either the password is not valid (not up to the specification) or it is not able to update the
    * password in the database.  The method here used the validateUser to do validation because the
    * encryption algorithm is a 2 way encryption.  I.e.  it will always generate different encrypted data
    * everytime it is called.
    *
    * @param string $newPassword The password to changed to.
    * @param string $oldPassword the old password to be used
    *
    * @return   boolean   If it succeed, returns true.  Otherwise return false.
    */
    public function updatePassword($newPassword, $oldPassword = null)
    {
        return $this->doUpdatePassword($newPassword, $oldPassword, 'password');
    }

    public function updatePassword2($newPassword, $oldPassword = null)
    {
        return $this->doUpdatePassword($newPassword, null, 'password2');
    }

    private function doUpdatePassword($newPassword, $oldPassword, $passwordField) {
        //Added by Devon on 2019/05/01 to support MySQL 8 authentication method
        $authenticator = App::getInstance()->getConfig()->{'snap.db.userauth'};
        if (0 == strlen($authenticator) || 'password' == $authenticator) {
            $authenticator = array('PASSWORD', '');
        } else {
            $authenticator = array('SHA2', ', '.self::PASSWORD_HASH_BITS /*hash length*/);
        }
        if (! $this->isPasswordValid($newPassword)) {
            return false;
        }
        //End add 2019/05/01
        if ($this->members['id'] > 0) {
            $tmpUsr = $this->getStore()->create();
            //$tmpUsr->setSignature($this->getSignature());
            if ($oldPassword === null || $tmpUsr->validateUser($this->members['username'], $oldPassword, $this->members['code'])) {
                $dbHandle = App::getInstance()->getDbHandle();
                $columnPrefix = $this->getStore()->getColumnPrefix();
                $dt = new \DateTime;
                $sql = sprintf(
                    "UPDATE %s SET %s%s = %s(%s%s), %spasswordmodified = '%s' WHERE %sid = %d",
                    $this->getStore()->getTableName(),
                    $columnPrefix,
                    $passwordField,
                    $authenticator[0],
                    $dbHandle->quote($newPassword),
                    $authenticator[1],
                    $columnPrefix,
                    $dt->format('Y-m-d H:i:s'),
                    $columnPrefix,
                    intval($this->members['id'])
                );
                $res = $dbHandle->query($sql);
                if ($res) {
                    $obj = $this->getStore()->searchTable()
                                     ->select([$passwordField])
                                     ->where('id', '=', $this->members['id'])
                                     ->execute();
                    $this->members['passwordmodified'] = $dt;
                    if ($obj) {
                        $this->members[$passwordField] = $obj->{$passwordField};
                        return true;
                    }
                }
            }
        } else {
            $this->members[$passwordField] = $newPassword;
            return true;
        }
        return false;
    }

	function updateUsername($currentUsername, $newUsername) {
		if (!$this->isUsernameValid($newUsername)) return false;
		if ($this->isUsernameExists($newUsername)) return false;
		if ($this->members['id'] > 0) {
			$tmpUsr = $this->getStore()->create();
			//$tmpUsr->setSignature($this->getSignature());
			if ($currentUsername == $this->members['username']) {
				return $this->query('UPDATE '.$this->getTableName().' SET #p#username='.$this->quote($newUsername).', #p#modifiedon=SYSDATE(), #p#modifiedby='.$this->quote(App::getInstance()->getUserSession()->getUser()->id).' WHERE #p#id='.$this->quote($this->members['id']));
			}
		}
		return false;
	}

    /**
    * This method is used to update the lastlogin on the database side.
    *
    * This method will update the last login time for a particular user.
    *
    * @param \Datetime $datetime The last login date & time
    *
    * @return   boolean   If it succeed, returns true.  Otherwise return false.
    */
    public function updateLastLogin(\DateTime $datetime, $ip)
    {
        if (! ($datetime instanceof \DateTime)) {
            $datetime = new \DateTime;
        }
        $this->members['lastlogin'] = $datetime->format('Y-m-d H:i:s');
        $this->members['lastloginip'] = $ip;
        return $this->getStore()->save($this, ['lastlogin', 'lastloginip']);
    }

	/**
	* Validates if the username and its password is correct and valid.
	*
	* If username & password is validated to be true, the object will
	* represent the user account that the username reflects.
	*
	* @param string $username Name of the user
	* @param string $password The password to be used
	*
	* @access   public
	* @return   boolean True if successful.  Otherwise false.
	*/
    public function validateUser($username, $password, $code = '')
    {
        //Added by Devon on 2019/05/01 to support MySQL 8 authentication method
        $authenticator = App::getInstance()->getConfig()->{'snap.db.userauth'};
        if (0 == strlen($authenticator) || 'password' == $authenticator) {
            $authenticator = sprintf("PASSWORD(%s)", $this->getStore()->quoteData($password));
        } else {
            $authenticator = sprintf("SHA2(%s, %d)", $this->getStore()->quoteData($password), self::PASSWORD_HASH_BITS);
        }
        //End add 2019/05/01
        $code = preg_replace('/[^0-9a-z_]/i', '', $code);
        $handle = $this->getStore()->searchTable()->select();
        $result = $handle->where('username', '=', $username);
        if(0 < strlen($code)) {
        	$result = $result->andWhere('partnerid', '=', $code);
        }
        $result = $result->andWhere('password', '=', $handle->raw($authenticator))
                        ->execute();
        //$sql = "SELECT * FROM {$this->getTableName()} WHERE #p#username={$this->quote($username)} AND #p#code='$code' AND #p#password=PASSWORD({$this->quote($password)})";
       
		if (is_array($result) && count($result)) {
            $this->members = $result[0]->members;
            $this->viewMembers = $result[0]->viewMembers;
            if ($this->isExpired()) {
                $this->log("Failed to validate user {$username} because expired with value of " . $this->members['expire']->format('Y-m-d H:i:s'), SNAP_LOG_ERROR);
                return false;
            }

            if (self::STATUS_INACTIVE == $this->members['status']) {
                $this->log("Failed to validate user {$username} because currently not active", SNAP_LOG_ERROR);
                return false;
            }
            $this->log("Successfully authenticated/validated user {$username}", SNAP_LOG_ERROR);
            return true;
        }
        $this->log("Failed to authenticated/validate user {$username}", SNAP_LOG_ERROR);
        return false;
    }

	/**
	* Validates if password2 is correct
	*
	* @param string $password2 The password2 to be validate
	*
	* @access   public
	* @return   boolean True if successful.  Otherwise false.
	*/
	function validatePassword2($password2) {
		if ($this->members['id'] == 0) return false;
		$id = $this->members['id'];
		$p = $this->getPrefix();
		$sql = "SELECT {$p}id FROM {$this->getTableName()} WHERE {$p}id={$this->quote($id)} AND {$p}password2=PASSWORD({$this->quote($password2)})";
		$result = $this->query($sql);
		if ($result) {
			if ($row = $result->FetchRow()) {
				if ($row[$p.'id'] == $this->members['id']) return true;
			}
		}
		return false;
	}

    public function getType()
    {
        return $this->members['type'];
    }

    public function isOperator()
    {
     
        $result = (self::TYPE_OPERATOR === $this->members['type'])  ? true : false;
    
        return $result;
    }

    public function isCustomer()
    {
        $result = (self::TYPE_CUSTOMER === $this->members['type'])  ? true : false;
    
        return $result;
    }

    public function isSale()
    {
        $result = (self::TYPE_SALE === $this->members['type'])  ? true : false;
    
        return $result;
    }

    public function isTrader()
    {
        $result = (self::TYPE_TRADER === $this->members['type'])  ? true : false;
    
        return $result;
    }

    public function isReferral()
    {
        $result = (self::TYPE_REFERRAL === $this->members['type'])  ? true : false;
    
        return $result;
    }

    public function isAgent()
    {
        $result = (self::TYPE_AGENT === $this->members['type'])  ? true : false;
    
        return $result;
    }    

    public function isBranch()
    {
        $result = (self::TYPE_OTC_BRANCH === $this->members['type'])  ? true : false;
    
        return $result;
    }    

    public function isHQ()
    {
        $result = (self::TYPE_OTC_HQ === $this->members['type'])  ? true : false;
    
        return $result;
    }    

    public function isRegional()
    {
        $result = (self::TYPE_OTC_REGIONAL === $this->members['type'])  ? true : false;
    
        return $result;
    }    
    
    public function isPasswordEmpty()
    {
        return empty($this->members['password']);
    }

    public function isPassword2Empty()
    {
        return empty($this->members['password2']);
    }

    public function getPartnerCode()
    {
        return $this->members['code'];
    }

    public function onPrepareUpdate()
    {
        $this->members['username'] = strtolower($this->members['username']);
        $this->members['code'] = strtoupper($this->members['code']);
        if (! $this->isUsernameValid($this->members['username'])) {
            return false;
        }
        if ($this->isUsernameExists($this->members['username'])) {
            return false;
        }

        if (strlen($this->members['password']) == 0 && count($this->members['password']) <= 0) {
            $this->log("Unable to update user ({$this->members['username']}) because the password field is empty", SNAP_LOG_ERROR);
            return false;
        }
        return true;
    }

    /**
     * Add additional data record for the user
     *
     * @param  string $staffid
     * @return UsrAdditionalData
     */
    public function addAdditionalData($staffId = null)
    {
        $additionalData = $this->getStore()->getRelatedStore('usradditionaldata')->create([
            'staffid'           => $staffId,
            'userid' => $this->members['id'],
            'status'          => User::STATUS_ACTIVE,
        ]);

        return $this->getStore()->getRelatedStore('usradditionaldata')->save($additionalData);
    }
    
    /**
     * Update address record for the account holder
     *
     * @param  string $staffid
     * @return UsrAdditionalData
     */
    public function updateAdditionalData(UsrAdditionalData $additionalData, $staffId)
    {
        $additionalData->staffid = $staffId;

        return $this->getStore()->getRelatedStore('usradditionaldata')->save($additionalData);
    }

}
?>
