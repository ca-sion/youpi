<?php

namespace App\Filament\Resources\EventLogisticResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\EventLogisticResource;

class ListEventLogistics extends ListRecords
{
    protected static string $resource = EventLogisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
