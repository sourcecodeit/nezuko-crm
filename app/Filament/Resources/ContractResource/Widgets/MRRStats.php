<?php

namespace App\Filament\Resources\ContractResource\Widgets;

use App\Filament\Resources\ContractResource\Pages\ListContracts;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Models\Contract;

class MRRStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListContracts::class;
    }

    protected function getStats(): array
    {
        // Calculate MRR by summing annual contract values divided by 12
        $contracts = $this->getPageTableQuery()
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
        
        return [
            Stat::make('Monthly Recurring Revenue (MRR)', '€' . number_format($mrr, 2))
                ->description('Based on active recurring contracts')
                ->color('success')
                ->chart([7, 8, 9, 10, 11, 12, 12.5]),
        ];
    }
}