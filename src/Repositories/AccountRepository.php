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

namespace CloudCreativity\LaravelStripe\Repositories;

use InvalidArgumentException;
use Stripe\Account;

class AccountRepository extends AbstractRepository
{

    use Concerns\All;
    use Concerns\Update;

    /**
     * @param string $type
     * @param iterable|array $params
     *      additional optional parameters.
     * @return Account
     */
    public function create($type = 'custom', $params = [])
    {
        if (!is_string($type) || empty($type)) {
            throw new InvalidArgumentException('Expecting a non-empty string.');
        }

        $this->params($params)->param('type', $type);

        return $this->send('create', $this->params ?: null, $this->options ?: null);
    }

    /**
     * Retrieve a Stripe account.
     *
     * If the id is not provided, the account associated with this
     * repository is returned.
     *
     * @param string $id
     * @return Account
     */
    public function retrieve($id = null)
    {
        if (!is_string($id) && !is_null($id)) {
            throw new InvalidArgumentException('Expecting a string or null.');
        }

        if ($id) {
            $this->param('id', $id);
        }

        return $this->send('retrieve', $this->params ?: null, $this->options ?: null);
    }

    /**
     * @inheritDoc
     */
    protected function fqn()
    {
        return Account::class;
    }


}
