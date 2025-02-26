<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use App\Filament\Resources\InvoiceResource\Pages\ListInvoices;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Models\Invoice;

class InvoiceStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListInvoices::class;
    }

    protected function getStats(): array
    {
        $totalPaid = $this->getPageTableQuery()
            ->where('paid', true)->sum('amount');
        $totalUnpaid = $this->getPageTableQuery()->where('paid', false)->sum('amount');
        $totalInvoices = $this->getPageTableQuery()->sum('amount');

        return [
            Stat::make('Paid Invoices', '€' . number_format($totalPaid, 2))
                ->description('Total amount of paid invoices')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Unpaid Invoices', '€' . number_format($totalUnpaid, 2))
                ->description('Total amount of unpaid invoices')
                ->color('danger')
                ->chart([3, 8, 5, 10, 7, 12, 6]),

            Stat::make('Total', '€' . number_format($totalInvoices, 2))
                ->description('Total amount of all invoices')
                ->color('primary')
                ->chart([8, 10, 12, 15, 18, 20, 25]),
        ];
    }
}
