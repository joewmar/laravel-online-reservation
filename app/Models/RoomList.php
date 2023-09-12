<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomList extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'name',
        'max_occupancy',
        'location',
        'many_room',
    ];

    public function roomList(){
        return $this->hasMany(Room::class, 'roomid');
    }
}
