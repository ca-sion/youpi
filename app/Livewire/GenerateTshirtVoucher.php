<?php

namespace App\Livewire;

use App\Models\Voucher;
use Livewire\Component;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;

class GenerateTshirtVoucher extends Component implements HasForms
{
    use InteractsWithForms;

    // Propriétés pour lier les champs du formulaire
    public ?array $data = [];

    // Propriété pour stocker le bon créé après soumission
    public ?Voucher $newVoucher = null;

    public function mount(): void
    {
        // Initialisation des données du formulaire
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Information de l'athlète
                TextInput::make('athlete_name')
                    ->label('Nom et prénom de l\'athlète')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex: Alex Dubois'),

                // Information de l'entraîneur (saisie manuelle car pas d'authentification)
                TextInput::make('coach_name')
                    ->label('Votre nom (Entraîneur/Responsable)')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex: Jean Dupont'),

                // Taille du T-shirt (liste fournie par le club)
                Select::make('tshirt_size')
                    ->label('Taille du T-shirt')
                    ->options([
                        '128' => '128',
                        '140' => '140',
                        '152' => '152',
                        '164' => '164',
                        '34'  => '34 - Femme',
                        '36'  => '36 - Femme',
                        '38'  => '38 - Femme',
                        '40'  => '40 - Femme',
                        '42'  => '42 - Femme',
                        'S'   => 'S - Homme',
                        'M'   => 'M - Homme',
                        'L'   => 'L - Homme',
                        'XL'  => 'XL - Homme',
                    ])
                    ->required(),

                // Date de validité (automatique, mais affiché pour info)
                DatePicker::make('date_validity')
                    ->label('Valide jusqu\'au')
                    ->default(now()->addMonths(1)->subDay())
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Le bon est valide 1 mois à partir d\'aujourd\'hui.'),
            ])
            ->statePath('data')
            ->model(Voucher::class);
    }

    /**
     * Gère la soumission du formulaire et l'émission du bon.
     */
    public function create(): void
    {
        try {
            // Valider les données du formulaire Filament
            $data = $this->form->getState();

            // Créer le bon dans la base de données
            $this->newVoucher = Voucher::create([
                'code_unique'   => Str::uuid(),
                'athlete_name'  => $data['athlete_name'],
                'tshirt_size'   => $data['tshirt_size'],
                'coach_name'    => $data['coach_name'],
                'date_emission' => now(),
                'date_validity' => now()->addMonths(1)->subDay(),
                'status'        => 'emitted',
                'type'          => 'tshirt',
            ]);

            // Notification de succès pour l'entraîneur
            Notification::make()
                ->title('Bon émis avec succès!')
                ->body("Le bon pour {$this->newVoucher->athlete_name} a été généré.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            // Gérer les erreurs de base de données ou autres
            Notification::make()
                ->title('Erreur lors de l\'émission du bon')
                ->body('Une erreur est survenue. Veuillez réessayer.')
                ->danger()
                ->send();

            // Log l'erreur pour le développeur
            logger()->error('Voucher creation failed: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.generate-tshirt-voucher');
    }
}
