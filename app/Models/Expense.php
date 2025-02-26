<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    //
    protected $fillable = ['customer_id', 'amount', 'tax_amount', 'date', 'recurring', 'start_date', 'end_date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
