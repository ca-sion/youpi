<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Models\Resource as ResourceModel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ResourceResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\ResourceResource\RelationManagers;
use Illuminate\Database\Eloquent\Model;

class ResourceResource extends Resource
{
    protected static ?string $model = ResourceModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return 'Ressource';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Ressources';
    }

    public static function form(Form $form): Form
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
                    ->hidden(fn (Get $get): bool => in_array($get('type'), ['documentation', null]))
                    ->required(fn (Get $get): bool => ! in_array($get('type'), ['sessions', 'exercises', 'documentation', null]))
                    ->live(),
                DatePicker::make('date_end')
                    ->label('Date de fin')
                    ->helperText('Date à laquelle se termine le plan')
                    ->hidden(fn (Get $get): bool => ! in_array($get('type'), ['year_plan', 'macro_plan', 'micro_plan']))
                    ->required(fn (Get $get): bool => in_array($get('type'), ['year_plan', 'macro_plan', 'micro_plan']))
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
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
                    ->icon('heroicon-s-arrow-top-right-on-square')
                    ->label('Ouvrir')
                    ->url(fn (Model $record): string|null => $record->attachment)
                    ->hidden(fn (Model $record): bool => empty($record->attachment))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->persistFiltersInSession();
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
            'index' => Pages\ListResources::route('/'),
            'create' => Pages\CreateResource::route('/create'),
            'edit' => Pages\EditResource::route('/{record}/edit'),
        ];
    }
}
