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

namespace CloudCreativity\LaravelStripe\Http\Requests;

use CloudCreativity\LaravelStripe\Connect\AuthorizeUrl;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountOwnerInterface;
use CloudCreativity\LaravelStripe\LaravelStripe;
use Illuminate\Foundation\Http\FormRequest;
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
     * Authorize the request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->owner() instanceof AccountOwnerInterface;
    }

    /**
     * Get the Stripe account owner for the request.
     *
     * @return AccountOwnerInterface
     */
    public function owner()
    {
        if ($fn = LaravelStripe::$currentOwnerResolver) {
            return call_user_func($fn, $this);
        }

        return $this->user();
    }

    /**
     * @return array
     */
    protected function validationData()
    {
        return $this->query();
    }
}
