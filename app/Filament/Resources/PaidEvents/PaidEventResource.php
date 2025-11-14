<?php

namespace App\Filament\Resources\PaidEvents;

use App\Filament\Resources\PaidEvents\Pages\CreatePaidEvent;
use App\Filament\Resources\PaidEvents\Pages\EditPaidEvent;
use App\Filament\Resources\PaidEvents\Pages\ListPaidEvents;
use App\Filament\Resources\PaidEvents\Pages\ManagePaidEventRegistrations;
use App\Filament\Resources\PaidEvents\Schemas\PaidEventForm;
use App\Filament\Resources\PaidEvents\Tables\PaidEventsTable;
use App\Models\PaidEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaidEventResource extends Resource
{
    protected static ?string $model = PaidEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Paid Events';

    protected static ?string $modelLabel = 'Paid Event';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'semester', 'description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Semester' => $record->semester,
            'Status' => ucfirst($record->status),
            'Deadline' => optional($record->registration_deadline)->format('M j, Y g:i A'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return PaidEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaidEventsTable::configure($table);
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
            'index' => ListPaidEvents::route('/'),
            'create' => CreatePaidEvent::route('/create'),
            'edit' => EditPaidEvent::route('/{record}/edit'),
            'registrations' => ManagePaidEventRegistrations::route('/{record}/registrations'),
        ];
    }
}
