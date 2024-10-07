<?php

namespace Snap\override\oauth2\repository;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Snap\api\exception\GeneralException;
use Snap\App;
use Snap\object\MyToken;
use Snap\override\oauth2\entity\AccessToken;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    protected $store;

    /** @var \Snap\App $app */
    protected $app;

    public function __construct(App $app, $store)
    {
        $this->app = $app;
        $this->store = $store;
    }

    /**
     * Create a new access token
     *
     * @param  ClientEntityInterface $clientEntity
     * @param  array $scopes
     * @param  UserEntityInterface $user
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $user = null)
    {
        return new AccessToken($user, $scopes, $clientEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $expireOn = $accessTokenEntity->getExpiryDateTime();
        $expireOn = new \DateTime($expireOn->format('Y-m-d H:i:s'));
        $expireOn->setTimezone($this->app->getUserTimezone());

        $token = $this->store->create([
            'type' => MyToken::TYPE_ACCESS,
            'token' => $accessTokenEntity->getIdentifier(),
            'accountholderid' => $accessTokenEntity->getUser()->getId(),
            'expireon' => $expireOn,
            'status' => MyToken::STATUS_ACTIVE,
        ]);

        $this->store->save($token);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId)
    {
        $token = $this->store->searchTable()->select()
            ->where('token', $tokenId)
            ->andWhere('type', MyToken::TYPE_ACCESS)
            ->one();

        if (! $token) {
            // Updated by Cheok on 2021-04-27 to remove invalid refresh token error when refreshing access token
        
            // Prevent error when unable to find token
            // throw GeneralException::fromTransaction([], ['message' => "Unable to validate token"]);

            // Expired access token will be removed from table, so we return instead of throwing error
            return;
            // End update by Cheok
        }

        $token->expireon = new \DateTime();
        $token->status = MyToken::STATUS_INACTIVE;

        $this->store->save($token);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId)
    {
        $expireOn = new \DateTime('now', $this->app->getServerTimezone());
        $expireOn->setTimezone($this->app->getUserTimezone());

        $token = $this->store->searchTable()->select()
            ->where('token', $tokenId)
            ->andWhere('type', MyToken::TYPE_ACCESS)
            ->one();

        if (! $token) {
            return true;
        }

        return MyToken::STATUS_INACTIVE === intval($token->status);
    }

    public function revokeOtherAccessTokenByToken(AccessTokenEntityInterface $accessTokenEntity) {
        $expireOn = new \DateTime();
        $tokens = $this->store->searchTable()
                        ->select()
                        ->whereNotIn('type', [MyToken::TYPE_VERIFICATION, MyToken::TYPE_VERIFICATION_PHONE])
                        ->andWhere('accountholderid', $accessTokenEntity->getUser()->getId())
                        ->andWhere('status', MyToken::STATUS_ACTIVE)
                        ->execute();
        foreach ($tokens as $token) {
            $token->expireon = $expireOn->format('Y-m-d H:i:s');
            $token->status = MyToken::STATUS_INACTIVE;
            $this->store->save($token, ['expireon', 'status']);
        }
    }
}
