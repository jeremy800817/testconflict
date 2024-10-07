<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

/**
 * This class provides a basic acocunt holder that is already authenticated
 * and sets the access token in the $_SERVER variable
 * 
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 15-Oct-2020
 */
class AuthenticatedTestCase extends BaseTestCase 
{
    /** @var \Snap\object\MyAccountHolder $accountHolder */
    protected static $accountHolder = null;

    /** @var \Snap\object\MyToken $token */
    protected static $accessToken = null;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();

        $app = self::$app;
        $decoded = [];
        self::$accountHolder = self::createDummyAccountHolder();
        self::$accessToken = self::$app->mygtpauthManager()->loginPasswordGrant(self::$accountHolder->email, "dummy", self::$partner, $decoded);
    }


    public function setUp()
    {
        parent::setUp();
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer " . self::$accessToken['access_token'];
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}