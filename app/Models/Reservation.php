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
        'otherinfo',
    ];

    protected $primaryKey = 'id'; // Set the custom ID as the primary key.
    public $incrementing = false; // Disable auto-incrementing.

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate a custom ID with a prefix, date, and time-based increment.
            $prefix = 'aar-'; // Customize the prefix based on your needs
            $dateTime = now()->format('Ymd'); // Get the date and time in the format YmdHis (e.g., 20231005123456)

            // Find the highest increment value for the current date and time
            $latestRecord = static::where('id', 'like', $prefix . $dateTime . '%')
                ->orderBy('id', 'desc')
                ->first();

            if ($latestRecord) {
                $latestIncrement = intval(substr($latestRecord->id, strlen($prefix . $dateTime)));
            } else {
                $latestIncrement = 0;
            }

            $newIncrement = $latestIncrement + 1;

            // Build the custom ID
            $customId = $prefix . $dateTime . str_pad($newIncrement, 3, '0', STR_PAD_LEFT);

            $model->id = $customId;
        });
    }
    public function userReservation(){
        $userInfo = json_decode($this->attributes['otherinfo'] ?? '');
        if(!empty($this->attributes['offline_user_id'])) return $this->belongsTo(UserOffline::class, 'offline_user_id');
        else return $this->belongsTo(User::class, 'user_id')->withDefault([
            'avatar' => null,
            'first_name' => $userInfo->first_name ?? 'None',
            'last_name'=> $userInfo->last_name ?? 'None',
            'birthday' => $userInfo->birthday ?? 'None',
            'nationality' => $userInfo->nationality ?? 'None',
            'country' => $userInfo->country ?? 'None',
            'contact' => $userInfo->contact ?? 'None',
            'email' =>$userInfo->email ?? 'None',
            'valid_id' => null,
        ]);
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
    protected function otherinfo(): Attribute
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
        if($date1->timestamp === $date2->timestamp) return 1;
        else return (int)$date1->diffInDays($date2) + 1; // Calculate the number of days between the two dates
    }
    public function getNoDaysInToday()
    {
        $date1 = Carbon::parse($this->attributes['check_in']); // Convert $date1 to Carbon object
        if(Carbon::now('Asia/Manila')->format('Y-m-d') === Carbon::parse($this->attributes['check_in'])->format('Y-m-d')) $date1 = Carbon::now();
        $date2 = Carbon::parse($this->attributes['check_out']); // Convert $date2 to Carbon object
        return (int)$date1->diffInDays($date2); // Calculate the number of days between the two dates
    }
    public function countSenior()
    {
        $num = false;
        $transaction = json_decode($this->attributes['transaction']);
        if(isset($transaction->payment->discountPerson)){
            $num = $transaction->payment->discountPerson;
        }
        return $num;
    }
    public function counrTour(){
        $c = 0;
        if(!empty($this->attributes['transaction'])){
            $trans = json_decode($this->attributes['transaction'], true);
            foreach($trans as $key => $item){
                if (strpos($key, 'tm') !== false || strpos($key, 'TA') !== false) {
                    $c++;
                }
            }
        }

        return $c;
    }
    public function getTotal($tourUsed = false)
    {
        $total = 0;
            // $transaction = json_decode($this->attributes['transaction'], true); 
        if($tourUsed) $total += $this->getTourTotal(true);
        else $total += $this->getTourTotal();
        $total += $this->getAddonTotal();
        $total += $this->getRoomAmount();
    
        return $total;
    }
    public function getServiceTotal($checkin = false)
    {
        $total = 0;
        if(!$checkin) $total += $this->getTourTotal();
        else $total += $this->getTourTotal(true);
        $total += $this->getAddonTotal();
        return $total;
       
    }
    public function getTourTotal($marked = false)
    {
        $total = 0;
        if(!empty($this->attributes['transaction'])) {
            foreach(json_decode($this->attributes['transaction'], true) as $key => $item) {
                if(strpos($key, 'tm') !== false) {
                    if($marked){
                        if($item['used'] == true) $total += (double)$item['amount'];
                    }
                    else $total += (double)$item['amount'];
                }
                if(strpos($key, 'TA') !== false) {
                    foreach($item as $key => $TA) {
                        if($marked){
                            if($TA['used'] == true) $total += (double)$TA['amount'];
                        }
                        else $total += (double)$TA['amount'];
                    }
                }
            }
            return $total;
        }
        else return $total;
    }
    public function getAddonTotal()
    {
        $total = 0;
        if(!empty($this->attributes['transaction'])) {
            foreach(json_decode($this->attributes['transaction'], true) as $key => $item) {
                if(strpos($key, 'OA') !== false) {
                    foreach($item as $key => $OA) $total += (double)$OA['amount'];
                }
            }
            return $total;
        }
        else return $total;
    }
    public function getRoomAmount($origAmount = false, $getPerson = false)
    {
        $amount = 0;
        if(!empty($this->attributes['transaction'])) {
            foreach(json_decode($this->attributes['transaction'], true) as $key => $item) {
                if(strpos($key, 'rid') !== false) {
                    if($getPerson){
                        $amount += (double)$item['person'];
                    }
                    else{
                        if($origAmount && isset($item['orig_amount'])) $amount += (double)$item['orig_amount'];
                        else $amount += (double)$item['amount'];
                    }

                }
            }
            return $amount;
        }
        else return $amount;
    }
    public static function discounted($amount = 0, $rate = 20){

        $total = (double)$amount;
        if($amount !== 0){
            $discounted = $rate / 100;
            $discounted = (double)($total * $discounted);
            $discounted = (double)($total - $discounted);
            $total = $discounted;
        }
        return $total;
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
            $paymentCustomer += $this->downpayment();
            $paymentCustomer +=  $this->checkInPayment();
            $paymentCustomer +=  $this->checkOutPayment();
        }
        if($this->getTotal() > $paymentCustomer){
            return abs($this->getTotal() - $paymentCustomer);
        }
        else return 0;
    }
    public function refund()
    {
        $refund = 0;
        $paymentCustomer = 0;
        $allPaid = json_decode($this->attributes['transaction'], true); 
        if(isset($allPaid['payment'])){
            $paymentCustomer += $allPaid['payment']['cinpay'] ?? 0;
            $paymentCustomer += $allPaid['payment']['downpayment'] ?? 0;
        }
        if($this->getTotal() < $paymentCustomer){
            $refund = abs($this->getTotal() - $paymentCustomer);
        }
        return $refund;
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
