<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AppSettings extends Settings
{
    public bool $email_enabled;

    public array $email_severities;

    public bool $ai_generation_enabled;

    public array $ai_severities;

    public string $ai_provider;

    public string $ai_model;

    public string $time_for_new_group;

    public string $time_to_generate_ai_solution;

    public string $ai_instructions;

    public static function group(): string
    {
        return 'notifications';
    }
}
