<?php

namespace App\Filament\Resources\PaidEvents\Pages;

use App\Filament\Resources\PaidEvents\PaidEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaidEvents extends ListRecords
{
    protected static string $resource = PaidEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
