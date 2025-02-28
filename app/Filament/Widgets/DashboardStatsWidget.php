<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    protected static ?int $sort = 1;
    
    // Ensure column span is set correctly (allow stats to be laid out horizontally)
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $selectedYear = session('selected_year', Carbon::now()->year);
        $currentMonth = Carbon::now()->month;
        $monthName = Carbon::createFromDate($selectedYear, $currentMonth, 1)->format('F');
        
        // ====== TOTAL INVOICES STAT ======
        $invoices = Invoice::query()
            ->whereYear('date', $selectedYear)
            ->get();
        
        $totalAmount = $invoices->sum('amount');
        
        $monthlyInvoiceData = Invoice::query()
            ->select(DB::raw('MONTH(date) as month'), DB::raw('SUM(amount) as total'))
            ->whereYear('date', $selectedYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();
            
        $invoiceChartData = array_fill(1, 12, 0);
        foreach ($monthlyInvoiceData as $month => $total) {
            $invoiceChartData[$month] = $total;
        }
        
        // ====== UNPAID INVOICES STAT ======
        $unpaidAmount = $invoices->where('paid', false)->sum('amount');
        $unpaidPercentage = $totalAmount > 0 ? round(($unpaidAmount / $totalAmount) * 100) : 0;
        
        $unpaidMonthlyData = Invoice::query()
            ->select(DB::raw('MONTH(date) as month'), DB::raw('SUM(amount) as total'))
            ->whereYear('date', $selectedYear)
            ->where('paid', false)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();
            
        $unpaidChartData = array_fill(1, 12, 0);
        foreach ($unpaidMonthlyData as $month => $total) {
            $unpaidChartData[$month] = $total;
        }
        
        // ====== CONTRACTS DUE STAT ======
        $activeContracts = Contract::query()
            ->where('active', true)
            ->where('recurring', true)
            ->get();
        
        $contractsDueThisMonth = collect();
        $monthlyAmount = 0;
        
        foreach ($activeContracts as $contract) {
            $startDate = Carbon::parse($contract->start_date);
            
            // Skip if contract hasn't started yet
            if ($startDate->year > $selectedYear || 
                ($startDate->year == $selectedYear && $startDate->month > $currentMonth)) {
                continue;
            }
            
            // If end date exists, skip if contract already ended
            if ($contract->end_date) {
                $endDate = Carbon::parse($contract->end_date);
                if ($endDate->year < $selectedYear || 
                    ($endDate->year == $selectedYear && $endDate->month < $currentMonth)) {
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
        
        // Prepare monthly data for contracts chart (past 12 months)
        $contractsChartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $pastMonth = Carbon::now()->subMonths($i);
            $pastYear = $pastMonth->year;
            $pastMonthNum = $pastMonth->month;
            
            $amount = 0;
            foreach ($activeContracts as $contract) {
                $startDate = Carbon::parse($contract->start_date);
                
                if ($startDate->year > $pastYear || 
                    ($startDate->year == $pastYear && $startDate->month > $pastMonthNum)) {
                    continue;
                }
                
                if ($contract->end_date) {
                    $endDate = Carbon::parse($contract->end_date);
                    if ($endDate->year < $pastYear || 
                        ($endDate->year == $pastYear && $endDate->month < $pastMonthNum)) {
                        continue;
                    }
                }
                
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
            
            $contractsChartData[] = $amount;
        }
        
        return [
            Stat::make('Total Invoices ' . $selectedYear, '€' . number_format($totalAmount, 2))
                ->description('All invoices from ' . $selectedYear)
                ->color('primary')
                ->chart(array_values($invoiceChartData)),
                
            Stat::make('Unpaid Invoices', '€' . number_format($unpaidAmount, 2))
                ->description($unpaidPercentage . '% of total amount')
                ->color('danger')
                ->chart(array_values($unpaidChartData)),
                
            Stat::make("Contracts due in $monthName", '€' . number_format($monthlyAmount, 2))
                ->description($contractsDueThisMonth->count() . ' contracts to be paid')
                ->color('success')
                ->chart($contractsChartData),
        ];
    }
}