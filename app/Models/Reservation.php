<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'room_id',
        'pax',
        'age',
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
    public function status(){
        $status = '';
        if($this->attributes['status'] == 0) $status = 'Pending';
        elseif($this->attributes['status'] == 1) $status = 'Confirmed';
        elseif($this->attributes['status'] == 2) $status = 'Check-in';
        elseif($this->attributes['status'] == 3) $status = 'Check-out';
        elseif($this->attributes['status'] == 4) $status = 'Cancel';
            
        return $status ?? $this->attributes['status'];
    }
}
