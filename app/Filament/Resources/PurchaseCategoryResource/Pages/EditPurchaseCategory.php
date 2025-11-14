<?php

namespace App\Filament\Resources\PurchaseCategoryResource\Pages;

use App\Filament\Resources\PurchaseCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseCategory extends EditRecord
{
    protected static string $resource = PurchaseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
