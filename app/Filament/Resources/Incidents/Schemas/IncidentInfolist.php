<?php

namespace App\Filament\Resources\Incidents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IncidentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Incident Overview')
                ->description('Core identification and classification details')
                ->icon('heroicon-o-shield-exclamation')
                ->columns(3)
                ->schema([
                    TextEntry::make('wazuh_incident_id')
                        ->label('Wazuh ID')
                        ->copyable()
                        ->badge()
                        ->color('gray'),
                    TextEntry::make('title')
                        ->columnSpan(2),
                    TextEntry::make('severity')
                        ->badge()
                        ->color(fn ($state) => match($state) {
                            'critical' => 'danger',
                            'high'     => 'warning',
                            'medium'   => 'info',
                            default    => 'gray',
                        }),
                    TextEntry::make('rule'),
                    TextEntry::make('host'),
                    TextEntry::make('status')
                        ->badge(),
                ]),

                Section::make('Assignment')
                ->icon('heroicon-o-user-circle')
                ->columns(2)
                ->schema([
                    TextEntry::make('assignedUser.name')
                        ->label('Assigned User')
                        ->placeholder('Unassigned')
                        ->icon('heroicon-o-user'),
                ]),

                Section::make('Timeline')
                ->icon('heroicon-o-clock')
                ->columns(2)
                ->schema([
                    TextEntry::make('opened_at')
                        ->label('Opened')
                        ->dateTime()
                        ->placeholder('-')
                        ->icon('heroicon-o-folder-open'),
                    TextEntry::make('closed_at')
                        ->label('Closed')
                        ->dateTime()
                        ->placeholder('-')
                        ->icon('heroicon-o-x-circle'),
                    TextEntry::make('occurrences_count')
                        ->label('Occurrences')
                        ->numeric()
                        ->icon('heroicon-o-arrow-path'),
                    TextEntry::make('last_occurrence_at')
                        ->label('Last Occurrence')
                        ->dateTime()
                        ->icon('heroicon-o-exclamation-circle'),
                    TextEntry::make('resolved_at')
                        ->label('Resolved')
                        ->dateTime()
                        ->placeholder('-')
                        ->icon('heroicon-o-check-circle'),    
                ]),

                Section::make('AI Analysis')
                    ->description('AI-generated insights for this incident')
                    ->icon('heroicon-o-cpu-chip')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('ai_description')
                            ->label('Description')
                            ->placeholder('No AI description available')
                            ->columnSpanFull()
                            ->prose(),
                        TextEntry::make('ai_root_cause')
                            ->label('Root Cause')
                            ->placeholder('No root cause identified')
                            ->columnSpanFull()
                            ->prose(),
                        TextEntry::make('ai_recommendations')
                            ->label('Recommendations')
                            ->placeholder('No recommendations available')
                            ->columnSpanFull()
                            ->prose(),
                    ]),

                Section::make('Raw JSON')
                    ->icon('heroicon-o-code-bracket')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                    TextEntry::make('raw_payload')
                        ->formatStateUsing(function ($state) {
                        $decoded = is_string($state) ? json_decode($state, true) : $state;
                        $json = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        $escaped = htmlspecialchars($json, ENT_QUOTES, 'UTF-8');

                        return "
                            <pre style='
                                background: #1e1e2e;
                                color: #cd33b4;
                                padding: 1rem;
                                border-radius: 0.5rem;
                                overflow-x: auto;
                                font-size: 0.8rem;
                                line-height: 1.6;
                                font-family: monospace;
                                width: 100%;
                                box-sizing: border-box;
                                white-space: pre-wrap;
                                word-break: break-all;
                            '>{$escaped}</pre>
                        ";
                    })
                    ->html()
                    ->columnSpanFull(),
                ]),
            ]);
    }
}
