<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Filament\Imports\PurchaseImporter;
use App\Models\Purchase;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListPurchases extends ListRecords
{
    protected static string $resource = PurchaseResource::class;

    protected static string $view = 'filament.resources.purchase-resource.pages.list-purchases';

    public function mount(): void
    {
        parent::mount();
        
        // Initialize selected year if not set
        if (!session()->has('purchase_selected_year')) {
            session(['purchase_selected_year' => Carbon::now()->year]);
        }
    }

    protected function getHeaderActions(): array
    {
        // Get available years from purchases
        $years = Purchase::selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();
        
        // Add current year if not in list
        $currentYear = Carbon::now()->year;
        if (!isset($years[$currentYear])) {
            $years = [$currentYear => $currentYear] + $years;
        }

        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
                ->importer(PurchaseImporter::class)
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success'),
            Actions\Action::make('selectYear')
                ->label('Select Year')
                ->form([
                    Select::make('year')
                        ->label('Year')
                        ->options($years)
                        ->default(session('purchase_selected_year', $currentYear))
                        ->reactive(),
                ])
                ->action(function (array $data): void {
                    session(['purchase_selected_year' => $data['year']]);
                    $this->dispatch('yearChanged');
                })
                ->modalHeading('Select Year')
                ->modalSubmitActionLabel('Apply')
                ->color('gray')
                ->icon('heroicon-o-calendar'),
            Actions\Action::make('viewDetails')
                ->label('View Detailed Purchases')
                ->icon('heroicon-o-list-bullet')
                ->color('info')
                ->url(fn (): string => PurchaseResource::getUrl('details'))
                ->openUrlInNewTab(false),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PurchaseResource\Widgets\PurchaseStatsWidget::class,
            PurchaseResource\Widgets\MonthlyPurchaseChart::class,
            PurchaseResource\Widgets\SupplierAggregateWidget::class,
        ];
    }
}
