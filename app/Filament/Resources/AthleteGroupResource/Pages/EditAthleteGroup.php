<?php

namespace App\Filament\Resources\AthleteGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\AthleteGroupResource;

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
