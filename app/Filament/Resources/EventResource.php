<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Event;
use Filament\Forms\Get;
use App\Enums\EventType;
use Filament\Forms\Form;
use App\Enums\EventStatus;
use Filament\Tables\Table;
use App\Enums\AthleteCategory;
use Filament\Resources\Resource;
use App\Enums\AthleteCategoryGroup;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EventResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Trainer;
use Filament\Tables\Filters\TernaryFilter;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return 'Événement';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Événements';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Base')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('status')
                        ->required()
                        ->options(EventStatus::class)
                        ->default(EventStatus::PLANNED),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Forms\Components\DatePicker::make('starts_at')
                        ->required(),
                    Forms\Components\DatePicker::make('ends_at'),
                    Forms\Components\TextInput::make('location')
                        ->maxLength(255),
                ]),

                Fieldset::make('Labels')
                ->schema([
                    Forms\Components\CheckboxList::make('types')
                        ->options(EventType::class)
                        ->columns(4)
                        ->gridDirection('row'),
                    Forms\Components\CheckboxList::make('athlete_category_groups')
                        ->options(AthleteCategoryGroup::class)
                        ->columns(11)
                        ->gridDirection('row')
                        ->columnSpanFull(),
                    Forms\Components\CheckboxList::make('athlete_categories')
                        ->options(AthleteCategory::class)
                        ->columns(11)
                        ->gridDirection('row')
                        ->columnSpanFull(),
                ]),

                Fieldset::make('Deadline')
                ->schema([
                    Forms\Components\Toggle::make('has_deadline')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Select::make('deadline_type')
                        ->visible(fn (Get $get) => $get('has_deadline'))
                        ->live()
                        ->options([
                            'tiiva' => 'Tiiva',
                            'text' => 'Texte',
                            'url' => 'URL',
                        ]),
                    Forms\Components\DateTimePicker::make('deadline_at')
                        ->visible(fn (Get $get) => $get('has_deadline'))
                        ->seconds(false),
                    Forms\Components\TextInput::make('deadline_text')
                        ->visible(fn (Get $get) => $get('deadline_type') == 'text')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('deadline_url')
                        ->visible(fn (Get $get) => $get('deadline_type') == 'url')
                        ->url()
                        ->maxLength(255),
                ]),

                Fieldset::make('Qualifiés')
                ->schema([
                    Forms\Components\Toggle::make('has_qualified')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Select::make('qualified_type')
                        ->visible(fn (Get $get) => $get('has_qualified'))
                        ->live()
                        ->options([
                            'list' => 'Liste',
                            'url' => 'URL',
                        ]),
                    Forms\Components\TextInput::make('qualified_url')
                        ->visible(fn (Get $get) => $get('qualified_type') == 'url')
                        ->url()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('qualified_list')
                        ->visible(fn (Get $get) => $get('qualified_type') == 'list')
                        ->autosize()
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('qualified_already_received')
                        ->visible(fn (Get $get) => $get('has_qualified'))
                        ->maxLength(65535)
                        ->default("Les invitations ont peut-être été reçu par les parents. Merci alors de regarder avec eux.")
                        ->autosize()
                        ->columnSpanFull(),
                ]),

                Fieldset::make('Convocation')
                ->schema([
                    Forms\Components\Toggle::make('has_convocation')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Select::make('convocation_type')
                        ->visible(fn (Get $get) => $get('has_convocation'))
                        ->live()
                        ->options([
                            'text' => 'Texte',
                        ]),
                    Forms\Components\Textarea::make('convocation_text')
                        ->visible(fn (Get $get) => $get('convocation_type') == 'text')
                        ->maxLength(65535)
                        ->default("Les convocations ont normalement été transmises soit par vous, soit directement aux athlètes par les chefs d'équipe. Il n'y a donc pas besoin de s'inscrire. *La présence de tous les athlètes convoqués est obligatoire*. Prière de contacter le chef d'équipe en cas de désistement.")
                        ->columnSpanFull(),
                ]),

                Fieldset::make('Inscrits')
                ->schema([
                    Forms\Components\Toggle::make('has_entrants')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Select::make('entrants_type')
                        ->visible(fn (Get $get) => $get('has_entrants'))
                        ->live()
                        ->options([
                            'text' => 'Texte',
                            'url' => 'URL',
                        ]),
                    Forms\Components\RichEditor::make('entrants_text')
                        ->visible(fn (Get $get) => $get('entrants_type') == 'text')
                        ->toolbarButtons([
                            'bold',
                            'bulletList',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'underline',
                            'undo',
                        ])
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('entrants_url')
                        ->visible(fn (Get $get) => $get('entrants_type') == 'url')
                        ->url()
                        ->maxLength(255),
                ]),

                Fieldset::make('Horaires')
                ->schema([
                    Forms\Components\Toggle::make('has_provisional_timetable')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('provisional_timetable_url')
                        ->visible(fn (Get $get) => $get('has_provisional_timetable'))
                        ->url()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('provisional_timetable_text')
                        ->visible(fn (Get $get) => $get('has_provisional_timetable'))
                        ->default("L'horaire définitif sera normalement disponible la semaine avant sur le site URL.")
                        ->maxLength(255),
                    Forms\Components\Toggle::make('has_final_timetable')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('final_timetable_url')
                        ->visible(fn (Get $get) => $get('has_final_timetable'))
                        ->url()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('final_timetable_text')
                        ->visible(fn (Get $get) => $get('has_final_timetable'))
                        ->maxLength(255),
                ]),

                Fieldset::make('Informations')
                ->schema([
                    Forms\Components\Toggle::make('has_publication')
                        ->live(),
                    Forms\Components\TextInput::make('publication_url')
                        ->visible(fn (Get $get) => $get('has_publication'))
                        ->url()
                        ->maxLength(255),
                    Forms\Components\Toggle::make('has_rules')
                        ->live(),
                    Forms\Components\TextInput::make('rules_url')
                        ->visible(fn (Get $get) => $get('has_rules'))
                        ->url()
                        ->maxLength(255),
                ]),

                Fieldset::make('Déplacement')
                ->schema([
                    Forms\Components\Toggle::make('has_trip')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Select::make('trip_type')
                        ->visible(fn (Get $get) => $get('has_trip'))
                        ->live()
                        ->options([
                            'text' => 'Texte',
                            'url' => 'URL',
                            'trip_document' => 'Document',
                        ]),
                    Forms\Components\TextInput::make('trip_url')
                        ->visible(fn (Get $get) => $get('trip_type') == 'url')
                        ->maxLength(255),
                    Forms\Components\Textarea::make('trip_text')
                        ->visible(fn (Get $get) => $get('trip_type') == 'text')
                        ->default("Un déplacement est organisé. Les informations se trouvent dans le document ci-après. Merci de communiquer rapidement si des changements sont à opérer.")
                        ->autosize()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('trip_id')
                        ->visible(fn (Get $get) => $get('trip_type') == 'trip_document')
                        ->maxLength(255),
                ]),

                Forms\Components\Repeater::make('sections')
                    ->columns(12)
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\TextInput::make('heading')
                            ->columnSpan(3),
                        Forms\Components\Textarea::make('content')
                            ->autosize()
                            ->rows(1)
                            ->columnSpan(7),
                        Forms\Components\Select::make('type')
                            ->columnSpan(2)
                            ->options([
                                'default' => 'Normal',
                                'block' => 'Block',
                            ])
                            ->default('default'),
                    ]),

                Fieldset::make('Présences')
                ->schema([
                    Forms\Components\Toggle::make('has_trainers_presences')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Select::make('trainers_presences_type')
                        ->visible(fn (Get $get) => $get('has_trainers_presences'))
                        ->live()
                        ->options([
                            'text' => 'Texte',
                            'table' => 'Tableau',
                        ]),
                    Forms\Components\Repeater::make('trainersPresences')
                        ->visible(fn (Get $get) => $get('trainers_presences_type') == 'table')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('trainer_id')->options(Trainer::all()->pluck('name', 'id'))->columnSpan(4),
                            Forms\Components\Radio::make('presence')->options([true => 'Présent', false => 'Absent'])->inline(false)->columnSpan(5),
                            Forms\Components\TextInput::make('note')->columnSpan(3),
                        ])
                        ->columns(12)
                        ->columnSpanFull(),
                        /*
                    Forms\Components\TextInput::make('trainers_presences_id')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('trainers_presences_data')
                        ->maxLength(255),
                        */
                ]),

                /*
                Fieldset::make('LABEL')
                ->schema([
                    //
                ]),
                Forms\Components\Textarea::make('data')
                    ->required()
                    ->columnSpanFull(),
                */
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('types')
                    ->badge(),
                Tables\Columns\TextColumn::make('athlete_category_groups')
                    ->badge(),
            ])
            ->defaultSort('starts_at', 'asc')
            ->filters([
                SelectFilter::make('types')
                    ->label('Type')
                    ->options(EventType::class)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn (Builder $query, $date): Builder => $query->whereJsonContains('types', $data['value']),
                            );
                    }),
                SelectFilter::make('athlete_category_groups')
                    ->label('Catégorie de groupe d\'athlètes')
                    ->options(AthleteCategoryGroup::class)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn (Builder $query, $date): Builder => $query->whereJsonContains('athlete_category_groups', $data['value']),
                            );
                    }),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(EventStatus::class),
                TernaryFilter::make('temporality')
                    ->label('Temporalité')
                    ->placeholder('Actuels')
                    ->trueLabel('Passés')
                    ->falseLabel('Futurs uniquement')
                    ->queries(
                        true: fn (Builder $query) => $query->where('starts_at', '<', now()->startOfDay()),
                        false: fn (Builder $query) => $query->where('starts_at', '>', now()->endOfDay()),
                        blank: fn (Builder $query) => $query->where('starts_at', '>', now()->subDays(7)->startOfDay()),
                    ),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('view trainers message')
                    ->label('Mess. entraîneurs')
                    ->action(fn (Event $record) => $record->advance())
                    ->modalContent(fn (Event $record): View => view(
                        'components.event-text-trainers-message',
                        ['event' => $record],
                    ))
                    ->modalSubmitAction(false),
                Action::make('show event')
                    ->label('Afficher')
                    ->url(fn (Event $record): string => route('events.show', ['event' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
