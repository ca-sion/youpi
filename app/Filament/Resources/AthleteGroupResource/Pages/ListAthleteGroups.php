<?php

namespace App\Filament\Resources\AthleteGroupResource\Pages;

use App\Filament\Resources\AthleteGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
