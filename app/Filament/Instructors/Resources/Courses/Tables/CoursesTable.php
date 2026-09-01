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
                    ->label(__('lms.instructor.course_table.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label(__('lms.instructor.course_table.category'))
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('lms.instructor.course_table.price'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('number_lessons')
                    ->label(__('lms.instructor.course_table.lessons'))
                    ->sortable(),
                TextColumn::make('duration')
                    ->label(__('lms.instructor.course_table.duration'))
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return \Carbon\CarbonInterval::seconds($state)
                            ->cascade()
                            ->forHumans();
                    }),
                IconColumn::make('is_published')
                    ->label(__('lms.instructor.course_table.published'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('lms.instructor.course_table.updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('delete')
                    ->action(function (Model $record) {
                        $record->sections()->with('videos')->get()
                            ->each(function ($section) {

                                $section->videos->each(function ($video) {
                                    $video->delete();
                                });

                                $section->delete();
                            });

                        return (bool) $record->delete();
                    })->modalSubmitActionLabel(__('filament-actions::delete.single.modal.actions.delete.label'))
                    ->modalHeading(fn(Action $action): string => __('filament-actions::delete.single.modal.heading', ['label' => $action->getRecordTitle()]))

                    ->modalSubmitActionLabel(__('filament-actions::delete.single.modal.actions.delete.label'))

                    ->successNotificationTitle(__('filament-actions::delete.single.notifications.deleted.title'))

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
            ]);
    }
}
