<?php

namespace CloudCreativity\LaravelStripe\Tests;

use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TestWebhookJob implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels, Queueable;

    /**
     * @var Webhook
     */
    public $webhook;

    /**
     * TestWebhookJob constructor.
     *
     * @param Webhook $webhook
     */
    public function __construct(Webhook $webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // noop
    }
}
