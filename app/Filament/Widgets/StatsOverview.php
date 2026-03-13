<?php

namespace App\Filament\Widgets;

use App\Models\Incident;
use App\Models\IncidentGroup;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Incidents', Incident::count())
                ->icon('heroicon-o-shield-exclamation')
                ->columnSpan(2),

            Stat::make('Incident Groups', IncidentGroup::where('status', 'open')->count())
                ->icon('heroicon-o-rectangle-group')
                ->columnSpan(2),

            Stat::make('Low', Incident::where('severity', 'low')->count())
                ->icon('heroicon-o-bell'),

            Stat::make('Medium', Incident::where('severity', 'medium')->count())
                ->icon('heroicon-o-exclamation-circle'),

            Stat::make('High', Incident::where('severity', 'high')->count())
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('Critical', Incident::where('severity', 'critical')->count())
                ->icon('heroicon-o-fire'),

            Stat::make('Open Incidents', IncidentGroup::where('status', 'open')->count())
                ->icon('heroicon-o-exclamation-circle'),

            Stat::make('Assigned Incidents', IncidentGroup::where('status', 'assigned')->count())
                ->icon('heroicon-o-user'),

            Stat::make('Closed Incidents', IncidentGroup::where('status', 'closed')->count())
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
