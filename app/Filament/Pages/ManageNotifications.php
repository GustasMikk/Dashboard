<?php

namespace App\Filament\Pages;

use App\Enums\AiProvider;
use App\Settings\NotificationSettings;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                ->description('Check which severities should be notifed. If AI generation is off emails will be off too.')
                ->schema([
                    Toggle::make('email_enabled')
                        ->label('Enable email notifications')
                        ->live(),
                    CheckboxList::make('email_severities')
                        ->label('Send email for these severities')
                        ->options([
                            'critical' => 'Critical',
                            'high' => 'High',
                            'medium' => 'Medium',
                            'low' => 'Low',
                        ])
                        ->visible(fn ($get) => $get('email_enabled')),
                ]),

            Section::make('AI Analysis')
                ->icon('heroicon-o-cpu-chip')
                ->description('Select which severities AI should look into.')
                ->schema([
                    Toggle::make('ai_generation_enabled')
                        ->label('Enable AI generation')
                        ->live(),
                    CheckboxList::make('ai_severities')
                        ->label('Run AI analysis for these severities')
                        ->options([
                            'critical' => 'Critical',
                            'high' => 'High',
                            'medium' => 'Medium',
                            'low' => 'Low',
                        ])
                        ->visible(fn ($get) => $get('ai_generation_enabled')),
                ]),

            Section::make('AI Provider')
                ->icon('heroicon-o-cpu-chip')
                ->schema([
                    Select::make('ai_provider')
                        ->label('Provider')
                        ->live()
                        ->options(AiProvider::options()),

                    Select::make('ai_model')
                        ->label('Model')
                        ->options(fn ($get) => AiProvider::tryFrom($get('ai_provider'))?->models() ?? []),
                ]),

            Section::make('Group Time Controls')
                ->icon('heroicon-o-clock')
                ->schema([
                    TextInput::make('time_for_new_group')
                        ->label('Window of time within put similar errors in same group before creating new one (minutes)')
                        ->numeric()
                        ->minValue(1)
                        ->step(1),

                    TextInput::make('time_to_generate_ai_solution')
                        ->label('How much time to wait since first error to generate AI solution (minutes)')
                        ->numeric()
                        ->minValue(1)
                        ->step(1),
                ]),

            Section::make('AI Instructions')
                ->icon('heroicon-o-document-text')
                ->columnSpanFull()
                ->schema([
                    Textarea::make('ai_instructions')
                        ->label('System Instructions')
                        ->rows(6)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
