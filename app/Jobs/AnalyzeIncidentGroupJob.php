<?php

namespace App\Jobs;

use App\Models\IncidentGroup;
use App\Models\User;
use App\Notifications\IncidentGroupAnalyzedNotification;
use App\Services\AiService;
use App\Settings\AppSettings;
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
        Log::info('Group analyze started', ['group_id' => $this->group->id]);

        $this->group->refresh();

        if ($this->group->ai_scheduled_at > now()) {
            Log::info('Skipping, newer job scheduled', ['group_id' => $this->group->id]);

            return;
        }

        $settings = app(AppSettings::class);

        $ai = app(AiService::class);
        $result = $ai->analyzeGroup($this->group);
        Log::alert($result);
        $this->group->update($result);
        $this->group->refresh();

        $result = (object) $result;

        try {
            if ($settings->email_enabled && in_array($this->group->highest_severity, $settings->email_severities) && $result->send_email) {
                User::where('emails_enabled', true)->each(function ($user) {
                    $user->notify(new IncidentGroupAnalyzedNotification($this->group));
                });
            }
        } catch (\Exception $e) {
            Log::error('Email notification failed', ['group_id' => $this->group->id, 'error' => $e->getMessage()]);
        }
    }
}
