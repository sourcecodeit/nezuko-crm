<?php

namespace App\Filament\Resources\ExpenseResource\Widgets;

use App\Filament\Resources\ExpenseResource\Pages\ListExpenses;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Models\Expense;
use Carbon\Carbon;

class ExpenseStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListExpenses::class;
    }

    protected function getStats(): array
    {
        $today = Carbon::today();
        $startOfYear = Carbon::now()->startOfYear();
        
        // Get active recurring expenses as of today
        $activeRecurringExpenses = Expense::where('recurring', true)
            ->where('start_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $today);
            })
            ->sum('amount');
        
        // All expenses (both recurring and one-time) from the beginning of the current year
        $totalExpensesThisYear = Expense::where(function($query) use ($startOfYear) {
                // For non-recurring expenses, check the date
                $query->where(function($q) use ($startOfYear) {
                    $q->where('recurring', false)
                      ->where('date', '>=', $startOfYear);
                })
                // For recurring expenses, check if they were active during this year
                ->orWhere(function($q) use ($startOfYear) {
                    $q->where('recurring', true)
                      ->where(function($qq) use ($startOfYear) {
                          $qq->where('start_date', '>=', $startOfYear)
                             ->orWhere(function($qqq) use ($startOfYear) {
                                 $qqq->where('start_date', '<', $startOfYear)
                                     ->where(function($qqqq) use ($startOfYear) {
                                         $qqqq->whereNull('end_date')
                                              ->orWhere('end_date', '>=', $startOfYear);
                                     });
                             });
                      });
                });
            })
            ->sum('amount');
        
        // All one-time expenses
        $totalOneTimeExpenses = Expense::where('recurring', false)->sum('amount');

        return [
            Stat::make('Active Recurring Expenses', '€' . number_format($activeRecurringExpenses, 2))
                ->description('Total amount of active recurring expenses')
                ->color('warning')
                ->chart([5, 7, 8, 10, 12, 15, 18]),

            Stat::make('Total Expenses (This Year)', '€' . number_format($totalExpensesThisYear, 2))
                ->description('Total amount of all expenses since Jan 1')
                ->color('danger')
                ->chart([8, 10, 12, 15, 18, 20, 22]),

            Stat::make('One-Time Expenses', '€' . number_format($totalOneTimeExpenses, 2))
                ->description('Total amount of one-time expenses')
                ->color('primary')
                ->chart([3, 5, 7, 10, 12, 14, 16]),
        ];
    }
}