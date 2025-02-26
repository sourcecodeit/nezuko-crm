<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    //
    protected $fillable = ['customer_id', 'amount', 'paid', 'date', 'number'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
