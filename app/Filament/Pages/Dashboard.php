<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\View\View;

class Dashboard extends BaseDashboard
{
    public ?int $selectedYear = null;
    public static ?string $title = "";
    public static function getNavigationLabel(): string
    {
        return __('filament-panels::pages/dashboard.title');
    }
    
    protected static string $view = 'filament.pages.dashboard';
    
    public function mount(): void
    {
        $this->selectedYear = session('selected_year', Carbon::now()->year);
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }

    protected function getTopWidgets(): array
    {
        return [
            \App\Filament\Widgets\UnpaidInvoicesWidget::class,
        ];
    }

    public function getBodyWidgets(): array
    {
        return [
            \App\Filament\Widgets\BalanceChart::class,
            \App\Filament\Widgets\MonthlyInvoiceChart::class,
            \App\Filament\Widgets\CustomerInvoiceChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 3;
    }
    
    public function updatedSelectedYear(): void
    {
        // Store the selected year in the session and redirect to refresh the page
        session(['selected_year' => $this->selectedYear]);
        $this->redirect(route('filament.admin.pages.dashboard'));
    }
    
    protected function getViewData(): array
    {
        $currentYear = Carbon::now()->year;
        
        $years = [
            $currentYear => $currentYear,
            ($currentYear - 1) => ($currentYear - 1),
            ($currentYear - 2) => ($currentYear - 2),
        ];
        
        return [
            'years' => $years,
        ];
    }
}