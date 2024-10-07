<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use DateTime;
use Snap\IObservable;
use Snap\object\MyAccountHolder;
use Snap\object\MyToken;
use Snap\TObservable;
use Snap\TLogging;

/**
 * This class handles token management
 *
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 07-Oct-2020
 */
class MyGtpTokenManager implements IObservable
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * This method verify if the given token is still valid
     *
     * @param  AccountHolder $accountHolder
     * @param  string        $token
     * @param  string        $type
     * @return bool
     */
    public function verifyTokenValidity(MyAccountHolder $accountHolder, string $tokenString, string $type)
    {
        $now = new \DateTime();
        $now = $now->format("Y-m-d H:i:s");

        return $this->app->mytokenStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', $accountHolder->id)
            ->where('token', $tokenString)
            ->where('type',  $type)
            ->where('status', MyToken::STATUS_ACTIVE)
            ->where(function ($q) use ($now) {
                $q->where('expireon', '>', $now);
            })
            ->exists();
    }

    /**
     * This method invalidate token / set the status to inactive
     *
     * @param  AccountHolder $accountHolder
     * @param  string        $tokenString
     * @return void
     */
    public function invalidatePasswordResetToken(MyAccountHolder $accountHolder, string $tokenString)
    {
        return $this->invalidateToken($accountHolder, $tokenString, MyToken::TYPE_PASSWORD_RESET);
    }

    /**
     * This method invalidate token / set the status to inactive
     *
     * @param  AccountHolder $accountHolder
     * @param  string        $tokenString
     * @return void
     */
    public function invalidateVerificationToken(MyAccountHolder $accountHolder, string $tokenString, $tokenType = null)
    {
        return $this->invalidateToken($accountHolder, $tokenString, $tokenType);
    }

    /**
     * Invalidates a token
     * 
     * @param AccountHolder     $accHolder
     * @param string            $tokenString
     * @param string|null       $tokenType
     * 
     * @return bool
     */
    public function invalidateToken(MyAccountHolder $accHolder, string $tokenString, $tokenType = null)
    {
        $tokenHdl = $this->app->mytokenStore()->searchTable()->select()
                        ->where('accountholderid', $accHolder->id)
                        ->andWhere('token', $tokenString)
                        ->andWhere('status', MyToken::STATUS_ACTIVE);
        if ($tokenType) {
            $tokenHdl = $tokenHdl->andWhere('type', $tokenType);
        }
        $token = $tokenHdl->one();

        if ($token) {
            $token->status = MyToken::STATUS_INACTIVE;
            $this->app->mytokenStore()->save($token);
            return true;
        }
        return false;
    }

    /**
     * This method save the unique password reset token for the account holder
     *
     * @param  AccountHolder $accountHolder
     * @param  integer       $length
     * @return Token
     */
    public function generatePasswordResetToken(MyAccountHolder $accountHolder, $length = 6)
    {
        $nextExpiry = new \DateTime();
        $nextExpiry->add(new \DateInterval("PT1H"));
        $nextExpiry->setTimezone($this->app->getUserTimezone());

        $token = "";
        for ($i = 0; $i < 6; $i++) {
            $token .= strval(rand(0,9));
        }

        $token = $this->app->mytokenStore()->create([
            'type'  => MyToken::TYPE_PASSWORD_RESET,
            'token' => $token,
            'remarks' => '',
            'accountholderid'   => $accountHolder->id,
            'status'    => MyToken::STATUS_ACTIVE,
            'expireon' => $nextExpiry
        ]);
        $token = $this->app->mytokenStore()->save($token);
        return $token;
    }

    /**
     * This method save the unique pin reset token for the account holder
     *
     * @param  AccountHolder $accountHolder
     * @param  integer       $length
     * @return Token
     */
    public function generatePinResetToken(MyAccountHolder $accountHolder, $length = 6)
    {
        $nextExpiry = new \DateTime();
        $nextExpiry->add(new \DateInterval("PT1H"));
        $nextExpiry->setTimezone($this->app->getUserTimezone());

        $token = "";
        for ($i = 0; $i < 6; $i++) {
            $token .= strval(rand(0,9));
        }

        $token = $this->app->mytokenStore()->create([
            'type'  => MyToken::TYPE_PIN_RESET,
            'token' => $token,
            'remarks' => '',
            'accountholderid'   => $accountHolder->id,
            'status'    => MyToken::STATUS_ACTIVE,
            'expireon' => $nextExpiry
        ]);
        $token = $this->app->mytokenStore()->save($token);
        return $token;
    }

    /**
     * Generate verification token
     */
    public function generateAccountVerificationToken(MyAccountHolder $accHolder)
    {
        $expireon = new \DateTime('now', $this->app->getUserTimezone());
        $expireon->add(new \DateInterval("PT24H"));

        $token = $this->generateToken($accHolder, MyToken::TYPE_VERIFICATION, $expireon);
        return $token;
    }

    /**
     * Generates a phone verification token
     * 
     * @param string $phoneNo 
     * @param MyAccountHolder $accountHolder 
     * @return MyToken
     */
    public function generatePhoneVerificationToken(string $phoneNo, $accountHolder = null)
    {
        $expireon = new \DateTime('now', $this->app->getUserTimezone());
        $expireon->add(new \DateInterval("PT10M"));

        $token = "";
        for ($i = 0; $i < 6; $i++) {
            $token .= strval(rand(0,9));
        }

        $token = $this->app->mytokenStore()->create([
            'type'  => MyToken::TYPE_VERIFICATION_PHONE,
            'token' => $token,
            'remarks' => $phoneNo,
            'accountholderid'   => $accountHolder ? $accountHolder->id : 0,
            'status'    => MyToken::STATUS_ACTIVE,
            'expireon' => $expireon
        ]);
        $token = $this->app->mytokenStore()->save($token);
        return $token;
    }

    /**
     * Get an access token from a token string
     *
     * @return MyToken|null
     */
    public function getValidAccessToken($tokenStr = "")
    {
        if (! strlen($tokenStr)) {
            $this->logDebug(__CLASS__.": No token string passed to getAccessToken().");
            return null;
        }

        // Search for token in cache/db
        $now = new \DateTime();
        $token = $this->app->mytokenStore()->searchTable()->select()
                        ->where('type', MyToken::TYPE_ACCESS)
                        ->andWhere('token', $tokenStr)
                        ->andWhere('expireon', '>', $now->format('Y-m-d H:i:s'))
                        ->andWhere('status', MyToken::STATUS_ACTIVE)
                        ->one();

        if (! $token) {
            $this->logDebug(__CLASS__.": Unable to find any valid access tokens.");
            return null;
        }

        return $token;
    }

    /**
     * Registers a push token for this user
     * @param AccountHolder $accountHolder      The account holder that is registering his device's push token
     * @param string        $tokenStr           The token string (FCM / APN)
     * @param string        $deviceName         Device name. Current not used & not editable by user(Oct 2020)
     *
     * @return Token
     */
    public function registerPushToken(MyAccountHolder $accountHolder, string $tokenStr, string $deviceName = "")
    {
        $app = $this->app;

        // Find if the current push token exists or not
        $token = $app->mytokenStore()->searchTable()->select()
                        ->where('token', $tokenStr)
                        ->andWhere('accountholderid', $accountHolder->id)
                        ->andWhere('type', MyToken::TYPE_PUSH)
                        ->one();

        // Create an entry for this token if it does not exist
        if (! $token) {
            $token = $app->mytokenStore()->create([
                'type'      => MyToken::TYPE_PUSH,
                'accountholderid'   => $accountHolder->id,
                'token'     => $tokenStr,
                'remarks'   => $deviceName,
            ]);
        }

        // Set expiry time for this token to 3 weeks from now
        $nextExpiry = new \DateTime();
        $nextExpiry->add(new \DateInterval("P3W"));
        $nextExpiry->setTimezone($app->getUserTimezone());

        $token->expireon = $nextExpiry;
        $token->status = MyToken::STATUS_ACTIVE;
        $token = $app->mytokenStore()->save($token);

        return $token;
    }

    /**
     * Generates an access token for the specified account holder
     *
     * @param MyAccountHolder $accountHolder      The accountholder object for the token to be generated for
     * @param string        $remarks            The remarks for this token
     *
     * @return Token
     */
    public function generateAccessToken(MyAccountHolder $accountHolder, string $remarks = "") : MyToken
    {
        $expiry = new \DateTime();
        $expireSeconds = $this->app->getConfig()->{'mygtp.access_token.lifetime'} ?? '3600';
        $expiry->add(new \DateInterval("PT{$expireSeconds}S"));
        $expiry->setTimezone($this->app->getUserTimezone());

        $token = $this->generateToken($accountHolder, MyToken::TYPE_ACCESS, $expiry, $remarks);

        if (0 < $token->id) {
            return $token;
        }

        throw new \Exception(gettext("Unable to generate access token."));
    }

    /**
     * Generate token
     * 
     * @param MyAccountHolder $accHolder 
     * @param string $tokenType 
     * @param DateTime $expireon 
     * @param string $remarks 
     * 
     * @return MyToken 
     */
    public function generateToken(MyAccountHolder $accHolder, string $tokenType, \DateTime $expireon, $remarks = '', $length = 128)
    {
        $token = $this->app->mytokenStore()->create([
            'type'  => $tokenType,
            'token' => $this->generateUniqueTokenString($length),
            'remarks' => $remarks,
            'accountholderid'   => $accHolder->id,
            'status'    => MyToken::STATUS_ACTIVE,
            'expireon' => $expireon
        ]);
        $token = $this->app->mytokenStore()->save($token);

        return $token;
    }

    /**
     * Generates a unique token string
     *
     * @param int $maxLen   The maximum length of the token.
     */
    private function generateUniqueTokenString(int $maxLen = 128) : string
    {
        $bytes = random_bytes(32);
        $token = substr(bin2hex($bytes),0, $maxLen > 0 ? $maxLen : 128);
        return $token;
    }

}

