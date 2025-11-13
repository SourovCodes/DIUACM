<?php

namespace App\Filament\Resources\PaidEvents\Pages;

use App\Filament\Resources\PaidEvents\PaidEventResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPaidEvent extends ViewRecord
{
    protected static string $resource = PaidEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
