<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('lms.category.fields.name'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('lms.category.fields.slug'))
                    ->searchable(),
                TextColumn::make('small_description')
                    ->label(__('lms.category.fields.small_description'))
                    ->searchable(),
                ImageColumn::make('icon')
                    ->getStateUsing(fn (Category $record): ?string => filled($record->icon)
                        ? Storage::disk(config('lms-upload.disk'))->url(
                            preg_replace('#/+#', '/', $record->icon),
                        )
                        : null)
                    ->square()
                    ->label(__('lms.category.fields.icon')),
                TextColumn::make('created_at')
                    ->label(__('lms.category.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('lms.category.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
