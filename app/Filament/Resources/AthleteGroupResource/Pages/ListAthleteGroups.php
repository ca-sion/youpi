<?php

namespace App\Filament\Resources\AthleteGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\AthleteGroupResource;

class ListAthleteGroups extends ListRecords
{
    protected static string $resource = AthleteGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
