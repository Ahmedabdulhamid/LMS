<?php

namespace App\Filament\Instructors\Resources\Reviews;

use App\Filament\Instructors\Resources\Reviews\Pages\ListReviews;
use App\Models\CourseReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = CourseReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string { return __('instructor.navigation.reviews'); }
    public static function getModelLabel(): string { return __('instructor.resources.review.singular'); }
    public static function getPluralModelLabel(): string { return __('instructor.resources.review.plural'); }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('course', fn (Builder $query) => $query->where('instructor_id', auth('instructor')->id()))
            ->with(['user:id,name', 'course:id,title']);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('user.name')->label(__('instructor.tables.common.student'))->searchable(),
            TextColumn::make('course.title')->label(__('instructor.tables.common.course'))->searchable(),
            TextColumn::make('rating')->label(__('instructor.tables.reviews.rating'))->suffix(' / 5')->sortable(),
            TextColumn::make('comment')->label(__('instructor.tables.reviews.comment'))->wrap()->limit(80),
            IconColumn::make('is_approved')->label(__('instructor.tables.reviews.approved'))->boolean(),
            TextColumn::make('created_at')->label(__('instructor.tables.reviews.reviewed'))->dateTime()->sortable(),
        ])->emptyStateHeading(__('instructor.tables.reviews.empty_heading'))
            ->emptyStateDescription(__('instructor.tables.reviews.empty_description'))
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListReviews::route('/')];
    }
}
