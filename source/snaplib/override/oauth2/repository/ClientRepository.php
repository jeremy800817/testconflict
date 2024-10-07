<?php

namespace Snap\override\oauth2\repository;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Snap\object\Partner;
use Snap\override\oauth2\entity\Client;

class ClientRepository implements ClientRepositoryInterface
{
    protected $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier)
    {
        $partner = $this->store->getByField('code', $clientIdentifier);

        if (!$partner) {
            return;
        }

        return new Client(
            $partner->id,
            $clientIdentifier,
            $partner->name
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        $partner = $this->store->getByField('code', $clientIdentifier);

        if (!$partner || Partner::STATUS_ACTIVE != $partner->status) {
            return false;
        }

        if ('refresh_token' === $grantType || 'password' === $grantType) {
            return true;
        }

        return !(0 < strlen($partner->apikey)) || $clientSecret == $partner->apikey;
    }
}
