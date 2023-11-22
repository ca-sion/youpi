<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Document;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\DocumentType;
use App\Enums\DocumentStatus;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DocumentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DocumentResource\RelationManagers;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->default('information'),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
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
