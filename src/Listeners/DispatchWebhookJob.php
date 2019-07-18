<?php

namespace CloudCreativity\LaravelStripe\Listeners;

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use CloudCreativity\LaravelStripe\Log\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;

class DispatchWebhookJob
{

    /**
     * @var Dispatcher
     */
    private $queue;

    /**
     * @var Logger
     */
    private $log;

    /**
     * DispatchWebhookJob constructor.
     *
     * @param Dispatcher $queue
     * @param Logger $log
     */
    public function __construct(Dispatcher $queue, Logger $log)
    {
        $this->queue = $queue;
        $this->log = $log;
    }

    /**
     * Handle the event.
     *
     * @param Webhook $webhook
     * @return void
     */
    public function handle(Webhook $webhook)
    {
        if ($job = Config::webhookJob($webhook->type())) {
            $this->dispatch($job, $webhook);
        }
    }

    /**
     * Dispatched the named job.
     *
     * Jobs are dispatched to the same queue and connection as the webhook.
     *
     * @param string $job
     *      the fully qualified job class name.
     * @param Webhook $webhook
     * @return void
     */
    private function dispatch($job, Webhook $webhook)
    {
        /** @var Queueable $job */
        $job = new $job($webhook);
        $job->onConnection($webhook->connection)->onQueue($webhook->queue);

        $this->queue->dispatch($job);
    }
}
