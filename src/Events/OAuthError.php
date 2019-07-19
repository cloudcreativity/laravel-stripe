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

namespace CloudCreativity\LaravelStripe\Events;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountOwnerInterface;
use Illuminate\Queue\SerializesModels;

class OAuthError extends AbstractOAuthEvent
{

    use SerializesModels;

    /**
     * User denied authorization.
     */
    const ACCESS_DENIED = 'access_denied';

    /**
     * Invalid scope parameter provided.
     */
    const INVALID_SCOPE = 'invalid_scope';

    /**
     * Provided redirect_uri parameter is either an invalid URL or is not allowed
     * by your Stripe application settings.
     */
    const INVALID_REDIRECT_URI = 'invalid_redirect_uri';

    /**
     * Missing `response_type` parameter.
     */
    const INVALID_REQUEST = 'invalid_request';

    /**
     * Unsupported `response_type` parameter.
     * Currently the only supported `response_type` is `code`.
     */
    const UNSUPPORTED_RESPONSE_TYPE = 'unsupported_response_type';

    /**
     * The state parameter was incorrect and the request was forbidden.
     */
    const LARAVEL_STRIPE_FORBIDDEN = 'laravel_stripe_forbidden';

    /**
     * The unique Stripe error code.
     *
     * @var string
     */
    public $error;

    /**
     * The human-readable description of the error.
     *
     * @var string
     */
    public $message;

    /**
     * OAuthError constructor.
     *
     * @param string $code
     * @param string $description
     * @param AccountOwnerInterface $owner
     * @param string $view
     * @param array $data
     */
    public function __construct($code, $description, $owner, $view, $data = [])
    {
        parent::__construct($owner, $view, $data);
        $this->error = $code;
        $this->message = $description;
    }

    /**
     * @inheritDoc
     */
    protected function defaults()
    {
        return ['error' => $this->error, 'message' => $this->message];
    }

}
