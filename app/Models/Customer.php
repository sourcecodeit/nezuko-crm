<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //
    protected $fillable = ['name', 'hourly_rate', 'tax_rate', 'active'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
