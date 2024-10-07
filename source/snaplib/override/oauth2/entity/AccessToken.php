<?php

namespace Snap\override\oauth2\entity;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

class AccessToken implements AccessTokenEntityInterface
{
    use AccessTokenTrait, EntityTrait, TokenEntityTrait;

    protected $user;

    /**
     * Create a new token instance.
     *
     * @param  string  $userIdentifier
     * @param  array   $scopes
     * @param  \League\OAuth2\Server\Entities\ClientEntityInterface  $client
     * @return void
     */
    public function __construct(UserEntityInterface $user, array $scopes, ClientEntityInterface $client)
    {        
        $this->setUserIdentifier($user->getIdentifier());

        foreach ($scopes as $scope) {
            $this->addScope($scope);
        }
        $this->setClient($client);
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}