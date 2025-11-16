<?php

namespace App\Filament\Exports;

use App\Models\Contract;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ContractExporter extends Exporter
{
    protected static ?string $model = Contract::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('customer.name')
                ->label('Customer'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('price')
                ->label('Price'),
            ExportColumn::make('active')
                ->label('Active')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
            ExportColumn::make('recurring')
                ->label('Recurring')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
            ExportColumn::make('consumable')
                ->label('Consumable')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
            ExportColumn::make('amount')
                ->label('Amount'),
            ExportColumn::make('consumed_amount')
                ->label('Consumed Amount'),
            ExportColumn::make('billing_period')
                ->label('Billing Period'),
            ExportColumn::make('start_date')
                ->label('Start Date'),
            ExportColumn::make('end_date')
                ->label('End Date'),
            ExportColumn::make('notes')
                ->label('Notes'),
            ExportColumn::make('created_at')
                ->label('Created At'),
            ExportColumn::make('updated_at')
                ->label('Updated At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your contract export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
