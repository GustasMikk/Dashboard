<?php

namespace App\Filament\Resources\IncidentGroups\Pages;

use App\Filament\Resources\IncidentGroups\IncidentGroupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIncidentGroup extends ViewRecord
{
    protected static string $resource = IncidentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
