<?php

namespace App\Filament\Resources\IncidentGroups\Pages;

use App\Filament\Resources\IncidentGroups\IncidentGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditIncidentGroup extends EditRecord
{
    protected static string $resource = IncidentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
