<?php

namespace App\Filament\Resources\AthleteGroupResource\Pages;

use App\Filament\Resources\AthleteGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAthleteGroup extends EditRecord
{
    protected static string $resource = AthleteGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
