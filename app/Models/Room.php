<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


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
    public function checkAvailability()
    {
        $countOccupancy = 0;
        // Check if Availability All
        if(isset($this->attributes['customer'])){
            foreach (json_decode($this->attributes['customer'], true) as $key => $item) {
                $countOccupancy += (int)$item;
                if($this->room->max_occupancy <= $countOccupancy){
                    $this->update(['availability' => true]);
                    return false;
                    break;
                }
            }
        }
        else{
            return true;
        } 
        unset($countOccupancy);
        return false;
    }
    public function getAllPax()
    {
        $countPax = 0;
        if(isset($this->attributes['customer'])){
            foreach (json_decode($this->attributes['customer'], true) as $key => $item) {
                $countPax += $item;
            }
        }
        return $countPax;
    }
    public function getVacantPax()
    {
        $countPax = 0;
        if(isset($this->attributes['customer'])){
            foreach (json_decode($this->attributes['customer'], true) as $key => $item) {
                $countPax += $item;
            }
            $vacant = abs($countPax - $this->room->max_occupancy);
        }
        return $vacant ?? 0;
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

}
