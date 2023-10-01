<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    protected $fillable = [
        'reservation_id',
        'name',
        'rating',
        'message',
    ];
    public function feedback(){
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }
    public function ratingScale(){
        $rating = null;
        if($this->attributes['rating'] === 1) $rating = 'Very Dissatisfied';
        if($this->attributes['rating'] === 2) $rating = 'Dissatisfied';
        if($this->attributes['rating'] === 3) $rating = 'Neutral';
        if($this->attributes['rating'] === 4) $rating = 'Satisfied';
        if($this->attributes['rating'] === 5) $rating = 'Very Satisfied';
        return $rating;
    }

}
