<?php

namespace App\Filament\Resources\PurchaseResource\Widgets;

use App\Models\Purchase;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class MonthlyPurchaseChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Purchase Spending';

    protected static ?int $sort = 2;
    
    protected function getData(): array
    {
        $months = [];
        $amounts = [];

        // Get data for the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');
            
            $monthlyTotal = Purchase::whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->sum('amount');
            
            $amounts[] = round($monthlyTotal, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Spending',
                    'data' => $amounts,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) { return '€' + value.toFixed(2); }",
                    ],
                ],
            ],
        ];
    }
}
