<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourMenuList extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'inclusion',
        'no_day',
        'hrs',
    ];
    public function tourMenuLists(){
        return $this->hasMany(TourMenu::class, 'menu_id');
    }
}
