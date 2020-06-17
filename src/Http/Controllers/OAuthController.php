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

namespace CloudCreativity\LaravelStripe\Http\Controllers;

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Contracts\Connect\StateProviderInterface;
use CloudCreativity\LaravelStripe\Events\OAuthError;
use CloudCreativity\LaravelStripe\Events\OAuthSuccess;
use CloudCreativity\LaravelStripe\Http\Requests\AuthorizeConnect;
use CloudCreativity\LaravelStripe\Log\Logger;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class OAuthController extends Controller
{

    /**
     * @var Logger
     */
    private $log;

    /**
     * OAuthController constructor.
     *
     * @param Logger $log
     */
    public function __construct(Logger $log)
    {
        $this->log = $log;
    }

    /**
     * Handle the Stripe Connect authorize endpoint.
     *
     * @param AuthorizeConnect $request
     * @param StateProviderInterface $state
     * @return Response
     */
    public function __invoke(AuthorizeConnect $request, StateProviderInterface $state)
    {
        $data = collect($request->query())->only([
            'code',
            'scope',
            'error',
            'error_description',
        ]);

        $owner = $request->owner();

        $this->log->log('Received OAuth redirect.', $data->all());

        /** Check the state parameter and return an error if it is not as expected. */
        if (true !== $state->check($request->query('state'))) {
            return $this->error(Response::HTTP_FORBIDDEN, [
                'error' => OAuthError::LARAVEL_STRIPE_FORBIDDEN,
                'error_description' => 'Invalid authorization token.',
            ], $owner);
        }

        /** If Stripe has told there is an error, return an error response. */
        if ($data->has('error')) {
            return $this->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $data,
                $owner
            );
        }

        /** Otherwise return our success view. */
        return $this->success($data, $owner);
    }

    /**
     * Handle success.
     *
     * @param $data
     * @param $user
     * @return Response
     */
    protected function success($data, $user)
    {
        event($success = new OAuthSuccess(
            $data['code'],
            $data['scope'],
            $user,
            Config::connectSuccessView()
        ));

        return response()->view($success->view, $success->all());
    }

    /**
     * Handle an error.
     *
     * @param int $status
     * @param $data
     * @param $user
     * @return Response
     */
    protected function error($status, $data, $user)
    {
        event($error = new OAuthError(
            $data['error'],
            $data['error_description'],
            $user,
            Config::connectErrorView()
        ));

        return response()->view(
            $error->view,
            $error->all(),
            $status
        );
    }
}
