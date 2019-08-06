<?php

namespace Iprop\Plans\Models;

use Illuminate\Database\Eloquent\Model;

class PlanFeatureModel extends Model
{
    protected $table = 'plans_features';
    protected $guarded = [];
    protected $casts = [
        'metadata' => 'object',
    ];

    public function plan()
    {
        return $this->belongsTo(config('plans.models.plan'), 'plan_id');
    }

    public function scopeCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopeLimited($query)
    {
        return $query->where('type', 'limit');
    }

    public function scopeFeature($query)
    {
        return $query->where('type', 'feature');
    }

    public function isUnlimited()
    {
        return (bool) ($this->type == 'limit' && $this->limit < 0);
    }
}
