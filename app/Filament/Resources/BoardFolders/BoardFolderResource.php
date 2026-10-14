<?php

namespace App\Filament\Resources\BoardFolders;

use App\Filament\Resources\BoardFolders\Pages\CreateBoardFolder;
use App\Filament\Resources\BoardFolders\Pages\EditBoardFolder;
use App\Filament\Resources\BoardFolders\Pages\ListBoardFolders;
use App\Filament\Resources\BoardFolders\RelationManagers\BoardsRelationManager;
use App\Filament\Resources\BoardFolders\Schemas\BoardFolderForm;
use App\Filament\Resources\BoardFolders\Tables\BoardFoldersTable;
use App\Models\BoardFolder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BoardFolderResource extends Resource
{
    protected static ?string $model = BoardFolder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('Board folder');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Boards');
    }

    public static function form(Schema $schema): Schema
    {
        return BoardFolderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BoardFoldersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BoardsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBoardFolders::route('/'),
            'create' => CreateBoardFolder::route('/create'),
            'edit' => EditBoardFolder::route('/{record}/edit'),
        ];
    }

    /**
     * A folder can only be deleted once its boards have no visible posts (deleted ones are purged with it).
     */
    public static function hasPosts(BoardFolder $folder): bool
    {
        return $folder->boards()->whereHas('posts')->exists();
    }

    public static function deletedPostsNote(int $count): ?string
    {
        return $count > 0 ? __(':count deleted post(s) will be permanently removed too.', ['count' => $count]) : null;
    }
}
