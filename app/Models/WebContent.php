<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebContent extends Model
{
    use HasFactory;
    protected $fillable = [
        'hero',
        'gallery',
        'contact',
        'operation',
        'from',
        'to',
        'reason',
    ];

    // public function addNews(array $array){
    //     $news = $array;

    //     if(!empty(json_decode($this->attributes['news'], true))){

    //         $this->update(['customer' => $customer]);
    //     }
    //     else{

    //     }
    //     $this->checkAvailability();

    // }
    protected function hero(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
    protected function gallery(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
    protected function contact(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
}
