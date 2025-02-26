<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = ['customer_id', 'name', 'notes', 'active', 'price', 'recurring', 'start_date', 'end_date', 'billing_period'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
