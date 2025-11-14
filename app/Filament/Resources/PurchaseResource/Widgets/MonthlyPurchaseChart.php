<?php

namespace App\Filament\Resources\PurchaseResource\Widgets;

use App\Models\Purchase;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class MonthlyPurchaseChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    protected $listeners = ['yearChanged' => '$refresh'];

    public function getHeading(): string 
    {
        $year = session('purchase_selected_year', Carbon::now()->year);
        return "Monthly Purchase Spending ($year)";
    }
    
    protected function getData(): array
    {
        $year = session('purchase_selected_year', Carbon::now()->year);
        
        $months = [];
        $amounts = [];

        // Get data for all 12 months of selected year
        for ($i = 1; $i <= 12; $i++) {
            $months[] = Carbon::create($year, $i, 1)->format('M');
            
            $monthlyTotal = Purchase::whereYear('date', $year)
                ->whereMonth('date', $i)
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
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'display' => true,
                    'grid' => [
                        'display' => true,
                        'drawBorder' => true,
                    ],
                    'ticks' => [
                        'display' => true,
                        'stepSize' => null,
                        'callback' => "function(value) { return '€' + value.toLocaleString('it-IT', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }",
                    ],
                ],
                'x' => [
                    'display' => true,
                    'grid' => [
                        'display' => true,
                    ],
                ],
            ],
        ];
    }

    protected function getHeight(): ?int
    {
        return 400;
    }
}
