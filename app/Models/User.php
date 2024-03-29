<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'google_id',
        'avatar',
        'first_name',
        'last_name',
        'birthday',
        'nationality',
        'country',
        'contact',
        'email',
        'password',
        'valid_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function age()
    {
        return Carbon::parse($this->attributes['birthday'])->age;
    }
    public function reservation(){
        return $this->hasOne(Reservation::class, 'user_id');
    }
    public function archive(){
        return $this->hasOne(Archive::class, 'user_id');
    }
    public function name(){
        return  $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }
}
