<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public const ROLES = ['member', 'admin'];

    public const MAX_LEVEL = 10;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->label(__('Username'))
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('name')
                    ->label(__('Nickname'))
                    ->required()
                    ->minLength(2)
                    ->maxLength(30)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('role')
                    ->label(__('Role'))
                    ->options(collect(self::ROLES)->mapWithKeys(fn (string $role) => [$role => __("role.{$role}")]))
                    ->required()
                    ->selectablePlaceholder(false)
                    // An admin cannot take away their own admin role.
                    ->disabled(fn (?User $record) => $record?->is(auth()->user())),
                Select::make('level')
                    ->label(__('Level'))
                    ->options(array_combine(range(1, self::MAX_LEVEL), range(1, self::MAX_LEVEL)))
                    ->required()
                    ->selectablePlaceholder(false),
                Toggle::make('password_must_change')
                    ->label(__('Must change password at next login')),
                Textarea::make('intro')
                    ->label(__('About me'))
                    ->maxLength(300)
                    ->columnSpanFull(),
            ]);
    }
}
