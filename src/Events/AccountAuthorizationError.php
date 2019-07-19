<?php

namespace CloudCreativity\LaravelStripe\Events;

use Illuminate\Queue\SerializesModels;

class AccountAuthorizationError extends AbstractConnectEvent
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
     * The unique Stripe error code.
     *
     * @var string
     */
    public $code;

    /**
     * The human-readable description of the error.
     *
     * @var string
     */
    public $description;

    /**
     * AccountAuthorizationError constructor.
     *
     * @param string $code
     * @param string $description
     * @param mixed|null $user
     * @param string $view
     * @param array $data
     */
    public function __construct($code, $description, $user, $view, $data = [])
    {
        parent::__construct($user, $view, $data);
        $this->code = $code;
        $this->description = $description;
    }

    /**
     * @inheritDoc
     */
    protected function defaults()
    {
        return ['code' => $this->code, 'description' => $this->description];
    }

}
