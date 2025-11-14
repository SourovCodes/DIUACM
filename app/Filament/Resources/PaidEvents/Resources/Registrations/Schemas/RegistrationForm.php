<?php

namespace App\Filament\Resources\PaidEvents\Resources\Registrations\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\RegistrationStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Registrant')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Grid::make()
                            ->schema([
                                TextInput::make('phone')
                                    ->maxLength(32),
                                TextInput::make('student_id')
                                    ->maxLength(64),
                            ]),

                        Grid::make()
                            ->schema([
                                Select::make('payment_method')
                                    ->options(fn () => array_combine(array_map(fn ($c) => $c->value, PaymentMethod::cases()), array_map(fn ($c) => $c->getLabel(), PaymentMethod::cases())))
                                    ->searchable()
                                    ->nullable(),

                                Select::make('status')
                                    ->options(fn () => array_combine(array_map(fn ($c) => $c->value, RegistrationStatus::cases()), array_map(fn ($c) => $c->getLabel(), RegistrationStatus::cases())))
                                    ->searchable()
                                    ->required(),
                            ]),
                    ]),

                Section::make('Metadata')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('M j, Y g:i A', timezone: 'Asia/Dhaka'),
                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('M j, Y g:i A', timezone: 'Asia/Dhaka'),
                            ]),
                    ])
                    ->collapsed()
                    ->hiddenOn('create'),
            ]);
    }
}
