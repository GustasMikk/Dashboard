<?php

namespace App\Filament\Pages;

use App\Settings\NotificationSettings;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManageNotifications extends SettingsPage
{
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    protected static ?string $navigationLabel = 'Settings';

    public static function getNavigationGroup(): string
    {
        return 'Wazuh Incidents';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    protected static string $settings = NotificationSettings::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Email Notifications')
                ->icon('heroicon-o-envelope')
                ->description('If AI generation is off emails will be off too.')
                ->schema([
                    Toggle::make('email_enabled')
                        ->label('Enable email notifications')
                        ->live(),
                    CheckboxList::make('email_severities')
                        ->label('Send email for these severities')
                        ->options([
                            'critical' => 'Critical',
                            'high'     => 'High',
                            'medium'   => 'Medium',
                            'low'      => 'Low',
                        ])
                        ->visible(fn ($get) => $get('email_enabled')),
                ]),

            Section::make('AI Analysis')
                ->icon('heroicon-o-cpu-chip')
                ->schema([
                    Toggle::make('ai_generation_enabled')
                        ->label('Enable AI generation')
                        ->live(),
                    CheckboxList::make('ai_severities')
                        ->label('Run AI analysis for these severities')
                        ->options([
                            'critical' => 'Critical',
                            'high'     => 'High',
                            'medium'   => 'Medium',
                            'low'      => 'Low',
                        ])
                        ->visible(fn ($get) => $get('ai_generation_enabled')),
                ]),
        ]);
    }
}