<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Archive extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'nationality',
        'type', /* 0 => online, 1 => physical*/ 
        'total',
        'created_date',
    ];
}
