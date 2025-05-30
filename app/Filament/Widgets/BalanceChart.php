<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Expense;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BalanceChart extends ChartWidget
{
    public function getHeading(): string
    {
        $year = session('selected_year', Carbon::now()->year);
        return "Monthly Balance ($year)";
    }
    protected static string $color = 'primary';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;
    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $year = session('selected_year', Carbon::now()->year);

        // Get monthly invoice amounts
        $monthlyInvoices = Invoice::select(
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

        // Get monthly one-time expenses
        $monthlyOneTimeExpenses = Expense::select(
            DB::raw('MONTH(date) as month'),
            DB::raw('SUM(amount) as total')
        )
            ->where('recurring', false)
            ->whereYear('date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(fn($item) => round($item->total, 2))
            ->toArray();

        // Calculate monthly recurring expenses
        $recurringExpenses = [];
        for ($month = 1; $month <= 12; $month++) {
            $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

            // Get all recurring expenses active in this month
            $total = Expense::where('recurring', true)
                ->where('start_date', '<=', $endOfMonth)
                ->where(function ($query) use ($startOfMonth) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $startOfMonth);
                })
                ->sum('amount');

            $recurringExpenses[$month] = round($total, 2);
        }

        // Calculate total expenses per month (one-time + recurring)
        $monthlyExpenses = [];
        for ($month = 1; $month <= 12; $month++) {
            $oneTimeAmount = $monthlyOneTimeExpenses[$month] ?? 0;
            $recurringAmount = $recurringExpenses[$month] ?? 0;
            $monthlyExpenses[$month] = $oneTimeAmount + $recurringAmount;
        }

        // Calculate balance (income - expenses)
        $monthlyBalance = [];
        $labels = [];
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        for ($month = 1; $month <= 12; $month++) {
            // Check if we should show actual data or zero
            $shouldShowActualData = true;

            // Only for the current year, set future months to zero
            if ($year == $currentYear && $month >= $currentMonth) {
                $shouldShowActualData = false;
            }

            if ($shouldShowActualData) {
                $income = $monthlyInvoices[$month] ?? 0;
                $expenses = $monthlyExpenses[$month] ?? 0;
                $balance = $income - $expenses;
            } else {
                $balance = 0;
            }

            $monthlyBalance[] = $balance;
            $labels[] = Carbon::create()->month($month)->format('F');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Balance (€)',
                    'data' => $monthlyBalance,
                    'backgroundColor' => array_map(function ($value) {
                        return $value >= 0 ? '#10b981' : '#ef4444'; // Green for positive, red for negative
                    }, $monthlyBalance),
                    'borderColor' => array_map(function ($value) {
                        return $value >= 0 ? '#059669' : '#dc2626'; // Darker green/red for borders
                    }, $monthlyBalance),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                    'grid' => [
                        'drawBorder' => false,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { 
                            var value = context.raw;
                            var sign = value >= 0 ? "+" : "";
                            return sign + "€" + value.toLocaleString();
                        }',
                    ],
                ],
            ],
        ];
    }
}