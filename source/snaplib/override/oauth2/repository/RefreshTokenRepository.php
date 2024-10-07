<?php

namespace Snap\override\oauth2\repository;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Snap\api\exception\GeneralException;
use Snap\App;
use Snap\InputException;
use Snap\object\MyToken;
use Snap\override\oauth2\entity\RefreshToken;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
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
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $expireOn = $refreshTokenEntity->getExpiryDateTime();
        $expireOn = new \DateTime($expireOn->format('Y-m-d H:i:s'));
        $expireOn->setTimezone($this->app->getUserTimezone());

        $token = $this->store->create([
            'type' => MyToken::TYPE_REFRESH,
            'token' => $refreshTokenEntity->getIdentifier(),
            'accountholderid' => $refreshTokenEntity->getAccessToken()->getUser()->getId(),
            'expireon' => $expireOn,
            'status' => MyToken::STATUS_ACTIVE,
        ]);

        $this->store->save($token);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        $token = $this->store->searchTable()->select()
            ->where('token', $tokenId)
            ->andWhere('type', MyToken::TYPE_REFRESH)
            ->one();
        
        if (! $token) {
            // Prevent error when unable to find token
            throw GeneralException::fromTransaction([], ['message' => "Unable to validate token"]);
        }

        $token->expireon = new \DateTime();
        $token->status = MyToken::STATUS_INACTIVE;
        $this->store->save($token);
    }

    public function revokeRefreshTokenByAccessTokenId($tokenId)
    {
        // Unimplemented
        throw new \Snap\InputException("Not implemented", InputException::GENERAL_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $token = $this->store->searchTable()->select(['status'])
            ->where('token', $tokenId)
            ->andWhere('type', 'REFRESH')
            ->one();
        
        if (!$token) {
            return true;
        }

        return 0 === intval($token->status);
    }
}
