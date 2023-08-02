<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Resource;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class ListResources extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function render()
    {
        return view('livewire.list-resources');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Resource::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Nom'),
                TextColumn::make('date')
                    ->label('Date')
                    ->date(config('youpi.date_format')),
                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => data_get(config('youpi.resource_types'), $state)),
                TextColumn::make('athleteGroup.name')
                    ->label('Groupe'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(config('youpi.resource_types')),
                SelectFilter::make('athleteGroup')
                    ->label('Groupe')
                    ->relationship('athleteGroup', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('open')
                    ->label('Ouvrir')
                    ->url(fn (Resource $record): string|null => $record->attachment)
                    ->hidden(fn (Resource $record): bool => empty($record->attachment))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // ...
            ])->recordUrl(
                fn (Model $record): string => route('resources.view', ['resource' => $record]),
            );
    }
}
