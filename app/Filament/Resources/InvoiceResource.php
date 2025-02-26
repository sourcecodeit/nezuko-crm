<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\Summarizers\Sum;



class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                BelongsToSelect::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                TextInput::make('number')
                    ->numeric(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Toggle::make('paid')
                    ->label('Paid')
                    ->default(false),
                DatePicker::make('date')
                    ->label('Invoice Date')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')->label('Customer')->sortable()->searchable(),
                TextColumn::make('number'),
                TextColumn::make('amount')
                    ->money('EUR')
                    ->summarize(Sum::make()->money('EUR')),
                IconColumn::make('paid')->boolean(),
                TextColumn::make('date')->sortable(),
            ])
            ->filters([])
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
