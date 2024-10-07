<?php

namespace Snap\override\oauth2\entity;

use \League\OAuth2\Server\Entities\Traits\EntityTrait;
use \League\OAuth2\Server\Entities\Traits\ScopeTrait;
use \League\OAuth2\Server\Entities\ScopeEntityInterface;

class Scope implements ScopeEntityInterface
{
    use ScopeTrait, EntityTrait;

    /**
     * Create a new scope instance
     *
     * @param  string $name
     * @return void
     */
    public function __construct($name)
    {
        $this->setIdentifier($name);
    }
}