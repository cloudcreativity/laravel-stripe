<?php

namespace CloudCreativity\LaravelStripe\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;

class OAuthError extends AbstractConnectEvent
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
     * @param Authenticatable|null $user
     * @param string $view
     * @param array $data
     */
    public function __construct($code, $description, $user, $view, $data = [])
    {
        parent::__construct($user, $view, $data);
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
