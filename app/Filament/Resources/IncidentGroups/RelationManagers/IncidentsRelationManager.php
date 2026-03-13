<?php

namespace App\Filament\Resources\IncidentGroups\RelationManagers;

use App\Filament\Resources\Incidents\IncidentResource;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IncidentsRelationManager extends RelationManager
{
    protected static string $relationship = 'incidents';

    protected static ?string $navigationLabel = 'Incidents';

    protected static ?string $relatedResource = IncidentResource::class;

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('severity')
                    ->sortable()
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'gray' => 'critical',
                    ]),

                TextColumn::make('rule')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('host')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('first_occurrence_at')
                    ->label('First Occurrence')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('last_occurrence_at')
                    ->label('Last Occurrence')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('occurrences_count')
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('severity')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),

                SelectFilter::make('rule')
                    ->options(fn () => \App\Models\Incident::query()
                        ->distinct()
                        ->pluck('rule', 'rule')
                        ->toArray()
                    ),

                SelectFilter::make('host')
                    ->options(fn () => \App\Models\Incident::query()
                        ->distinct()
                        ->pluck('host', 'host')
                        ->toArray()
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
