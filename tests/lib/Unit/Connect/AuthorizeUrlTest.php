<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

namespace CloudCreativity\LaravelStripe\Tests\Unit\Connect;

use CloudCreativity\LaravelStripe\Connect\AuthorizeUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stripe\Stripe;
use Stripe\Util\Util;

class AuthorizeUrlTest extends TestCase
{

    /**
     * @var AuthorizeUrl
     */
    private $url;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Stripe::setClientId('my_client_id');
        $this->url = new AuthorizeUrl('state_secret');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Stripe::setClientId(null);
    }

    /**
     * @return array
     */
    public static function valueProvider(): array
    {
        return [
            'read_only' => [
                ['scope' => 'read_only'],
                'readOnly',
            ],
            'read_write' => [
                ['scope' => 'read_write'],
                'readWrite',
            ],
            'redirect_uri' => [
                ['redirect_uri' => 'https://example.com'],
                'redirectUri',
                'https://example.com',
            ],
            'login' => [
                ['stripe_landing' => 'login'],
                'login',
            ],
            'register' => [
                ['stripe_landing' => 'register'],
                'register',
            ],
            'always_prompt' => [
                ['always_prompt' => 'true'],
                'alwaysPrompt',
            ],
            'user' => [
                ['stripe_user' => ['email' => 'bob@example.com']],
                'user',
                ['email' => 'bob@example.com'],
            ],
            'stripe_user' => [
                ['stripe_user' => ['email' => 'bob@example.com']],
                'stripeUser',
                ['email' => 'bob@example.com', 'foo' => null],
            ],
        ];
    }

    #[DataProvider('valueProvider')]
    public function testStandard(array $expected, string $method, mixed $value = null): void
    {
        $args = !is_null($value) ? [$value] : [];
        $result = call_user_func_array([$this->url, $method], $args);

        $this->assertSame($this->url, $result, "{$method} is fluent");
        $this->assertUrl('https://connect.stripe.com/oauth/authorize', $expected, "{$method}");
    }

    #[DataProvider('valueProvider')]
    public function testExpress(array $expected, string $method, mixed $value = null): void
    {
        $this->assertSame($this->url, $this->url->express(), 'express is fluent');

        $args = !is_null($value) ? [$value] : [];
        $result = call_user_func_array([$this->url, $method], $args);

        $this->assertSame($this->url, $result, "{$method} is fluent");
        $this->assertUrl('https://connect.stripe.com/express/oauth/authorize', $expected, "{$method}");
    }

    private function assertUrl(string $uri, array $params, string $message = ''): void
    {
        $params = array_replace([
            'state' => 'state_secret',
            'response_type' => 'code',
        ], $params);

        ksort($params);

        $params['client_id'] = 'my_client_id';

        $expected = $uri . '?' . Util::encodeParameters($params);

        $this->assertSame($expected, (string) $this->url, $message);
    }
}
