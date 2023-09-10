<?php

namespace App\Models;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomReserve extends Model
{
    use HasFactory;
    protected $fillable = [
        'reservation_id',
        'rooms',
        'reserved_at',
    ];

    public function reservation(){
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }
    protected function rooms(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
}
