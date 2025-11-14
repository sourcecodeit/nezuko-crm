<?php

namespace App\Filament\Resources\PurchaseResource\Widgets;

use App\Models\Purchase;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class CategoryAggregateWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected $listeners = ['yearChanged' => '$refresh'];

    protected function getTableHeading(): ?string
    {
        $year = session('purchase_selected_year', Carbon::now()->year);
        return "Purchases by Category ($year)";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('category_name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('purchase_count')
                    ->label('# of Purchases')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),
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
            ->selectRaw('
                purchase_categories.name as category_name,
                COUNT(purchases.id) as purchase_count,
                SUM(purchases.amount) as total_amount
            ')
            ->join('purchase_categories', 'purchases.purchase_category_id', '=', 'purchase_categories.id')
            ->whereYear('purchases.date', $year)
            ->groupBy('purchase_categories.id', 'purchase_categories.name')
            ->orderBy('total_amount', 'desc');
    }

    public function getTableRecordKey($record): string
    {
        return $record->category_name;
    }
}
