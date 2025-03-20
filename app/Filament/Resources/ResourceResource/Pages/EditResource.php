<?php

namespace App\Filament\Resources\ResourceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ResourceResource;

class EditResource extends EditRecord
{
    protected static string $resource = ResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save_top')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action(fn () => $this->save())
                ->color('success'),
            Actions\DeleteAction::make(),
        ];
    }
}
