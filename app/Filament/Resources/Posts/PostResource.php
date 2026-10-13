<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Moderation only: posts are written and edited on the site.
 */
class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModelLabel(): string
    {
        return __('Post');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Posts');
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['board', 'user'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
