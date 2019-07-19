<?php

namespace CloudCreativity\LaravelStripe\Connect;

use CloudCreativity\LaravelStripe\Client;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Contracts\Connect\StateProviderInterface;
use RuntimeException;
use Stripe\OAuth;
use Stripe\StripeObject;

class Authorizer
{

    const CODE = 'code';
    const GRANT_TYPE = 'grant_type';
    const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    const SCOPE_READ_ONLY = 'read_only';
    const SCOPE_READ_WRITE = 'read_write';
    const STRIPE_USER_ID = 'stripe_user_id';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var StateProviderInterface
     */
    private $state;

    /**
     * Authorizer constructor.
     *
     * @param Client $client
     * @param StateProviderInterface $state
     */
    public function __construct(Client $client, StateProviderInterface $state)
    {
        $this->client = $client;
        $this->state = $state;
    }

    /**
     * Create a Stripe Connect OAuth link.
     *
     * @param array|null $options
     * @return AuthorizeUrl
     * @see https://stripe.com/docs/connect/standard-accounts#integrating-oauth
     */
    public function authorizeUrl(array $options = null)
    {
        if (!$state = $this->state->get()) {
            throw new RuntimeException('State parameter cannot be empty.');
        }

        return new AuthorizeUrl($state, $options);
    }

    /**
     * Authorize access to an account.
     *
     * @param string $code
     * @param array|null $options
     * @return StripeObject
     * @see https://stripe.com/docs/connect/standard-accounts#token-request
     */
    public function authorize($code, array $options = null)
    {
        $params = [
            self::CODE => $code,
            self::GRANT_TYPE => self::GRANT_TYPE_AUTHORIZATION_CODE,
        ];

        return call_user_func($this->client, OAuth::class, 'token', $params, $options);
    }

    public function refresh()
    {
        // @todo
    }

    /**
     * Revoke access to an account.
     *
     * @param AccountInterface $account
     * @param array|null $options
     * @return StripeObject
     * @see https://stripe.com/docs/connect/standard-accounts#revoked-access
     */
    public function deauthorize(AccountInterface $account, array $options = null)
    {
        $params = [
            self::STRIPE_USER_ID => $account->getStripeAccountId(),
        ];

        return call_user_func($this->client, OAuth::class, 'deauthorize', $params, $options);
    }
}
