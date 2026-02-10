<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\EventLogistic;
use Filament\Resources\Resource;
use App\Filament\Resources\EventLogisticResource\Pages;

class EventLogisticResource extends Resource
{
    protected static ?string $model = EventLogistic::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return 'Logistique';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Logistique d\'événements';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de l\'événement')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $state) => $set('slug', Str::slug($state))),
                        // Forms\Components\TextInput::make('slug')
                        //     ->required()
                        //     ->unique(ignoreRecord: true),
                    ])->columns(2),
                Forms\Components\Tabs::make('Logistique')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Paramètres')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\DatePicker::make('settings.start_date')
                                            ->label('Début')
                                            ->required(),
                                        Forms\Components\TextInput::make('settings.days_count')
                                            ->label('Jours')
                                            ->numeric()
                                            ->default(2)
                                            ->required(),
                                        Forms\Components\Select::make('document_id')
                                            ->label('Document')
                                            ->relationship('document', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ]),
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('settings.distance_km')
                                            ->label('Distance (km)')
                                            ->numeric()
                                            ->required(),
                                        Forms\Components\TextInput::make('settings.bus_speed')
                                            ->label('Bus (km/h)')
                                            ->numeric()
                                            ->default(80),
                                        Forms\Components\TextInput::make('settings.car_speed')
                                            ->label('Voiture (km/h)')
                                            ->numeric()
                                            ->default(100),
                                        Forms\Components\TextInput::make('settings.bus_capacity')
                                            ->label('Capacité bus')
                                            ->numeric()
                                            ->default(9)
                                            ->required(),
                                    ]),
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('settings.duration_prep_min')
                                            ->label('Prép. (min)')
                                            ->numeric()
                                            ->default(90),
                                        Forms\Components\TextInput::make('settings.duration_recup_min')
                                            ->label('Récup. (min)')
                                            ->numeric()
                                            ->default(60),
                                        Forms\Components\TextInput::make('settings.home_departure_threshold')
                                            ->label('Seuil heure de départ du domicile pour l\'hôtel')
                                            ->placeholder('07:00')
                                            ->default('07:00'),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('settings.survey_deadline_at')
                                            ->label('Date limite du sondage (Fixe)')
                                            ->helperText('Si rempli, le sondage se fermera à cette date précise.'),
                                        Forms\Components\TextInput::make('settings.survey_deadline_days_before')
                                            ->label('Nombre de jours avant l\'événement')
                                            ->numeric()
                                            ->helperText('Ex: 3 pour fermer le sondage 3 jours avant le début de l\'événement.'),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Horaire')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Forms\Components\Section::make('Instruction IA')
                                    ->description('Copiez ce prompt pour générer le JSON via une IA')
                                    ->schema([
                                        Forms\Components\Placeholder::make('prompt_help')
                                            ->label('')
                                            ->content('Utilisez ce prompt : "A. Analyse ce texte ou document PDF d\'horaire. Extrais les données en JSON pur sous ce format : [{"jour": "samedi", "time": "14:15", "cat": "U18M", "discipline": "100m"}, ...]. Il faut mapper les disciplines en français (ex: Longueur : Weit, Long). Parfois il y a des tours sur les courses : séries (Z, VL ou rien précisé), demi-finales (DF), finales (F). Parfois il y a plusieurs disciplines pour la même catégorie, il faut les distinguer (ex: Longueur W1 <> Longueur W2 <> Longueur (4.50)). Parfois il y a la même discipline le samedi et le dimanche, il faut les distinguer (ex: Longueur (samedi) <> Longueur (dimanche)). S\'il n\'y a pas de catégorie spécifique, mettre M (Hommes, Männer) et W (Femmes, Frauen). Je te donne aussi les inscriptions des athlètes au format brut pour t\'aider à identifier les disciplines (jour, etc.). B. Transforme ensuite les inscriptions au format suivant (une ligne par athlète) : Prénom Nom (CATEGORIE) : Discipline 1, Discipline 2, … . Il faut que les noms des disciplines de l\'horaire (en sortie JSON) correspondent exactement aux noms des disciplines des inscriptions (en sortie ligne par ligne). Tenir compte des indications de jour si l\'indication est transmise dans les inscriptions des athlètes pour la correspondance exacte. Inscriptions des athlètes brutes :"'),
                                    ]),
                                Forms\Components\Textarea::make('schedule_raw')
                                    ->label('JSON Horaire')
                                    ->rows(15)
                                    ->formatStateUsing(fn ($state) => is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Inscriptions')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Forms\Components\Textarea::make('inscriptions_raw')
                                    ->label('Inscriptions Brutes')
                                    ->rows(5)
                                    ->placeholder("Dupont Pierre (U18M) : 100m, 200m\nTudor Jean (MAN) : Hauteur")
                                    ->helperText('Format : Nom Prénom (CAT) : Discipline 1, Discipline 2')
                                    ->columnSpanFull(),
                                Forms\Components\Repeater::make('inscriptions_data')
                                    ->label('Données Analysées')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')->label('Nom')->required(),
                                        Forms\Components\TextInput::make('category')->label('Cat')->required(),
                                        Forms\Components\TagsInput::make('disciplines')->label('Disciplines')->required(),
                                    ])
                                    ->columns(3)
                                    ->grid(1)
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Participants (Planning)')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                Forms\Components\Repeater::make('participants_data')
                                    ->label('Planning détaillé')
                                    ->schema([
                                        Forms\Components\Grid::make(5)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nom')
                                                    ->required(),
                                                Forms\Components\TextInput::make('first_competition_datetime')
                                                    ->label('1ère discipline'),
                                                Forms\Components\TextInput::make('last_competition_datetime')
                                                    ->label('Dernière discipline'),
                                                Forms\Components\Checkbox::make('hotel_override')
                                                    ->label('Hôtel requis (Manuel)')
                                                    ->inline(false),
                                                Forms\Components\Checkbox::make('survey_response.hotel_needed')
                                                    ->label('Hôtel (Sondage)'),
                                            ]),
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('survey_response.filled_at')
                                                    ->label('Rempli le')
                                                    ->disabled()
                                                    ->dehydrated(),
                                                Forms\Components\TextInput::make('survey_response.remarks')
                                                    ->label('Remarques (Sondage)'),
                                            ]),
                                        Forms\Components\Repeater::make('survey_response.responses_array')
                                            ->label('Détails transports par jour')
                                            ->schema([
                                                Forms\Components\TextInput::make('date')->disabled()->label('Jour'),
                                                Forms\Components\Select::make('aller_mode')
                                                    ->label('Aller')
                                                    ->options([
                                                        'bus'       => 'Bus',
                                                        'train'     => 'Train',
                                                        'car'       => 'Voiture',
                                                        'car_seats' => 'Voiture + places',
                                                        'on_site'   => 'Sur place/Pas besoin',
                                                        'absent'    => 'Absent',
                                                        ''          => '--',
                                                    ]),
                                                Forms\Components\Select::make('retour_mode')
                                                    ->label('Retour')
                                                    ->options([
                                                        'bus'       => 'Bus',
                                                        'train'     => 'Train',
                                                        'car'       => 'Voiture',
                                                        'car_seats' => 'Voiture + places',
                                                        'on_site'   => 'Sur place/Pas besoin',
                                                        'absent'    => 'Absent',
                                                        ''          => '--',
                                                    ]),
                                                Forms\Components\TextInput::make('aller_seats')
                                                    ->label('Places (Aller)')
                                                    ->numeric(),
                                                Forms\Components\TextInput::make('retour_seats')
                                                    ->label('Places (Retour)')
                                                    ->numeric(),
                                            ])
                                            ->columns(5)
                                            ->addable(false)
                                            ->deletable(false)
                                            ->formatStateUsing(function ($state, $get) {
                                                $responses = $get('survey_response.responses') ?? [];
                                                $settings = $get('../../settings');
                                                $startDateStr = $settings['start_date'] ?? null;
                                                $daysCount = (int) ($settings['days_count'] ?? 2);

                                                if (! $startDateStr) {
                                                    return [];
                                                }

                                                $startDate = \Carbon\Carbon::parse($startDateStr);
                                                $data = [];
                                                for ($i = 0; $i < $daysCount; $i++) {
                                                    $date = $startDate->copy()->addDays($i)->toDateString();
                                                    $resp = $responses[$date] ?? [];
                                                    $data[] = [
                                                        'date'         => $date,
                                                        'aller_mode'   => $resp['aller']['mode'] ?? '',
                                                        'retour_mode'  => $resp['retour']['mode'] ?? '',
                                                        'aller_seats'  => $resp['aller']['seats'] ?? null,
                                                        'retour_seats' => $resp['retour']['seats'] ?? null,
                                                    ];
                                                }

                                                return $data;
                                            })
                                            ->dehydrated(false)
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                $responses = $get('survey_response.responses') ?? [];
                                                foreach ($state as $item) {
                                                    $date = $item['date'];
                                                    if (! isset($responses[$date])) {
                                                        $responses[$date] = [
                                                            'aller'  => ['mode' => ''],
                                                            'retour' => ['mode' => ''],
                                                        ];
                                                    }
                                                    $responses[$date]['aller']['mode'] = $item['aller_mode'] ?? '';
                                                    $responses[$date]['retour']['mode'] = $item['retour_mode'] ?? '';

                                                    if (isset($item['aller_seats']) && $item['aller_seats'] !== '') {
                                                        $responses[$date]['aller']['seats'] = $item['aller_seats'];
                                                    } else {
                                                        unset($responses[$date]['aller']['seats']);
                                                    }

                                                    if (isset($item['retour_seats']) && $item['retour_seats'] !== '') {
                                                        $responses[$date]['retour']['seats'] = $item['retour_seats'];
                                                    } else {
                                                        unset($responses[$date]['retour']['seats']);
                                                    }
                                                }
                                                $set('survey_response.responses', $responses);
                                                // Mark as updated for sync logic
                                                $settings = $get('../../settings');
                                                $settings['survey_updated_at'] = now()->toDateTimeString();
                                                $set('../../settings', $settings);
                                            }),
                                        Forms\Components\Hidden::make('survey_response.responses'),
                                        Forms\Components\Hidden::make('id'),
                                        Forms\Components\Hidden::make('role'),
                                        Forms\Components\Hidden::make('competition_days'),
                                        Forms\Components\Hidden::make('note'),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                    ->collapsible()
                                    ->collapsed()
                                    ->addable()
                                    ->deletable()
                                    ->reorderable()
                                    ->columnSpanFull()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // Update global sync flag if any participant data changed
                                        $settings = $get('settings');
                                        $settings['survey_updated_at'] = now()->toDateTimeString();
                                        $set('settings', $settings);
                                    }),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('settings.start_date')
                    ->label('Date')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('document.name')
                    ->label('Document')
                    ->url(fn ($record) => route('documents.show', $record->document)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('public_survey')
                    ->label('Sondage public')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->url(fn ($record) => route('logistics.survey', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('public_view')
                    ->label('Vue résumé')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('logistics.show', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'     => Pages\ListEventLogistics::route('/'),
            'create'    => Pages\CreateEventLogistic::route('/create'),
            'edit'      => Pages\EditEventLogistic::route('/{record}/edit'),
            'transport' => Pages\ManageTransport::route('/{record}/transport'),
        ];
    }
}
