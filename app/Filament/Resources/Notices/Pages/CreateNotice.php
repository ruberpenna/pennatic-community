<?php

namespace App\Filament\Resources\Notices\Pages;

use App\Filament\Resources\Notices\NoticeResource;
use App\Models\Notice;
use App\Support\PostContent;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateNotice extends CreateRecord
{
    protected static string $resource = NoticeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['content'] = PostContent::clean($data['content']);

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $notice = new Notice($data);
        $notice->user()->associate(auth()->user());
        $notice->save();

        return $notice;
    }
}
