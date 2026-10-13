<?php

namespace App\Filament\Resources\Attachments;

use App\Filament\Resources\Attachments\Pages\ListAttachments;
use App\Filament\Resources\Attachments\Tables\AttachmentsTable;
use App\Models\Attachment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttachmentResource extends Resource
{
    protected static ?string $model = Attachment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperClip;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'original_name';

    public static function getModelLabel(): string
    {
        return __('Attachment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Attachments');
    }

    public static function table(Table $table): Table
    {
        return AttachmentsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttachments::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Include files of deleted posts so they can be cleaned up.
        return parent::getEloquentQuery()->with(['post' => fn ($query) => $query->withTrashed()]);
    }
}
