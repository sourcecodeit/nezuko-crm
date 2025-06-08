<?php

namespace App\Filament\Resources\ContractResource\Widgets;

use App\Models\Contract;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ContractsDueThisMonth extends TableWidget
{
    protected static ?string $heading = 'Contracts Due This Month';

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|null
    {
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;

        $contracts = Contract::query()
            ->where('active', true)
            ->where('recurring', true)
            ->get();

        $dueIds = [];

        foreach ($contracts as $contract) {
            $startDate = Carbon::parse($contract->start_date);

            if ($startDate->year > $year || ($startDate->year === $year && $startDate->month > $month)) {
                continue;
            }

            if ($contract->end_date) {
                $endDate = Carbon::parse($contract->end_date);
                if ($endDate->year < $year || ($endDate->year === $year && $endDate->month < $month)) {
                    continue;
                }
            }

            $diffMonths = ($year - $startDate->year) * 12 + ($month - $startDate->month);

            $isDue = match ($contract->billing_period) {
                'monthly' => true,
                'bimonthly' => $diffMonths % 2 === 0,
                'quarterly' => $diffMonths % 3 === 0,
                'half-yearly' => $diffMonths % 6 === 0,
                'yearly' => $startDate->month === $month,
                default => false,
            };

            if ($isDue) {
                $dueIds[] = $contract->id;
            }
        }

        return Contract::query()->whereIn('id', $dueIds)->with('customer');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')->label('Contract')->searchable(),
                TextColumn::make('customer.name')->label('Customer')->searchable(),
                TextColumn::make('price')->money('EUR'),
                TextColumn::make('billing_period')->label('Period'),
                TextColumn::make('start_date')->date('Y-m-d')->label('Start'),
                TextColumn::make('end_date')->date('Y-m-d')->label('End'),
            ])
            ->defaultSort('name');
    }
}
