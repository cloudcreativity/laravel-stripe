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

namespace CloudCreativity\LaravelStripe\Log;

use Illuminate\Support\Arr;
use JsonSerializable;
use Psr\Log\LoggerInterface;

class Logger
{

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var string
     */
    private $level;

    /**
     * @var array
     */
    private $exclude;

    /**
     * Logger constructor.
     *
     * @param LoggerInterface $log
     * @param string|null $level
     *      the log level, defaults to 'debug'.
     * @param array $exclude
     *      mapping of Stripe objects and paths that should be excluded from logging.
     */
    public function __construct(LoggerInterface $log, $level = null, array $exclude = [])
    {
        $this->log = $log;
        $this->level = $level ?: 'debug';
        $this->exclude = $exclude;
    }

    /**
     * Log a message at the configured level.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($message, array $context = [])
    {
        $this->log->log($this->level, $message, $context);
    }

    /**
     * Encode data into an error message.
     *
     * @param string $message
     * @param mixed $data
     * @param array $context
     * @return void
     */
    public function encode($message, $data, array $context = [])
    {
        $message .= ':' . PHP_EOL . $this->toJson($data);

        $this->log($message, $context);
    }

    /**
     * Encode a Stripe object for a log message.
     *
     * @param mixed $data
     * @return string
     */
    private function toJson($data)
    {
        if ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        $data = $this->serialize((array) $data);

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array $data
     * @return array
     */
    private function serialize(array $data)
    {
        $this->sanitise($data);

        return collect($data)->map(function ($value) {
            return is_array($value) ? $this->serialize($value) : $value;
        })->all();
    }

    /**
     * @param array $data
     */
    private function sanitise(array &$data)
    {
        $name = isset($data['object']) ? $data['object'] : null;

        /** Stripe webhooks contain an object key that is not a string. */
        if (!is_string($name)) {
            return;
        }

        foreach ($this->exclude($data['object']) as $path) {
            if (!$value = Arr::get($data, $path)) {
                continue;
            }

            if (is_string($value)) {
                Arr::set($data, $path, '***');
            } else {
                Arr::forget($data, $path);
            }
        }
    }

    /**
     * Get the paths to exclude from logging.
     *
     * @param mixed $name
     * @return array
     */
    private function exclude($name)
    {
        if (isset($this->exclude[$name])) {
            return (array) $this->exclude[$name];
        }

        return $this->exclude[$name] = [];
    }
}
