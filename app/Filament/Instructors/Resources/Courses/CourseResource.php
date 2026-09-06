<?php

namespace App\Filament\Instructors\Resources\Courses;

use App\Filament\Instructors\Resources\Courses\Pages\CreateCourse;
use App\Filament\Instructors\Resources\Courses\Pages\EditCourse;
use App\Filament\Instructors\Resources\Courses\Pages\ListCourses;
use App\Filament\Instructors\Resources\Courses\Pages\ViewCourse;
use App\Filament\Instructors\Resources\Courses\Schemas\CourseForm;
use App\Filament\Instructors\Resources\Courses\Tables\CoursesTable;
use App\Models\Course;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('instructor.navigation.courses');
    }

    public static function getModelLabel(): string
    {
        return __('instructor.resources.course.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('instructor.resources.course.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return CourseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoursesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('instructor_id', auth('instructor')->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'view' => ViewCourse::route('/{record}'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }
}
