<?php

namespace App\Filament\Instructors\Resources\Students;

use App\Filament\Instructors\Resources\Students\Pages\ListStudents;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string { return __('instructor.navigation.students'); }
    public static function getModelLabel(): string { return __('instructor.resources.student.singular'); }
    public static function getPluralModelLabel(): string { return __('instructor.resources.student.plural'); }

    public static function getEloquentQuery(): Builder
    {
        $instructorId = auth('instructor')->id();

        return parent::getEloquentQuery()
            ->whereHas('enrollments.course', fn (Builder $query) => $query->where('instructor_id', $instructorId))
            ->with(['enrollments' => fn ($query) => $query
                ->whereHas('course', fn (Builder $query) => $query->where('instructor_id', $instructorId))
                ->with('course:id,title')]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label(__('instructor.tables.common.student'))->searchable()->sortable(),
            TextColumn::make('email')->label(__('instructor.tables.common.email'))->searchable(),
            TextColumn::make('enrollments.course.title')->label(__('instructor.tables.students.courses'))->badge()->listWithLineBreaks(),
            TextColumn::make('created_at')->label(__('instructor.tables.students.joined'))->date()->sortable(),
        ])->emptyStateHeading(__('instructor.tables.students.empty_heading'))
            ->emptyStateDescription(__('instructor.tables.students.empty_description'))
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return ['index' => ListStudents::route('/')];
    }
}
