<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    public bool $email_enabled;
    public array $email_severities;
    public bool $ai_generation_enabled;
    public array $ai_severities;
    public string $ai_provider;
    public string $ai_model;

    public static function group(): string
    {
        return 'notifications';
    }
}