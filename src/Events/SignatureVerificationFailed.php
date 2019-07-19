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
