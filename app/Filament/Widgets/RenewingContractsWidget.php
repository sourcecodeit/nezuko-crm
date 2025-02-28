<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RenewingContractsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?int $sort = 3;
    protected function getStats(): array
    {
        $selectedYear = session('selected_year', Carbon::now()->year);
        $currentMonth = Carbon::now()->month;
        $monthName = Carbon::createFromDate($selectedYear, $currentMonth, 1)->format('F');

        // Get active recurring contracts
        $activeContracts = Contract::query()
            ->where('active', true)
            ->where('recurring', true)
            ->get();

        // Filter contracts that need to be paid in the current month
        $contractsDueThisMonth = collect();
        $monthlyAmount = 0;

        foreach ($activeContracts as $contract) {
            $startDate = Carbon::parse($contract->start_date);

            // Skip if contract hasn't started yet
            if (
                $startDate->year > $selectedYear ||
                ($startDate->year == $selectedYear && $startDate->month > $currentMonth)
            ) {
                continue;
            }

            // If end date exists, skip if contract already ended
            if ($contract->end_date) {
                $endDate = Carbon::parse($contract->end_date);
                if (
                    $endDate->year < $selectedYear ||
                    ($endDate->year == $selectedYear && $endDate->month < $currentMonth)
                ) {
                    continue;
                }
            }

            // Check if payment is due this month based on billing period
            $isDue = false;
            switch ($contract->billing_period) {
                case 'monthly':
                    $isDue = true;
                    break;
                case 'bimonthly':
                    $monthDiff = ($selectedYear - $startDate->year) * 12 + ($currentMonth - $startDate->month);
                    $isDue = $monthDiff % 2 == 0;
                    break;
                case 'quarterly':
                    $monthDiff = ($selectedYear - $startDate->year) * 12 + ($currentMonth - $startDate->month);
                    $isDue = $monthDiff % 3 == 0;
                    break;
                case 'half-yearly':
                    $monthDiff = ($selectedYear - $startDate->year) * 12 + ($currentMonth - $startDate->month);
                    $isDue = $monthDiff % 6 == 0;
                    break;
                case 'yearly':
                    $isDue = $startDate->month == $currentMonth;
                    break;
            }

            if ($isDue) {
                $contractsDueThisMonth->push($contract);
                $monthlyAmount += $contract->price;
            }
        }

        // Prepare monthly data for chart (past 12 months)
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $pastMonth = Carbon::now()->subMonths($i);
            $pastYear = $pastMonth->year;
            $pastMonthNum = $pastMonth->month;

            $amount = 0;
            foreach ($activeContracts as $contract) {
                $startDate = Carbon::parse($contract->start_date);

                // Skip if contract hasn't started yet in that month
                if (
                    $startDate->year > $pastYear ||
                    ($startDate->year == $pastYear && $startDate->month > $pastMonthNum)
                ) {
                    continue;
                }

                // If end date exists, skip if contract already ended
                if ($contract->end_date) {
                    $endDate = Carbon::parse($contract->end_date);
                    if (
                        $endDate->year < $pastYear ||
                        ($endDate->year == $pastYear && $endDate->month < $pastMonthNum)
                    ) {
                        continue;
                    }
                }

                // Check if payment was due that month based on billing period
                $wasDue = false;
                switch ($contract->billing_period) {
                    case 'monthly':
                        $wasDue = true;
                        break;
                    case 'bimonthly':
                        $monthDiff = ($pastYear - $startDate->year) * 12 + ($pastMonthNum - $startDate->month);
                        $wasDue = $monthDiff % 2 == 0;
                        break;
                    case 'quarterly':
                        $monthDiff = ($pastYear - $startDate->year) * 12 + ($pastMonthNum - $startDate->month);
                        $wasDue = $monthDiff % 3 == 0;
                        break;
                    case 'half-yearly':
                        $monthDiff = ($pastYear - $startDate->year) * 12 + ($pastMonthNum - $startDate->month);
                        $wasDue = $monthDiff % 6 == 0;
                        break;
                    case 'yearly':
                        $wasDue = $startDate->month == $pastMonthNum;
                        break;
                }

                if ($wasDue) {
                    $amount += $contract->price;
                }
            }

            $chartData[] = $amount;
        }

        return [
            Stat::make("Contracts due in $monthName", '€' . number_format($monthlyAmount, 2))
                ->description($contractsDueThisMonth->count() . ' contracts to be paid')
                ->color('success')
                ->chart($chartData),
        ];
    }
}