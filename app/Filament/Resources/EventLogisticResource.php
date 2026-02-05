<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventLogisticResource\Pages;
use App\Filament\Resources\EventLogisticResource\RelationManagers;
use App\Models\EventLogistic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EventLogisticResource extends Resource
{
    protected static ?string $model = EventLogistic::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('event_name')
                            ->label('Nom de l\'événement')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $state) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
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
                                        Forms\Components\TextInput::make('settings.distance_km')
                                            ->label('Distance (km)')
                                            ->numeric()
                                            ->required(),
                                    ]),
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('settings.vitesse_bus')
                                            ->label('Bus (km/h)')
                                            ->numeric()
                                            ->default(80),
                                        Forms\Components\TextInput::make('settings.vitesse_voiture')
                                            ->label('Voiture (km/h)')
                                            ->numeric()
                                            ->default(100),
                                        Forms\Components\TextInput::make('settings.bus_capacity')
                                            ->label('Capacité bus')
                                            ->numeric()
                                            ->default(9)
                                            ->required(),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('settings.temps_prep_min')
                                            ->label('Prép. (min)')
                                            ->numeric()
                                            ->default(90),
                                        Forms\Components\TextInput::make('settings.temps_recup_min')
                                            ->label('Récup. (min)')
                                            ->numeric()
                                            ->default(60),
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
                                            ->content('Utilisez ce prompt : "A. Analyse ce texte ou document PDF d\'horaire. Extrais les données en JSON pur sous ce format : [{"jour": "Samedi", "time": "14:15", "cat": "U18M", "discipline": "100m"}, ...]. Il faut mapper les disciplines en français (ex: Longueur : Weit, Long). Parfois il y a des tours sur les courses : séries (Z, VL ou rien précisé), demi-finales (DF), finales (F). Parfois il y a plusieurs disciplines pour la même catégorie, il faut les distinguer (ex: Longueur W1, Longueur W2 ou Longueur (4.50)). S\'il n\'y a pas de catégorie spécifique, mettre M (Hommes, Männer) et W (Femmes, Frauen). Ne fournis aucune explication. Je te donne aussi les inscriptions des athlètes au format brutes pour t\'aider à identifier les horaires des disciplines pour chacun. B. Transforme ensuite les inscriptions au format suivant (une ligne par athlète) : Nom Prénom (CAT) : Discipline 1, Discipline 2, … . Il faut que les noms des disciplines de l\'horaire (en sortie JSON) correspondent exactement aux noms des disciplines des inscriptions (en sortie ligne par ligne). Tenir compte (dans le cas de disciplines ayant lieu, indépendamment des finales ou demi-finales, à plusieurs jours différents) des indications de jour si l\'indication est transmise dans les inscriptions des athlètes pour la correspondance exacte. Inscriptions des athlètes brutes :"'),
                                    ]),
                                Forms\Components\Textarea::make('raw_schedule')
                                    ->label('JSON Horaire')
                                    ->rows(15)
                                    ->formatStateUsing(fn ($state) => is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Inscriptions')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Forms\Components\Textarea::make('athletes_inscriptions_raw')
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
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('name')->label('Nom')->required(),
                                                Forms\Components\TextInput::make('first_competition_datetime')->label('1er départ'),
                                                Forms\Components\TextInput::make('last_competition_datetime')->label('Dernier départ'),
                                            ]),
                                        Forms\Components\Textarea::make('survey_response')
                                            ->label('Sondage')
                                            ->rows(2)
                                            ->formatStateUsing(fn ($state) => is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                    ->collapsible()
                                    ->collapsed()
                                    ->addable()
                                    ->deletable()
                                    ->reorderable()
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_name')
                    ->searchable(),
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
            'index' => Pages\ListEventLogistics::route('/'),
            'create' => Pages\CreateEventLogistic::route('/create'),
            'edit' => Pages\EditEventLogistic::route('/{record}/edit'),
            'transport' => Pages\ManageTransport::route('/{record}/transport'),
        ];
    }
}
