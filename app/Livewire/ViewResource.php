<?php

namespace App\Livewire;

use Filament\Forms\Get;
use Livewire\Component;
use App\Models\Resource;
use Filament\Infolists\Infolist;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Concerns\InteractsWithInfolists;

class ViewResource extends Component implements HasForms, HasInfolists
{
    use InteractsWithInfolists;
    use InteractsWithForms;

    public Resource $resource;

    public function render()
    {
        return view('livewire.view-resource');
    }

    public function resourceInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->resource)
            ->schema([
                TextEntry::make('name')
                    ->label('Nom'),
                TextEntry::make('date')
                    ->date(config('youpi.date_format'))
                    ->hidden(fn (Resource $record): bool => empty($record->date)),
                TextEntry::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => data_get(config('youpi.resource_types'), $state)),
                TextEntry::make('athleteGroup.name')
                    ->label('Groupe')
                    ->hidden(fn (Resource $record): bool => empty($record->athleteGroup)),
                TextEntry::make('url')
                    ->label('URL')
                    ->hidden(fn (Resource $record): bool => empty($record->url))
                    ->url(fn (Resource $record): string => $record->url)
                    ->openUrlInNewTab(),
                TextEntry::make('text')
                    ->label('Texte')
                    ->hidden(fn (Resource $record): bool => empty($record->text))
                    ->html(),
                TextEntry::make('attachment')
                    ->label('Fichier ou URL')
                    ->hidden(fn (Resource $record): bool => empty($record->attachment))
                    ->url(fn (Resource $record): string|null => $record->attachment, true)
            ]);
    }
}
