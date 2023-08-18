<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class News extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'title',
        'description',
        'from',
        'to',
        'image',
        'links',
    ];
    protected function links(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }
    public function scopeNews($query)
    {
        return $query->where('type', 0);
    }
    public function scopeAnnouncements($query)
    {
        return $query->where('type', 1);
    }
    public function type()
    {
        $type = null;
        if($this->attributes['type'] === 0) $type = "News";
        if($this->attributes['type'] === 1) $type = "Announcement";
        return $type;
    }
}
