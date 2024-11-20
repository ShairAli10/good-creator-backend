<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    Use HasFactory;


    protected $fillable = [
        'user_id',
        'creator_id',
        'rating',
        'comments'
    ];

    public function review_by(){
        return $this->belongsTo(User::class,'user_id')->select('id', 'name', 'profile_pic');
    }
}
