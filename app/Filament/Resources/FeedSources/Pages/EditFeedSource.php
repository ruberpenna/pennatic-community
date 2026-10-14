<?php

namespace App\Filament\Resources\FeedSources\Pages;

use App\Filament\Resources\FeedSources\FeedSourceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedSource extends EditRecord
{
    protected static string $resource = FeedSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
