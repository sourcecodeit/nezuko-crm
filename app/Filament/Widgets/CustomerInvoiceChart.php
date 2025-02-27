<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CustomerInvoiceChart extends ChartWidget
{
    public function getHeading(): string 
    {
        $year = session('selected_year', Carbon::now()->year);
        return "Customer Invoice Totals ($year)";
    }
    protected static string $color = 'primary';
    
    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $year = session('selected_year', Carbon::now()->year);

        $customerTotals = Invoice::select(
            'customers.name',
            DB::raw('SUM(invoices.amount) as total')
        )
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->whereYear('invoices.date', $year)
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Invoiced Amount (€)',
                    'data' => $customerTotals->pluck('total')->map(fn($amount) => round($amount, 2))->toArray(),
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#d97706',
                ],
            ],
            'labels' => $customerTotals->pluck('name')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "€" + value.toLocaleString() }',
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return "€" + context.raw.toLocaleString() }',
                    ],
                ],
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }
}