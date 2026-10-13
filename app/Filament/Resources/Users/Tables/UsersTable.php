<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->extraImgAttributes(['alt' => '']),
                TextColumn::make('username')
                    ->label(__('Username'))
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('Nickname'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('role')
                    ->label(__('Role'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __("role.{$state}"))
                    ->color(fn (string $state) => $state === 'admin' ? 'warning' : 'gray'),
                TextColumn::make('level')
                    ->label(__('Level'))
                    ->sortable(),
                TextColumn::make('posts_count')
                    ->label(__('Posts'))
                    ->counts('posts')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Joined'))
                    ->date('Y.m.d')
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label(__('Withdrawn'))
                    ->date('Y.m.d')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label(__('Role'))
                    ->options(['member' => __('role.member'), 'admin' => __('role.admin')]),
                TrashedFilter::make()
                    ->label(__('Withdrawn members')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label(__('Withdraw'))
                    ->modalHeading(__('Withdraw this member?'))
                    ->modalDescription(__('The member can no longer log in. Their posts stay. You can restore them later.'))
                    ->hidden(fn (User $record) => $record->trashed() || $record->is(auth()->user())),
                RestoreAction::make(),
                ForceDeleteAction::make()
                    ->modalDescription(__('This permanently removes the member. Their posts stay without an author.')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Withdraw selected'))
                        ->using(fn (Collection $records) => $records
                            ->reject(fn (User $user) => $user->is(auth()->user()))
                            ->each->delete()),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
