<?php

namespace App\Filament\Resources\PaidEvents\Pages;

use App\Filament\Resources\PaidEvents\PaidEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaidEvent extends CreateRecord
{
    protected static string $resource = PaidEventResource::class;
}
