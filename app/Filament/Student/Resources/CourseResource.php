<?php

namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\CourseResource\Pages\ListCourses;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('student-panel.navigation.courses');
    }

    public static function getModelLabel(): string
    {
        return __('student-panel.courses.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('student-panel.courses.title');
    }

    public static function getEloquentQuery(): Builder
    {
        $student = auth('student')->user();

        return parent::getEloquentQuery()
            ->whereIn('courses.id', $student->enrolledCourses()->select('courses.id'))
            ->with(['instructor', 'progress' => fn ($query) => $query->where('user_id', $student->id)]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    ImageColumn::make('thumbnail')->disk(config('lms-upload.disk'))->height(150)->width('100%'),
                    TextColumn::make('title')->weight('bold')->size('lg')->searchable(),
                    TextColumn::make('instructor.name')->label(__('student-panel.courses.teacher'))->icon('heroicon-m-user'),
                    TextColumn::make('number_lessons')->formatStateUsing(fn ($state): string => __('student-panel.courses.lessons', ['count' => $state]))->icon('heroicon-m-play-circle'),
                    TextColumn::make('student_progress')->label(__('student-panel.courses.progress'))->state(fn (Course $record): string => ($record->progress->first()?->progress ?? 0).'%')->badge()->color('primary'),
                ])->space(3),
            ])
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->recordActions([
                Action::make('view')->label(__('student-panel.courses.view'))->icon('heroicon-m-play')->url(fn (Course $record): string => route('courses.learn', $record)),
            ])
            ->emptyStateHeading(__('student-panel.courses.empty'))
            ->paginated([9, 18, 36]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCourses::route('/')];
    }
}
