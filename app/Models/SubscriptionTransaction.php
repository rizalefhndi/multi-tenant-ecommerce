<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionTransaction extends Model
{
    protected $fillable = [
        'tenant_id',
        'package_id',
        'order_id',
        'gross_amount',
        'payment_type',
        'payment_provider',
        'status',
        'midtrans_transaction_id',
        'payment_details',
        'paid_at',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'paid_at' => 'datetime',
        'gross_amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
