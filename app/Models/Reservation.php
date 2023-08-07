<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;


class Reservation extends Model
{
    use HasFactory;
    use HasUuids;
    protected $fillable = [
        'user_id',
        'roomid',
        'roomrateid',
        'pax',
        'tour_pax',
        'age',
        'accommodation_type',
        'payment_method',
        'check_in',
        'check_out',
        'status',
        'transaction',
        'valid_id',
        'message',
    ];
    public function userReservation(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function room(){
        return $this->hasMany(Room::class, 'roomid');
    }
    public function roomRate(){
        return $this->hasMany(RoomRate::class, 'roomrateid');
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
        return $this->hasMany(OnlinePayment::class, 'reservation_id');
    }
    protected function roomid(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
    protected function transaction(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
}
