<?php

namespace App\Filament\Resources\Attachments\Tables;

use App\Models\Attachment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class AttachmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('original_name')
                    ->label(__('File name'))
                    ->searchable()
                    ->limit(40),
                TextColumn::make('post.title')
                    ->label(__('Post'))
                    ->limit(40)
                    ->description(fn (Attachment $record) => $record->post?->trashed() ? __('Deleted post') : null),
                TextColumn::make('size')
                    ->label(__('Size'))
                    ->formatStateUsing(fn (int $state) => Number::fileSize($state))
                    ->sortable(),
                TextColumn::make('download_count')
                    ->label(__('Downloads'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->dateTime('Y.m.d H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('deleted_post')
                    ->label(__('Files of deleted posts'))
                    ->query(fn (Builder $query) => $query->whereHas('post', fn ($q) => $q->onlyTrashed())),
            ])
            ->recordActions([
                Action::make('download')
                    ->label(__('Download'))
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(fn (Attachment $record) => $record->download()),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
