<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class System extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guard = 'system';
    protected $fillable = [
        'avatar',
        'first_name',
        'last_name',
        'contact',
        'email',
        'username',
        'password',
        'passcode',
        'telegram_username',
        'telegram_chatID',
        'type'
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
    public function role(){
        $role = '';
        if($this->attributes['type'] === 0) $role = "Admin";
        elseif ($this->attributes['type'] === 1)  $role = "Manager";
        elseif ($this->attributes['type'] === 2)  $role = "Front Desk";
        elseif ($this->attributes['type'] === 3)  $role = "Staff";
        return $role ?? $this->attributes['type'];
    }
    public function name(){
        return $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }
}
