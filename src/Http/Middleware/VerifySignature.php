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

namespace CloudCreativity\LaravelStripe\Http\Middleware;

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Log\Logger;
use CloudCreativity\LaravelStripe\Webhooks\Verifier;
use Illuminate\Http\Response;
use Stripe\Error\SignatureVerification;

class VerifySignature
{

    /**
     * @var Verifier
     */
    private $verifier;

    /**
     * @var Logger
     */
    private $log;

    /**
     * VerifySignature constructor.
     *
     * @param Verifier $verifier
     * @param Logger $log
     */
    public function __construct(Verifier $verifier, Logger $log)
    {
        $this->verifier = $verifier;
        $this->log = $log;
    }

    /**
     * @param $request
     * @param \Closure $next
     * @param string $signingSecret
     * @return mixed
     */
    public function handle($request, \Closure $next, $signingSecret = 'default')
    {
        if ($level = Config::logLevel()) {
            $this->log->log("Verifying Stripe webhook using signing secret: {$signingSecret}");
        }

        try {
            $this->verifier->verify($request, $signingSecret);
        } catch (SignatureVerification $ex) {
            $this->log->log("Stripe webhook signature verification failed: {$ex->getMessage()}", [
                'header' => $ex->getSigHeader(),
                'signing_secret_key' => $signingSecret,
            ]);

            abort(Response::HTTP_BAD_REQUEST);
        }

        $this->log->log("Verified Stripe webhook with signing secret: {$signingSecret}");

        return $next($request);
    }

}
