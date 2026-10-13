<?php

namespace App\Filament\Resources\BoardFolders\Pages;

use App\Filament\Resources\BoardFolders\BoardFolderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBoardFolder extends CreateRecord
{
    protected static string $resource = BoardFolderResource::class;
}
