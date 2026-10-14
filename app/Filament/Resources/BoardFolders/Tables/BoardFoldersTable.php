<?php

namespace App\Filament\Resources\BoardFolders\Tables;

use App\Filament\Resources\BoardFolders\BoardFolderResource;
use App\Models\BoardFolder;
use App\Models\Post;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BoardFoldersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Folder name'))
                    ->searchable(),
                TextColumn::make('boards.name')
                    ->label(__('Boards'))
                    ->badge(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn (BoardFolder $record) => BoardFolderResource::hasPosts($record))
                    ->tooltip(fn (BoardFolder $record) => BoardFolderResource::hasPosts($record) ? __('Boards in this folder still have posts.') : null)
                    ->modalDescription(fn (BoardFolder $record) => BoardFolderResource::deletedPostsNote(
                        Post::onlyTrashed()->whereIn('board_id', $record->boards()->select('id'))->count(),
                    )),
            ]);
    }
}
