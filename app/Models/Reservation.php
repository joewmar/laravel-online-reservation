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
        'offline_user_id',
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
        'message',
    ];
    public function userReservation(){
        if(!empty($this->attributes['offline_user_id'])) return $this->belongsTo(UserOffline::class, 'offline_user_id');
        else return $this->belongsTo(User::class, 'user_id');
    }
    public function other_rooms(){
        return $this->hasOne(RoomReserve::class, 'id');
    }
    public function room(){
        return $this->hasMany(Room::class, 'roomid');
    }
    public function roomRate(){
        return $this->hasMany(RoomRate::class, 'roomrateid');
    }
    public function status($intSt = null){
        $status = '';
        $intst = $intSt ??  $this->attributes['status'];
        if($intst == 0) $status = 'Pending';
        elseif($intst == 1) $status = 'Confirmed';
        elseif($intst == 2) $status = 'Check-in';
        elseif($intst == 3) $status = 'Check-out';
        elseif($intst == 4) $status = 'Reshedule';
        elseif($intst == 5) $status = 'Cancel';
        elseif($intst == 6) $status = 'Disaprove';
        elseif($intst == 7) $status = 'Pending Reschedule';
        elseif($intst == 8) $status = 'Pending Cancel';
            
        return $status ?? $intSt;
    }
    public function payment(){
        return $this->hasMany(OnlinePayment::class, 'reservation_id');
    }
    public function previous(){
        return $this->hasMany(Archive::class, 'reservation_id');
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
    protected function message(): Attribute
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
    public function getNoDaysInToday()
    {
        $date1 = Carbon::parse($this->attributes['check_in']); // Convert $date1 to Carbon object
        if(Carbon::now()->format('Y-m-d') === Carbon::parse($this->attributes['check_in'])->format('Y-m-d')) $date1 = Carbon::now();
        $date2 = Carbon::parse($this->attributes['check_out']); // Convert $date2 to Carbon object
        return (int)$date1->diffInDays($date2); // Calculate the number of days between the two dates
    }
    public function getAllArray($attribute)
    {
        if(!empty($replace)) {
            foreach(json_decode($this->attributes[$attribute], true) as $key => $item) $replace[$key] = $item;
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
        $paymentCustomer = 0;
        $allPaid = json_decode($this->attributes['transaction'], true); 
        if(isset($allPaid['payment'])){
            $paymentCustomer += $allPaid['payment']['cinpay'] ?? 0;
            $paymentCustomer += $allPaid['payment']['downpayment'] ?? 0;
        }
        return abs($this->getTotal() - $paymentCustomer) ?? 0;
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
            }
            $type = 0;
            if(!empty($this->attributes['offline_user_id'])) $type = 1;
            if(!empty($this->attributes['offline_user_id']) && $this->attributes['accommodation_type'] === 'Other Online Booking') $type = 2;
            Archive::create([
                'reservation_id' => $this->attributes['id'],
                'type'=> $type,
                'nationality'=> $this->userReservation->nationality,
                'total'=> (double)$this->getTotal(),
            ]);
        }
    }
}
