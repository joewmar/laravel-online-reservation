<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserOffline extends Model
{
    use HasFactory;
    protected $primaryKey = 'id'; // Set the custom primary key field name

    protected static function booted()
    {
        static::creating(function ($model) {
            // Generate the custom ID
            $model->custom_id = 'aa' . Str::random(3) . '-' . Str::random(3) . '-' . Str::random(2);
        });
    }
}
