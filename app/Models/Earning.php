<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'booking_id',
        'date',
        'hours',
        'amount',
        'service_name',
    ];

}
