<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_no',
        'availability',
        'customer',
    ];

    public function room(){
        return $this->belongsTo(RoomList::class, 'roomid');
    }
    protected function customer(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
}
