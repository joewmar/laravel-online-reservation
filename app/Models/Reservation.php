<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Reservation extends Model
{
    use HasFactory;
    use HasUuids;
    protected $fillable = [
        'user_id',
        'roomid',
        'roomrateid',
        'pax',
        'tour_pax',
        'age',
        'accommodation_type',
        'payment_method',
        'check_in',
        'check_out',
        'status',
        'transaction',
        'valid_id',
        'message',
    ];
    public function userReservation(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function room(){
        return $this->hasMany(Room::class, 'roomid');
    }
    public function roomRate(){
        return $this->hasMany(RoomRate::class, 'roomrateid');
    }
    public function status(){
        $status = '';
        if($this->attributes['status'] == 0) $status = 'Pending';
        elseif($this->attributes['status'] == 1) $status = 'Confirmed';
        elseif($this->attributes['status'] == 2) $status = 'Check-in';
        elseif($this->attributes['status'] == 3) $status = 'Previous';
        elseif($this->attributes['status'] == 4) $status = 'Reshedule';
        elseif($this->attributes['status'] == 5) $status = 'Cancel';
        elseif($this->attributes['status'] == 6) $status = 'Disaprove';
            
        return $status ?? $this->attributes['status'];
    }
    public function payment(){
        return $this->hasMany(OnlinePayment::class, 'reservation_id');
    }
    protected function roomid(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
    protected function transaction(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }
    public function getNoDays()
    {
        $date1 = Carbon::parse($this->attributes['check_in']); // Convert $date1 to Carbon object
        $date2 = Carbon::parse($this->attributes['check_out']); // Convert $date2 to Carbon object
        return (int)$date1->diffInDays($date2); // Calculate the number of days between the two dates
    }
    public function getAllArray($attribute)
    {
        if(!empty($replace)) {
            foreach(json_decode($this->attributes[$attribute]) as $key => $item) $replace[$key] = $item;
            return $replace;
        }
        else return [];
    }
    public function getTotal()
    {
        $total = 0;
        if(!empty($this->attributes['transaction'])) {
            foreach(json_decode($this->attributes['transaction']) as $item) {
                if(is_array($item)){
                    foreach($item as $value){
                        $total += (double)$value->amount;
                    }
                }
                if(isset($item->amount)){
                    $total += (double)$item->amount;
                }
            }
            return $total;
        }
        else return $total;
    }
    public function downpayment()
    {
        return json_decode($this->attributes['transaction'])->payment->downpayment ?? 0;
    }
    public function checkInPayment()
    {
        return json_decode($this->attributes['transaction'])->payment->cinpay ?? 0;
    }
    public function checkOutPayment()
    {
        return json_decode($this->attributes['transaction'])->payment->coutpay ?? 0;
    }
    public function balance()
    {
        $payment = 0;
        foreach (json_decode($this->attributes['transaction'])->payment ?? [] as $item){
            $payment += $item;
        }
        return abs($this->getTotal() - $payment) ?? 0;
    }
    public function checkedOut()
    {
        if(isset($this->attributes['roomid'])){
            foreach(json_decode($this->attributes['roomid']) as $item){
                $room = Room::find($item);
                if(isset($room->customer)){
                    $customer = $room->customer;
                    if (array_key_exists($this->attributes['id'], $customer)) {
                        unset($customer[$this->attributes['id']]);
                    }
                    $room->update(['customer' => $customer]);
                    $room->checkAvailability();
                }
                else{
                    return false;
                    break;
                }
            }
        }
        else {
            return false;
        }
        return true;
    }
}
