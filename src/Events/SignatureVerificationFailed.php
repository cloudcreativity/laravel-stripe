<?php

namespace CloudCreativity\LaravelStripe\Events;

use CloudCreativity\LaravelStripe\Config;
use Illuminate\Contracts\Support\Arrayable;

class SignatureVerificationFailed implements Arrayable
{

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $header;

    /**
     * The key of the signing secret.
     *
     * @var string
     */
    public $signingSecret;

    /**
     * SignatureVerificationFailed constructor.
     *
     * @param string $message
     * @param string $header
     * @param string $signingSecret
     */
    public function __construct($message, $header, $signingSecret)
    {
        $this->message = $message;
        $this->header = $header;
        $this->signingSecret = $signingSecret;
    }

    /**
     * @return string
     */
    public function signingSecret()
    {
        return Config::webhookSigningSecrect($this->signingSecret);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'message' => $this->message,
            'header' => $this->header,
            'signing_secret' => $this->signingSecret,
        ];
    }

}
