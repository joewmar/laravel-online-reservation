<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlinePayment extends Model
{
    use HasFactory;
    use HasUuids;
    protected $fillable =[
       'reservation_id',
        'payment_method', 
        'payment_name', 
        'image',
        'amount',
        'approval',
        'reference_no',
    ];
    public function reserve(){
        $this->belongsTo(Reservation::class, 'reservation_id');
    }
}
