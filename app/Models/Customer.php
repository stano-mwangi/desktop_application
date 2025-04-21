<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_name', 'phone_number', 'location', 'total_debt'
    ];

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }
}
