<?php

namespace App\Filament\Actions;

use Filament\Forms;
use Filament\Actions\Action;
use App\Models\EventLogistic;
use Filament\Notifications\Notification;
use App\Services\LogisticsMessageGenerator;

class GenerateMessageAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'generate_message';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Générer Message')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('success')
            ->modalHeading('Générer un message modèle prêt à l\'emploi')
            ->modalDescription('Sélectionnez le type de message souhaité pour générer le modèle exact conforme au club avec vos données.')
            ->modalSubmitActionLabel('Copier le message')
            ->modalIcon('heroicon-o-chat-bubble-bottom-center-text')
            ->form([
                Forms\Components\Select::make('template')
                    ->label('Modèle de message')
                    ->options([
                        'comp_info_long'    => '📢 Compétition - Info générale (Long)',
                        'comp_info_short'   => '⚡ Compétition - Briefing jour J (Court)',
                        'travel_preliminary'=> '📋 Déplacement - Infos préliminaires (Hébergement et transports)',
                        'travel_survey'     => '✍️ Déplacement - Inscription et sondage',
                        'travel_plan'       => '🚘 Déplacement - Plan de transport définitif',
                        'travel_expenses'   => '💰 Déplacement - Note de frais et remboursement',
                    ])
                    ->default('comp_info_long')
                    ->live()
                    ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('registration_type')
                            ->label('Type d\'inscription')
                            ->options([
                                'tiiva'        => 'Tiiva / Sondage (Standard)',
                                'convocation'  => 'Convocations (CSI / Équipe)',
                                'qualification'=> 'Qualifications (Invitations)',
                            ])
                            ->default('tiiva')
                            ->visible(fn ($get) => $get('template') === 'comp_info_long')
                            ->live()
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),

                        Forms\Components\TextInput::make('location')
                            ->label('Lieu de la compétition')
                            ->placeholder('Ex: Lausanne, Bern, Sion...')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('trainers_XXX')
                            ->label('Destinataires entraîneurs')
                            ->placeholder('Ex: U14/U16 ou Sprint')
                            ->visible(fn ($get) => $get('template') === 'comp_info_long')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),

                        Forms\Components\TextInput::make('hotel_name')
                            ->label('Nom de l\'hôtel')
                            ->placeholder('Ex: Hôtel Ibis')
                            ->visible(fn ($get) => in_array($get('template'), ['travel_preliminary', 'travel_survey']))
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('hotel_link')
                            ->label('Lien de l\'hôtel')
                            ->placeholder('https://...')
                            ->visible(fn ($get) => in_array($get('template'), ['travel_preliminary', 'travel_survey']))
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),

                        Forms\Components\TextInput::make('participants_list_url')
                            ->label('URL de la liste des participants')
                            ->placeholder('https://...')
                            ->visible(fn ($get) => $get('template') === 'travel_survey')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('info_url')
                            ->label('URL d\'information de la compétition')
                            ->placeholder('https://...')
                            ->visible(fn ($get) => in_array($get('template'), ['travel_preliminary', 'travel_survey']))
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),

                        Forms\Components\TextInput::make('schedule_url')
                            ->label('URL de l\'horaire')
                            ->placeholder('https://...')
                            ->visible(fn ($get) => $get('template') === 'travel_survey')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),
                    ]),

                Forms\Components\TextInput::make('stay_athletes')
                    ->label('Athlètes et entraîneurs prévus pour dormir')
                    ->placeholder('Ex: Jean Dupont, Marie Curie...')
                    ->visible(fn ($get) => in_array($get('template'), ['travel_preliminary', 'travel_survey']))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('qualification_url')
                            ->label('Lien des qualifiés')
                            ->placeholder('https://...')
                            ->visible(fn ($get) => $get('template') === 'comp_info_long' && $get('registration_type') === 'qualification')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),

                        Forms\Components\TextInput::make('qualified_athletes')
                            ->label('Athlètes qualifiés')
                            ->placeholder('Ex: Jean Dupont (2008), Marie Tudor (2010)...')
                            ->visible(fn ($get) => $get('template') === 'comp_info_long' && $get('registration_type') === 'qualification')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),
                    ]),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('meeting_time')
                            ->label('Heure de rendez-vous')
                            ->placeholder('Ex: 07h30')
                            ->default('xxhxx')
                            ->visible(fn ($get) => $get('template') === 'comp_info_short')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),

                        Forms\Components\TextInput::make('spikes_info')
                            ->label('Pointes')
                            ->default('en céramique de 5mm. Vous pouvez en acheter sur place (6 CHF).')
                            ->visible(fn ($get) => $get('template') === 'comp_info_short')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),
                    ]),

                Forms\Components\Section::make('Accompagnement / Présence entraîneurs')
                    ->visible(fn ($get) => $get('template') === 'comp_info_long')
                    ->schema([
                        Forms\Components\Grid::make(5)
                            ->schema([
                                Forms\Components\TextInput::make('coaches_by_cat.u10')->label('U10')->placeholder('1 ➝ Marc'),
                                Forms\Components\TextInput::make('coaches_by_cat.u12')->label('U12')->placeholder('2 ➝ Sophie'),
                                Forms\Components\TextInput::make('coaches_by_cat.u14')->label('U14')->placeholder('2 ➝ Jean'),
                                Forms\Components\TextInput::make('coaches_by_cat.u16')->label('U16')->placeholder('1 ➝ Thomas'),
                                Forms\Components\TextInput::make('coaches_by_cat.u18')->label('U18+')->placeholder('1 ➝ Paul'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\CheckboxList::make('weather')
                    ->label('Conditions météo')
                    ->options([
                        'hot'  => '🥵 Canicule',
                        'cold' => '🥶 Froid',
                        'rain' => '☔️ Pluie',
                    ])
                    ->columns(3)
                    ->visible(fn ($get) => $get('template') === 'comp_info_short')
                    ->live()
                    ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),

                Forms\Components\Toggle::make('include_checklist')
                    ->label('Inclure la checklist du sac de compétition')
                    ->default(true)
                    ->visible(fn ($get) => $get('template') === 'comp_info_short')
                    ->live()
                    ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),

                Forms\Components\Textarea::make('custom_note')
                    ->label('Remarque / Note (optionnel)')
                    ->placeholder('Ex: Pensez à vérifier vos maillots du club...')
                    ->rows(2)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, $get, $record) => static::updateMessageOutput($set, $get, $record)),

                Forms\Components\Textarea::make('message_output')
                    ->label('Message généré')
                    ->rows(14)
                    ->dehydrated(false),
            ])
            ->mountUsing(function (Forms\Form $form, ?EventLogistic $record) {
                if (! $record) {
                    return;
                }
                $participants = collect($record->participants_data ?? []);
                $stayAthletes = $participants
                    ->filter(fn ($p) => ($p['survey_response']['hotel_needed'] ?? false) || ($p['hotel_override'] ?? false) || (! empty($p['stay_needed'])))
                    ->pluck('name')
                    ->map(fn ($n) => \Illuminate\Support\Str::replace('[E] ', '', $n))
                    ->implode(', ');

                $data = [
                    'template'              => 'comp_info_long',
                    'registration_type'     => 'tiiva',
                    'location'              => $record->settings['location'] ?? '',
                    'hotel_name'            => $record->settings['hotel_name'] ?? '',
                    'hotel_link'            => $record->settings['hotel_url'] ?? $record->settings['hotel_link'] ?? '',
                    'participants_list_url' => $record->settings['participants_list_url'] ?? '',
                    'info_url'              => $record->settings['info_url'] ?? $record->settings['competition_url'] ?? '',
                    'schedule_url'          => $record->settings['schedule_url'] ?? '',
                    'stay_athletes'         => $stayAthletes,
                    'meeting_time'          => 'xxhxx',
                    'spikes_info'           => 'en céramique de 5mm. Vous pouvez en acheter sur place (6 CHF).',
                    'include_checklist'     => true,
                ];
                $data['message_output'] = LogisticsMessageGenerator::generate($record, $data);
                $form->fill($data);
            })
            ->action(function (array $data, ?EventLogistic $record, $livewire) {
                if (! $record) {
                    return;
                }
                $text = LogisticsMessageGenerator::generate($record, $data);
                $livewire->js("navigator.clipboard.writeText(" . json_encode($text) . ");");
                Notification::make()
                    ->title('Message copié dans le presse-papier !')
                    ->success()
                    ->send();
            });
    }

    protected static function updateMessageOutput($set, $get, ?EventLogistic $record): void
    {
        if (! $record) {
            return;
        }
        $options = [
            'template'              => $get('template'),
            'registration_type'     => $get('registration_type'),
            'location'              => $get('location'),
            'trainers_XXX'          => $get('trainers_XXX'),
            'hotel_name'            => $get('hotel_name'),
            'hotel_link'            => $get('hotel_link'),
            'participants_list_url' => $get('participants_list_url'),
            'info_url'              => $get('info_url'),
            'external_info_url'     => $get('info_url'),
            'schedule_url'          => $get('schedule_url'),
            'stay_athletes'         => $get('stay_athletes'),
            'qualification_url'     => $get('qualification_url'),
            'qualified_athletes'    => $get('qualified_athletes'),
            'meeting_time'          => $get('meeting_time'),
            'spikes_info'           => $get('spikes_info'),
            'coaches_by_cat'        => $get('coaches_by_cat'),
            'weather'               => $get('weather'),
            'include_checklist'     => $get('include_checklist'),
            'custom_note'           => $get('custom_note'),
        ];
        $set('message_output', LogisticsMessageGenerator::generate($record, $options));
    }
}
