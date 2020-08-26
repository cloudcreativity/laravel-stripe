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

declare(strict_types=1);

namespace CloudCreativity\LaravelStripe\Repositories\Concerns;

use Illuminate\Support\Collection as IlluminateCollection;
use Stripe\Collection as StripeCollection;

/**
 * Trait All
 *
 * @todo add cursor paging helper methods
 */
trait All
{

    /**
     * Query all resources.
     *
     * @param iterable|array $params
     * @return StripeCollection
     */
    public function all($params = []): StripeCollection
    {
        $this->params($params);

        return $this->send('all', $this->params ?: null, $this->options ?: null);
    }

    /**
     * Query all resources, and return a Laravel collection.
     *
     * @param iterable|array $params
     * @return IlluminateCollection
     */
    public function collect($params = []): IlluminateCollection
    {
        return collect($this->all($params));
    }
}
