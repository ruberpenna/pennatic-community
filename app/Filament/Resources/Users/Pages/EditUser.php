<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('Withdraw'))
                ->hidden(fn () => $this->record->is(auth()->user())),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // The role field is disabled for yourself, so it is not submitted; keep the current role.
        if ($this->record->is(auth()->user())) {
            unset($data['role']);
        }

        return $data;
    }

    /**
     * role, level and password_must_change are not mass assignable on the site,
     * so the admin form (whose fields are fixed) sets them explicitly.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->forceFill($data)->save();

        return $record;
    }
}
