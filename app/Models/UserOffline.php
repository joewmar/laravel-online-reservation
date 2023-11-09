<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserOffline extends Model
{
    use HasFactory;
    protected $primaryKey = 'id'; // Set the custom primary key field name
    protected $fillable = [
        "first_name" ,
        "last_name",
        "birthday",
        "country" ,
        "email" ,
        "nationality",
        "contact",
        'valid_id',
    ];
    public function reservation(){
        return $this->hasOne(Reservation::class, 'offline_user_id');
    }
    public function age()
    {
        return Carbon::parse($this->attributes['birthday'])->age;
    }
    public function name(){
        return  $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }
}
