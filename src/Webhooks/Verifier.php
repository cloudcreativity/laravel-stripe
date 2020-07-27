<?php
/**
 * Copyright 2020 Cloud Creativity Limited
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

namespace CloudCreativity\LaravelStripe\Webhooks;

use CloudCreativity\LaravelStripe\Config;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\WebhookSignature;

class Verifier
{

    const SIGNATURE_HEADER = 'Stripe-Signature';

    /**
     * Verify the request is from Stripe.
     *
     * @param Request $request
     * @param string $name
     *      the signing secret key name.
     * @return void
     * @throws SignatureVerificationException
     */
    public function verify($request, $name)
    {
        if (!$header = $request->header(self::SIGNATURE_HEADER)) {
            throw SignatureVerificationException::factory(
                'Expecting ' . self::SIGNATURE_HEADER . ' header.',
                $request->getContent(),
                $header
            );
        }

        WebhookSignature::verifyHeader(
            $request->getContent(),
            $header,
            Config::webhookSigningSecrect($name),
            Config::webhookTolerance()
        );
    }
}
