<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\Toggle;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

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
                Toggle::make('active')
                    ->default(true),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')->label('Customer')->sortable()->searchable(),
                TextColumn::make('name')->sortable()->searchable(),
                IconColumn::make('active')->boolean(),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('EUR')
                    ->formatStateUsing(function ($state) {
                        if ($state > 0) {
                            return '+' . number_format($state, 2, '.', ',') . ' €';
                        }
                        return number_format($state, 2, '.', ',') . ' €';
                    })
                    ->getStateUsing(function (Project $record, $livewire) {
                        // Get selected year from filter
                        $selectedYear = $livewire->getTableFilterState('year');

                        // Query for invoices
                        $invoicesQuery = Invoice::where('customer_id', $record->customer_id);
                        if ($selectedYear["value"]) {
                            $invoicesQuery->whereYear('date', $selectedYear);
                        }
                        $invoicesAmount = $invoicesQuery->sum('amount');

                        // Handle one-time expenses
                        $oneTimeExpensesQuery = Expense::where('customer_id', $record->customer_id)
                            ->where('recurring', false);
                        if ($selectedYear) {
                            $oneTimeExpensesQuery->whereYear('date', $selectedYear);
                        }
                        $oneTimeExpensesAmount = $oneTimeExpensesQuery->sum('amount');

                        // Handle recurring expenses
                        $recurringExpensesQuery = Expense::where('customer_id', $record->customer_id)
                            ->where('recurring', true);
                        if ($selectedYear) {
                            $recurringExpensesQuery->where(function ($query) use ($selectedYear) {
                                $query->whereYear('start_date', '<=', $selectedYear)
                                    ->where(function ($query) use ($selectedYear) {
                                        $query->whereYear('end_date', '>=', $selectedYear)
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
