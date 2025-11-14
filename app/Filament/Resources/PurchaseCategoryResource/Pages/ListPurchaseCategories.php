<?php

namespace App\Filament\Resources\PurchaseCategoryResource\Pages;

use App\Filament\Resources\PurchaseCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseCategories extends ListRecords
{
    protected static string $resource = PurchaseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
