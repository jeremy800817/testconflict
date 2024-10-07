<?php

namespace Snap\override\oauth2\repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Snap\override\oauth2\entity\Scope;

class ScopeRepository implements ScopeRepositoryInterface
{
    static $scopes = [];

    /**
     * {@inheritdoc}
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        if (self::hasScope($identifier)) {
            return new Scope($identifier);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null)
    {
        if (! in_array($grantType, ['password', 'client_credentials'])) {
            $scopes = array_filter($scopes, function ($scope) {
                return trim($scope->getIdentifier()) !== '*';
            });
        }

        return array_filter($scopes, function ($scope) {
            return self::hasScope($scope->getIdentifier());
        });
    }

    public static function hasScope($identifier)
    {
        return '*' === $identifier || array_key_exists($identifier, static::$scopes);
    }
}
