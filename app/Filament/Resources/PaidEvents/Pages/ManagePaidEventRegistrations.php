<?php

namespace App\Filament\Resources\PaidEvents\Pages;

use App\Filament\Resources\PaidEvents\PaidEventResource;
use App\Filament\Resources\PaidEvents\Resources\Registrations\RegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;

class ManagePaidEventRegistrations extends ManageRelatedRecords
{
    protected static string $resource = PaidEventResource::class;

    protected static string $relationship = 'registrations';

    protected static ?string $relatedResource = RegistrationResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
