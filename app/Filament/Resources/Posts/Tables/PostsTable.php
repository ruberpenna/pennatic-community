<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Board;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('board.name')
                    ->label(__('Board')),
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->limit(50)
                    ->url(fn (Post $record) => $record->trashed() ? null : route('posts.show', $record))
                    ->openUrlInNewTab(),
                TextColumn::make('user.name')
                    ->label(__('Author'))
                    ->searchable()
                    ->placeholder(__('Unknown')),
                TextColumn::make('view_count')
                    ->label(__('Views'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('comments_count')
                    ->label(__('Comments'))
                    ->counts('comments')
                    ->sortable(),
                IconColumn::make('is_pinned')
                    ->label(__('Pinned'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime('Y.m.d H:i')
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label(__('Deleted'))
                    ->dateTime('Y.m.d H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('board_id')
                    ->label(__('Board'))
                    ->options(fn () => Board::with('folder')->get()->mapWithKeys(fn (Board $board) => [$board->id => "{$board->folder->name} / {$board->name}"])),
                TernaryFilter::make('is_pinned')
                    ->label(__('Pinned')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('togglePin')
                    ->label(fn (Post $record) => $record->is_pinned ? __('Unpin') : __('Pin'))
                    ->icon(Heroicon::OutlinedBookmark)
                    ->hidden(fn (Post $record) => $record->trashed())
                    ->action(fn (Post $record) => $record->forceFill(['is_pinned' => ! $record->is_pinned])->save()),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
