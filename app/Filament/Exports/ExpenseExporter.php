<?php

namespace App\Filament\Exports;

use App\Models\Expense;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ExpenseExporter extends Exporter
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('customer.name')
                ->label('Customer'),
            ExportColumn::make('amount')
                ->label('Amount'),
            ExportColumn::make('date')
                ->label('Date'),
            ExportColumn::make('recurring')
                ->label('Recurring')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
            ExportColumn::make('start_date')
                ->label('Start Date'),
            ExportColumn::make('end_date')
                ->label('End Date'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your expense export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
