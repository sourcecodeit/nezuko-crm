<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\InvoiceResource\Widgets\InvoiceStats;
use Filament\Pages\Concerns\ExposesTableToWidgets;


class ListInvoices extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InvoiceStats::class
        ];
    }
}
