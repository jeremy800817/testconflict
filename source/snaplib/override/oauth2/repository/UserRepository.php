<?php

namespace Snap\override\oauth2\repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Snap\IEntity;
use Snap\object\MyAccountHolder;
use Snap\override\oauth2\entity\User;
use Snap\override\oauth2\entity\Client;

class UserRepository implements UserRepositoryInterface
{
    protected $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $client)
    {
        if (! $user = $this->validateForPasswordGrant($username, $password, $client)) {
            return;
        }

        return new User($user->id, $user->email);

    }

    /**
     * Validate the username and password
     *
     * @param  string $username
     * @param  string $password
     * @param  Client $client
     * @return mixed
     */
    public function validateForPasswordGrant($username, $password, $client)
    {
        $user = $this->getUserObject($username, $client);

        if (! password_verify($password, $user->password)) {

            throw OAuthServerException::invalidCredentials();
        }

        return $user;
    }

    /**
     * Get a user entity
     *
     * @param  string $username
     * @param  Client $client
     * @return UserEntityInterface|null
     */
    public function getUserEntityByUsername($username, $client)
    {
        $user = $this->getUserObject($username, $client);

        if (! $user) {
            return;
        }

        return new User($user->id, $user->email);
    }

    protected function getUserObject($username, $client)
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $this->getUserObjectByEmail($username, $client->getId());
        } elseif (preg_match('/^\+\d+$/', $username)) {
            $user = $this->getUserObjectByPhone($username, $client->getId());
        } elseif (preg_match('/_accountnumber$/', $username)) {
            $username = explode('_', $username);
            $user = $this->getUserObjectByAccountNo($username[0], $client->getId());
        } else {
            $user = $this->getUserObjectByEmail($username, $client->getId());
        }

        return $user;
    }

    /**
     * Get snap user object
     *
     * @param  string $username
     * @param  int    $partnerid
     * @return IEntity
     */
    protected function getUserObjectByEmail($username, $partnerid)
    {
        return $this->store->searchTable()->select()
                    ->where('email', $username)
                    ->andWhere('partnerid', $partnerid)
                    ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
                    ->one();
    }

    protected function getUserObjectByPhone($phoneNo, $partnerId)
    {
        return $this->store->searchTable()->select()
                    ->where('phoneno', $phoneNo)
                    ->andWhere('partnerid', $partnerId)
                    ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
                    ->one();
    }

    protected function getUserObjectByPartnerCustomerId($username, $partnerId)
    {
        return $this->store->searchTable()->select()
                    ->where('partnercusid', $username)
                    ->andWhere('partnerid', $partnerId)
                    ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
                    ->one();
    }

    protected function getUserObjectByAccountNo($accountNo, $partnerId)
    {
        return $this->store->searchTable()->select()
                    ->where('accountnumber', $accountNo)
                    ->andWhere('partnerid', $partnerId)
                    ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
                    ->one();
    }
}
