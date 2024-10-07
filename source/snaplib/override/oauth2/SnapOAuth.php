<?php

namespace Snap\override\oauth2;

use Defuse\Crypto\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\ResourceServer;

use Snap\App;
use Snap\TLogging;
use Snap\override\oauth2\grant\PasswordGrant;
use Snap\override\oauth2\grant\RefreshTokenGrant;
use Snap\override\oauth2\repository\AccessTokenRepository;
use Snap\override\oauth2\repository\ClientRepository;
use Snap\override\oauth2\repository\ScopeRepository;
use Snap\override\oauth2\repository\RefreshTokenRepository;
use Snap\override\oauth2\repository\UserRepository;

class SnapOAuth
{
    use TLogging;

    /** @var \Snap\App */
    protected $app = null;

    protected $authorizationServer;
    protected $resourceServer;
    protected $accessTokenLifetime;
    protected $refreshTokenLifetime;
    protected $privateKeyPath;
    protected $publicKeyPath;
    protected $encryptionKey;

    public function __construct(App $app, $partnerStore, $accountHolderStore, $tokenStore)
    {
        $this->app = $app;
        $this->encryptionKey        = $this->app->getConfig()->{'mygtp.oauth.encryptionkey'};
        $this->publicKeyPath        = $this->app->getConfig()->{'mygtp.oauth.publickeypath'};
        $this->privateKeyPath       = $this->app->getConfig()->{'mygtp.oauth.privatekeypath'};
        $this->partnerStore         = $partnerStore;
        $this->accountHolderStore   = $accountHolderStore;
        $this->tokenStore           = $tokenStore;

        if (!$this->privateKeyPath || !$this->publicKeyPath || !$this->encryptionKey) {
            $this->logDebug(__CLASS__ . ": OAuth public, private, or encryption key configuration not set");
            throw new \Exception("OAuth public, private, or encryption key configuration is missing");
        }
    }

    /**
     * Set the access token lifetime
     *
     * @param  int   $minutes
     * @return void
     */
    public function setAccessTokenLifetime($minutes)
    {
        $this->accessTokenLifetime = $minutes;
    }

    /**
     * Set the refresh token lifetime
     *
     * @param  int   $minutes
     * @return void
     */
    public function setRefreshTokenLifetime($minutes)
    {
        $this->refreshTokenLifetime = $minutes;
    }

    /**
     * Issue new access token
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function issueToken($request)
    {
        $this->initAuthorizationServer();

        $this->logDebug(__METHOD__ . ": Issuing token");
        $response = $this->authorizationServer->respondToAccessTokenRequest($request, new \GuzzleHttp\Psr7\Response);
        return $response;
    }

    /**
     * Validate the incoming request token
     *
     * @param  ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function validateToken($request)
    {
        $this->initResourceServer();

        $this->logDebug(__METHOD__ . ": Validating token");
        $request = $this->resourceServer->validateAuthenticatedRequest($request);

        return $request;
    }

    /**
     * Revoke the access token
     *
     * @param  App    $app
     * @param  string $tokenId
     * @return void
     */
    public function revokeAccessToken($tokenId)
    {
        $this->logDebug(__METHOD__ . ": Revoking access token");
        (new AccessTokenRepository($this->app, $this->tokenStore))->revokeAccessToken($tokenId);
    }

    /**
     * Check if access token has been revoked
     *
     * @param  string $tokenId
     * @return boolean
     */
    public function isAccessTokenRevoked($tokenId)
    {
        return (new AccessTokenRepository($this->app, $this->tokenStore))->isAccessTokenRevoked($tokenId);
    }

    /**
     * Revoke the refresh token
     *
     * @param  App    $app
     * @param  string $tokenId
     * @return void
     */
    public function revokeRefreshTokenByAccessTokenId($tokenId)
    {
        $this->logDebug(__METHOD__ . ": Revoking refresh token");
        (new RefreshTokenRepository($this->app, $this->tokenStore))->revokeRefreshTokenByAccessTokenId($tokenId);
    }

    /**
     * Create new request instance
     *
     * @return ServerRequestInterface
     */
    public function requestFactory()
    {
        return new \GuzzleHttp\Psr7\ServerRequest('POST', '/');
    }
    
    /**
     * Get access token using password grant
     *
     * @param  string $loginId
     * @param  string $password
     * @param  string $partnerCode
     * @return mixed
     */
    public function loginPasswordGrant($loginId, $password, $partnerCode)
    {
        $request = $this->requestFactory()->withParsedBody([
            'grant_type' => 'password',
            'client_id' => $partnerCode,
            'username' => $loginId,
            'password' => $password,
        ]);

        $response = $this->issueToken($request);
        $contents = $response->getBody();
        $token = json_decode($contents);

        $tokenArr = [
            'access_token' => $token->access_token,
            'expires_in' => $token->expires_in,
            'token_type' => $token->token_type,
        ];

        if ($token->refresh_token) {
            $tokenArr['refresh_token'] = $token->refresh_token;
        }
        return $tokenArr;
    }

    /**
     * Return new access token object using refresh token
     *
     * @param  string $refeshToken
     * @param  string $partnerCode
     * @return mixed
     */
    public function refreshToken($refeshToken, $partnerCode)
    {
        $request = $this->requestFactory()->withParsedBody([
            'grant_type' => 'refresh_token',
            'client_id' => $partnerCode,
            'refresh_token' => $refeshToken,
        ]);

        $response = $this->issueToken($request);
        $contents = $response->getBody();
        $token = json_decode($contents);
        
        return [
            'access_token' => $token->access_token,
            'expires_in' => $token->expires_in,
            'refresh_token' => $token->refresh_token,
            'token_type' => $token->token_type,
        ];
    }

    /**
     * Validate and return decoded the token
     *
     * @param  string $accessToken
     * @return array
     */
    public function decodeToken($accessToken)
    {
        $request = $this->requestFactory()->withHeader('Authorization', 'Bearer ' . $accessToken);
        $request = $this->validateToken($request);
        
        return [
            'access_token_id' => $request->getAttribute('oauth_access_token_id'),
            'partner_code' => $request->getAttribute('oauth_client_id'),
            'email' => $request->getAttribute('oauth_user_id'),
            'scopes' => $request->getAttribute('oauth_scopes')
        ];
    }

    /**
     * Init the authorization and resource server
     *
     * @return void
     */
    protected function initAuthorizationServer()
    {
        $this->logDebug(__METHOD__ . ": Init Authorization server");

        $passphrase = $this->app->getConfig()->{'mygtp.oauth.privatekeypass'};
        $privateKey = $this->privateKeyPath;
        if (0 < strlen($passphrase)) {
            $privateKey = new CryptKey($privateKey, $passphrase);
        }
        
        $this->authorizationServer = new AuthorizationServer(
            new ClientRepository($this->partnerStore),
            new AccessTokenRepository($this->app, $this->tokenStore),
            new ScopeRepository(),
            $privateKey,
            Key::loadFromAsciiSafeString($this->encryptionKey)
        );        

        $this->authorizationServer = $this->enablePasswordGrant(
            $this->authorizationServer,
            new UserRepository($this->accountHolderStore),
            new RefreshTokenRepository($this->app, $this->tokenStore)
        );

        if (0 < $this->refreshTokenLifetime) {
            $this->authorizationServer = $this->enableRefreshTokenGrant(
                $this->authorizationServer,
                new UserRepository($this->accountHolderStore),
                new RefreshTokenRepository($this->app, $this->tokenStore)
            );
        }

    }

    protected function initResourceServer()
    {
        $this->logDebug(__METHOD__ . ": Init Resource server");

        $this->resourceServer = new ResourceServer(
            new AccessTokenRepository($this->app, $this->tokenStore),
            $this->publicKeyPath
        );
    }

    /**
     * Enable password grant for the server
     *
     * @param  AuthorizationServer $server
     * @param  UserRepository $userRepository
     * @param  RefreshTokenRepository $refreshTokenRepository
     * @return AuthorizationServer
     */
    protected function enablePasswordGrant($server, $userRepository, $refreshTokenRepository)
    {
        $grant = new PasswordGrant(
            $userRepository,
            $refreshTokenRepository
        );

        $grant->setRefreshTokenTTL($this->refreshTokenInterval());
        $server->enableGrantType($grant, $this->accessTokenInterval());

        $this->logDebug(__METHOD__ . ": Password grant enabled");

        return $server;
    }

    /**
     * Enable refresh token grant on the server
     *
     * @param  AuthorizationServer    $server
     * @param  UserRepository         $UserRepository
     * @param  RefreshTokenRepository $refreshTokenRepository
     * @return AuthorizationServer
     */
    protected function enableRefreshTokenGrant($server, $userRepository, $refreshTokenRepository)
    {
        $grant = new RefreshTokenGrant(
            $userRepository,
            $refreshTokenRepository
        );

        $grant->setRefreshTokenTTL($this->refreshTokenInterval());
        $server->enableGrantType($grant, $this->accessTokenInterval());

        $this->logDebug(__METHOD__ . ": Refresh token grant enabled");

        return $server;
    }

    /**
     * Refresh token interval in minutes
     *
     */
    protected function refreshTokenInterval()
    {
        if (! $this->refreshTokenLifetime) {
            $this->refreshTokenLifetime = 0;
            // throw new \Exception("OAuth refresh token is not configured");
        }

        return new \DateInterval("PT{$this->refreshTokenLifetime}M");
    }

    /**
     * Access token interval in minutes
     *
     */
    protected function accessTokenInterval()
    {
        if (! $this->accessTokenLifetime) {
            throw new \Exception("OAuth access token is not configured");
        }

        return new \DateInterval("PT{$this->accessTokenLifetime}M");
    }
}
