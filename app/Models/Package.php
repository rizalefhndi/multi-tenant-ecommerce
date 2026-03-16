<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_in_days',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function subscriptionTransactions()
    {
        return $this->hasMany(SubscriptionTransaction::class);
    }
}
