<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class InvoiceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 1;

    protected function getStats(): array
    {
        $selectedYear = session('selected_year', Carbon::now()->year);

        // Get all invoices for the selected year
        $invoices = Invoice::query()
            ->whereYear('date', $selectedYear)
            ->get();

        // Calculate total
        $totalAmount = $invoices->sum('amount');

        // Get monthly data for chart
        $monthlyData = Invoice::query()
            ->select(DB::raw('MONTH(date) as month'), DB::raw('SUM(amount) as total'))
            ->whereYear('date', $selectedYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill in missing months with zeros
        $chartData = array_fill(1, 12, 0);
        foreach ($monthlyData as $month => $total) {
            $chartData[$month] = $total;
        }

        return [
            Stat::make('Total Invoices ' . $selectedYear, '€' . number_format($totalAmount, 2))
                ->description('All invoices from ' . $selectedYear)
                ->color('primary')
                ->chart(array_values($chartData)),
        ];
    }
}