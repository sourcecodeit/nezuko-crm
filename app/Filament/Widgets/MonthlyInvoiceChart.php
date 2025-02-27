<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyInvoiceChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Invoice Amounts';

    protected static string $color = 'success';


    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $year = Carbon::now()->year;

        $monthlyTotals = Invoice::select(
            DB::raw('MONTH(date) as month'),
            DB::raw('SUM(amount) as total')
        )
            ->whereYear('date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(fn($item) => round($item->total, 2))
            ->toArray();

        // Ensure all months are represented (1-12)
        $monthlyData = [];
        $monthNames = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = $monthlyTotals[$i] ?? 0;
            $monthNames[] = Carbon::create()->month($i)->format('F');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Invoiced Amount (€)',
                    'data' => $monthlyData,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#059669',
                ],
            ],
            'labels' => $monthNames,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return "€" + context.raw.toLocaleString() }',
                    ],
                ],
            ],
        ];
    }
}