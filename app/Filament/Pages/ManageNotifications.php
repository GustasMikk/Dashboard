<?php

namespace App\Filament\Pages;

use App\Settings\NotificationSettings;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
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

            Section::make('AI Provider')
                ->icon('heroicon-o-cpu-chip')
                ->schema([
                    Select::make('ai_provider')
                        ->label('Provider')
                        ->live()
                        ->options([
                            'gemini'    => 'Google Gemini',
                            'openai'    => 'OpenAI',
                            'anthropic' => 'Anthropic (Claude)',
                        ]),
                    Select::make('ai_model')
                        ->label('Model')
                        ->options(fn ($get) => match($get('ai_provider')) {
                            'gemini'    => [
                                'gemini-1.5-flash' => 'Gemini 1.5 Flash (Free)',
                                'gemini-1.5-pro'   => 'Gemini 1.5 Pro',
                                'gemini-3-flash-preview' => 'Gemini 3 Flash Preview',
                            ],
                            'openai'    => [
                                'gpt-4o-mini' => 'GPT-4o Mini (Cheap)',
                                'gpt-4o'      => 'GPT-4o',
                            ],
                            'anthropic' => [
                                'claude-haiku-4-5'  => 'Claude Haiku (Fast)',
                                'claude-sonnet-4-5' => 'Claude Sonnet',
                            ],
                            default => []
                        }),
                ]),
        ]);
    }
}