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
        'atpermit',
    ];
    public function tourMenuLists(){
        return $this->hasMany(TourMenu::class, 'menu_id');
    }
    public function atStatus(){
        if($this->attributes['atpermit'] === 1) return "Day Tour";
        else return "All";
    }
}
