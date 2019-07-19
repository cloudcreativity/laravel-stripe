<?php

namespace CloudCreativity\LaravelStripe\Tests\Integration\Connect;

use CloudCreativity\LaravelStripe\Contracts\Connect\StateProviderInterface;
use CloudCreativity\LaravelStripe\Events\OAuthError;
use CloudCreativity\LaravelStripe\Events\OAuthSuccess;
use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\Jobs\FetchUserCredentials;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Queue;

class AuthorizeTest extends TestCase
{

    /**
     * @var Authenticatable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $user;

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        Stripe::oauth('/test/authorize');

        Queue::fake();

        $this->instance(
            StateProviderInterface::class,
            $state = $this->createMock(StateProviderInterface::class)
        );

        $state->method('get')->willReturn('session_token');
        $state->method('check')->willReturnCallback(function ($v) {
            return 'session_token' === $v;
        });

        $this->user = $this->createMock(Authenticatable::class);
        $this->user->method('getAuthIdentifier')->willReturn(123);
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
            'user' => $this->user,
            'foo' => 'bar',
        ];

        $this->app['events']->listen(OAuthSuccess::class, function (OAuthSuccess $event) {
            $this->assertSame('access_code', $event->code, 'event code');
            $this->assertSame('read_write', $event->scope, 'event scope');
            $this->assertSame('session_token', $event->state, 'event state');
            $this->assertSame($this->user, $event->user, 'event user');
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

    public function testError()
    {
        $params = [
            'state' => 'session_token',
            'error' => 'invalid_scope',
            'error_description' => 'Invalid scope!',
        ];

        $expected = [
            'error' => $params['error'],
            'message' => $params['error_description'],
            'user' => $this->user,
            'foo' => 'bar',
        ];

        $this->app['events']->listen(OAuthError::class, function (OAuthError $event) {
            $this->assertSame('invalid_scope', $event->error, 'event error');
            $this->assertSame('Invalid scope!', $event->message, 'event message');
            $this->assertSame('session_token', $event->state, 'event state');
            $this->assertSame($this->user, $event->user, 'event user');
            $this->assertSame('test::oauth.error', $event->view);
            $event->with('foo', 'bar');
        });

        $this->withoutExceptionHandling()
            ->actingAs($this->user)
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
            'user' => $this->user,
            'foo' => 'bar',
        ];

        $this->app['events']->listen(OAuthError::class, function (OAuthError $event) {
            $this->assertSame('laravel_stripe_forbidden', $event->error, 'event error');
            $this->assertSame('Invalid authorization token.', $event->message, 'event message');
            $this->assertSame('foobar', $event->state, 'event state');
            $this->assertSame($this->user, $event->user, 'event user');
            $this->assertSame('test::oauth.error', $event->view);
            $event->with('foo', 'bar');
        });

        $this->actingAs($this->user)
            ->get('/test/authorize?' . http_build_query($params))
            ->assertStatus(403)
            ->assertViewIs('test::oauth.error')
            ->assertViewHas($expected);

        Queue::assertNotPushed(FetchUserCredentials::class);
    }
}
