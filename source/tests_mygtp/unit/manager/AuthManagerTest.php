<?php

use Snap\api\exception\CredentialInvalid;
use Snap\object\MyAccountHolder;

class AuthManagerTest extends BaseTestCase
{
    private static $accHolder1 = null;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();

        // Create valid Accountholder
        $accountHolder = self::createDummyAccountHolder();
        self::$app->mygtpaccountManager()->editPassword($accountHolder, "password123", "dummy");
        self::$accHolder1 = self::$app->myaccountholderStore()->getById($accountHolder->id);
    }

    public function testSuccessfulPasswordGrant()
    {
        /** @var \Snap\manager\MyAuthManager */
        $authMgr = self::$app->mygtpauthManager();
        $partner = self::$partner;

        $decoded = [];
        $token = $authMgr->loginPasswordGrant(self::$accHolder1->email, "password123", $partner, $decoded);

        $this->assertNotNull($token);
        $this->assertNotNull($decoded['accountholder']);
        
        $this->assertGreaterThan(0, $token['expires_in'], "Token expiry invalid.");
    }

    public function testFailedPasswordGrant()
    {
        $this->expectException(CredentialInvalid::class);

        /** @var \Snap\manager\MyAuthManager */
        $authMgr = self::$app->mygtpauthManager();
        $partner = self::$partner;

        $decoded = [];
        $token = $authMgr->loginPasswordGrant(self::$accHolder1->email, "badpassword", $partner, $decoded);
    }

}

?>