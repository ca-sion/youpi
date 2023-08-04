<?php

namespace App\Livewire;

use Filament\Forms\Get;
use Livewire\Component;
use App\Models\Resource;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Redirect;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
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
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->autofocus()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Type')
                    ->required()
                    ->options(config('youpi.resource_types'))
                    ->default('week_plan')
                    ->live(),
                DatePicker::make('date')
                    ->label('Date')
                    ->helperText('Date à laquelle le plan ou la séance est prévue ou débute')
                    ->minDate(now()->subYear())
                    ->hidden(fn (Get $get): bool => in_array($get('type'), ['documentation', null]))
                    ->required(fn (Get $get): bool => ! in_array($get('type'), ['sessions', 'exercises', 'documentation', null]))
                    ->live(),
                Select::make('athlete_group_id')
                    ->label('Groupe d\'athlètes')
                    ->helperText('Groupe pour lequel la ressource est destinée')
                    ->relationship('athleteGroup', 'name')
                    ->hidden(fn (Get $get): bool => in_array($get('type'), ['documentation', null]))
                    ->required(fn (Get $get): bool => in_array($get('type'), ['week_plan', 'day_plan', 'session', null]))
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
                TextInput::make('author')
                    ->label('Auteur')
                    ->helperText('Auteur de la ressource')
                    ->maxLength(255),
                // Textarea::make('description'),
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
