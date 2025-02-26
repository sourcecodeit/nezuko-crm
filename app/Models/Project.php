<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
    protected $fillable = ['customer_id', 'name', 'active'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
