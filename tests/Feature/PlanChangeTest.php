<?php

use App\Domain\Actions\ChangeSubscriptionPlanAction;
use App\Models\SubscriptionPlan;

test('changes an active subscription plan once and records the integer adjustment', function () {
    [$payment] = ledgerPayment();
    $newPlan = SubscriptionPlan::factory()->create(['price_minor' => 20000, 'term_days' => 30]);
    $effective = $payment->subscription->starts_on->copy()->addDays(15);
    $action = app(ChangeSubscriptionPlanAction::class);
    $change = $action->handle($payment->subscription, $newPlan, $effective, 'plan-change-1');
    $again = $action->handle($payment->subscription, $newPlan, $effective, 'plan-change-1');
    expect($again->id)->toBe($change->id)->and($change->adjustment_minor)->toBe(5000);
    $this->assertDatabaseCount('plan_changes', 1);
    $this->assertDatabaseHas('subscriptions', ['id' => $payment->subscription_id, 'subscription_plan_id' => $newPlan->id]);
});
test('rejects a plan change outside the active term or with another currency', function () {
    [$payment] = ledgerPayment();
    $newPlan = SubscriptionPlan::factory()->create(['currency' => 'EUR']);
    expect(fn () => app(ChangeSubscriptionPlanAction::class)->handle($payment->subscription, $newPlan, $payment->subscription->starts_on->copy()->subDay(), 'bad-date'))->toThrow(InvalidArgumentException::class);
    expect(fn () => app(ChangeSubscriptionPlanAction::class)->handle($payment->subscription, $newPlan, $payment->subscription->starts_on->copy()->addDay(), 'bad-currency'))->toThrow(InvalidArgumentException::class);
    $this->assertDatabaseCount('plan_changes', 0);
});
