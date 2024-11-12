<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CreatorMediaFiles extends Model
{
    Use HasFactory;

    protected $fillable = [
        'media',
        'resturant_id'
    ];

}
