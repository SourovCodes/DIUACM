<?php

namespace App\Filament\Resources\PaidEvents\Pages;

use App\Filament\Resources\PaidEvents\PaidEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaidEvent extends EditRecord
{
    protected static string $resource = PaidEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
