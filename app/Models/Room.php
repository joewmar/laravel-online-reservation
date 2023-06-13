<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_no',
        'availability',
        'customer_id',
    ];

    public function room(){
        return $this->belongsTo(Accommodation::class, 'accommodation_id');
    }
}
