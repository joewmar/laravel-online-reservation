<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Archive extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name' ,
        'age',
        'country',
        'nationality' ,
        'contact',
        'email' ,
        'user_id' ,
        'pax',
        'room_id',
        'accommodation_type' ,
        'payment_method' ,
        'menu',
        'check_in',
        'check_out' ,
        'status',
        'additional_menu',
        'amount',
        'total' ,
        'message',
    ];
    public function userArchive(){
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
    protected function room_id(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
    protected function menu(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
    protected function additional_menu(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
}
