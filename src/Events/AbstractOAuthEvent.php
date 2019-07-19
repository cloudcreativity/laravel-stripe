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

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountOwnerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

abstract class AbstractOAuthEvent
{

    use SerializesModels;

    /**
     * The signed in user at the time of the event.
     *
     * @var AccountOwnerInterface|Model
     */
    public $owner;

    /**
     * The view that will be rendered.
     *
     * @var string
     */
    public $view;

    /**
     * Additional data to provide to the view.
     *
     * @var array
     */
    public $data;

    /**
     * Get view data.
     *
     * @return array
     */
    abstract protected function defaults();

    /**
     * AbstractConnectEvent constructor.
     *
     * @param AccountOwnerInterface $owner
     * @param string $view
     * @param array $data
     */
    public function __construct(AccountOwnerInterface $owner, $view, $data = [])
    {
        $this->owner = $owner;
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * @param array|string $key
     * @param mixed|null $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Get all view data.
     *
     * @return array
     */
    public function all()
    {
        return collect($this->data)
            ->merge($this->defaults())
            ->put('owner', $this->owner)
            ->all();
    }
}
