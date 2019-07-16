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

namespace CloudCreativity\LaravelStripe\Listeners;

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Events\ClientReceivedResult;
use Illuminate\Support\Arr;
use JsonSerializable;

class LogClientResults
{

    /**
     * @var array
     */
    private $exclude;

    /**
     * LogClientResults constructor.
     */
    public function __construct()
    {
        $this->exclude = Config::logExclude();
    }

    /**
     * Handle the event.
     *
     * @param ClientReceivedResult $event
     * @return void
     */
    public function handle(ClientReceivedResult $event)
    {
        $message = "Stripe: result for {$event->name}.{$event->method}";
        $context = $event->toArray();

        if ($event->result instanceof JsonSerializable) {
            $message .= ':' . PHP_EOL . $this->encode($event->result);
            unset($context['result']);
        }

        logger()->log(Config::logLevel(), $message, $context);
    }

    /**
     * Encode a Stripe object for a log message.
     *
     * @param JsonSerializable $data
     * @return string
     */
    private function encode($data)
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
        if (!isset($data['object'])) {
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
     * @param $name
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
