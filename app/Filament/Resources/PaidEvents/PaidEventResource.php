<?php

namespace App\Filament\Resources\PaidEvents;

use App\Filament\Resources\PaidEvents\Pages\CreatePaidEvent;
use App\Filament\Resources\PaidEvents\Pages\EditPaidEvent;
use App\Filament\Resources\PaidEvents\Pages\ListPaidEvents;
use App\Filament\Resources\PaidEvents\Pages\ViewPaidEvent;
use App\Filament\Resources\PaidEvents\Schemas\PaidEventForm;
use App\Filament\Resources\PaidEvents\Schemas\PaidEventInfolist;
use App\Filament\Resources\PaidEvents\Tables\PaidEventsTable;
use App\Models\PaidEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaidEventResource extends Resource
{
    protected static ?string $model = PaidEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Paid Events';

    protected static ?string $modelLabel = 'Paid Event';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return PaidEventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PaidEventInfolist::configure($schema);
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
            'view' => ViewPaidEvent::route('/{record}'),
            'edit' => EditPaidEvent::route('/{record}/edit'),
        ];
    }
}
