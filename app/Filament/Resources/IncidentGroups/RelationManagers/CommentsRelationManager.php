<?php

namespace App\Filament\Resources\IncidentGroups\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';
    protected static ?string $navigationLabel = 'Comments';

    protected static bool $isLazy = false;

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('comment_text')
                    ->required()
                    ->label('Add comment'),

                Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment')
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-o-user-circle')
                    ->grow(false)
                    ->width('150px'),
                TextColumn::make('comment_text')
                    ->label('Comment')
                    ->wrap()
                    ->grow(true),
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->since()
                    ->tooltip(fn ($state) => $state?->format('M j, Y H:i'))
                    ->grow(false)
                    ->width('150px')
                    ->color('gray'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
