<?php

namespace App\Filament\Resources\InventoryCounts\Pages;

use App\Filament\Resources\InventoryCounts\InventoryCountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryCount extends CreateRecord
{
    protected static string $resource = InventoryCountResource::class;
}
