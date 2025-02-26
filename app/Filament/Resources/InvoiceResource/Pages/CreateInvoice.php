<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;
use App\Models\Invoice;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Get the year from the invoice date
        $invoiceYear = Carbon::parse($data['date'])->year;

        // Find the highest invoice number for the current year
        $lastInvoice = Invoice::whereYear('date', $invoiceYear)
            ->orderBy('number', 'desc')
            ->first();

        // Set the new invoice number (increment or start from 1)
        $data['number'] = $lastInvoice ? ($lastInvoice->number + 1) : 1;

        return $data;
    }
}
