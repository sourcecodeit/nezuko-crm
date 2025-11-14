<?php

namespace App\Filament\Resources\PurchaseResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Purchase;
use Carbon\Carbon;

class PurchaseStatsWidget extends BaseWidget
{
    protected $listeners = ['yearChanged' => '$refresh'];

    protected function getStats(): array
    {
        $selectedYear = session('purchase_selected_year', Carbon::now()->year);
        $startOfYear = Carbon::create($selectedYear, 1, 1)->startOfYear();
        $endOfYear = Carbon::create($selectedYear, 12, 31)->endOfYear();
        $now = Carbon::now();
        
        // Total purchases for selected year
        $totalThisYear = Purchase::whereYear('date', $selectedYear)
            ->sum('amount');
        
        // Total all purchases
        $totalAllTime = Purchase::sum('amount');
        
        // Average monthly spending (based on selected year data)
        // If selected year is current year, use elapsed months, otherwise use all 12 months
        if ($selectedYear == $now->year) {
            $monthsElapsed = $startOfYear->diffInMonths($now) + 1;
        } else {
            $monthsElapsed = 12;
        }
        $averageMonthly = $monthsElapsed > 0 ? $totalThisYear / $monthsElapsed : 0;
        
        // Get monthly data for the chart (last 6 months of selected year)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::create($selectedYear, 12, 1)->subMonths($i);
            $monthlyData[] = Purchase::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('amount');
        }

        return [
            Stat::make('Total Purchases (This Year)', '€' . number_format($totalThisYear, 2))
                ->description('Total amount since Jan 1')
                ->color('success')
                ->chart($monthlyData),

            Stat::make('Average Monthly Spending', '€' . number_format($averageMonthly, 2))
                ->description('Average per month this year')
                ->color('warning')
                ->chart($monthlyData),

            Stat::make('Total Purchases (All Time)', '€' . number_format($totalAllTime, 2))
                ->description('Total amount of all purchases')
                ->color('primary')
                ->chart($monthlyData),
        ];
    }
}
