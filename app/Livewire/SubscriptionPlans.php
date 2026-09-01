<?php

namespace App\Livewire;

use App\Exceptions\AlreadySubscribedException;
use App\Exceptions\InactiveSubscriptionPlanException;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;
use App\Enums\SubscriptionStatus;
use App\Services\OrderService;
use App\Services\PaymobService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SubscriptionPlans extends Component
{
    public ?string $notice = null;

    public function subscribe(int $planId, OrderService $orderService,PaymobService $paymobService): void
    {
        $student = Auth::guard('student')->user();

        if (! $student) {
            session()->put('url.intended', route('subscription-plans.index'));
            $this->redirectRoute('filament.students.auth.login');

            return;
        }

        if (Subscription::query()
            ->where('user_id', $student->id)
            ->where('subscription_plan_id', $planId)
            ->where('status', SubscriptionStatus::Active)
            ->where('starts_at', '<=', now())
            ->where(fn ($query) => $query
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>', now()))
            ->exists()) {
            $this->redirectRoute('my-courses.index');

            return;
        }

        try {
            $order = $orderService->createSubscriptionPlanOrder(
                $student,
                SubscriptionPlan::query()->findOrFail($planId),
            );
            $paymobService->getPaymentToken($order);
        } catch (InactiveSubscriptionPlanException|AlreadySubscribedException $exception) {
            $this->notice = $exception->getMessage();

            return;
        }

        $this->redirectRoute('checkout.orders.show', ['order' => $order]);
    }

    public function render(): View
    {
        return view('livewire.subscription-plans', [
            'plans' => SubscriptionPlan::query()
                ->where('is_active', true)
                ->with(['courses'])
                ->withCount('courses')
                ->latest('created_at')
                ->get(),
        ])->layout('layouts.course-public');
    }
}
