<?php

namespace Iprop\Plans\Test;

use Carbon\Carbon;

class StripeTest extends TestCase
{
    protected $user;
    protected $plan;
    protected $newPlan;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(\Iprop\Plans\Test\Models\User::class)->create();
        $this->plan = factory(\Iprop\Plans\Models\PlanModel::class)->create();
        $this->newPlan = factory(\Iprop\Plans\Models\PlanModel::class)->create();

        $this->initiateStripeAPI();
    }

    public function testStripeCustomer()
    {
        $this->assertFalse($this->user->isStripeCustomer());
        $this->assertFalse($this->user->deleteStripeCustomer());

        $this->assertNotNull($this->user->createStripeCustomer());
        $this->assertTrue($this->user->isStripeCustomer());
        $this->assertNotNull($this->user->createStripeCustomer());

        $this->assertTrue($this->user->deleteStripeCustomer());
        $this->assertFalse($this->user->deleteStripeCustomer());
    }

    public function testChargeOnSubscribeTo()
    {
        $customer = $this->user->createStripeCustomer();
        $subscription = $this->user->withStripe()->withStripeToken($this->getStripeTestToken())->subscribeTo($this->plan, 53);
        sleep(1);

        $this->assertTrue($subscription->is_paid);
        $this->assertEquals($subscription->recurring_each_days, 53);
        $this->assertEquals($subscription->charging_price, $this->plan->price);
        $this->assertEquals($subscription->charging_currency, $this->plan->currency);
        $this->assertEquals($this->user->subscriptions()->stripe()->count(), 1);
    }

    public function testChargeOnSubscribeToWithInvalidToken()
    {
        $customer = $this->user->createStripeCustomer();
        $subscription = $this->user->withStripe()->withStripeToken($this->getInvalidStripeToken())->subscribeTo($this->plan, 53);
        sleep(1);

        $this->assertFalse($subscription->is_paid);
        $this->assertEquals($subscription->recurring_each_days, 53);
        $this->assertEquals($subscription->charging_price, $this->plan->price);
        $this->assertEquals($subscription->charging_currency, $this->plan->currency);
        $this->assertEquals($this->user->subscriptions()->stripe()->count(), 1);
        $this->assertEquals($this->user->subscriptions()->stripe()->paid()->count(), 0);
    }

    public function testChargeOnSubscribeToUntil()
    {
        $subscription = $this->user->withStripe()->withStripeToken($this->getStripeTestToken())->subscribeToUntil($this->plan, Carbon::now()->addDays(53));
        sleep(1);

        $this->assertTrue($subscription->is_paid);
        $this->assertEquals($subscription->charging_price, $this->plan->price);
        $this->assertEquals($subscription->charging_currency, $this->plan->currency);
        $this->assertEquals($this->user->subscriptions()->stripe()->count(), 1);
    }

    public function testChargeOnSubscribeToUntilWithInvalidToken()
    {
        $subscription = $this->user->withStripe()->withStripeToken($this->getInvalidStripeToken())->subscribeToUntil($this->plan, Carbon::now()->addDays(53));
        sleep(1);

        $this->assertFalse($subscription->is_paid);
        $this->assertEquals($subscription->charging_price, $this->plan->price);
        $this->assertEquals($subscription->charging_currency, $this->plan->currency);
        $this->assertEquals($this->user->subscriptions()->stripe()->count(), 1);
        $this->assertEquals($this->user->subscriptions()->stripe()->paid()->count(), 0);
    }

    public function testChargeOnSubscribeToWithDifferentPrice()
    {
        $customer = $this->user->createStripeCustomer();
        $subscription = $this->user->withStripe()->setChargingPriceTo(10, 'USD')->withStripeToken($this->getStripeTestToken())->subscribeTo($this->plan, 53);
        sleep(1);

        $this->assertTrue($subscription->is_paid);
        $this->assertEquals($subscription->recurring_each_days, 53);
        $this->assertEquals($subscription->charging_price, '10');
        $this->assertEquals($subscription->charging_currency, 'USD');
        $this->assertEquals($this->user->subscriptions()->stripe()->count(), 1);
    }

    public function testChargeOnSubscribeToWithDifferentPriceAndInvalidToken()
    {
        $customer = $this->user->createStripeCustomer();
        $subscription = $this->user->withStripe()->setChargingPriceTo(10, 'USD')->withStripeToken($this->getInvalidStripeToken())->subscribeTo($this->plan, 53);
        sleep(1);

        $this->assertFalse($subscription->is_paid);
        $this->assertEquals($subscription->recurring_each_days, 53);
        $this->assertEquals($subscription->charging_price, '10');
        $this->assertEquals($subscription->charging_currency, 'USD');
        $this->assertEquals($this->user->subscriptions()->stripe()->count(), 1);
        $this->assertEquals($this->user->subscriptions()->stripe()->paid()->count(), 0);
    }

    public function testChargeOnSubscribeToUntilWithDifferentPrice()
    {
        $subscription = $this->user->withStripe()->setChargingPriceTo(100, 'JPY')->withStripeToken($this->getStripeTestToken())->subscribeToUntil($this->plan, Carbon::now()->addDays(53));
        sleep(1);

        $this->assertTrue($subscription->is_paid);
        $this->assertEquals($subscription->recurring_each_days, 53);
        $this->assertEquals($subscription->charging_price, 100);
        $this->assertEquals($subscription->charging_currency, 'JPY');
        $this->assertEquals($this->user->subscriptions()->stripe()->count(), 1);
    }

    public function testChargeOnSubscribeToUntilWithDifferentPriceAndInvalidStripeToken()
    {
        $subscription = $this->user->withStripe()->withStripeToken($this->getInvalidStripeToken())->setChargingPriceTo(100, 'JPY')->subscribeToUntil($this->plan, Carbon::now()->addDays(53));
        sleep(1);

        $this->assertFalse($subscription->is_paid);
        $this->assertEquals($subscription->recurring_each_days, 53);
        $this->assertEquals($subscription->charging_price, 100);
        $this->assertEquals($subscription->charging_currency, 'JPY');
        $this->assertEquals($this->user->subscriptions()->stripe()->count(), 1);
        $this->assertEquals($this->user->subscriptions()->stripe()->paid()->count(), 0);
    }

    public function testChargeForLastDueSubscriptionWithStripe()
    {
        $subscription = $this->user->withStripe()->withStripeToken($this->getInvalidStripeToken())->subscribeTo($this->plan, 53);
        sleep(1);

        $this->assertFalse($subscription->is_paid);

        $subscription = $this->user->withStripe()->withStripeToken($this->getStripeTestToken())->chargeForLastDueSubscription();
        sleep(1);

        $this->assertTrue($subscription->is_paid);
        $this->assertEquals($this->user->subscriptions()->count(), 1);

        $this->assertTrue($this->user->hasActiveSubscription());

        $subscription = $this->user->withStripe()->withStripeToken($this->getStripeTestToken())->chargeForLastDueSubscription();
        $this->assertFalse($subscription);

        $subscription = $this->user->withStripe()->withStripeToken($this->getInvalidStripeToken())->chargeForLastDueSubscription();
        $this->assertFalse($subscription);
    }

    public function testChargeForLastDueSubscriptionWithInvalidStripeToken()
    {
        $subscription = $this->user->withStripe()->withStripeToken($this->getInvalidStripeToken())->subscribeTo($this->plan, 53);
        sleep(1);

        $this->assertFalse($subscription->is_paid);

        $subscription = $this->user->withStripe()->withStripeToken($this->getInvalidStripeToken())->chargeForLastDueSubscription();
        sleep(1);

        $this->assertFalse($subscription);
        $this->assertEquals($this->user->subscriptions()->count(), 1);
        $this->assertEquals($this->user->subscriptions()->stripe()->paid()->count(), 0);

        $this->assertFalse($this->user->hasActiveSubscription());
    }

    public function testSubscribeWhenHavingDueSubscription()
    {
        $subscription = $this->user->withStripe()->withStripeToken($this->getInvalidStripeToken())->subscribeTo($this->plan, 53);
        sleep(1);

        $this->assertFalse($subscription->is_paid);
        $this->assertEquals($this->user->subscriptions()->count(), 1);

        $subscription = $this->user->withStripe()->withStripeToken($this->getInvalidStripeToken())->subscribeTo($this->plan, 53);
        sleep(1);

        $this->assertFalse($subscription->is_paid);
        $this->assertEquals($this->user->subscriptions()->count(), 1);

        $newSubscription = $this->user->withStripe()->withStripeToken($this->getStripeTestToken())->subscribeTo($this->plan, 53);
        sleep(1);

        $this->assertTrue($newSubscription->is_paid);
        $this->assertEquals($this->user->subscriptions()->count(), 1);

        $this->assertFalse($this->user->hasDueSubscription());
        $this->assertTrue($this->user->hasActiveSubscription());
    }

    public function testSubscribeUntilWhenHavingDueSubscription()
    {
        $subscription = $this->user->withStripe()->withStripeToken($this->getInvalidStripeToken())->subscribeToUntil($this->plan, Carbon::now()->addDays(53));
        sleep(1);

        $this->assertFalse($subscription->is_paid);
        $this->assertEquals($this->user->subscriptions()->count(), 1);

        $subscription = $this->user->withStripe()->withStripeToken($this->getInvalidStripeToken())->subscribeToUntil($this->plan, Carbon::now()->addDays(53));
        sleep(1);

        $this->assertFalse($subscription->is_paid);
        $this->assertEquals($this->user->subscriptions()->count(), 1);

        $newSubscription = $this->user->withStripe()->withStripeToken($this->getStripeTestToken())->subscribeToUntil($this->plan, Carbon::now()->addDays(53));
        sleep(1);

        $this->assertTrue($newSubscription->is_paid);
        $this->assertEquals($this->user->subscriptions()->count(), 1);

        $this->assertFalse($this->user->hasDueSubscription());
        $this->assertTrue($this->user->hasActiveSubscription());
    }
}
