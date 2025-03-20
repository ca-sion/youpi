<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use App\Models\Document;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\DocumentType;
use App\Enums\DocumentStatus;
use Filament\Resources\Resource;
use Illuminate\Contracts\View\View;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DocumentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
                Forms\Components\TextInput::make('number')->disabled(),
                Forms\Components\DatePicker::make('published_on')
                    ->default(now())
                    ->required(),
                Forms\Components\DatePicker::make('expires_on'),
                Forms\Components\Select::make('type')
                    ->options(DocumentType::class)
                    ->default('information')
                    ->reactive(),
                Forms\Components\Select::make('status')
                    ->options(DocumentStatus::class)
                    ->default('validated'),
                Forms\Components\Textarea::make('salutation')
                    ->rows(1)
                    ->autosize()
                    ->maxLength(255),
                Forms\Components\Textarea::make('signature')
                    ->rows(1)
                    ->autosize()
                    ->maxLength(255),
                Forms\Components\TextInput::make('author')
                    ->maxLength(255),
                Forms\Components\Builder::make('sections')
                    ->blocks([
                        Forms\Components\Builder\Block::make('paragraph')
                            ->schema([
                                Forms\Components\RichEditor::make('content')
                                    ->label('Paragraphe')
                                    ->required(),
                            ]),
                        Forms\Components\Builder\Block::make('block')
                            ->schema([
                                Forms\Components\RichEditor::make('content')
                                    ->label('Paragraphe')
                                    ->required(),
                            ]),
                        Forms\Components\Builder\Block::make('description')
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('heading')
                                    ->label('En-tête')
                                    ->columnSpan(1),
                                Forms\Components\Textarea::make('content')
                                    ->label('Contenu')
                                    ->hint('Markdown')
                                    ->autosize()
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->columnSpanFull(),
                Forms\Components\Section::make('Déplacement')
                    ->description('Prevent abuse by limiting the number of requests per period')
                    ->visible(fn (Get $get) => $get('type') == DocumentType::TRAVEL->value)
                    ->schema([
                        Forms\Components\Section::make('Informations')
                            ->schema([
                                Forms\Components\DatePicker::make('travel_data.data.modification_deadline')->label('Délai pour informer'),
                                Forms\Components\TextInput::make('travel_data.data.modification_deadline_phone')->label('Téléphone'),
                                Forms\Components\TextInput::make('travel_data.data.location')->label('Lieu'),
                                Forms\Components\TextInput::make('travel_data.data.date')->label('Date'),
                            ]),
                        Forms\Components\Repeater::make('travel_data.data.departures')
                            ->label('Allers')
                            ->columns(2)
                            ->addActionLabel('Ajouter un aller')
                            ->schema([
                                Forms\Components\TextInput::make('day_hour')->label('Jour et heure'),
                                Forms\Components\TextInput::make('location')->label('Lieu'),
                                Forms\Components\TextInput::make('means')->label('Moyen de transport'),
                                Forms\Components\TextInput::make('driver')->label('Chauffeur'),
                                Forms\Components\TextInput::make('travelers')->label('Nom des voyageurs'),
                                Forms\Components\TextInput::make('travelers_number')->label('Nombre de voyageur')->numeric(),
                            ]),
                        Forms\Components\Repeater::make('travel_data.data.arrivals')
                            ->label('Retours')
                            ->columns(2)
                            ->addActionLabel('Ajouter un retour')
                            ->schema([
                                Forms\Components\TextInput::make('day_hour')->label('Jour et heure'),
                                Forms\Components\TextInput::make('location')->label('Lieu'),
                                Forms\Components\TextInput::make('means')->label('Moyen de transport'),
                                Forms\Components\TextInput::make('driver')->label('Chauffeur'),
                                Forms\Components\TextInput::make('travelers')->label('Nom des voyageurs'),
                                Forms\Components\TextInput::make('travelers_number')->label('Nombre de voyageur')->numeric(),
                            ]),
                        Forms\Components\Section::make('Hébergement')
                            ->schema([
                                Forms\Components\Textarea::make('travel_data.data.accomodation')->label('Nom, adresse et renseignement'),
                                Forms\Components\Repeater::make('travel_data.data.nights')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('day')->label('Jour'),
                                        Forms\Components\TextInput::make('travelers')->label('Nom des voyageurs'),
                                    ]),
                            ]),
                        Forms\Components\Section::make('Compétition')
                            ->schema([
                                Forms\Components\TextInput::make('travel_data.data.competition')->label('Nom de la compétition'),
                                Forms\Components\Textarea::make('travel_data.data.competition_informations_important')->label('Information importante'),
                                Forms\Components\RichEditor::make('travel_data.data.competition_informations')->label('Informations')
                                    ->hint('URLs de la publication officielle, de l\'horaire et du règlement'),
                                Forms\Components\Textarea::make('travel_data.data.competition_schedules')->label('Horaires')
                                    ->hint('PRENOM : (JOUR) XXhXX DISCIPLINE'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->wrap()
                    ->limit(90)
                    ->searchable(),
                Tables\Columns\TextColumn::make('identifier')
                    ->searchable(['id'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_on')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_on')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
            ])
            ->defaultSort('published_on', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('share')
                    ->label('Partager')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->action(fn (Document $record) => $record->advance())
                    ->modalContent(fn (Document $record): View => view(
                        'components.document-text-share',
                        ['document' => $record],
                    ))
                    ->modalSubmitAction(false),
                ActionGroup::make([
                    Tables\Actions\Action::make('show document')
                        ->label('Afficher')
                        ->url(fn (Document $record): string => route('documents.show', ['document' => $record]))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-arrow-top-right-on-square'),
                    Tables\Actions\ReplicateAction::make()
                        ->excludeAttributes(['name', 'number', 'published_on', 'status'])
                        ->form([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\DatePicker::make('published_on')->required(),
                            Forms\Components\Select::make('status')
                                ->required()
                                ->options(DocumentStatus::class)
                                ->default(DocumentStatus::VALIDATED),
                        ])
                        ->beforeReplicaSaved(function (Document $replica, array $data): void {
                            $replica->fill($data);
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index'  => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit'   => Pages\EditDocument::route('/{record}/edit'),
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
