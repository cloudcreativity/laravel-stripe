<?php

namespace CloudCreativity\LaravelStripe\Http\Requests;

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Connect\AuthorizeUrl;
use CloudCreativity\LaravelStripe\Contracts\Connect\StateProviderInterface;
use CloudCreativity\LaravelStripe\Events\OAuthError;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthorizeConnect extends FormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'code' => [
                'required_without:error',
                'string',
            ],
            'state' => [
                'required',
                'string',
            ],
            'scope' => [
                'required_with:code',
                Rule::in(AuthorizeUrl::scopes()),
            ],
            'error' => [
                'required_without:code',
                'string',
            ],
            'error_description' => [
                'required_with:error',
                'string',
            ],
        ];
    }

    /**
     * @param StateProviderInterface $provider
     * @return bool
     */
    public function authorize(StateProviderInterface $provider)
    {
        if (!$state = $this->query('state')) {
            return false;
        }

        return $provider->check($state);
    }

    /**
     * Handle the request not being authorized.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization()
    {
        event($event = new OAuthError(
            OAuthError::LARAVEL_STRIPE_FORBIDDEN,
            'Invalid authorization token.',
            $this->query('state'),
            Auth::user(),
            Config::connectErrorView()
        ));

        throw new HttpResponseException(
            response()->view($event->view, $event->all(), Response::HTTP_FORBIDDEN)
        );
    }

    /**
     * @return array
     */
    protected function validationData()
    {
        return $this->query();
    }
}
