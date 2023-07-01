<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourMenu extends Model
{
    use HasFactory;
    protected $fillable = [
        'menu_id',
        'type',
        'price',
        'pax',
    ];
    public function tourMenu(){
        return $this->belongsTo(TourMenuList::class, 'room_id');
    }
}
