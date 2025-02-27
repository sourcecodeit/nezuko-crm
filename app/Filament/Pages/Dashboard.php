<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\View\View;

class Dashboard extends BaseDashboard
{
    public ?int $selectedYear = null;
    
    protected static string $view = 'filament.pages.dashboard';
    
    public function mount(): void
    {
        $this->selectedYear = session('selected_year', Carbon::now()->year);
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            // Any specific header widgets you want to add
        ];
    }
    
    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
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