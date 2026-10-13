<?php

namespace App\Filament\Resources\Notices\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NoticeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(100),
                // Saved as HTML and sanitized with the "post" purifier profile (see the create/edit pages).
                RichEditor::make('content')
                    ->label(__('Content'))
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'link'],
                        ['h2', 'h3', 'blockquote', 'codeBlock'],
                        ['bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ]),
            ]);
    }
}
