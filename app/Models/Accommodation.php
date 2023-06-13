<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'name',
        'type',
        'amenities',
        'description',
        'price',
        'occupancy',
        'location'
    ];
    public function accommodations(){
        return $this->hasMany(Rooms::class, 'accommodation_id');
    }
}
