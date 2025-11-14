<?php

namespace App\Filament\Resources\PurchaseResource\Widgets;

use App\Models\Purchase;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class SupplierAggregateWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected $listeners = ['yearChanged' => '$refresh'];

    protected function getTableHeading(): ?string
    {
        $year = session('purchase_selected_year', Carbon::now()->year);
        return "Purchases by Supplier ($year)";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
            ])
            ->defaultSort('total_amount', 'desc')
            ->paginated(false);
    }

    protected function getTableQuery(): Builder
    {
        $year = session('purchase_selected_year', Carbon::now()->year);

        return Purchase::query()
            ->selectRaw('supplier, SUM(amount) as total_amount')
            ->whereYear('date', $year)
            ->groupBy('supplier')
            ->orderBy('total_amount', 'desc');
    }

    public function getTableRecordKey($record): string
    {
        return $record->supplier;
    }
}
