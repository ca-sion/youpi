<?php

namespace App\Livewire;

use App\Models\AthleteGroup;
use Filament\Forms\Get;
use Livewire\Component;
use App\Models\Resource;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Redirect;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class CreateResource extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public Resource $resource;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.create-resource');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('type')
                    ->label('Type')
                    ->required()
                    ->options(config('youpi.resource_types'))
                    ->default('week_plan')
                    ->autofocus()
                    ->live(),
                Select::make('athlete_group_id')
                    ->label('Groupe d\'athlètes')
                    ->helperText('Groupe pour lequel la ressource est destinée')
                    ->relationship('athleteGroup', 'name')
                    ->hidden(fn (Get $get): bool => in_array($get('type'), ['documentation', null]))
                    ->required(fn (Get $get): bool => in_array($get('type'), ['week_plan', 'day_plan', 'session', null]))
                    ->live(),
                DatePicker::make('date')
                    ->label('Date')
                    ->helperText('Date à laquelle le plan ou la séance est prévue ou débute')
                    ->minDate(now()->subYear())
                    ->hidden(fn (Get $get): bool => in_array($get('type'), ['documentation', null]))
                    ->required(fn (Get $get): bool => ! in_array($get('type'), ['sessions', 'exercises', 'documentation', null]))
                    ->live(),
                DatePicker::make('date_end')
                    ->label('Date de fin')
                    ->helperText('Date à laquelle se termine le plan')
                    ->minDate(now()->subYear())
                    ->hidden(fn (Get $get): bool => ! in_array($get('type'), ['year_plan', 'macro_plan', 'micro_plan']))
                    ->required(fn (Get $get): bool => in_array($get('type'), ['year_plan', 'macro_plan', 'micro_plan']))
                    ->live(),
                SpatieMediaLibraryFileUpload::make('media')
                    ->label('Fichier')
                    ->collection('resources')
                    ->required(fn (Get $get): bool => ! ($get('text') || $get('url')))
                    ->hidden(fn (Get $get): bool => ($get('text') || $get('url')))
                    ->live()
                    ->columnSpanFull(),
                RichEditor::make('text')
                    ->label('Texte')
                    ->required(fn (Get $get): bool => ! ($get('media') || $get('url')))
                    ->hidden(fn (Get $get): bool => ($get('media') || $get('url')))
                    ->live()
                    ->columnSpanFull(),
                TextInput::make('url')
                    ->label('URL')
                    ->required(fn (Get $get): bool => ! ($get('media') || $get('text')))
                    ->hidden(fn (Get $get): bool => ($get('media') || $get('text')))
                    ->url()
                    ->live()
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->label('Nom')
                    ->required(fn (Get $get): bool => in_array($get('type'), ['sessions', 'exercises', 'documentation', null]))
                    ->maxLength(255)
                    ->live(),
                Placeholder::make('computedName')
                    ->label('Nom affiché')
                    ->live()
                    ->content(function (Get $get): string {
                        $whithWeek = true;
                        $whithAthleteGroup = true;
                        $whithName = true;

                        $cDate = Carbon::parse($get('date'));
                        $cDateEnd = Carbon::parse($get('date_end'));
                        $year = $cDate->year;
                        $yearEnd = $cDateEnd->year;
                        $week = $cDate->weekOfYear;
                        $day = $cDate->day;
                        $shortDayName = $cDate->locale('fr')->shortDayName;
                        $dayName = $cDate->locale('fr')->dayName;
                        $type = $get('type');
                        $group = $get('athlete_group_id') ? AthleteGroup::find($get('athlete_group_id'))->name : null;
                        $name = $get('name');

                        $hasYear = in_array($type, ['year_plan', 'macro_plan', 'micro_plan']);
                        $hasWeek = $whithWeek && in_array($type, ['week_plan', 'day_plan', 'session']);
                        $hasDay = $type == 'session';
                        $hasGroup = ! empty($group) && $whithAthleteGroup;

                        $value = '';
                        if ($hasYear) {
                            $value .= ($year == $yearEnd) ? $year : $year.'-'.$yearEnd;
                        }
                        $value .= ($hasWeek && $hasYear ? ' · ' : null);
                        if ($hasWeek) {
                            $value .= 'Semaine '.$week;
                        }
                        $value .= ($hasWeek && $hasDay ? ' · ' : null);
                        if ($hasDay) {
                            $value .= str($dayName)->ucfirst().' '.$day;
                        }
                        $value .= (($hasGroup && $hasYear) || ($hasGroup && $hasDay) || ($hasGroup && $hasWeek)  ? ' · ' : null);
                        if ($hasGroup) {
                            $value .= $group;
                        }
                        $value .= (($hasYear || $hasWeek || $hasDay || $hasGroup) && $name  ? ' · ' : null);
                        if (! empty($name) && $whithName) {
                            $value .= $name;
                        }

                        return $value;
                    }),
                TextInput::make('author')
                    ->label('Auteur')
                    ->helperText('Auteur de la ressource')
                    ->maxLength(255),

                // TextInput::make('description')
                //     ->label('Description ou indication'),
            ])
            ->columns()
            ->statePath('data')
            ->model(Resource::class);
    }

    public function create(): mixed
    {
        $resource = Resource::create($this->form->getState());

        // Save the relationships from the form to the post after it is created.
        $this->form->model($resource)->saveRelationships();

        Notification::make()
        ->title('Ressource créée')
        ->success()
        ->send();

        return $this->redirect(ListResources::class);
    }
}
