<?php

namespace App\Filament\Resources\BoardFolders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BoardFolderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Folder name'))
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
            ]);
    }
}
