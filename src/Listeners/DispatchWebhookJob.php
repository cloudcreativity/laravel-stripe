<?php

namespace CloudCreativity\LaravelStripe\Listeners;

use CloudCreativity\LaravelStripe\Log\Logger;
use CloudCreativity\LaravelStripe\Webhooks\Webhook;
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
        if (!$job = $webhook->job()) {
            return;
        }

        /** @var Queueable $job */
        $job = new $job($webhook);
        $job->onConnection($webhook->connection());
        $job->onQueue($webhook->queue());

        $this->log->log("Dispatching job for webhook '{$webhook->type()}'.", [
            'id' => $webhook->id(),
            'connection' => $webhook->connection(),
            'queue' => $webhook->queue(),
            'job' => $webhook->job(),
        ]);

        $this->queue->dispatch($job);
    }
}
