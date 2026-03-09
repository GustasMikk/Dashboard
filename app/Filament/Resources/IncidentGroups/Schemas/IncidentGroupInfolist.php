<?php

namespace App\Filament\Resources\IncidentGroups\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IncidentGroupInfolist
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
                        TextEntry::make('mitre_id')
                            ->label('Mitre ID')
                            ->copyable()
                            ->badge()
                            ->color('gray'),
                    TextEntry::make('highest_severity')
                        ->label('Highest Severity')
                        ->badge()
                        ->color(fn ($state) => match($state) {
                            'critical' => 'danger',
                            'high'     => 'warning',
                            'medium'   => 'info',
                            default    => 'gray',
                        }),
                    
                    TextEntry::make('host'),
                ]),

                Section::make('Assignment')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                    TextEntry::make('assignedUser.name')
                        ->label('Assigned User')
                        ->placeholder('Unassigned')
                        ->icon('heroicon-o-user'),
                    TextEntry::make('status')
                        ->badge(),
                ]),

                Section::make('Timeline')
                    ->icon('heroicon-o-clock')
                    ->columns(4)
                    ->columnSpanFull()
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
                    TextEntry::make('total_occurrences')
                        ->label('Total Occurrences')
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
            ]);
    }
}
