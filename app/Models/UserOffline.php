<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserOffline extends Model
{
    use HasFactory;
    protected $primaryKey = 'id'; // Set the custom primary key field name
    protected $fillable = [
        "first_name" ,
        "last_name",
        "age",
        "country" ,
        "email" ,
        "nationality",
        "contact",
    ];
    public function reservation(){
        return $this->hasOne(Reservation::class, 'offline_user_id');
    }
    public function age()
    {
        return $this->attributes['age'];
    }
    public function name(){
        return  $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }
}
