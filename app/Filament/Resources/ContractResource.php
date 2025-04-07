<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Filament\Resources\ContractResource\RelationManagers\ServicesRelationManager;
use App\Models\Contract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-euro';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                BelongsToSelect::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull()
                    ->rows(5)
                    ->placeholder('Enter detailed notes about this contract')
                    ->maxLength(65535),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Toggle::make('recurring')
                    ->label('Recurring')
                    ->default(false),
                Toggle::make('consumable')
                    ->label('Consumable')
                    ->default(false)
                    ->live(),
                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->placeholder('Total available units')
                    ->hidden(fn (Forms\Get $get): bool => ! $get('consumable')),
                TextInput::make('consumed_amount')
                    ->label('Consumed Amount')
                    ->numeric()
                    ->default(0)
                    ->placeholder('Units already consumed')
                    ->hidden(fn (Forms\Get $get): bool => ! $get('consumable')),
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('End Date'),
                Select::make('billing_period')
                    ->options([
                        'monthly' => 'Monthly',
                        'bimonthly' => 'Bimonthly',
                        'quarterly' => 'Quarterly',
                        'half-yearly' => 'Half-Yearly',
                        'yearly' => 'Yearly',
                    ])
                    ->required(),
                Forms\Components\CheckboxList::make('services')
                    ->relationship('services', 'name')
                    ->columnSpanFull(),
                Toggle::make('active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')->label('Customer')->sortable()->searchable(),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('price')->label('Price')->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                Tables\Columns\IconColumn::make('recurring')
                    ->boolean()
                    ->label('Recurring')
                    ->sortable(),
                Tables\Columns\IconColumn::make('consumable')
                    ->boolean()
                    ->label('Consumable')
                    ->sortable(),
                Tables\Columns\TextColumn::make('consumed_amount')
                    ->label('Consumed')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record->consumable) {
                            return '-';
                        }
                        
                        return $record->consumed_amount . '/' . $record->amount;
                    })
                    ->sortable(),
                TextColumn::make('billing_period')->label('Billing Period')->sortable(),
                TextColumn::make('start_date')->label('Start Date')->sortable(),
                TextColumn::make('end_date')->label('End Date')->sortable(),
                Tables\Columns\TextColumn::make('services.name')
                    ->badge()
                    ->label('Services'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('View Contract')
                    ->slideOver()
                    ->label(''),
                Tables\Actions\EditAction::make()
                    ->label(''),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Contract')
                    ->modalDescription('Are you sure you want to delete this contract? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete contract')
                    ->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Are you sure you want to delete the selected contracts? This action cannot be undone.'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ServicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
