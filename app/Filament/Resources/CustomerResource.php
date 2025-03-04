<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Expense;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Enums\FiltersLayout;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('hourly_rate')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                TextInput::make('tax_rate')
                    ->required()
                    ->numeric()
                    ->suffix('%'),
                Toggle::make('active')
                    ->default(state: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('hourly_rate')
                    ->label('Hourly Rate')
                    ->money('EUR')
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 2, '.', ',') . ' €';
                    }),
                TextColumn::make('tax_rate')
                    ->label('Tax Rate')
                    ->formatStateUsing(function ($state) {
                        return (int)$state . ' %';
                    }),
                IconColumn::make('active')->boolean(),
                TextColumn::make('invoiced')
                    ->label('Invoiced')
                    ->money('EUR')
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 2, '.', ',') . ' €';
                    })
                    ->getStateUsing(function (Customer $record, $livewire) {
                        // Get selected year from filter
                        $selectedYear = $livewire->getTableFilterState('year');

                        // Query for invoices
                        $invoicesQuery = Invoice::where('customer_id', $record->id);
                        if (isset($selectedYear["value"]) && $selectedYear["value"]) {
                            $invoicesQuery->whereYear('date', $selectedYear["value"]);
                        }
                        
                        return $invoicesQuery->sum('amount');
                    }),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('EUR')
                    ->icon(function ($state) {
                        if ($state > 0) {
                            return 'heroicon-s-arrow-up';
                        } elseif ($state < 0) {
                            return 'heroicon-s-arrow-down';
                        }
                        return null; // No arrow for zero balance
                    })
                    ->iconColor(function ($state) {
                        if ($state > 0) {
                            return 'success';
                        }
                        return 'danger';
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state > 0) {
                            return '+' . number_format($state, 2, '.', ',') . ' €';
                        }
                        return number_format($state, 2, '.', ',') . ' €';
                    })
                    ->getStateUsing(function (Customer $record, $livewire) {
                        // Get selected year from filter
                        $selectedYear = $livewire->getTableFilterState('year');

                        // Query for invoices
                        $invoicesQuery = Invoice::where('customer_id', $record->id);
                        if (isset($selectedYear["value"]) && $selectedYear["value"]) {
                            $invoicesQuery->whereYear('date', $selectedYear["value"]);
                        }
                        $invoicesAmount = $invoicesQuery->sum('amount');

                        // Handle one-time expenses
                        $oneTimeExpensesQuery = Expense::where('customer_id', $record->id)
                            ->where('recurring', false);
                        if (isset($selectedYear["value"]) && $selectedYear["value"]) {
                            $oneTimeExpensesQuery->whereYear('date', $selectedYear["value"]);
                        }
                        $oneTimeExpensesAmount = $oneTimeExpensesQuery->sum('amount');

                        // Handle recurring expenses
                        $recurringExpensesQuery = Expense::where('customer_id', $record->id)
                            ->where('recurring', true);
                        if (isset($selectedYear["value"]) && $selectedYear["value"]) {
                            $recurringExpensesQuery->where(function ($query) use ($selectedYear) {
                                $query->whereYear('start_date', '<=', $selectedYear["value"])
                                    ->where(function ($query) use ($selectedYear) {
                                        $query->whereYear('end_date', '>=', $selectedYear["value"])
                                            ->orWhereNull('end_date');
                                    });
                            });
                        }
                        $recurringExpensesAmount = $recurringExpensesQuery->sum('amount');

                        $expensesAmount = $oneTimeExpensesAmount + $recurringExpensesAmount;

                        // Calculate balance
                        return $invoicesAmount - $expensesAmount;
                    }),
            ])
            ->filters([
                //
                Tables\Filters\SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        $currentYear = now()->year;
                        return [
                            $currentYear => $currentYear,
                            $currentYear - 1 => $currentYear - 1,
                            $currentYear - 2 => $currentYear - 2,
                        ];
                    })
                    ->default(now()->year)
                    ->placeholder('All Years')
                    ->query(function (Builder $query, array $data) {
                        return $query; // Don't filter projects table, we'll use the selected year only in the balance calculation
                    })
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('View Customer')
                    ->slideOver()
                    ->label(''),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->modalHeading('Edit Customer')
                    ->slideOver(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Customer')
                    ->modalDescription('Are you sure you want to delete this customer? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete customer')
                    ->label(''),
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
            'index' => Pages\ListCustomers::route('/'),
        ];
    }
}
