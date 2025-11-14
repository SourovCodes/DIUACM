<?php

namespace App\Filament\Resources\PaidEvents\Resources\Registrations\Tables;

use App\Enums\PaymentMethod;
use App\Enums\RegistrationStatus;
use App\Filament\Resources\PaidEvents\PaidEventResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->limit(40)
                    ->weight('medium'),

                TextColumn::make('email')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (?PaymentMethod $state): ?string => $state?->getLabel())
                    ->color(fn (?PaymentMethod $state): string|array|null => $state?->getColor())
                    ->icon(fn (?PaymentMethod $state): ?string => $state?->getIcon())
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?RegistrationStatus $state): ?string => $state?->getLabel())
                    ->color(fn (?RegistrationStatus $state): string|array|null => $state?->getColor())
                    ->icon(fn (?RegistrationStatus $state): ?string => $state?->getIcon())
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y g:i A')
                    ->timezone('Asia/Dhaka')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('paid_event')
                    ->label('Paid Event')
                    ->icon('heroicon-o-ticket')
                    ->url(fn ($record) => PaidEventResource::getUrl('edit', ['record' => $record->paid_event_id]))
                    ->color('gray'),
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->emptyStateHeading('No registrations')
            ->emptyStateDescription('Create the first registration for this paid event.')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }
}
