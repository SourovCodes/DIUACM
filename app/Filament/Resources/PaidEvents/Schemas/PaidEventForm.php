<?php

namespace App\Filament\Resources\PaidEvents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PaidEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('semester')
                    ->required()
                    ->placeholder('e.g., Fall 2023, Spring 2024'),
                RichEditor::make('description')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'link',
                        'bulletList',
                        'orderedList',
                        'h2',
                        'h3',
                        'blockquote',
                        'codeBlock',
                    ])
                    ->columnSpanFull(),
                DateTimePicker::make('registration_start_time')
                    ->required()
                    ->native(false),
                DateTimePicker::make('registration_deadline')
                    ->required()
                    ->native(false)
                    ->after('registration_start_time'),
                TextInput::make('registration_limit')
                    ->numeric()
                    ->minValue(1)
                    ->placeholder('Leave empty for unlimited'),
                Select::make('status')
                    ->required()
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'closed' => 'Closed',
                    ])
                    ->default('draft')
                    ->native(false),
            ]);
    }
}
