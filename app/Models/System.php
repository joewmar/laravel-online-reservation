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
        'access',
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
        return $role ?? $this->attributes['type'];
    }
    protected function access(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }

    public function accessList(){
        $arrAccessList = array(
            "reservation" => [
                "module",
                "confirm",
                "check_in",
                "check_out",
                "cancel",
                "reschedule",
                "edit",
                "create",
            ],
            "rooms" => [
            "module",
                "create_list",
                "show_list",
                "edit_list",
                "delete_list",
                "create_rate",
                "edit_rate",
                "delete_rate",
            ],
            "tour_menu" => [
                "module",
                "create_menu",
                "edit_menu",
                "delete_menu",
                "create_price",
                "edit_price",
                "delete_price",
            ],
            "tour_menu" => [
                "module",
                "create_menu",
                "edit_menu",
                "delete_menu",
                "create_price",
                "edit_price",
                "delete_price",
            ],
            "news" => [
                "module",
                "create_news",
                "edit_news",
                "delete_news",
                "create_announcement",
                "edit_announcement",
                "delete_announcement",
            ],
            "feedback" => [
                "module",
            ],
            "web_content" => [
                "module",
                "create_hero",
                "edit_hero",
                "delete_hero",
                "create_gallery",
                "edit_gallery",
                "delete_gallery",
                "create_contact",
                "edit_contact",
                "delete_contact",
                "create_payment",
                "edit_payment",
                "delete_payment",
                "reservation_operation",
            ],
            "log" => [
                "module",
            ],
            "account" => [
                "module",
                "create",
                "edit",
                "delete",
            ],
        );
        return $arrAccessList;
    }
    public function name(){
        return $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }
    public function activityLogs()
    {
        return $this->hasMany(AuditTrail::class, 'system_id');
    }

}
