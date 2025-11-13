<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{    
    protected $fillable = [
        'invoice_number',
        'date',
        'fiscal_code',
        'vat_code',
        'supplier',
        'amount',
        'tax',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
    ];

    /**
     * Boot method to auto-calculate total
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($purchase) {
            $purchase->total = $purchase->amount + $purchase->tax;
        });
    }
}
