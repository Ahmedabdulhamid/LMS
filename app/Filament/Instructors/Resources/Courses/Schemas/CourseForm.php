<?php

namespace App\Filament\Instructors\Resources\Courses\Schemas;

use App\Models\Course;
use App\Services\CategoryService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make(__('instructor.courses.sections.details'))
                        ->icon('heroicon-o-information-circle')
                        ->columns(2)
                        ->schema([
                            TextInput::make('title')
                                ->label(__('instructor.courses.fields.title'))
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Select::make('category_id')
                                ->label(__('instructor.courses.fields.category'))
                                ->options(fn (): array => app(CategoryService::class)->retrievCategories()),

                            Select::make('lang')
                                ->label(__('instructor.courses.fields.language'))
                                ->options([
                                    'ar' => __('instructor.languages.ar'),
                                    'en' => __('instructor.languages.en'),
                                    'fr' => __('instructor.languages.fr'),
                                ])
                                ->required(),
                            Select::make('level')
                                ->label(__('instructor.courses.fields.level'))
                                ->options([
                                    'beginner' => __('instructor.levels.beginner'),
                                    'intermediate' => __('instructor.levels.intermediate'),
                                    'advanced' => __('instructor.levels.advanced'),
                                ])
                                ->required(),
                            Toggle::make('is_published')
                                ->label(__('instructor.courses.fields.published'))
                                ->default(false),
                            TextInput::make('price')
                                ->label(__('instructor.courses.fields.price'))
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->prefix('$'),
                            TextInput::make('price_after_discount')
                                ->label(__('instructor.courses.fields.discount_price'))
                                ->numeric()
                                ->minValue(0)
                                ->lt('price')
                                ->prefix('$'),
                            Textarea::make('description')
                                ->label(__('instructor.courses.fields.description'))
                                ->required()
                                ->rows(6)
                                ->columnSpanFull(),
                            FileUpload::make('thumbnail')
                                ->label(__('instructor.courses.fields.thumbnail'))
                                ->disk(config('lms-upload.disk'))
                                ->directory('courses/thumbnails')
                                ->image()
                                ->imageEditor()
                                ->maxSize(5120)
                                ->columnSpanFull(),
                        ]),

                    Step::make(__('instructor.courses.sections.learning_goals'))
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Repeater::make('goals')
                                ->label(__('instructor.courses.sections.learning_goals'))
                                ->relationship()
                                ->orderColumn('order')
                                ->schema([
                                    TextInput::make('goal')
                                        ->label(__('instructor.courses.fields.goal'))
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->reorderable()
                                ->addActionLabel(__('instructor.courses.actions.add_goal'))
                                ->columnSpanFull(),
                        ]),

                    Step::make(__('instructor.courses.sections.requirements'))
                        ->icon('heroicon-o-clipboard-document-check')
                        ->schema([
                            Repeater::make('requirements')
                                ->label(__('instructor.courses.sections.requirements'))
                                ->relationship()
                                ->schema([
                                    TextInput::make('content')
                                        ->label(__('instructor.courses.fields.requirement'))
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->addActionLabel(__('instructor.courses.actions.add_requirement'))
                                ->columnSpanFull(),
                        ]),

                    Step::make(__('instructor.courses.sections.curriculum'))
                        ->icon('heroicon-o-play-circle')
                        ->description(__('instructor.courses.help.r2_upload_hint'))
                        ->visible(fn (?Course $record): bool => $record !== null)
                        ->schema([
                            Repeater::make('sections')
                                ->label(__('instructor.courses.sections.curriculum'))
                                ->relationship()
                                ->orderColumn('order')
                                ->schema([
                                    TextInput::make('title')
                                        ->label(__('instructor.courses.fields.section_title'))
                                        ->required()
                                        ->maxLength(255),
                                    Repeater::make('videos')
                                        ->label(__('instructor.courses.fields.videos'))
                                        ->relationship()
                                        ->orderColumn('order')
                                        ->schema([
                                            TextInput::make('title')
                                                ->label(__('instructor.courses.fields.video_title'))
                                                ->required()
                                                ->maxLength(255),
                                            Textarea::make('description')
                                                ->label(__('instructor.courses.fields.video_description'))
                                                ->rows(3)
                                                ->columnSpanFull(),
                                            Hidden::make('url')->required(),
                                            ViewField::make('r2_upload')
                                                ->label(__('instructor.courses.fields.video_file'))
                                                ->view('filament.forms.components.r2-video-upload')
                                                ->viewData(fn (object $livewire): array => [
                                                    'uploadBaseUrl' => self::uploadBaseUrl($livewire),
                                                ])
                                                ->dehydrated(false)
                                                ->columnSpanFull(),
                                            Toggle::make('is_free')
                                                ->label(__('instructor.courses.fields.is_free'))
                                                ->default(false),
                                            Toggle::make('is_published')
                                                ->label(__('instructor.courses.fields.published'))
                                                ->default(false),

                                        ])
                                        ->columns(2)
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                        ->addActionLabel(__('instructor.courses.actions.add_video'))
                                        ->columnSpanFull(),
                                ])
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                ->addActionLabel(__('instructor.courses.actions.add_section'))
                                ->columnSpanFull(),
                        ])->visibleOn('edit'),
                ])
                    ->persistStepInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    private static function uploadBaseUrl(object $livewire): ?string
    {
        if (! method_exists($livewire, 'getRecord')) {
            return null;
        }

        $course = $livewire->getRecord();

        if (! $course instanceof Course || ! $course->exists) {
            return null;
        }

        return url("api/instructor/courses/{$course->getKey()}/video-uploads");
    }
}
