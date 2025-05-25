<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected array $data = [];
    protected string $rule_slug;
    /**
     * Create a new job instance.
     */
    public function __construct(string $ruleSlug, array $data)
    {
        $this->data = $data;
        $this->rule_slug = $ruleSlug;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Notification Job in handling..................");
        $notificationHelper = new NotificationHelper();
        $notificationHelper->handle($this->rule_slug, $this->data);
    }
}
