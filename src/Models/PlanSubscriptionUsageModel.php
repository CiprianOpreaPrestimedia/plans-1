<?php

namespace Iprop\Plans\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSubscriptionUsageModel extends Model
{
    protected $table = 'plans_usages';
    protected $guarded = [];

    public function subscription()
    {
        return $this->belongsTo(config('plans.models.subscription'), 'subscription_id');
    }

    public function scopeCode($query, string $code)
    {
        return $query->where('code', $code);
    }
}
