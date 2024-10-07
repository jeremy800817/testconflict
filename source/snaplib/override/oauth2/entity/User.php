<?php

namespace Snap\override\oauth2\entity;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

class User implements UserEntityInterface
{
    use EntityTrait;

    protected $id;

    /**
     * Create a new user instance.
     * @param string|int $id
     * @param string|int $email
     * @return void
     */
    public function __construct($id, $email)
    {
        $this->setIdentifier($email);
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}