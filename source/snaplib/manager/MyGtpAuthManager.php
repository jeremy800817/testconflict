<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Snap\api\exception\AccessTokenInvalid;
use Snap\api\exception\CredentialInvalid;
use Snap\api\exception\IpMaximumRetries;
use Snap\api\exception\LoginMaximumRetries;
use Snap\api\exception\RefreshTokenInvalid;
use Snap\IObservable;
use Snap\object\MyAccountHolder;
use Snap\object\Partner;
use Snap\object\MyToken;
use Snap\TObservable;
use Snap\TLogging;

/**
 * This class contains methods related to the authentication process
 *
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 07-Oct-2020
 */
class MyGtpAuthManager implements IObservable
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    private $app = null;

    private $oauth = null;

    public function __construct($app)
    {
        $this->app = $app;
        $this->oauth = new \Snap\override\oauth2\SnapOAuth(
            $app, 
            $app->partnerStore(), 
            $app->myaccountholderStore(), 
            $app->mytokenStore()
        );
    }

    /**
     * Login using password grant by email
     *
     * @param string $email             Email address provided by application
     * @param string $password          Password provided by application
     * @param array  $decodedData       The decoded data array
     *
     * @return array
     */
    public function loginPasswordGrant(string $email, string $password, Partner $partner, &$decodedData)
    {
        return $this->loginPasswordGrant_common($email, $password, $partner, 'email', $decodedData);
    }


    /**
     * Login using phone number
     * 
     * @param string $phoneNo       Phone number of account holder
     * @param string $password      Password of account holder
     * @param Partner $partner      Partner of account holder
     * @param mixed $decodedData    Decoded data array from ApiParam
     * @return array                Token array
     * @throws IpMaximumRetries 
     */
    public function loginPasswordGrantPhone(string $phoneNo, string $password, Partner $partner, &$decodedData)
    {
        return $this->loginPasswordGrant_common($phoneNo, $password, $partner, 'phoneno', $decodedData);
    }

    /**
     * Login using partner customer id
     * 
     * @param  string  $partnercusid Partner provided unique id
     * @param  string  $password     Password of account holder
     * @param  Partner $partner      Partner of account holder
     * @param  mixed   $decodedData  Decoded data array from ApiParam
     * @return array                 Token array
     * @throws IpMaximumRetries 
     */
    public function loginPasswordGrantPartner(string $phoneNo, string $password, Partner $partner, &$decodedData)
    {
        return $this->loginPasswordGrant_common($phoneNo, $password, $partner, 'partnercusid', $decodedData);
    }

    /**
     * Login using gold account no
     * 
     * @param  string  $accNo        Account number of account holder
     * @param  string  $password     Password of account holder
     * @param  Partner $partner      Partner of account holder
     * @param  mixed   $decodedData  Decoded data array from ApiParam
     * @return array                 Token array
     * @throws IpMaximumRetries 
     */
    public function loginPasswordGrantAccNo(string $accNo, string $password, Partner $partner, &$decodedData)
    {
        return $this->loginPasswordGrant_common($accNo, $password, $partner, 'accountnumber', $decodedData);
    }

    protected function loginPasswordGrant_common(string $username, string $password, Partner $partner, $usernameField, &$decodedData)
    {
	$this->log(__METHOD__."(): username -> ".$username."usernameField -> ".$usernameField ,SNAP_LOG_DEBUG);    
	// Check if IP still can perform log in process
        if (($loginRestriction = $this->getLoginRestriction()) && isset($loginRestriction[$partner->id]) && !$this->ipCanLogin($partner->id)) {
            throw LoginMaximumRetries::fromTransaction(null, ['failTimes' => $this->getLoginRestrictionFailTimes($partner->id) ?? 5]);
        }

        try {
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

            $this->oauth->setAccessTokenLifetime($settings->accesstokenlifetime);
            $this->oauth->setRefreshTokenLifetime($settings->refreshtokenlifetime);

            $token = $this->oauth->loginPasswordGrant($username, $password, $partner->code);
        } catch (\Exception $e) {
            $this->saveLoginRestrictionLog($partner->id, $username);
            $this->incrementIpLoginFail($partner->id);
            throw CredentialInvalid::fromTransaction(null);
        }

        if ($usernameField == 'accountnumber') {
            $accNo = explode('_', $username);
            $username = $accNo[0];
        }

        // Get the account holder
        $account = $this->app->myaccountholderStore()->searchTable()->select()
            ->where($usernameField, $username)
            ->andWhere('partnerid', $partner->id)
            ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
            ->one();

        if (!$account instanceof MyAccountHolder) {
            $this->incrementIpLoginFail($partner->id);
            throw CredentialInvalid::fromTransaction(null);
        }

        // Update the account holder's last login time
        $account = $this->updateAccountHolderLastLogin($account);
        $decodedData['accountholder'] = $account;

        return $token;
    }

    /**
     * Get new access token using the refresh token
     *
     * @param  string  $refreshToken
     * @param  Partner $partner
     * @return mixed
     */
    public function getNewAccessToken(string $refreshToken, Partner $partner)
    {
        try {
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

            $this->oauth->setAccessTokenLifetime($settings->accesstokenlifetime);
            $this->oauth->setRefreshTokenLifetime($settings->refreshtokenlifetime);

            return $this->oauth->refreshToken($refreshToken, $partner->code);
        } catch (\Exception $e) {
            throw RefreshTokenInvalid::fromTransaction(null);
        }
    }


    /**
     * This function verfies the access token is valid or not. If
     *
     * @return AccountHolder|null
     */
    public function getAccountFromAccessToken($tokenStr = "")
    {
        if (!strlen($tokenStr)) {
            $tokenStr = $this->extractBearerTokenFromHeader();
        }

        try {
            $tokenArr = $this->oauth->decodeToken($tokenStr);
        } catch (\Exception $e) {
            throw AccessTokenInvalid::fromTransaction(null);
        }

        // Grab token from header
        $token = $this->app->mygtptokenManager()->getValidAccessToken($tokenArr['access_token_id']);
        if (!$token) {
            $this->logDebug(__CLASS__ . ": Unable to get a Token object.");
            return null;
        }

        $account = $this->getAccountFromToken($token);

        return $account;
    }

    public function getAccountFromToken(MyToken $token)
    {
        $account = $this->app->myaccountholderStore()->getById($token->accountholderid);
        if (!$account) {
            $this->logDebug(__CLASS__ . ": Token does not contain valid accountholderid.");
            return null;
        }
        return $account;
    }

    /**
     * Extracts a token string from the Authorization header within the current request
     *
     * @return string|null
     */
    public function extractBearerTokenFromHeader()
    {
        $matches = [];
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? getallheaders()['Authorization'];
        if (!$authorizationHeader || !preg_match('/^Bearer (.*)$/', $authorizationHeader, $matches)) {
            $this->logDebug(__CLASS__ . ": No bearer token found in request header.");
            return null;
        }

        return $matches[1];
    }

    /**
     * Updates the last login time & IP of account holder
     * @param MyAccountHolder $accHolder
     * 
     * @return MyAccountHolder  The updated accountholder
     */
    protected function updateAccountHolderLastLogin($accHolder)
    {
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        $accHolder->lastloginon = $now;
        $accHolder->lastloginip = \Snap\Common::getRemoteIP();

        $accHolder = $this->app->myaccountholderStore()->save($accHolder);
        return $accHolder;
    }

    /**
     * Checks if the current Ip can continue login process
     */
    private function ipCanLogin($partnerId)
    {
        $ip = \Snap\Common::getRemoteIP();
        if (0 != strlen($ip)) {
            $key = "{ipretry}:$ip";
            $retries = $this->app->getCache($key);
            $loginFailedTimes = $this->getLoginRestrictionFailTimes($partnerId) ?? 5;

            // Allow the ip to continue if less than this amount
            return $loginFailedTimes > $retries;
        }

        return true;
    }

    private function getIpLoginTimeToExpire()
    {
        $ip = \Snap\Common::getRemoteIP();
        if (0 != strlen($ip)) {
            $key = "{ipretry}:$ip";
            $timeleft = $this->app->getCacher()->getEngine()->ttl($key);

            return $timeleft;
        }
        return 0;
    }

    /**
     * Increments the number of times this IP failed the login process
     */
    private function incrementIpLoginFail($partnerId)
    {
        $ip = \Snap\Common::getRemoteIP();
        if (0 != strlen($ip)) {
            $key = "{ipretry}:$ip";
            $loginFailedTimeRange = $this->getLoginRestrictionTimeRange($partnerId) ?? 300;

            // Increment last failed amount and keep for 5 minutes(300 seconds)
            $this->app->getCacher()->increment($key, 1, $loginFailedTimeRange);
        }
    }
    
    /**
     * Get member login restriction
     */
    private function getLoginRestriction () {
        $loginRestriction = $this->app->getConfig()->{'gtp.member.login.restriction'};
        return $loginRestriction ? json_decode($loginRestriction, true) : false;
    }
    
    /**
     * Get member login restriction time range
     */
    private function getLoginRestrictionTimeRange ($partnerId) {        
        return $this->getLoginRestriction()[$partnerId]['timeRange'] ?? false;
    }
    
    /**
     * Get member login restriction fail times
     */
    private function getLoginRestrictionFailTimes ($partnerId) {
        return $this->getLoginRestriction()[$partnerId]['failTimes'] ?? false;
    }
    
    /**
     * Save member login restriction log
     */
    private function saveLoginRestrictionLog ($partnerId, $username) {
        if ($this->getLoginRestriction()[$partnerId]['log']) {
            $store = $this->app->getStore('userlog');
            $object = $store->create([
                'usrid' => 0,
                'username' => preg_replace('/[^-0-9a-zA-z@._]/', '', $username),
                'sessid' => $partnerId,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'browser' => $_SERVER['HTTP_USER_AGENT'],
                'logintime' => $_SERVER['REQUEST_TIME'],
                'lastactive' => 0,
                'logouttime' => $this->members['logintime']
            ]);
            $store->save($object);
        }
    }
}
