<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ExpenseResource\Widgets\ExpenseStats;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListExpenses extends ListRecords
{
    use ExposesTableToWidgets;
    
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            ExpenseStats::class
        ];
    }
}
