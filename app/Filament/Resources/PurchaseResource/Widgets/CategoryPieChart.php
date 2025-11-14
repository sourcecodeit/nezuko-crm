<?php

namespace App\Filament\Resources\PurchaseResource\Widgets;

use App\Models\Purchase;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CategoryPieChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    protected $listeners = ['yearChanged' => '$refresh'];

    public function getHeading(): string 
    {
        $year = session('purchase_selected_year', Carbon::now()->year);
        return "Top Purchase Categories ($year)";
    }
    
    protected function getData(): array
    {
        $year = session('purchase_selected_year', Carbon::now()->year);
        
        // Get top 5 categories by total amount for the selected year
        $topCategories = Purchase::whereYear('date', $year)
            ->whereNotNull('purchase_category_id')
            ->select('purchase_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('purchase_category_id')
            ->orderBy('total', 'desc')
            ->limit(7)
            ->with('category')
            ->get();

        $labels = [];
        $amounts = [];
        $colors = [
            'rgba(59, 130, 246, 0.8)',   // Blue
            'rgba(16, 185, 129, 0.8)',   // Green
            'rgba(249, 115, 22, 0.8)',   // Orange
            'rgba(239, 68, 68, 0.8)',    // Red
            'rgba(168, 85, 247, 0.8)',   // Purple
            'rgba(236, 72, 153, 0.8)',   // Pink
            'rgba(245, 158, 11, 0.8)',   // Amber
        ];

        foreach ($topCategories as $index => $item) {
            $labels[] = $item->category ? $item->category->name : 'Uncategorized';
            $amounts[] = round($item->total, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Category Spending',
                    'data' => $amounts,
                    'backgroundColor' => array_slice($colors, 0, count($amounts)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) { 
                            let label = context.label || '';
                            let value = context.parsed || 0;
                            return label + ': €' + value.toLocaleString('it-IT', {minimumFractionDigits: 2, maximumFractionDigits: 2}); 
                        }",
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
        ];
    }

    protected function getHeight(): ?int
    {
        return 400;
    }
}
