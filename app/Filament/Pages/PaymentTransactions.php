<?php

namespace App\Filament\Pages;

use App\Models\PaymentTransaction;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use UnitEnum;

class PaymentTransactions extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Payments';

    public static function getNavigationGroup(): ?string
    {
        return __('lms.navigation.payments');
    }

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.payment-transactions';

    public static function getNavigationLabel(): string
    {
        return __('lms.navigation.payment_transactions');
    }

    public function getTitle(): string
    {
        return __('lms.navigation.payment_transactions');
    }

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    public int $perPage = 12;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected function getViewData(): array
    {
        return [
            'transactions' => $this->transactions(),
            'stats' => Cache::flexible('admin.payment-transactions.stats', [10, 30], fn (): array => [
                'total' => PaymentTransaction::query()->count(),
                'paid' => PaymentTransaction::query()->where('status', 'paid')->count(),
                'attention' => PaymentTransaction::query()->whereIn('status', ['failed', 'rejected'])->count(),
                'revenue' => (int) PaymentTransaction::query()->where('status', 'paid')->sum('amount_cents'),
            ]),
        ];
    }

    private function transactions(): LengthAwarePaginator
    {
        return PaymentTransaction::query()
            ->with(['order:id,number,user_id', 'order.user:id,name,email'])
            ->when($this->status !== 'all', fn (Builder $query) => $query->where('status', $this->status))
            ->when(filled($this->search), function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('provider_transaction_id', 'like', $term)
                        ->orWhere('provider_order_id', 'like', $term)
                        ->orWhereHas('order', fn (Builder $order) => $order->where('number', 'like', $term));
                });
            })
            ->latest('received_at')
            ->paginate($this->perPage);
    }
}
