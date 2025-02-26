<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Invoice;

class InvoicesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $year = date('Y');
        $firstDay = date('Y-m-d', mktime(0, 0, 0, 1, 1, $year));

        // get total amount for $year
        $total = Invoice::where('date', '>', $firstDay)->sum('amount');

        return [
            Stat::make("Total {$year}", $total)
        ];
    }
}
