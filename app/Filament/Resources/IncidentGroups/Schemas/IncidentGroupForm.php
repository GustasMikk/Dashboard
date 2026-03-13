<?php

namespace App\Filament\Resources\IncidentGroups\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IncidentGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('mitre_id')
                    ->required(),
                TextInput::make('total_occurrences')
                    ->required()
                    ->numeric()
                    ->default(1),
                DateTimePicker::make('last_occurrence_at')
                    ->required(),
                TextInput::make('highest_severity')
                    ->required(),
                Select::make('status')
                    ->options(['open' => 'Open', 'assigned' => 'Assigned', 'resolved' => 'Resolved', 'closed' => 'Closed'])
                    ->default('open')
                    ->required(),
                Select::make('assigned_user_id')
                    ->relationship('assignedUser', 'name'),
                DateTimePicker::make('opened_at'),
                DateTimePicker::make('resolved_at'),
                DateTimePicker::make('closed_at'),
                Textarea::make('ai_description')
                    ->columnSpanFull(),
                Textarea::make('ai_recommendations')
                    ->columnSpanFull(),
                Textarea::make('ai_root_cause')
                    ->columnSpanFull(),
            ]);
    }
}
