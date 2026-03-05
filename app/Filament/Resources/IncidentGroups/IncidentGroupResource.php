<?php

namespace App\Filament\Resources\IncidentGroups;

use App\Filament\Resources\IncidentGroups\Pages\CreateIncidentGroup;
use App\Filament\Resources\IncidentGroups\Pages\EditIncidentGroup;
use App\Filament\Resources\IncidentGroups\Pages\ListIncidentGroups;
use App\Filament\Resources\IncidentGroups\Pages\ViewIncidentGroup;
use App\Filament\Resources\IncidentGroups\Schemas\IncidentGroupForm;
use App\Filament\Resources\IncidentGroups\Schemas\IncidentGroupInfolist;
use App\Filament\Resources\IncidentGroups\Tables\IncidentGroupsTable;
use App\Models\IncidentGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IncidentGroupResource extends Resource
{
    protected static ?string $model = IncidentGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return IncidentGroupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IncidentGroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncidentGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncidentGroups::route('/'),
            'create' => CreateIncidentGroup::route('/create'),
            'view' => ViewIncidentGroup::route('/{record}'),
            'edit' => EditIncidentGroup::route('/{record}/edit'),
        ];
    }
}
