<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\DocumentResource;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save_top')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action(fn () => $this->save())
                ->color('success'),
            Actions\Action::make('show document')
                ->label('Afficher')
                ->url(fn (): string => route('documents.show', ['document' => $this->record]))
                ->openUrlInNewTab()
                ->icon('heroicon-o-arrow-top-right-on-square'),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
