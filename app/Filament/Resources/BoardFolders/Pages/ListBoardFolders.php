<?php

namespace App\Filament\Resources\BoardFolders\Pages;

use App\Filament\Resources\BoardFolders\BoardFolderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBoardFolders extends ListRecords
{
    protected static string $resource = BoardFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
