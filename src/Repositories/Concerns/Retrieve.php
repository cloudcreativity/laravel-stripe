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

namespace CloudCreativity\LaravelStripe\Repositories\Concerns;

use InvalidArgumentException;
use Stripe\StripeObject;

trait Retrieve
{

    /**
     * Retrieve a Stripe object.
     *
     * @param string $id
     * @return StripeObject
     */
    public function retrieve($id)
    {
        if (!is_string($id) || empty($id)) {
            throw new InvalidArgumentException('Expecting a non-empty resource id.');
        }

        $this->param(self::PARAM_ID, $id);

        return $this->send('retrieve', $this->params, $this->options ?: null);
    }

}
