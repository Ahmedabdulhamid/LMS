<?php

namespace App\Filament\Instructors\Resources\Courses\Tables;

use App\Filament\Instructors\Resources\Courses\CourseResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\View\ActionsIconAlias;
use Filament\Actions\ViewAction;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CoursesTable
{
    protected static string $resource = CourseResource::class;
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('instructor.courses.fields.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label(__('instructor.courses.fields.category'))
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('instructor.courses.fields.price'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('number_lessons')
                    ->label(__('instructor.courses.fields.lessons'))
                    ->sortable(),
                TextColumn::make('duration')
                    ->label(__('instructor.courses.fields.duration'))
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return \Carbon\CarbonInterval::seconds($state)
                            ->cascade()
                            ->forHumans();
                    }),
                IconColumn::make('is_published')
                    ->label(__('instructor.courses.fields.published'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('instructor.courses.fields.updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published')->label(__('instructor.courses.fields.published')),
            ])
            ->recordActions([
                ViewAction::make()->label(__('instructor.courses.actions.view')),
                EditAction::make()->label(__('instructor.courses.actions.edit')),
                Action::make('delete')
                    ->label(__('instructor.courses.actions.delete'))
                    ->action(function (Model $record) {
                        $record->sections()->with('videos')->get()
                            ->each(function ($section) {

                                $section->videos->each(function ($video) {
                                    $video->delete();
                                });

                                $section->delete();
                            });

                        return (bool) $record->delete();
                    })->modalSubmitActionLabel(__('instructor.courses.actions.delete'))
                    ->modalHeading(fn(Action $action): string => __('instructor.courses.actions.delete_confirm', ['course' => $action->getRecordTitle()]))
                    ->successNotificationTitle(__('instructor.courses.actions.delete_success'))

                    ->defaultColor('danger')

                    ->tableIcon(FilamentIcon::resolve(ActionsIconAlias::DELETE_ACTION) ?? Heroicon::Trash)
                    ->groupedIcon(FilamentIcon::resolve(ActionsIconAlias::DELETE_ACTION_GROUPED) ?? Heroicon::Trash)

                    ->requiresConfirmation()

                    ->modalIcon(FilamentIcon::resolve(ActionsIconAlias::DELETE_ACTION_MODAL) ?? Heroicon::OutlinedTrash)
                    ->disabled(fn(Model $record) => $record->is_published),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('instructor.courses.empty_heading'))
            ->emptyStateDescription(__('instructor.courses.empty_description'));
    }
}
