<?php

namespace Snap\override\oauth2\grant;

use DateInterval;
use DateTimeImmutable;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Grant\PasswordGrant as BasePasswordGrant;
use Psr\Http\Message\ServerRequestInterface;

class PasswordGrant extends BasePasswordGrant 
{
    /**
     * {@inheritdoc}
     */
    protected function validateClient(ServerRequestInterface $request)
    {
        // Implementation without using client secret
        list($clientId, $clientSecret) = $this->getClientCredentials($request);

        $client = $this->getClientEntityOrFail($clientId, $request);

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    protected function issueRefreshToken($accessToken)
    {
        if ($this->refreshTokenTTL->format('%i')) {
            return parent::issueRefreshToken($accessToken);
        }
        return null;
    }

    /**
     * Issue an access token.
     *
     * @param DateInterval           $accessTokenTTL
     * @param ClientEntityInterface  $client
     * @param string|null            $userIdentifier
     * @param ScopeEntityInterface[] $scopes
     *
     * @throws OAuthServerException
     * @throws UniqueTokenIdentifierConstraintViolationException
     *
     * @return AccessTokenEntityInterface
     */
    protected function issueAccessToken(
        DateInterval $accessTokenTTL,
        ClientEntityInterface $client,
        $userIdentifier,
        array $scopes = []
    ) {
        $maxGenerationAttempts = self::MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS;
        
        $user = $this->userRepository->getUserEntityByUsername($userIdentifier, $client);
        $accessToken = $this->accessTokenRepository->getNewToken($client, $scopes, $user);
        $accessToken->setExpiryDateTime((new DateTimeImmutable())->add($accessTokenTTL));
        $accessToken->setPrivateKey($this->privateKey);

        while ($maxGenerationAttempts-- > 0) {
            $accessToken->setIdentifier($this->generateUniqueIdentifier());
            try {
                $this->accessTokenRepository->revokeOtherAccessTokenByToken($accessToken);
                $this->accessTokenRepository->persistNewAccessToken($accessToken);

                return $accessToken;
            } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                if ($maxGenerationAttempts === 0) {
                    throw $e;
                }
            }
        }
    }

}