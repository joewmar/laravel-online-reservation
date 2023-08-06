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
        'roomid',
        'accommodation_type' ,
        'payment_method' ,
        'menu',
        'check_in',
        'check_out' ,
        'status',
        'additional',
        'amount',
        'total' ,
        'message',
    ];
    public function userArchive(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function room(){
        return $this->hasMany(Room::class, 'roomid');
    }
    public function status(){
        $status = '';
        if($this->attributes['status'] == 0) $status = 'Done';
        elseif($this->attributes['status'] == 1) $status = 'Disaprove';
        elseif($this->attributes['status'] == 2) $status = 'Cancel';
        elseif($this->attributes['status'] == 3) $status = 'Check-out';
        elseif($this->attributes['status'] == 4) $status = 'Canceled';
        elseif($this->attributes['status'] == 5) $status = 'Disaproved'; /* 0 => pending, 1 => confirmed, 2 => check-in, 3 => done, 4 => canceled, 5 => disaprove, 6 => reshedule*/
        elseif($this->attributes['status'] == 6) $status = 'Reshedule'; 
            
        return $status ?? $this->attributes['status'];
    }
    protected function roomid(): Attribute
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
    protected function additional(): Attribute
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
