<?php

namespace App\Filament\Resources\IncidentGroups\Pages;

use App\Filament\Resources\IncidentGroups\IncidentGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIncidentGroups extends ListRecords
{
    protected static string $resource = IncidentGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
