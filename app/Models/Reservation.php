<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;
    use HasUuids;
    protected $fillable = [
        'user_id',
        'room_id',
        'room_rate_id',
        'pax',
        'age',
        'accommodation_type',
        'payment_method',
        'menu',
        'check_in',
        'check_out',
        'status',
        'additional_menu',
        'amount',
        'total',
    ];
    public function userReservation(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function room(){
        return $this->hasMany(Room::class, 'room_id');
    }
    public function roomRate(){
        return $this->hasMany(RoomRate::class, 'room_rate_id');
    }
    public function status(){
        $status = '';
        if($this->attributes['status'] == 0) $status = 'Pending';
        elseif($this->attributes['status'] == 1) $status = 'Confirmed';
        elseif($this->attributes['status'] == 2) $status = 'Check-in';
        elseif($this->attributes['status'] == 3) $status = 'Check-out';
            
        return $status ?? $this->attributes['status'];
    }
    public function payment(){
        $this->belongsTo(OnlinePayment::class, 'reservation_id');
    }
}
