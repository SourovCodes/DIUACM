<?php

namespace App\Filament\Resources\PaidEvents\Resources\Registrations;

use App\Filament\Resources\PaidEvents\PaidEventResource;
use App\Filament\Resources\PaidEvents\Resources\Registrations\Pages\CreateRegistration;
use App\Filament\Resources\PaidEvents\Resources\Registrations\Pages\EditRegistration;
use App\Filament\Resources\PaidEvents\Resources\Registrations\Schemas\RegistrationForm;
use App\Filament\Resources\PaidEvents\Resources\Registrations\Tables\RegistrationsTable;
use App\Models\Registration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $parentResource = PaidEventResource::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Registrations';

    protected static ?string $modelLabel = 'Registration';

    protected static ?string $pluralModelLabel = 'Registrations';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Paid Event' => $record->paidEvent?->title,
            'Status' => (string) $record->status,
            'Amount' => (string) $record->amount,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function form(Schema $schema): Schema
    {
        return RegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegistrationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateRegistration::route('/create'),
            'edit' => EditRegistration::route('/{record}/edit'),
        ];
    }
}
