<?php

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\Pages\ViewSubscription;
use App\Models\Subscription;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'LMS Management';

    public static function getNavigationGroup(): ?string
    {
        return __('lms.navigation.lms_management');
    }

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('lms.navigation.subscriptions');
    }

    public static function getModelLabel(): string
    {
        return __('lms.resources.subscription.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('lms.resources.subscription.plural');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label(__('lms.common.student'))->searchable()->sortable(),
                TextColumn::make('plan.name')->label(__('lms.common.subscription_plan'))->searchable()->sortable(),
                TextColumn::make('status')->label(__('lms.common.status'))->badge()->sortable(),
                TextColumn::make('starts_at')->label(__('lms.common.starts_at'))->dateTime()->sortable(),
                TextColumn::make('ends_at')->label(__('lms.common.ends_at'))->dateTime()->sortable(),
                TextColumn::make('order.number')->label(__('lms.common.order'))->placeholder('—')->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('user.name')->label(__('lms.common.student')),
            TextEntry::make('user.email')->label(__('lms.common.student_email')),
            TextEntry::make('plan.name')->label(__('lms.common.subscription_plan')),
            TextEntry::make('status')->label(__('lms.common.status'))->badge(),
            TextEntry::make('starts_at')->label(__('lms.common.starts_at'))->dateTime(),
            TextEntry::make('ends_at')->label(__('lms.common.ends_at'))->dateTime(),
            TextEntry::make('order.number')->label(__('lms.common.order'))->placeholder('—'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptions::route('/'),
            'view' => ViewSubscription::route('/{record}'),
        ];
    }
}
