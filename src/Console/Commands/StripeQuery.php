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

namespace CloudCreativity\LaravelStripe\Console\Commands;

use CloudCreativity\LaravelStripe\Connector;
use CloudCreativity\LaravelStripe\Exceptions\InvalidArgumentException;
use CloudCreativity\LaravelStripe\Repositories\AbstractRepository;
use CloudCreativity\LaravelStripe\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Stripe\Error\Base as StripeException;

class StripeQuery extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "stripe 
        {resource : The resource name } 
        {id? : The resource id } 
        {--A|account= : The connected account }
        {--e|expand=* : The paths to expand }
    ";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve data from Stripe.';

    /**
     * Execute the console command.
     *
     * @param StripeService $stripe
     * @return int
     */
    public function handle(StripeService $stripe)
    {
        $resource = Str::snake($this->argument('resource'));
        $id = $this->argument('id');
        $account = $this->option('account');

        try {
            /** @var Connector $connector */
            $connector = $account ? $stripe->account($account) : $stripe->app();

            /** @var AbstractRepository $repository */
            $repository = call_user_func($connector, $resource);

            if ($expand = $this->option('expand')) {
                $repository->expand(...$expand);
            }

            /** Get the result */
            $result = $id ?
                $this->retrieve($repository, $resource, $id) :
                $this->query($repository, $resource);
        } catch (InvalidArgumentException $ex) {
            $this->error($ex->getMessage());
            return 1;
        } catch (StripeException $ex) {
            $this->error('Stripe Error: ' . $ex->getMessage());
            return 2;
        }

        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return 0;
    }

    /**
     * @param AbstractRepository $repository
     * @param string $resource
     * @param string $id
     * @return \JsonSerializable
     * @throws StripeException
     */
    private function retrieve(AbstractRepository $repository, $resource, $id)
    {
        if (!method_exists($repository, 'retrieve')) {
            throw new InvalidArgumentException("Retrieving resource '{$resource}' is not supported.");
        }

        $this->info(sprintf('Retrieving %s %s', Str::singular($resource), $id));

        return $repository->retrieve($id);
    }

    /**
     * @param AbstractRepository $repository
     * @param $resource
     * @return \JsonSerializable
     * @throws StripeException
     * @todo add support for pagination.
     */
    private function query(AbstractRepository $repository, $resource)
    {
        if (!method_exists($repository, 'all')) {
            throw new InvalidArgumentException("Querying resource '{$resource}' is not supported.");
        }

        $this->info("Querying {$resource}");

        return $repository->all();
    }

}
