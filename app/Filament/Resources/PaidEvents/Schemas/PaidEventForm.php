<?php

namespace App\Filament\Resources\PaidEvents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PaidEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event Details')
                    ->schema([
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
                    ])
                    ->columns(2),

                Section::make('Registration Settings')
                    ->schema([
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
                    ])
                    ->columns(2),

                Section::make('Media')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('banner_image')
                            ->label('Banner Image')
                            ->collection('banner_image')
                            ->image()
                            ->imageEditor()
                            ->openable()
                            ->imageEditorAspectRatios([
                                '10:7',
                                '16:9',
                                '4:3',
                            ])
                            ->visibility(visibility: 'public')
                            ->helperText('Recommended size: 1000x700px (10:7 aspect ratio)'),
                    ]),
            ]);
    }
}
