<?php

namespace App\Filament\Resources\Notices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class NoticesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->limit(60),
                TextColumn::make('user.name')
                    ->label(__('Author'))
                    ->placeholder(__('Unknown')),
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
