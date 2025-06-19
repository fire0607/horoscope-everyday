<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horoscope extends Model
{
    protected $fillable = [
        'date',
        'sign',
        'content',
        'lucky_color',
    ];
}
