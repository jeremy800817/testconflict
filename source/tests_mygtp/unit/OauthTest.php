<?php

use Lcobucci\JWT\Configuration;
use League\OAuth2\Server\Exception\OAuthServerException;
use Snap\override\oauth2\SnapOAuth;

final class OauthTest extends BaseTestCase
{
    static $encryptionKey;
    static $accHolder;
    static $partner;
    static $plainClientSecret;

    static $oauth;
    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();

        self::$partner = self::createDummyPartner();
        self::$accHolder = self::createDummyAccountHolder(self::$partner);

        self::$oauth = new SnapOAuth(
            self::$app,
            self::$app->partnerStore(),
            self::$app->myaccountholderStore(),
            self::$app->mytokenStore()
        );
    }

    public function testUnavailableGrant()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionCode(2);

        $request = self::$oauth->requestFactory()->withParsedBody([
            'grant_type' => 'client_credentials',
            'client_id' => self::$partner->code,
            'username' => self::$accHolder->email,
            'password' => 'dummy',
        ]);

        self::$oauth->issueToken($request);
    }

    public function testInvalidRequest()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionCode(3);

        $request = self::$oauth->requestFactory()->withParsedBody([
            'grant_type' => 'password',
            'client_id' => self::$partner->code,
            'username' => self::$accHolder->email,
        ]);

        self::$oauth->issueToken($request);

    }

    public function testInvalidClient()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionCode(4);
        
        $request = self::$oauth->requestFactory()->withParsedBody([
            'grant_type' => 'password',
            'client_id' => 'unknown',
            'username' => self::$accHolder->email,
            'password' => 'dummy',
        ]);

        self::$oauth->issueToken($request);
    }


    public function testInvalidCredentials()
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionCode(6);
        $request = self::$oauth->requestFactory()->withParsedBody([
            'grant_type' => 'password',
            'client_id' => self::$partner->code,
            'username' => self::$accHolder->email,
            'password' => '',
        ]);
        self::$oauth->issueToken($request);
    }

    public function testCanIssueToken()
    {
        $request = self::$oauth->requestFactory()->withParsedBody([
            'grant_type' => 'password',
            'client_id' => self::$partner->code,
            'username' => self::$accHolder->email,
            'password' => 'dummy',
        ]);

        $response = self::$oauth->issueToken($request);
        $this->assertNotNull($response);

        $contents = $response->getBody();
        $this->assertJson((string) $contents);
        $token = json_decode($contents);

        $this->assertObjectHasAttribute('token_type', $token);
        $this->assertEquals('Bearer', $token->token_type);
        $this->assertObjectHasAttribute('expires_in', $token);
        $this->assertObjectHasAttribute('access_token', $token);
        $this->assertObjectHasAttribute('refresh_token', $token);

        return $token;
    }

    /**
     * @depends testCanIssueToken
     * */
    public function testCanValidateToken($token)
    {
        $request = self::$oauth->requestFactory()->withHeader('Authorization', 'Bearer ' . $token->access_token);
        $request = self::$oauth->validateToken($request);
        $this->assertNotNull($request);
        $this->assertNotNull($request->getAttribute('oauth_access_token_id'));
        $this->assertNotNull($request->getAttribute('oauth_client_id'));
        $this->assertEquals(self::$partner->code, $request->getAttribute('oauth_client_id'));
        $this->assertNotNull($request->getAttribute('oauth_user_id'));
        $this->assertEquals(self::$accHolder->email, $request->getAttribute('oauth_user_id'));

        return $token;
    }

    /**
     * @depends testCanIssueToken
     * */
    public function testCanRefreshToken($token)
    {
        $request = self::$oauth->requestFactory()->withParsedBody([
            'client_id' => self::$partner->code,
            'refresh_token' => $token->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        $response = self::$oauth->issueToken($request);

        $contents = $response->getBody();
        $this->assertJson((string) $contents);
        $token = json_decode($contents);

        $this->assertObjectHasAttribute('token_type', $token);
        $this->assertEquals('Bearer', $token->token_type);
        $this->assertObjectHasAttribute('expires_in', $token);
        $this->assertObjectHasAttribute('access_token', $token);
        $this->assertObjectHasAttribute('refresh_token', $token);

        $old = $token;

        // Try refresh again
        $request = self::$oauth->requestFactory()->withParsedBody([
            'client_id' => self::$partner->code,
            'refresh_token' => $old->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        $response = self::$oauth->issueToken($request);

        $contents = $response->getBody();
        $this->assertJson((string) $contents);
        $token = json_decode($contents);

        $this->assertObjectHasAttribute('token_type', $token);
        $this->assertEquals('Bearer', $token->token_type);
        $this->assertObjectHasAttribute('expires_in', $token);
        $this->assertObjectHasAttribute('access_token', $token);
        $this->assertObjectHasAttribute('refresh_token', $token);

        return $token;
    }

    /**
     * @depends testCanIssueToken
     * */
    public function testCannotReuseRefreshToken($token)
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionCode(8);

        // Try refresh again
        $request = self::$oauth->requestFactory()->withParsedBody([
            'client_id' => self::$partner->code,
            'refresh_token' => $token->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        self::$oauth->issueToken($request);
    }

    /**
     * @depends testCanRefreshToken
     * */
    public function testCanUseRefreshedToken($token)
    {
        $request = self::$oauth->requestFactory()->withHeader('Authorization', 'Bearer ' . $token->access_token);
        $request = self::$oauth->validateToken($request);

        $this->assertNotNull($request);
        $this->assertNotNull($request->getAttribute('oauth_access_token_id'));
        $this->assertNotNull($request->getAttribute('oauth_client_id'));
        $this->assertEquals(self::$partner->code, $request->getAttribute('oauth_client_id'));
        $this->assertNotNull($request->getAttribute('oauth_user_id'));
        $this->assertEquals(self::$accHolder->email, $request->getAttribute('oauth_user_id'));

        return $token;
    }

    /**
     * @depends testCanUseRefreshedToken
     * */
    public function testCanRevokeAccessToken($token)
    {
        $parser = Configuration::forUnsecuredSigner()->parser();
        $jwt = $parser->parse($token->access_token);
        $tokenId = $jwt->claims()->get('jti');
        self::$oauth->revokeAccessToken($tokenId);

        $this->assertTrue(self::$oauth->isAccessTokenRevoked($tokenId));

        return $token;
    }

    /**
     * @depends testCanRevokeAccessToken
     * */
    public function testCannotUseRevokedAccessToken($token)
    {
        $this->expectException(OAuthServerException::class);
        $this->expectExceptionCode(9);

        $request = self::$oauth->requestFactory()->withHeader('Authorization', 'Bearer ' . $token->access_token);
        $request = self::$oauth->validateToken($request);

        return $token;
    }
}
