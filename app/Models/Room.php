<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Date;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_no',
        'availability',
        'customer',
    ];

    public function room(){
        return $this->belongsTo(RoomList::class, 'roomid');
    }
    protected function customer(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    } 
    public function checkAvailability(){
        $countOccupancy = 0;
        // Check if Availability All
        $isFull = false;
        if(isset($this->attributes['customer'])){
            foreach (json_decode($this->attributes['customer'], true) as $key => $item) {
                $countOccupancy += (int)$item;
                if($this->room->max_occupancy === $countOccupancy){
                    $isFull = true;
                }
            }
        }
        if($this->attributes['availability'] !== $isFull ) $this->update(['availability' => $isFull]);

        unset($countOccupancy);
        return $isFull;
    }
    public function getAllPax($check_in = null, $check_out = null, $id = null){
        $countPax = 0;
        if(isset($check_in) && isset($check_out)){
            $r_lists = Reservation::where(function ($query) use ($check_in, $check_out) {
                $query->whereBetween('check_in', [$check_in, $check_out])
                      ->orWhereBetween('check_out', [$check_in, $check_out])
                      ->orWhere(function ($query) use ($check_in, $check_out) {
                          $query->where('check_in', '<=', $check_in)
                                ->where('check_out', '>=', $check_out);
                      });
            })->whereBetween('status', [1, 2]);
            if(isset($id)) $r_lists = $r_lists->where('id', '!=', $id)->pluck('id');
            else $r_lists = $r_lists->pluck('id');

            if(isset($this->attributes['customer'])){
                foreach($r_lists as $list){
                    $customer = json_decode($this->attributes['customer'], true);
                    if(array_key_exists($list, $customer)) $countPax += $customer[$list];
                }
            }
           
        }
        if($countPax >= $this->room->max_occupancy) $countPax = $this->room->max_occupancy;

        return $countPax;
    }
    public function getVacantPax($check_in = null, $check_out = null, $id = null){
        $countPax = 0;
        $vacant = $this->room->max_occupancy;
        if(isset($check_in) && isset($check_out)){
            $r_lists = Reservation::where(function ($query) use ($check_in, $check_out) {
                $query->whereBetween('check_in', [$check_in, $check_out])
                      ->orWhereBetween('check_out', [$check_in, $check_out])
                      ->orWhere(function ($query) use ($check_in, $check_out) {
                          $query->where('check_in', '<=', $check_in)
                                ->where('check_out', '>=', $check_out);
                      });
            })->whereBetween('status', [1, 2]);
            if(isset($id)) $r_lists = $r_lists->where('id', '!=', $id)->pluck('id');
            else $r_lists = $r_lists->pluck('id');
            if(isset($this->attributes['customer'])){
                foreach($r_lists as $list){
                    $customer = json_decode($this->attributes['customer'], true);
                    if(array_key_exists($list, $customer)) $countPax += $customer[$list];
                }
            }
            if($countPax < $vacant) $vacant = abs($countPax - $vacant);
            else $vacant = 0;
        }
        return $vacant;
    }
    public function removeCustomer($id){
        $customer = json_decode($this->attributes['customer'], true);
        if(isset($customer) && isset($customer[$id])){
            unset($customer[$id]);
            $this->update(['customer' => $customer]);
        }
        $this->checkAvailability();

    }
    public function addCustomer($id, $pax){
        $customer = [];
        if(isset($this->attributes['customer'])){
            $customer = json_decode($this->attributes['customer'], true);
            $customer[$id] = $pax;
        }
        else{
            $customer[$id] = $pax;
        }
        $this->update(['customer' => $customer]);
        
        $this->checkAvailability();

    }
    public static function checkAllAvailable(){
        $isNotFull = true;
        $rooms = self::all(); // Get all Room Info
        $countAllVacant = 0;
        $countMaxCapacity = 0;
        
        foreach($rooms as $room){
            $countAllVacant += $room->getVacantPax();
            $countMaxCapacity += $room->room->max_occupancy;
            $isNotFull = !$room->checkAvailability();
        }
        $isNotFull = ($countMaxCapacity >= $countAllVacant && $isNotFull);
        return $isNotFull;
    }

}
