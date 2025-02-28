<?php

namespace App\Filament\Resources\ContractResource\Widgets;

use App\Models\Contract;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MRRStats extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        // Calculate MRR by summing annual contract values divided by 12
        $contracts = Contract::query()
            ->where('active', true)
            ->where('recurring', true)
            ->get();
        
        $annualTotal = 0;
        
        foreach ($contracts as $contract) {
            // Convert different billing periods to annual values
            switch ($contract->billing_period) {
                case 'monthly':
                    $annualTotal += $contract->price * 12;
                    break;
                case 'bimonthly':
                    $annualTotal += $contract->price * 6;
                    break;
                case 'quarterly':
                    $annualTotal += $contract->price * 4;
                    break;
                case 'half-yearly':
                    $annualTotal += $contract->price * 2;
                    break;
                case 'yearly':
                    $annualTotal += $contract->price;
                    break;
            }
        }
        
        $mrr = $annualTotal / 12;
        
        // Get monthly contract values for chart
        $monthlyData = [];
        for ($i = 12; $i >= 1; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyTotal = 0;
            
            // Find contracts active in this month
            foreach ($contracts as $contract) {
                $startDate = Carbon::parse($contract->start_date);
                $endDate = $contract->end_date ? Carbon::parse($contract->end_date) : null;
                
                // Skip if contract wasn't active yet or already ended
                if ($startDate->greaterThan($date) || ($endDate && $endDate->lessThan($date))) {
                    continue;
                }
                
                // Calculate monthly value based on billing period
                switch ($contract->billing_period) {
                    case 'monthly':
                        $monthlyTotal += $contract->price;
                        break;
                    case 'bimonthly':
                        $monthlyTotal += $contract->price / 2;
                        break;
                    case 'quarterly':
                        $monthlyTotal += $contract->price / 3;
                        break;
                    case 'half-yearly':
                        $monthlyTotal += $contract->price / 6;
                        break;
                    case 'yearly':
                        $monthlyTotal += $contract->price / 12;
                        break;
                }
            }
            
            $monthlyData[] = $monthlyTotal;
        }
        
        return [
            Stat::make('Monthly Recurring Revenue (MRR)', '€' . number_format($mrr, 2))
                ->description('Based on ' . $contracts->count() . ' active recurring contracts')
                ->color('success')
                ->chart($monthlyData),
        ];
    }
}