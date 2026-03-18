<?php

use App\Models\Incident;
use App\Models\IncidentGroup;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Incident::where('created_at', '<', now()->subDays(30))->delete();
    IncidentGroup::where('created_at', '<', now()->subDays(30))->delete();
})->daily();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
