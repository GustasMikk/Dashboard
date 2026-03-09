<?php

namespace App\Filament\Resources\Incidents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IncidentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('wazuh_incident_id')
                    ->required(),
                TextInput::make('mitre_id')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('severity')
                    ->required(),
                TextInput::make('incident_group_id'),
                TextInput::make('rule')
                    ->required(),
                TextInput::make('host')
                    ->required(),
                TextInput::make('raw_payload'),
                DateTimePicker::make('first_occurrence_at')
                    ->required(),
                DateTimePicker::make('last_occurrence_at')
                    ->required(),
                TextInput::make('occurrences_count')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
