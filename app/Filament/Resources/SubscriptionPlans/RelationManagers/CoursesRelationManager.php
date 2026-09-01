<?php

namespace App\Filament\Resources\SubscriptionPlans\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'courses';

    protected static ?string $title = 'Courses';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lms.resources.course.plural');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('instructor.name')->label(__('lms.common.instructor'))->searchable()->sortable(),
                IconColumn::make('is_published')->label(__('lms.common.published'))->boolean()->sortable(),
            ])
            ->headerActions([
                AttachAction::make()->preloadRecordSelect()->recordSelectSearchColumns(['title']),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DetachBulkAction::make()]),
            ]);
    }
}
