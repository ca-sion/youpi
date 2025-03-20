<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Resource;
use Filament\Tables\Table;
use Livewire\Attributes\Url;
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
    use InteractsWithForms;
    use InteractsWithTable;

    #[Url]
    public bool $isTableReordering = false;

    /**
     * @var array<string, mixed> | null
     */
    #[Url]
    public ?array $tableFilters = null;

    #[Url]
    public ?string $tableGrouping = null;

    #[Url]
    public ?string $tableGroupingDirection = null;

    /**
     * @var ?string
     */
    #[Url]
    public $tableSearch = '';

    #[Url]
    public ?string $tableSortColumn = null;

    #[Url]
    public ?string $tableSortDirection = null;

    public function render()
    {
        return view('livewire.list-resources');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Resource::query())
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('computedName')
                    ->label('Nom')
                    ->searchable(['name', 'date']),
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
                    ->url(fn (Resource $record): ?string => $record->attachment)
                    ->hidden(fn (Resource $record): bool => empty($record->attachment))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // ...
            ])
            ->recordUrl(
                fn (Model $record): string => route('resources.view', ['resource' => $record]),
            )
            ->persistFiltersInSession();
    }
}
