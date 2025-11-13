<?php

namespace App\Filament\Resources\PurchaseResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Purchase;
use Carbon\Carbon;

class PurchaseStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $startOfYear = Carbon::now()->startOfYear();
        $now = Carbon::now();
        
        // Total purchases this year
        $totalThisYear = Purchase::where('date', '>=', $startOfYear)
            ->sum('amount');
        
        // Total all purchases
        $totalAllTime = Purchase::sum('amount');
        
        // Average monthly spending (based on current year data)
        $monthsElapsed = $startOfYear->diffInMonths($now) + 1;
        $averageMonthly = $monthsElapsed > 0 ? $totalThisYear / $monthsElapsed : 0;
        
        // Get monthly data for the chart (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
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
