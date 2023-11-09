<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Prunable;

class TourMenu extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'menu_id',
        'type',
        'price',
        'pax',
    ];


    public function tourMenu(){
        return $this->belongsTo(TourMenuList::class, 'menu_id');
    }
}
