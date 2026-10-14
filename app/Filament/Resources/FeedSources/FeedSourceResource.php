<?php

namespace App\Filament\Resources\FeedSources;

use App\Filament\Resources\FeedSources\Pages\CreateFeedSource;
use App\Filament\Resources\FeedSources\Pages\EditFeedSource;
use App\Filament\Resources\FeedSources\Pages\ListFeedSources;
use App\Filament\Resources\FeedSources\Schemas\FeedSourceForm;
use App\Filament\Resources\FeedSources\Tables\FeedSourcesTable;
use App\Models\FeedSource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeedSourceResource extends Resource
{
    protected static ?string $model = FeedSource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRss;

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('News source');
    }

    public static function getPluralModelLabel(): string
    {
        return __('News sources');
    }

    public static function form(Schema $schema): Schema
    {
        return FeedSourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedSourcesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeedSources::route('/'),
            'create' => CreateFeedSource::route('/create'),
            'edit' => EditFeedSource::route('/{record}/edit'),
        ];
    }
}
