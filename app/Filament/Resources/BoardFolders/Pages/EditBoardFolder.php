<?php

namespace App\Filament\Resources\BoardFolders\Pages;

use App\Filament\Resources\BoardFolders\BoardFolderResource;
use App\Models\Post;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBoardFolder extends EditRecord
{
    protected static string $resource = BoardFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->disabled(fn () => BoardFolderResource::hasPosts($this->record))
                ->tooltip(fn () => BoardFolderResource::hasPosts($this->record) ? __('Boards in this folder still have posts.') : null)
                ->modalDescription(fn () => BoardFolderResource::deletedPostsNote(
                    Post::onlyTrashed()->whereIn('board_id', $this->record->boards()->select('id'))->count(),
                )),
        ];
    }
}
