<?php

namespace App\Filament\Resources\IncidentGroups\Tables;

use App\Models\IncidentGroup;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
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

class IncidentGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('highest_severity')
                    ->sortable()
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'gray' => 'critical',
                    ]),

                TextColumn::make('status')
                    ->sortable()
                    ->colors([
                        'danger' => 'open',
                        'warning' => 'assigned',
                        'success' => 'resolved',
                        'gray' => 'closed',
                    ]),

                TextColumn::make('host')
                    ->label('Host')
                    ->sortable(),

                TextColumn::make('assignedUser.name')
                    ->label('Assigned User')
                    ->sortable(),

                TextColumn::make('total_occurrences')
                    ->label('Total Occurrences')
                    ->sortable(),

                TextColumn::make('opened_at')
                    ->label('Opened')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('last_occurrence_at')
                    ->label('Last Occurrence')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('highest_severity')
                    ->label('Severity')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),

                SelectFilter::make('host')
                    ->options(fn () => IncidentGroup::query()
                        ->distinct()
                        ->pluck('host', 'host')
                        ->toArray()
                    ),

                SelectFilter::make('assigned_user_id')
                    ->relationship('assignedUser', 'name')
                    ->label('Assigned User'),

                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'assigned' => 'Assigned',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),

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
            ->defaultSort('opened_at', 'desc')
            ->toolbarActions([
                ActionGroup::make([
                    Action::make('clearLow')
                        ->label('Clear Low Severity')
                        ->icon('heroicon-o-trash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalDescription('This will permanently delete all low severity incidents.')
                        ->action(fn () => IncidentGroup::where('highest_severity', 'low')->delete()),

                    Action::make('clearMedium')
                        ->label('Clear Medium Severity')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('This will permanently delete all medium severity incidents.')
                        ->action(fn () => IncidentGroup::where('highest_severity', 'low')->delete()),
                ])
                    ->label('Cleanup')
                    ->icon('heroicon-o-trash'),

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
                    ->action(fn ($records) => $records->toQuery()->update([
                        'status' => 'resolved',
                        'resolved_at' => now(),
                    ])
                    ),

                BulkAction::make('closeIncident')
                    ->label('Close incident')
                    ->requiresConfirmation()
                    ->color('gray')
                    ->icon('heroicon-o-x-mark')
                    ->action(fn ($records) => $records->toQuery()->update([
                        'status' => 'closed',
                        'closed_at' => now(),
                    ])
                    ),
            ]);
    }
}
