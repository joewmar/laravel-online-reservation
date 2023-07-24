<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Archive extends Model
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
        'message',
    ];
    public function userReservation(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function room(){
        return $this->hasMany(Room::class, 'room_id');
    }
    public function status(){
        $status = '';
        if($this->attributes['status'] == 0) $status = 'Done';
        elseif($this->attributes['status'] == 1) $status = 'Disaprove';
        elseif($this->attributes['status'] == 2) $status = 'Cancel';
        // elseif($this->attributes['status'] == 3) $status = 'Check-out';
            
        return $status ?? $this->attributes['status'];
    }
}
