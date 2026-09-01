<?php

namespace App\Filament\Resources\Courses;

use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Filament\Resources\Courses\Pages\ViewCourse;
use App\Filament\Resources\Courses\RelationManagers\SubscriptionPlansRelationManager;
use App\Models\Course;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'LMS Management';

    public static function getNavigationGroup(): ?string
    {
        return __('lms.navigation.lms_management');
    }

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return __('lms.navigation.courses');
    }

    public static function getModelLabel(): string
    {
        return __('lms.resources.course.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('lms.resources.course.plural');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('instructor.name')->label(__('lms.common.instructor'))->searchable()->sortable(),
                TextColumn::make('category.name')->label(__('lms.common.category'))->sortable(),
                TextColumn::make('subscription_plans_count')->counts('subscriptionPlans')->label(__('lms.common.plans')),
                IconColumn::make('is_published')->label(__('lms.common.published'))->boolean()->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [SubscriptionPlansRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'view' => ViewCourse::route('/{record}'),
        ];
    }
}
