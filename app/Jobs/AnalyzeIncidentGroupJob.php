<?php

namespace App\Jobs;

use App\Models\IncidentGroup;
use App\Notifications\IncidentGroupAnalyzedNotification;
use App\Services\AiService;
use App\Settings\NotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AnalyzeIncidentGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 120, 300];

    public function __construct(public IncidentGroup $group) {}

    public function handle(): void
    {
        Log::info('Group analyze started');

        $this->group->refresh();

        if ($this->group->ai_scheduled_at > now()) {
            return;
        }

        $settings = app(NotificationSettings::class);

        try {
            $ai = app(AiService::class);
            $result = $ai->analyzeGroup($this->group);
            $this->group->update($result);
            $this->group->refresh();

            if ($settings->email_enabled && in_array($this->group->highest_severity, $settings->email_severities)) {
                Notification::route('mail', config('mail.admin_address'))
                    ->notify(new IncidentGroupAnalyzedNotification($this->group));
            }
        } catch (\Exception $e) {
            Log::error('Group AI analysis failed', ['group_id' => $this->group->id, 'error' => $e->getMessage()]);
        }
    }
}
