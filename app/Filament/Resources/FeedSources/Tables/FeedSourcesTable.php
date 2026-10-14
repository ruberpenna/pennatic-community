<?php

namespace App\Filament\Resources\FeedSources\Tables;

use App\Models\FeedSource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class FeedSourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->description(fn (FeedSource $record) => $record->url),
                TextColumn::make('board.name')
                    ->label(__('Board')),
                TextColumn::make('subject')
                    ->label(__('Subject'))
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('items_count')
                    ->label(__('Posted'))
                    ->counts('items'),
                TextColumn::make('last_fetched_at')
                    ->label(__('Last fetched'))
                    ->dateTime('Y.m.d H:i')
                    ->placeholder('-')
                    ->description(fn (FeedSource $record) => $record->last_error ? __('Error: :message', ['message' => $record->last_error]) : null),
                ToggleColumn::make('is_active')
                    ->label(__('Active')),
            ])
            ->recordActions([
                Action::make('fetchNow')
                    ->label(__('Fetch now'))
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->action(function (FeedSource $record) {
                        Artisan::call('news:fetch', ['--source' => $record->id]);
                        $record->refresh();

                        Notification::make()
                            ->title($record->last_error ? __('Fetching failed') : __('Fetched'))
                            ->body(trim(Artisan::output()))
                            ->{$record->last_error ? 'danger' : 'success'}()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription(__('Posts already created stay on the board.')),
            ]);
    }
}
