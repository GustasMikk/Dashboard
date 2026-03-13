<?php

namespace App\Filament\Resources\Incidents\Tables;

use App\Models\Incident;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncidentsTable
{
    public static function configure(Table $table): Table
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

                TextColumn::make('incidentGroup.title')
                    ->label('Incident Group')
                    ->searchable()
                    ->default('None')
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
                    ->options(fn () => Incident::query()
                        ->distinct()
                        ->pluck('rule', 'rule')
                        ->toArray()
                    ),

                SelectFilter::make('host')
                    ->options(fn () => Incident::query()
                        ->distinct()
                        ->pluck('host', 'host')
                        ->toArray()
                    ),

                SelectFilter::make('grouped')
                    ->label('Grouping')
                    ->options([
                        'grouped' => 'Grouped',
                        'ungrouped' => 'Not Grouped',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'grouped') {
                            $query->whereNotNull('incident_group_id');
                        } elseif ($data['value'] === 'ungrouped') {
                            $query->whereNull('incident_group_id');
                        }
                    }),

                SelectFilter::make('incident_group_id')
                    ->label('Incident Group')
                    ->relationship('incidentGroup', 'title'),

                Filter::make('first_occurrence_at')
                    ->form([
                            DatePicker::make('from'),
                            DatePicker::make('until'),
                        ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query) => $query->whereDate('opened_at', '>=', $data['from'])
                            )
                            ->when(
                                $data['until'],
                                fn ($query) => $query->whereDate('opened_at', '<=', $data['until'])
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),

                ActionGroup::make([
                    Action::make('clearLow')
                        ->label('Clear Low Severity')
                        ->icon('heroicon-o-trash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalDescription('This will permanently delete all low severity incidents.')
                        ->action(fn () => Incident::where('severity', 'low')->delete()),

                    Action::make('clearMedium')
                        ->label('Clear Medium Severity')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('This will permanently delete all medium severity incidents.')
                        ->action(fn () => Incident::where('severity', 'medium')->delete()),
                ])
                    ->label('Cleanup')
                    ->icon('heroicon-o-trash'),
            ])
            ->defaultSort('first_occurrence_at', 'desc');
    }
}
