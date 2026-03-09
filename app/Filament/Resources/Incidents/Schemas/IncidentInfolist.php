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
                ->icon('heroicon-o-shield-exclamation')
                ->columns(3)
                ->schema([
                    TextEntry::make('title')
                        ->columnSpan(3),
                    TextEntry::make('wazuh_incident_id')
                        ->label('Wazuh ID')
                        ->copyable()
                        ->badge()
                        ->color('gray'),
                    TextEntry::make('mitre_id')
                        ->label('Mitre ID')
                        ->copyable()
                        ->badge()
                        ->color('gray'),
                    TextEntry::make('rule'),
                    TextEntry::make('severity')
                        ->badge()
                        ->color(fn ($state) => match($state) {
                            'critical' => 'danger',
                            'high'     => 'warning',
                            'medium'   => 'info',
                            default    => 'gray',
                        }),
                    
                    TextEntry::make('host')
                        ->columnSpan(2),
                    
                ]),

                Section::make('Occurences')
                ->icon('heroicon-o-wrench')
                ->schema([
                    TextEntry::make('first_occurrence_at')
                        ->label('First Occurrence')
                        ->dateTime()
                        ->placeholder('-')
                        ->icon('heroicon-o-folder-open'),
                    TextEntry::make('occurrences_count')
                        ->label('Occurrences')
                        ->numeric()
                        ->icon('heroicon-o-arrow-path'),
                    TextEntry::make('last_occurrence_at')
                        ->label('Last Occurrence')
                        ->dateTime()
                        ->icon('heroicon-o-exclamation-circle'),   
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
