<?php

namespace App\Filament\Resources\FeedSources\Schemas;

use App\Models\Board;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FeedSourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->helperText(__('Shown in the link under each post, e.g. "SBS headlines".'))
                    ->required()
                    ->maxLength(50),
                TextInput::make('url')
                    ->label(__('RSS/Atom address'))
                    ->url()
                    ->rule('starts_with:http://,https://')
                    ->required()
                    ->maxLength(500),
                Select::make('board_id')
                    ->label(__('Board to post to'))
                    ->options(fn () => Board::with('folder')->get()->mapWithKeys(fn (Board $board) => [$board->id => "{$board->folder->name} / {$board->name}"]))
                    ->required(),
                TextInput::make('max_items')
                    ->label(__('Items per run'))
                    ->helperText(__('Only the newest items, up to this number, are posted each day.'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(50)
                    ->default(15)
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),
            ]);
    }
}
