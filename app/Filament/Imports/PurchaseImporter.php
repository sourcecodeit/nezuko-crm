<?php

namespace App\Filament\Imports;

use App\Models\Purchase;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PurchaseImporter extends Importer
{
    protected static ?string $model = Purchase::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('invoice_number')
                ->label('Invoice Number')
                ->requiredMapping()
                ->rules(['required'])
                ->guess(['Numero fattura / Documento', 'Numero fattura/Documento', 'Invoice Number', 'Invoice', 'Numero fattura'])
                ->example('5019823')
                ->castStateUsing(function (?string $state): ?string {
                    return self::cleanValue($state);
                }),

            ImportColumn::make('date')
                ->label('Date')
                ->requiredMapping()
                ->rules(['required'])
                ->guess(['Data emissione', 'Data emissione fattura', 'Date', 'Data'])
                ->example('23/03/2025')
                ->castStateUsing(function (?string $state): ?string {
                    $state = self::cleanValue($state);

                    if (blank($state)) {
                        return null;
                    }

                    try {
                        $parsed = Carbon::createFromFormat('d/m/Y', trim($state))->startOfDay();
                        // Puoi anche tornare un Carbon, ma 'Y-m-d' va benissimo
                        return $parsed->format('Y-m-d');
                    } catch (\Throwable $e) {
                        // la riga poi fallirà se hai regole di validazione,
                        // oppure ti puoi spingere oltre usando RowImportFailedException
                        return null;
                    }
                }),

            ImportColumn::make('supplier')
                ->label('Supplier')
                ->requiredMapping()
                ->rules(['required'])
                ->guess(['Denominazione fornitore', 'Denominazione fornitore estero', 'Supplier', 'Fornitore'])
                ->example('Radius Business Solutions (Italia) SRL')
                ->castStateUsing(fn (?string $state) => self::cleanValue($state)),

            ImportColumn::make('amount')
                ->label('Amount')
                ->requiredMapping()
                ->rules(['required'])
                ->guess(['Imponibile/Importo (totale in euro)', 'Amount', 'Imponibile', 'Importo'])
                ->example('000000000041,39')
                ->castStateUsing(function (?string $state): float {
                    return self::parseItalianNumber($state);
                }),

            ImportColumn::make('tax')
                ->label('Tax')
                ->requiredMapping()
                ->rules(['required'])
                ->guess(['Imposta (totale in euro)', 'Tax', 'Imposta'])
                ->example('000000000009,11')
                ->castStateUsing(function (?string $state): float {
                    return self::parseItalianNumber($state);
                }),
        ];
    }

    public function resolveRecord(): ?Purchase
    {
        $purchase = Purchase::firstOrNew([
            'invoice_number' => $this->data['invoice_number']
        ]);
        
        return $purchase;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your purchase import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    /* ----------------------
     * Helper “static”
     * ---------------------- */

    private static function cleanValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value, " '\"\t\n\r\0\x0B");

        if ($value === '' || $value === 'Non presente') {
            return null;
        }

        return $value;
    }

    private static function parseItalianNumber(?string $number): float
    {
        $number = self::cleanValue($number);

        if ($number === null) {
            return 0.0;
        }

        // "000000000041,39" → "41.39"
        $number = str_replace(',', '.', $number);
        $number = preg_replace('/[^0-9.]/', '', $number);

        return (float) $number;
    }
}
