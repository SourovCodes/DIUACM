<?php

namespace App\Filament\Resources\PaidEvents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
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
                            ->seconds(false)
                            ->displayFormat('M j, Y g:i A')
                            ->timezone('Asia/Dhaka')
                            ->label('Registration Start')
                            ->required(),
                        DateTimePicker::make('registration_deadline')
                            ->seconds(false)
                            ->displayFormat('M j, Y g:i A')
                            ->timezone('Asia/Dhaka')
                            ->label('Registration Deadline')
                            ->after('registration_start_time')
                            ->required(),
                        TextInput::make('registration_limit')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Leave empty for unlimited'),
                        TextInput::make('registration_fee')
                            ->numeric()
                            ->prefix('৳')
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText('Enter 0 for free events'),
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

                Section::make('Registration Form Configuration')
                    ->columnSpanFull()
                    ->description('Configure the registration form fields and validation rules')
                    ->collapsed()
                    ->schema([
                        TextInput::make('student_id_rules')
                            ->label('Student ID Validation Rules')
                            ->placeholder('regex:/^[0-9-]+$/')
                            ->columnSpan(2)
                            ->helperText('Enter a regex pattern to validate student IDs (e.g., regex:/^[0-9-]+$/)'),

                        TextInput::make('student_id_rules_guide')
                            ->label('Student ID Rules Guide')
                            ->columnSpan(2)
                            ->placeholder('Student ID must contain only numbers and dashes')
                            ->helperText('Provide instructions for users on how to format their student ID'),

                        Repeater::make('pickup_points')
                            ->label('Pickup Points')
                            ->columnSpan(2)
                            ->grid(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Location Name')
                                    ->required(),
                            ])
                            ->addActionLabel('Add Pickup Point')
                            ->helperText('Add pickup locations where participants can collect their items'),

                        Repeater::make('departments')
                            ->label('Departments')
                            ->columnSpan(2)
                            ->grid(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Department Name')
                                    ->required(),
                            ])
                            ->addActionLabel('Add Department')
                            ->helperText('Add available departments for registration'),

                        Repeater::make('sections')
                            ->label('Sections')
                            ->columnSpan(2)
                            ->grid(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Section Name')
                                    ->required(),
                            ])
                            ->addActionLabel('Add Section')
                            ->helperText('Add available sections for registration'),

                        Repeater::make('lab_teacher_names')
                            ->label('Lab Teachers')
                            ->columnSpan(2)
                            ->grid(2)
                            ->columns(2)
                            ->schema([
                                TextInput::make('initial')
                                    ->label('Initial')
                                    ->required(),
                                TextInput::make('full_name')
                                    ->label('Full Name')
                                    ->required(),
                            ])
                            ->addActionLabel('Add Teacher')
                            ->helperText('Add lab teacher information for registration'),
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
                        SpatieMediaLibraryFileUpload::make('tshirt_size_guideline')
                            ->label('T-shirt Size Guideline')
                            ->collection('tshirt_size_guideline')
                            ->image()
                            ->imageEditor()
                            ->openable()
                            ->visibility(visibility: 'public')
                            ->helperText('Upload a size chart image to help participants choose their T-shirt size'),
                    ])
                    ->columns(2),
            ]);
    }
}
