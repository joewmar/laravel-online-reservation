<?php

namespace App\Models;

use App\Models\System;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditTrail extends Model
{
    use HasFactory;
    protected $fillable = [
        'system_id',
        'name',
        'role',
        'action',
        'module',
    ];
    protected static function boot()
    {
        parent::boot();

        // Set the default timezone for the created_at attribute
        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Manila');
        });
    }
    public function employee(){
        return $this->belongsTo(System::class, 'system_id');
    }
    public function role(){
        $role = '';
        if($this->attributes['role'] === 0) $role = "Admin";
        elseif ($this->attributes['role'] === 1)  $role = "Manager";
        elseif ($this->attributes['role'] === 2)  $role = "Front Desk";
        return $role ?? $this->attributes['role'];
    }
}
