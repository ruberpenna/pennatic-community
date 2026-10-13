<?php

namespace App\Filament\Resources\BoardFolders\RelationManagers;

use App\Models\Board;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

class BoardsRelationManager extends RelationManager
{
    protected static string $relationship = 'boards';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Boards');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Board name'))
                    ->required()
                    ->maxLength(50)
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule) => $rule->where('board_folder_id', $this->getOwnerRecord()->getKey()),
                    ),
                ToggleButtons::make('icon')
                    ->label(__('Menu icon'))
                    ->helperText(__('Shown next to the board name in the menu, and alone when the menu is collapsed.'))
                    ->options(collect(Board::ICONS)->map(fn (string $label) => __($label))->all())
                    ->icons(collect(Board::ICONS)->mapWithKeys(fn ($label, string $icon) => [$icon => "heroicon-o-{$icon}"])->all())
                    ->default(Board::DEFAULT_ICON)
                    ->in(array_keys(Board::ICONS))
                    ->required()
                    ->inline(),
                TagsInput::make('subjects')
                    ->label(__('Subjects'))
                    ->helperText(__('Post prefixes writers can choose. Leave empty to not use them.'))
                    ->nestedRecursiveRules(['string', 'max:30'])
                    ->reorderable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modelLabel(__('Board'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                IconColumn::make('icon')
                    ->label('')
                    ->icon(fn (Board $record) => 'heroicon-o-'.$record->menuIcon())
                    ->color('gray'),
                TextColumn::make('name')
                    ->label(__('Board name'))
                    ->url(fn (Board $record) => route('boards.show', $record))
                    ->openUrlInNewTab(),
                TextColumn::make('subjects')
                    ->label(__('Subjects'))
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('posts_count')
                    ->label(__('Posts'))
                    ->counts('posts'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn (Board $record) => $record->posts()->withTrashed()->exists())
                    ->tooltip(fn (Board $record) => $record->posts()->withTrashed()->exists() ? __('Boards with posts cannot be deleted.') : null),
            ]);
    }
}
