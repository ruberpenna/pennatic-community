<?php

namespace App\Filament\Resources\Notices\Pages;

use App\Filament\Resources\Notices\NoticeResource;
use App\Support\PostContent;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditNotice extends EditRecord
{
    protected static string $resource = NoticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label(__('View on site'))
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn () => route('notices.show', $this->record))
                ->openUrlInNewTab()
                ->hidden(fn () => $this->record->trashed()),
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['content'] = PostContent::clean($data['content']);

        return $data;
    }
}
