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

namespace CloudCreativity\LaravelStripe\Tests\Integration\Connect;

use CloudCreativity\LaravelStripe\Contracts\Connect\StateProviderInterface;
use CloudCreativity\LaravelStripe\Events\OAuthError;
use CloudCreativity\LaravelStripe\Events\OAuthSuccess;
use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\Jobs\FetchUserCredentials;
use CloudCreativity\LaravelStripe\LaravelStripe;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use CloudCreativity\LaravelStripe\Tests\TestUser;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;

class OAuthTest extends TestCase
{

    /**
     * @var User
     */
    private $user;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->instance(
            StateProviderInterface::class,
            $state = $this->createMock(StateProviderInterface::class)
        );

        $state->method('get')->willReturn('session_token');
        $state->method('check')->willReturnCallback(function ($v) {
            return 'session_token' === $v;
        });

        $this->user = factory(TestUser::class)->create();

        Route::group(['namespace' => 'App\Http\Controllers'], function () {
            Stripe::oauth('/test/authorize')->name('test.authorize');
        });
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        LaravelStripe::currentOwnerResolver(null);
    }

    public function test()
    {
        config()->set('stripe.connect.queue', 'my_queue');
        config()->set('stripe.connect.queue_connection', 'my_connection');

        $params = [
            'state' => 'session_token',
            'scope' => 'read_write',
            'code' => 'access_code',
        ];

        $expected = [
            'scope' => $params['scope'],
            'owner' => $this->user,
            'foo' => 'bar',
        ];

        $this->app['events']->listen(OAuthSuccess::class, function (OAuthSuccess $event) {
            $this->assertSame('access_code', $event->code, 'event code');
            $this->assertSame('read_write', $event->scope, 'event scope');
            $this->assertTrue($this->user->is($event->owner), 'event user');
            $this->assertSame('test::oauth.success', $event->view);
            $event->with('foo', 'bar');
        });

        $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->get('/test/authorize?' . http_build_query($params))
            ->assertStatus(200)
            ->assertViewIs('test::oauth.success')
            ->assertViewHas($expected);

        Queue::assertPushedOn('my_queue', FetchUserCredentials::class, function ($job) {
            $this->assertSame('my_connection', $job->connection, 'job connection');
            return true;
        });
    }

    /**
     * Checks that we handle missing scope parameter from Stripe.
     *
     * A null scope should be used and
     * a job to fetch the user credetials should be queued.
     */
    public function testScopeIsOptional(): void
    {
        $params = [
            'state' => 'session_token',
            'code' => 'access_code',
        ];

        $this->actingAs($this->user)
            ->get('/test/authorize?' . http_build_query($params))
            ->assertStatus(200);

        $this->app['events']->listen(OAuthSuccess::class, function (OAuthSuccess $event) {
            $this->assertSame('access_code', $event->code, 'event code');
            $this->assertNull($event->scope, 'event scope');
            $this->assertTrue($this->user->is($event->owner), 'event user');
            $this->assertSame('test::oauth.success', $event->view);
            $event->with('foo', 'bar');
        });

        Queue::assertPushed(FetchUserCredentials::class);
    }

    public function testError()
    {
        /** This checks we can override the owner resolver as we're not using `actingAs` */
        LaravelStripe::currentOwnerResolver(function () {
            return $this->user;
        });

        $params = [
            'state' => 'session_token',
            'error' => 'invalid_scope',
            'error_description' => 'Invalid scope!',
        ];

        $expected = [
            'error' => $params['error'],
            'message' => $params['error_description'],
            'owner' => $this->user,
            'foo' => 'bar',
        ];

        $this->app['events']->listen(OAuthError::class, function (OAuthError $event) {
            $this->assertSame('invalid_scope', $event->error, 'event error');
            $this->assertSame('Invalid scope!', $event->message, 'event message');
            $this->assertSame($this->user, $event->owner, 'event user');
            $this->assertSame('test::oauth.error', $event->view);
            $event->with('foo', 'bar');
        });

        $this->withoutExceptionHandling()
            ->get('/test/authorize?' . http_build_query($params))
            ->assertStatus(422)
            ->assertViewIs('test::oauth.error')
            ->assertViewHas($expected);

        Queue::assertNotPushed(FetchUserCredentials::class);
    }

    public function testForbidden()
    {
        $params = [
            'state' => 'foobar',
            'scope' => 'read_write',
            'code' => 'access_code',
        ];

        $expected = [
            'error' => 'laravel_stripe_forbidden',
            'message' => 'Invalid authorization token.',
            'owner' => $this->user,
            'foo' => 'bar',
        ];

        $this->app['events']->listen(OAuthError::class, function (OAuthError $event) {
            $this->assertSame('laravel_stripe_forbidden', $event->error, 'event error');
            $this->assertSame('Invalid authorization token.', $event->message, 'event message');
            $this->assertTrue($this->user->is($event->owner), 'event user');
            $this->assertSame('test::oauth.error', $event->view);
            $event->with('foo', 'bar');
        });

        $this->withoutExceptionHandling()
            ->actingAs($this->user)
            ->get('/test/authorize?' . http_build_query($params))
            ->assertStatus(403)
            ->assertViewIs('test::oauth.error')
            ->assertViewHas($expected);

        Queue::assertNotPushed(FetchUserCredentials::class);
    }

    /**
     * @return array
     */
    public function invalidProvider()
    {
        return [
            'state' => ['state'],
            'code' => ['code'],
        ];
    }


    /**
     * Checks that we handle any missing parameters from Stripe.
     *
     * In theory a user should never encounter this, as Stripe will send what
     * we expect it to send. But it is good to handle the scenario just in case.
     *
     * @param string $missing
     * @dataProvider invalidProvider
     */
    public function testInvalid($missing)
    {
        $params = collect([
            'state' => 'session_token',
            'scope' => 'read_write',
            'code' => 'access_code',
        ])->forget($missing)->all();

        $this->actingAs($this->user)
            ->get('/test/authorize?' . http_build_query($params))
            ->assertStatus(400);

        Queue::assertNotPushed(FetchUserCredentials::class);
    }
}
