<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Tour extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'no_day',
        'hrs',
        'location',
        'price',
        'pax',
    ];

    // public static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         $model->unique_id = $model->generateUniqueId();
    //     });
    // }

    // public function generateUniqueId()
    // {
    //     $uniqueId = Str::random(10); // Generate a random string of length 10
    //     $model = static::where('unique_id', $uniqueId)->first();

    //     // If a record with the generated ID already exists, regenerate the ID
    //     if ($model) {
    //         return $this->generateUniqueId();
    //     }

    //     return $uniqueId;
    // }

}
