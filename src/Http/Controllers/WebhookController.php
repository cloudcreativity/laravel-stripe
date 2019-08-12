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

namespace CloudCreativity\LaravelStripe\Http\Controllers;

use CloudCreativity\LaravelStripe\Contracts\Webhooks\ProcessorInterface;
use CloudCreativity\LaravelStripe\Log\Logger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Stripe\Event;

class WebhookController extends Controller
{

    /**
     * @var Logger
     */
    private $log;

    /**
     * WebhookController constructor.
     *
     * @param Logger $log
     */
    public function __construct(Logger $log)
    {
        $this->log = $log;
    }

    /**
     * Handle a Stripe webhook.
     *
     * @param Request $request
     * @param ProcessorInterface $processor
     * @return Response
     */
    public function __invoke(Request $request, ProcessorInterface $processor)
    {
        if ('event' !== $request->json('object') || empty($request->json('id'))) {
            $this->log->log("Invalid Stripe webhook payload.");

            return response()->json(['error' => 'Invalid JSON payload.'], Response::HTTP_BAD_REQUEST);
        }

        $event = Event::constructFrom($request->json()->all());

        /** Only process the webhook if it has not already been processed. */
        if ($processor->didReceive($event)) {
            $this->log->log(sprintf(
                "Ignoring Stripe webhook %s for event %s, as it is already processed.",
                $event->id,
                $event->type
            ));
        } else {
            $this->log->encode("Received new Stripe webhook event {$event->type}", $event);
            $processor->receive($event);
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

}
