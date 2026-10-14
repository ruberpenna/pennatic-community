<?php

namespace App\Filament\Resources\BoardFolders\RelationManagers;

use App\Filament\Resources\BoardFolders\BoardFolderResource;
use App\Models\Board;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
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
                Toggle::make('is_private')
                    ->label(__('Private (admins only)'))
                    ->helperText(__('Members and guests cannot see this board at all. Its menu icon is a lock.'))
                    ->live(),
                ToggleButtons::make('icon')
                    ->label(__('Menu icon'))
                    ->helperText(__('Shown next to the board name in the menu, and alone when the menu is collapsed.'))
                    ->options(collect(Board::ICONS)->map(fn (string $label) => __($label))->all())
                    ->icons(collect(Board::ICONS)->mapWithKeys(fn ($label, string $icon) => [$icon => "heroicon-o-{$icon}"])->all())
                    ->default(Board::DEFAULT_ICON)
                    ->in(array_keys(Board::ICONS))
                    // Private boards always show the lock; the chosen icon comes back if the board is made public again.
                    ->hidden(fn (Get $get) => (bool) $get('is_private'))
                    ->required(fn (Get $get) => ! $get('is_private'))
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
                    ->description(fn (Board $record) => $record->is_private ? __('Private (admins only)') : null)
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
                    ->disabled(fn (Board $record) => $record->posts()->exists())
                    ->tooltip(fn (Board $record) => $record->posts()->exists() ? __('Boards with posts cannot be deleted.') : null)
                    ->modalDescription(fn (Board $record) => BoardFolderResource::deletedPostsNote($record->posts()->onlyTrashed()->count())),
            ]);
    }
}
