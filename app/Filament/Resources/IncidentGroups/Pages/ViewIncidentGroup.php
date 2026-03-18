<?php

namespace App\Filament\Resources\IncidentGroups\Pages;

use App\Filament\Resources\IncidentGroups\IncidentGroupResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;

class ViewIncidentGroup extends ViewRecord
{
    protected static string $resource = IncidentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            
            Action::make('assignUser')
                ->label('Assign to user')
                ->form([
                    Select::make('assigned_user_id')
                        ->relationship('assignedUser', 'name')
                        ->preload()
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'assigned_user_id' => $data['assigned_user_id'],
                        'status' => 'assigned',
                    ]);
                })
                ->icon('heroicon-o-user'),

            Action::make('markResolved')
                ->label('Mark as Resolved')
                ->color('success')
                ->action(function () {
                    $this->record->update([
                        'status' => 'resolved',
                    ]);
                })
                ->icon('heroicon-o-check-circle'),

            Action::make('markClosed')
                ->label('Close Incident')
                ->color('gray')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'closed',
                    ]);
                }),
            ];
    }
}
