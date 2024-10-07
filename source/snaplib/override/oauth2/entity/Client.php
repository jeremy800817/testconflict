<?php

namespace Snap\override\oauth2\entity;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class Client implements ClientEntityInterface
{
    use EntityTrait, ClientTrait;

    protected $id;

    public function __construct($id, $code, $name)
    {
        $this->setIdentifier($code);
        $this->name = $name;
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}