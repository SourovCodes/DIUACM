<?php

namespace App\Filament\Resources\PaidEvents\Pages;

use App\Filament\Resources\PaidEvents\PaidEventResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaidEvent extends EditRecord
{
    protected static string $resource = PaidEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('registrations')
                ->label('Manage Registrations')
                ->icon('heroicon-o-clipboard')
                ->url(fn () => PaidEventResource::getUrl('registrations', ['record' => $this->record])),
            DeleteAction::make(),
        ];
    }
}
