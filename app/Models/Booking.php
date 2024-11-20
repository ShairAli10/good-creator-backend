<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'creator_id',
        'service_id',
        'package_id',
        'date',
        'time',
        'email',
        'lat',
        'longi',
        'location',
        'status',
        'payment_method',
        'payment_id'
    ];

    public function user_detail(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function creator_detail(){
        return $this->belongsTo(User::class,'creator_id');
    }

    public function service_detail(){
        return $this->belongsTo(Service::class,'service_id');
    }


}
