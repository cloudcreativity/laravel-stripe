<?php
/**
 * Copyright 2019 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CloudCreativity\LaravelStripe\Connect;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\RedirectResponse;
use Stripe\OAuth;

class AuthorizeUrl implements Arrayable
{

    const EXPRESS_CONNECT_BASE = 'https://connect.stripe.com/express';
    const STRIPE_LANDING_LOGIN = 'login';
    const STRIPE_LANDING_REGISTER = 'register';

    /**
     * @var string
     */
    private $scope;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string|null
     */
    private $redirectUri;

    /**
     * @var string|null
     */
    private $stripeLanding;

    /**
     * @var bool|null
     */
    private $alwaysPrompt;

    /**
     * @var array|null
     */
    private $user;

    /**
     * @var array
     */
    private $options;

    /**
     * @return array
     */
    public static function scopes()
    {
        return [
            Authorizer::SCOPE_READ_ONLY,
            Authorizer::SCOPE_READ_WRITE,
        ];
    }

    /**
     * AuthorizeUrl constructor.
     *
     * @param string $state
     * @param array|null $options
     */
    public function __construct($state, array $options = null)
    {
        $this->state = $state;
        $this->options = $options ?: [];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return string
     */
    public function toString()
    {
        return OAuth::authorizeUrl(
            $this->toArray(),
            $this->options ?: null
        );
    }

    /**
     * Redirect to the authorize URL.
     *
     * @param int $status
     * @param array $headers
     * @return RedirectResponse
     */
    public function redirect($status = 302, array $headers = [])
    {
        return redirect()->away(
            $this->toString(),
            $status,
            $headers
        );
    }

    /**
     * Use the Express OAuth endpoint.
     *
     * @return $this
     */
    public function express()
    {
        $this->options['connect_base'] = self::EXPRESS_CONNECT_BASE;

        return $this;
    }

    /**
     * @return $this
     */
    public function readOnly()
    {
        $this->scope = Authorizer::SCOPE_READ_ONLY;

        return $this;
    }

    /**
     * @return $this
     */
    public function readWrite()
    {
        $this->scope = Authorizer::SCOPE_READ_WRITE;

        return $this;
    }

    /**
     * The URL for the authorize response redirect.
     *
     * If provided, this must exactly match one of the comma-separated `redirect_uri` values
     * in your application settings.
     *
     * To protect yourself from certain forms of man-in-the-middle attacks, the live mode
     * `redirect_uri` must use a secure HTTPS connection.
     *
     * Defaults to the `redirect_uri` in your application settings if not provided.
     *
     * @param string|null $uri
     * @return $this
     */
    public function redirectUri($uri)
    {
        $this->redirectUri = $uri ?: null;

        return $this;
    }

    /**
     * Make the user login to Stripe.
     *
     * Only use login if you expect all your users to have Stripe accounts already
     * (e.g., most read-only applications, like analytics dashboards or accounting software).
     *
     * @return $this
     */
    public function login()
    {
        $this->stripeLanding = self::STRIPE_LANDING_LOGIN;

        return $this;
    }

    /**
     * Make the user register with Stripe.
     *
     * @return $this
     */
    public function register()
    {
        $this->stripeLanding = self::STRIPE_LANDING_REGISTER;

        return $this;
    }

    /**
     * Always ask the user to connect, even if they're already connected.
     *
     * @return $this
     */
    public function alwaysPrompt()
    {
        $this->alwaysPrompt = 'true';

        return $this;
    }

    /**
     * Set key/value pairs for the `stripe_user`.
     *
     * @param iterable|array $user
     * @return $this
     */
    public function user($user)
    {
        return $this->stripeUser($user);
    }

    /**
     * Set key/value pairs for the `stripe_user`.
     *
     * @param iterable|array $user
     * @return $this
     */
    public function stripeUser($user)
    {
        $this->user = collect($user)->reject(function ($value) {
            return is_null($value);
        })->toArray();

        ksort($this->user);

        return $this;
    }

    /**
     * @return array
     */
    public function all()
    {
        return [
            'always_prompt' => $this->alwaysPrompt,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => $this->scope,
            'state' => $this->state,
            'stripe_user' => $this->user ?: null,
            'stripe_landing' => $this->stripeLanding,
        ];
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return collect($this->all())->reject(function ($value) {
            return is_null($value);
        });
    }

}
