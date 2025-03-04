<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('recurring')
                    ->live()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\DatePicker::make('date')
                    ->required(fn(Forms\Get $get) => !$get('recurring'))
                    ->visible(fn(Forms\Get $get) => !$get('recurring')),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\DatePicker::make('start_date')
                    ->visible(fn(Forms\Get $get) => $get('recurring'))
                    ->required(fn(Forms\Get $get) => $get('recurring')),
                Forms\Components\DatePicker::make('end_date')
                    ->visible(fn(Forms\Get $get) => $get('recurring'))
                    ->afterOrEqual('start_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('recurring')
                    ->boolean(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('View Expense')
                    ->modalDescription('Detailed expense information')
                    ->slideOver()
                    ->label(''),
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Expense')
                    ->slideOver()
                    ->label(''),
                Tables\Actions\Action::make('duplicate')
                    ->tooltip('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->label('')
                    ->action(function (Expense $record): void {
                        $newExpense = $record->replicate();
                        
                        // If expense is not recurring, set date to today
                        if (!$newExpense->recurring) {
                            $newExpense->date = now()->toDateString();
                        }
                        
                        $newExpense->save();
                    })
                    ->successNotificationTitle('Expense duplicated successfully'),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Expense')
                    ->modalDescription('Are you sure you want to delete this expense? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete expense')
                    ->label(''),
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
            'index' => Pages\ListExpenses::route('/'),
        ];
    }
}
