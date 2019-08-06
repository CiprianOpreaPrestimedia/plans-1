<?php

namespace Iprop\Plans\Test;

use Carbon\Carbon;

class RecurrencyTest extends TestCase
{
    protected $user;
    protected $plan;
    protected $newPlan;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(\Iprop\Plans\Test\Models\User::class)->create();
        $this->plan = factory(\Iprop\Plans\Models\PlanModel::class)->create();
        $this->newPlan = factory(\Iprop\Plans\Models\PlanModel::class)->create();

        $this->initiateStripeAPI();
    }

    public function testRecurrency()
    {
        $this->user->subscribeToUntil($this->plan, Carbon::now()->addDays(7));
        sleep(1);

        $this->user->currentSubscription()->update([
            'starts_on' => Carbon::now()->subDays(7),
            'expires_on' => Carbon::now(),
        ]);

        $this->assertFalse($this->user->hasActiveSubscription());
        $this->assertEquals($this->user->subscriptions()->count(), 1);

        $this->assertNotNull($this->user->renewSubscription($this->getStripeTestToken()));
        sleep(1);

        $this->assertTrue($this->user->hasActiveSubscription());
        $this->assertEquals($this->user->subscriptions()->count(), 2);
    }

    public function testRecurrencyWithStripe()
    {
        $this->user->withStripe()->withStripeToken($this->getStripeTestToken())->subscribeToUntil($this->plan, Carbon::now()->addDays(7));
        sleep(1);

        $this->user->currentSubscription()->update([
            'starts_on' => Carbon::now()->subDays(7),
            'expires_on' => Carbon::now(),
        ]);

        $this->assertFalse($this->user->hasActiveSubscription());
        $this->assertEquals($this->user->subscriptions()->count(), 1);

        $this->assertNotNull($this->user->renewSubscription($this->getStripeTestToken()));
        sleep(1);

        $activeSubscription = $this->user->activeSubscription();

        $this->assertTrue($this->user->hasActiveSubscription());
        $this->assertEquals($this->user->subscriptions()->count(), 2);
        $this->assertTrue($activeSubscription->is_paid);
    }
}
