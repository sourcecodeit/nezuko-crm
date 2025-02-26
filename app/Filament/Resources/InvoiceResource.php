<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Filament\Resources\InvoiceResource\Widgets\InvoiceStats;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\HtmlString;
use Filament\Tables\Actions\Action;

use Carbon\Carbon;



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
        $currentYear = Carbon::now()->year;
        $years = array_combine(
            range($currentYear - 5, $currentYear),
            range($currentYear - 5, $currentYear)
        );

        return $table
            ->columns([
                TextColumn::make('customer.name')->label('Customer')->sortable()->searchable(),
                TextColumn::make('number')
                    ->formatStateUsing(function ($state, $record) {
                        $year = date('Y', strtotime($record->date));
                        return new HtmlString(
                            $state . '<span style="font-size: 0.75em; color: #6b7280;">/' . $year . '</span>'
                        );
                    })
                    ->searchable(),
                TextColumn::make('amount')
                    ->money('EUR')
                    ->summarize(Sum::make()->money('EUR')),
                IconColumn::make('paid')
                    ->boolean()
                    ->action(
                        Action::make('togglePaid')
                            ->requiresConfirmation()
                            ->modalHeading(fn(Invoice $record) => $record->paid ?
                                'Mark Invoice as Unpaid?' : 'Mark Invoice as Paid?')
                            ->modalDescription(fn(Invoice $record) => $record->paid ?
                                "Are you sure you want to mark invoice #{$record->number} as unpaid?" :
                                "Are you sure you want to mark invoice #{$record->number} as paid?")
                            ->modalSubmitActionLabel('Yes, change status')
                            ->modalCancelActionLabel('No, cancel')
                            ->action(function (Invoice $record): void {
                                $record->update(['paid' => !$record->paid]);
                            })
                    ),
                TextColumn::make('date')
                    ->date('F') // Format 'F' in PHP displays the full month name
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->label('Year')
                    ->options($years)
                    ->default($currentYear)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn(Builder $query, $year): Builder => $query
                                    ->whereYear('date', $year)
                            );
                    }),
                TernaryFilter::make('paid')
                    ->label('Payment Status')
                    ->placeholder('All Invoices')
                    ->trueLabel('Paid Invoices')
                    ->falseLabel('Unpaid Invoices')
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
                //
            InvoiceStats::class
        ];
    }
}
