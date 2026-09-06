<?php

namespace App\Filament\Resources\Contacts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\RichEditor;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
 TextColumn::make('name')
 ->label(__('contacts.fields.name'))
                    ->sortable()
                    ->searchable(),
 TextColumn::make('email')
 ->label(__('contacts.fields.email'))
                    ->sortable()
                    ->searchable(),
 TextColumn::make('phone')
 ->label(__('contacts.fields.phone'))
                    ->sortable()
                    ->searchable(),


 TextColumn::make('status')
 ->label(__('contacts.fields.status'))
 ->badge()
 ->formatStateUsing(fn (string $state): string => __('contacts.statuses.'.$state))
 ->color(fn (string $state): string => match ($state) {
 'new' => 'warning',
 'read' => 'info',
 'replied' => 'success',
 'archived' => 'gray',
 default => 'gray',
 }),
 TextColumn::make('created_at')
 ->label(__('contacts.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
 SelectFilter::make('status')
 ->label(__('contacts.fields.status'))
 ->options([
 'new' => __('contacts.statuses.new'),
 'read' => __('contacts.statuses.read'),
 'replied' => __('contacts.statuses.replied'),
 'archived' => __('contacts.statuses.archived'),
                    ]),
            ])
 ->recordActions([
 ViewAction::make()
 ->label(__('contacts.actions.view'))
 ->schema([
 TextEntry::make('name')->label(__('contacts.fields.name')),
 TextEntry::make('email')->label(__('contacts.fields.email'))->copyable(),
 TextEntry::make('phone')->label(__('contacts.fields.phone'))->copyable(),
 TextEntry::make('subject')->label(__('contacts.fields.subject'))->columnSpanFull(),
 TextEntry::make('message')->label(__('contacts.fields.message'))->columnSpanFull(),
 TextEntry::make('status')
 ->label(__('contacts.fields.status'))
 ->badge()
 ->formatStateUsing(fn (string $state): string => __('contacts.statuses.'.$state)),
 TextEntry::make('created_at')->label(__('contacts.fields.created_at'))->dateTime(),
 ])
 ->modalWidth('3xl'),
 EditAction::make(),
                DeleteAction::make(),
 Action::make('reply')
 ->label(__('contacts.actions.reply'))
                    ->schema([
 RichEditor::make('reply_message')
 ->label(__('contacts.actions.reply_message'))
                            ->required()
                            ->columnSpanFull()
                            ->extraInputAttributes([
                                'style' => 'min-height: 200px;',
                            ]),
                    ])
                    ->action(function ($record, $data) {
                        // Handle the reply action here
                        // For example, you can send an email or update the record status
                        $record->status = 'replied';
                        $record->save();

                        // You can also send a notification to the user
                        $record->notify(new \App\Notifications\ReplyContactQuestion($record, $data['reply_message']));
                    })
                    ->icon(Heroicon::OutlinedArrowUturnLeft),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
