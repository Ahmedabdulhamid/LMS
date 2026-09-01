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
                    Step::make(__('lms.instructor.course_form.course_details'))
                        ->icon('heroicon-o-information-circle')
                        ->columns(2)
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Select::make('category_id')
                            ->options(fn():array=>app(CategoryService::class)->retrievCategories()),

                            Select::make('lang')
                                ->label(__('lms.instructor.course_form.language'))
                                ->options([
                                    'ar' => __('lms.instructor.languages.ar'),
                                    'en' => __('lms.instructor.languages.en'),
                                    'fr' => __('lms.instructor.languages.fr'),
                                ])
                                ->required(),
                            Select::make('level')
                                ->label(__('lms.instructor.course_form.level'))
                                ->options([
                                    'beginner' => __('lms.instructor.levels.beginner'),
                                    'intermediate' => __('lms.instructor.levels.intermediate'),
                                    'advanced' => __('lms.instructor.levels.advanced'),
                                ])
                                ->required(),
                            Toggle::make('is_published')
                                ->label(__('lms.instructor.course_form.is_published'))
                                ->default(false),
                            TextInput::make('price')
                                ->label(__('lms.instructor.course_form.price'))
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->prefix('$'),
                            TextInput::make('price_after_discount')
                                ->label(__('lms.instructor.course_form.discount_price'))
                                ->numeric()
                                ->minValue(0)
                                ->lt('price')
                                ->prefix('$'),
                            Textarea::make('description')
                                ->label(__('lms.instructor.course_form.description'))
                                ->required()
                                ->rows(6)
                                ->columnSpanFull(),
                            FileUpload::make('thumbnail')
                                ->label(__('lms.instructor.course_form.thumbnail'))
                                ->disk(config('lms-upload.disk'))
                                ->directory('courses/thumbnails')
                                ->image()
                                ->imageEditor()
                                ->maxSize(5120)
                                ->columnSpanFull(),
                        ]),

                    Step::make(__('lms.instructor.course_form.learning_goals'))
                        ->icon('heroicon-o-academic-cap')
                        ->schema([
                            Repeater::make('goals')
                                ->relationship()
                                ->orderColumn('order')
                                ->schema([
                                    TextInput::make('goal')
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->reorderable()
                                ->addActionLabel(__('lms.instructor.course_form.add_goal'))
                                ->columnSpanFull(),
                        ]),

                    Step::make(__('lms.instructor.course_form.requirements'))
                        ->icon('heroicon-o-clipboard-document-check')
                        ->schema([
                            Repeater::make('requirements')
                                ->relationship()
                                ->schema([
                                    TextInput::make('content')
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->addActionLabel(__('lms.instructor.course_form.add_requirement'))
                                ->columnSpanFull(),
                        ]),

                    Step::make(__('lms.instructor.course_form.curriculum'))
                        ->icon('heroicon-o-play-circle')
                        ->description(__('lms.instructor.course_form.r2_upload_hint'))
                        ->visible(fn(?Course $record): bool => $record !== null)
                        ->schema([
                            Repeater::make('sections')
                                ->relationship()
                                ->orderColumn('order')
                                ->schema([
                                    TextInput::make('title')
                                        ->required()
                                        ->maxLength(255),
                                    Repeater::make('videos')
                                        ->relationship()
                                        ->orderColumn('order')
                                        ->schema([
                                            TextInput::make('title')
                                                ->required()
                                                ->maxLength(255),
                                            Textarea::make('description')
                                                ->rows(3)
                                                ->columnSpanFull(),
                                            Hidden::make('url')
                                                ->required(),
                                            ViewField::make('r2_upload')
                                                ->label(__('lms.instructor.course_form.video_file'))
                                                ->view('filament.forms.components.r2-video-upload')
                                                ->viewData(fn(object $livewire): array => [
                                                    'uploadBaseUrl' => self::uploadBaseUrl($livewire),
                                                ])
                                                ->dehydrated(false)
                                                ->columnSpanFull(),
                                            Toggle::make('is_free')
                                                ->label(__('lms.instructor.course_form.is_free'))
                                                ->default(false),
                                            Toggle::make('is_published')
                                                ->label(__('lms.instructor.course_form.is_published'))
                                                ->default(false),

                                        ])
                                        ->columns(2)
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                                        ->addActionLabel(__('lms.instructor.course_form.add_video'))
                                        ->columnSpanFull(),
                                ])
                                ->reorderable()
                                ->collapsible()
                                ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                                ->addActionLabel(__('lms.instructor.course_form.add_section'))
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
