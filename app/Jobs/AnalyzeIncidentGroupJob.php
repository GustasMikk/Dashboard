<?php

namespace App\Jobs;

use App\Mail\IncidentAnalyzedMail;
use App\Models\IncidentGroup;
use App\Services\AiService;
use App\Settings\NotificationSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AnalyzeIncidentGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 120, 300];

    public function __construct(public IncidentGroup $group) {}

    public function handle(): void
    {
        Log::info('Group job started');

        $this->group->refresh(); // get latest state with all incidents

        // If a newer job has been scheduled, skip this one
        if ($this->group->ai_scheduled_at > now()) {
            return; // a newer job will handle it
        }

        $settings = app(NotificationSettings::class);

        try {
            $ai = app(AiService::class);
            $result = $ai->analyzeGroup($this->group);
            $this->group->update($result);
            $this->group->refresh();

            // if ($settings->email_enabled && in_array($this->group->highest_severity, $settings->email_severities)) {
            //     Mail::to(config('mail.admin_address'))->queue(new IncidentAnalyzedMail($this->group));
            // }
        } catch (\Exception $e) {
            Log::error('Group AI analysis failed', ['group_id' => $this->group->id, 'error' => $e->getMessage()]);
        }
    }
}
