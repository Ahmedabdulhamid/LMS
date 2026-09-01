<?php

namespace App\Filament\Pages;

use App\Models\PaymentWebhookEvent;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use UnitEnum;

class PaymentWebhookEvents extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static string|UnitEnum|null $navigationGroup = 'Payments';

    public static function getNavigationGroup(): ?string
    {
        return __('lms.navigation.payments');
    }

    protected static ?int $navigationSort = 21;

    protected string $view = 'filament.pages.payment-webhook-events';

    public static function getNavigationLabel(): string
    {
        return __('lms.navigation.webhook_events');
    }

    public function getTitle(): string
    {
        return __('lms.navigation.webhook_events');
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
            'events' => $this->events(),
            'stats' => Cache::flexible('admin.payment-webhook-events.stats', [10, 30], fn (): array => [
                'total' => PaymentWebhookEvent::query()->count(),
                'processed' => PaymentWebhookEvent::query()->where('status', 'processed')->count(),
                'attention' => PaymentWebhookEvent::query()->whereIn('status', ['failed', 'rejected'])->count(),
                'retries' => (int) PaymentWebhookEvent::query()->sum('processing_attempts'),
            ]),
        ];
    }

    private function events(): LengthAwarePaginator
    {
        return PaymentWebhookEvent::query()
            ->with('order:id,number')
            ->when($this->status !== 'all', fn (Builder $query) => $query->where('status', $this->status))
            ->when(filled($this->search), function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('provider_transaction_id', 'like', $term)
                        ->orWhere('provider_order_id', 'like', $term)
                        ->orWhere('event_type', 'like', $term)
                        ->orWhereHas('order', fn (Builder $order) => $order->where('number', 'like', $term));
                });
            })
            ->latest('received_at')
            ->paginate($this->perPage);
    }
}
