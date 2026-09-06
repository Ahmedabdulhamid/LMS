<?php

namespace App\Filament\Instructors\Resources\Payments;

use App\Filament\Instructors\Resources\Payments\Pages\ListPayments;
use App\Models\CoursePurchase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = CoursePurchase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string { return __('instructor.navigation.payments'); }
    public static function getModelLabel(): string { return __('instructor.resources.payment.singular'); }
    public static function getPluralModelLabel(): string { return __('instructor.resources.payment.plural'); }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('course', fn (Builder $query) => $query->where('instructor_id', auth('instructor')->id()))
            ->with(['user:id,name,email', 'course:id,title']);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('user.name')->label(__('instructor.tables.common.student'))->searchable(),
            TextColumn::make('course.title')->label(__('instructor.tables.common.course'))->searchable(),
            TextColumn::make('price')->label(__('instructor.tables.payments.price'))->money('EGP')->sortable(),
            TextColumn::make('payment_status')->label(__('instructor.tables.common.status'))->badge()
                ->formatStateUsing(fn (string $state): string => __("instructor.statuses.{$state}"))
                ->color(fn (string $state) => match ($state) {
                'completed' => 'success', 'failed' => 'danger', default => 'warning',
            }),
            TextColumn::make('purchased_at')->label(__('instructor.tables.payments.date'))->dateTime()->sortable(),
        ])->emptyStateHeading(__('instructor.tables.payments.empty_heading'))
            ->emptyStateDescription(__('instructor.tables.payments.empty_description'))
            ->defaultSort('purchased_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListPayments::route('/')];
    }
}
