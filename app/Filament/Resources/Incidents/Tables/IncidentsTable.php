<?php

namespace App\Filament\Resources\Incidents\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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

                TextColumn::make('status')
                    ->sortable()
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'assigned',
                        'success' => 'resolved',
                        'gray' => 'closed',
                    ]),

                TextColumn::make('assignedUser.name')
                    ->label('Assigned User')
                    ->sortable(),

                TextColumn::make('opened_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('last_occurrence_at')
                    ->label('Last Occurrence')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('occurrences_count')
                    ->sortable(),

                TextColumn::make('incident_type')
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
                    ->options(fn () =>
                        \App\Models\Incident::query()
                            ->distinct()
                            ->pluck('rule', 'rule')
                            ->toArray()
                    ),

                SelectFilter::make('host')
                    ->options(fn () =>
                        \App\Models\Incident::query()
                            ->distinct()
                            ->pluck('host', 'host')
                            ->toArray()
                    ),

                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'assigned' => 'Assigned',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),

                SelectFilter::make('assigned_user_id')
                    ->relationship('assignedUser', 'name')
                    ->label('Assigned User'),

                // SelectFilter::make('incident_type')
                //     ->options(fn () =>
                //         \App\Models\Incident::query()
                //             ->distinct()
                //             ->pluck('incident_type', 'incident_type')
                //             ->toArray()
                //     ),

                Filter::make('opened_at')
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

                BulkAction::make('assignUser')
                    ->label('Assign to user')
                    ->form([
                        Select::make('assigned_user_id')
                            ->relationship('assignedUser', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function ($records, array $data) {
                        $records->toQuery()->update([
                            'assigned_user_id' => $data['assigned_user_id'],
                            'status' => 'assigned',
                        ]);
                    })
                    ->icon('heroicon-o-user'),
                    

                BulkAction::make('markResolved')
                    ->label('Mark as resolved')
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->action(fn ($records) =>
                        $records->toQuery()->update([
                            'status' => 'resolved',
                            'resolved_at' => now(),
                        ])
                    ),

                BulkAction::make('closeIncident')
                    ->label('Close incident')
                    ->requiresConfirmation()
                    ->color('gray')
                    ->icon('heroicon-o-x-mark')
                    ->action(fn ($records) =>
                        $records->toQuery()->update([
                            'status' => 'closed',
                            'closed_at' => now(),
                        ])
                    ),
            ]);
    }
}
