<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomRate;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EditReservationController extends Controller
{
    private function roomAssign(array $rooms, Reservation $reservation, $validated, bool $forceAssign = false, bool $changeAssign = false){
        $roomCustomer = [];
        $reservationPax = 0;

        if($forceAssign){
            foreach($rooms as $room_id => $newPax){
                $reservationPax += (int)$newPax;
                $room = Room::find($room_id);
                $roomCustomer[$room_id] = $newPax;
            }
        }
        else{
            if(Room::checkAllAvailable()){
                foreach($rooms as $room_id => $newPax){
                    $reservationPax += (int)$newPax;
                    $room = Room::find($room_id);
                    if($newPax > $room->room->max_occupancy) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot choose due invalid guest ('.$newPax.' pax) and Room Capacity ('.$room->room->max_occupancy.' capacity)')->withInput($validated);
                    if($newPax > $room->getVacantPax() && $reservationPax <= $room->getVacantPax()) return back()->with('error', 'Room No. ' . $room->room_no. ' are only '.$room->getVacantPax().' pax to reserved and your guest ('.$reservationPax.' pax)')->withInput($validated);
                    $roomCustomer[$room_id] = $newPax;
                }
            }
            else{
                $r_lists = Reservation::where(function ($query) use ($reservation) {
                    $query->whereBetween('check_in', [$reservation->check_in, $reservation->check_out])
                          ->orWhereBetween('check_out', [$reservation->check_in, $reservation->check_out])
                          ->orWhere(function ($query) use ($reservation) {
                              $query->where('check_in', '<=', $reservation->check_in)
                                    ->where('check_out', '>=', $reservation->check_out);
                          });
                })->where('id', '!=', $reservation->id)->pluck('id');
    
                foreach($rooms as $room_id => $newPax){
                    $reservationPax += (int)$newPax;
                    $count_paxes = 0;
                    foreach($r_lists as $r_list){
                        $rooms = Room::whereRaw("JSON_KEYS(customer) LIKE ?", ['%"' . $r_list . '"%'])->where('id', $room_id)->get();
                        foreach($rooms as $room) $count_paxes += $room->customer[$r_list];
                    }
                    // dd($count_paxes);
                    $room = Room::find($room_id);
    
                    if($count_paxes > $room->room->max_occupancy) return back()->with('error', 'Room No. ' . $room->room_no. ' cannot proceed due not Available based on guest ('.$newPax.' pax) on '.Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y'))->withInput($validated);
            
                    if($count_paxes > $reservationPax && $reservationPax < $count_paxes)  return back()->with('error', 'Room No. ' . $room->room_no. ' cannot proceed due invalid guest between customer ('.$newPax.' pax) and vacant guest ('.$count_paxes.' pax) on '.Carbon::createFromFormat('Y-m-d', $reservation->check_in)->format('F j, Y'))->withInput($validated);
    
                    $roomCustomer[$room_id] = $newPax;
                }
            }
        }
        if($changeAssign){
            foreach(Room::all() as $value){
                $value->removeCustomer($reservation->id);
            }
        }
        return $roomCustomer; 
    }
    public function information($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $rooms = Room::all();
        $rate = RoomRate::all();
        $rateID = null;
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'rid') !== false)  $rateID= explode('rid', $key)[1];
        }
        $roomReserved = [];
        $r_lists = Reservation::where(function ($query) use ($reservation) {
            $query->whereBetween('check_in', [$reservation->check_in, $reservation->check_out])
                  ->orWhereBetween('check_out', [$reservation->check_in, $reservation->check_out])
                  ->orWhere(function ($query) use ($reservation) {
                      $query->where('check_in', '<=', $reservation->check_in)
                            ->where('check_out', '>=', $reservation->check_out);
                  });
        })->where('id', '!=', $reservation->id)->pluck('id');
        
        foreach($rooms as $key => $room){
            $count_paxes = 0;
            foreach($r_lists as $r_list){
                $rs= Room::whereRaw("JSON_KEYS(customer) LIKE ?", ['%"' . $r_list . '"%'])->where('id', $room->id)->get();
                foreach($rs as $room) $count_paxes += $room->customer[$r_list];
            }
            if($count_paxes >= $room->room->max_occupancy) {
                $roomReserved[] = $room->id;
            }

        }
        return view('system.reservation.edit.information', ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rate, 'rateid' => $rateID, 'reserved' => $roomReserved]);
    }
    public function updateInfo(Request $request, $id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $validate = Validator::make($request->all(), [
            'age' => ['required', 'numeric'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after_or_equal:'.$request['check_in']],
            'accommodation_type' => ['required'],
            'pax' => ['required', 'numeric'],
            'payment_method' => ['required'],
            'status' => ['required'],
            'force' => Rule::when(isset($request['force']), ['required']),
            'passcode' => Rule::when(isset($request['passcode']) && $reservation->pax == $request['pax'], ['required', 'digits:4']),
            'room_rate' => Rule::when($reservation->pax != $request['pax'], ['required']),
            'room_pax' => Rule::when($reservation->pax != $request['pax'], ['required']),
        ], [
            'required' => 'Required (:attribute)',
        ]);
        
        if($validate->fails()) return back()->with('error', $validate->errors()->all());
        $validate = $validate->validate();

        if($validate['pax'] != $reservation->pax){
            $rp = 0;
            foreach($validate['room_pax'] as $newPax){
                $rp += (int)$newPax;
                if($rp > $validate['pax'] && $rp < $validate['pax']) return back()->with('error', 'Guest you choose ('.$rp.' pax) does not match on Customer Guest ('.$validate['pax'].' pax)')->withInput($validate);
            }

            $transaction = $reservation->transaction;
            $roomCustomer = $this->roomAssign($validate['room_pax'], $reservation, $validate, ($request['force'] === 'on' ? true : false), true);
            // dd($roomCustomer);

            if(!is_array($roomCustomer)) return $roomCustomer;
            foreach(Room::all() as $room) $room->removeCustomer($reservation->id);
            foreach($roomCustomer as $roomid => $pax){
                $room = Room::find($roomid);
                $room->addCustomer($reservation->id, $pax);
            }
            $rate = RoomRate::find(decrypt($validate['room_rate']));
            foreach($transaction ?? [] as $key => $item){
                if (strpos($key, 'rid') !== false ) unset($transaction[$key]);
            }
            if($rate){
                $transaction['rid'.$rate->id]['title'] = $rate->name;
                $transaction['rid'.$rate->id]['price'] = $rate->price;
                $transaction['rid'.$rate->id]['amount'] = $rate->price * $reservation->getNoDays();
            }
            unset($validate['room_pax'], $validate['passcode'], $validate['room_rate']);
            if(isset($validate['force'])) unset($validate['force']);
            $validate['roomid'] = array_keys($roomCustomer);
            $validate['roomrateid'] = $rate->id;
            
            $updated = $reservation->update($validate);
        }
        else{
            if(!Hash::check($validate['passcode'], auth('system')->user()->passcode)) return back()->with('error', 'Invalid Passcode');
            unset($validate['room_pax'], $validate['room_rate'], $validate['passcode']);
            $updated = $reservation->update($validate);
        }
        if($updated) return redirect()->route('system.reservation.show', $id)->with('success', $reservation->userReservation->name() . ' Information was Updated');
    }
    public function services($id){
        $reservation = Reservation::findOrFail(decrypt($id));
        $count = 0;
        foreach($reservation->transaction ?? [] as $transKey => $item){
            if (strpos($transKey, 'tm') !== false && $reservation->accommodation_type != 'Room Only') {
                $tour_menu[$count]['id'] = $transKey;
                $tour_menu[$count]['title'] = $item['title'];
                $tour_menu[$count]['price'] = $item['price'];
                $tour_menu[$count]['amount'] = $item['amount'];
            }
            if (strpos($transKey, 'TA') !== false && is_array($item)) {
                foreach($item as $key => $tourAddons){
                    $tour_addons[$count]['id'] = $transKey;
                    $tour_addons[$count]['title'] = $tourAddons['title'];
                    $tour_addons[$count]['price'] = $tourAddons['price'];
                    $tour_addons[$count]['amount'] = $tourAddons['amount'];
                }
            }
            if (strpos($transKey, 'OA') !== false && is_array($item)) {
                foreach($item as $key => $tourAddons){
                    $other_addons[$count]['id'] = $transKey;
                    $other_addons[$count]['title'] = $tourAddons['title'];
                    $other_addons[$count]['pcs'] = $tourAddons['pcs'];
                    $other_addons[$count]['price'] = $tourAddons['price'];
                    $other_addons[$count]['amount'] = $tourAddons['amount'];
                }
            }
            $count++;
        }
        unset($count);
        return view('system.reservation.edit.services', ['activeSb' => 'Reservation', 'r_list' => $reservation, 'tour_menu' => $tour_menu ?? [],  'tour_addons' => $tour_addons ?? [],  'other_addons' => $other_addons ?? [],]);
    }
    public function rooms($id){
        $id = decrypt($id);
        $reservation = Reservation::findOrFail($id);
        $rooms = Room::all();
        $rate = RoomRate::all();
        $rateID = null;
        $roomReserved = [];
        foreach($reservation->transaction ?? [] as $key => $item){
            if (strpos($key, 'rid') !== false)  $rateID= explode('rid', $key)[1];
        }
        $r_lists = Reservation::where(function ($query) use ($reservation) {
            $query->whereBetween('check_in', [$reservation->check_in, $reservation->check_out])
                  ->orWhereBetween('check_out', [$reservation->check_in, $reservation->check_out])
                  ->orWhere(function ($query) use ($reservation) {
                      $query->where('check_in', '<=', $reservation->check_in)
                            ->where('check_out', '>=', $reservation->check_out);
                  });
        })->where('id', '!=', $reservation->id)->pluck('id');
        foreach($rooms as $key => $room){
            $count_paxes = 0;
            foreach($r_lists as $r_list){
                $rs= Room::whereRaw("JSON_KEYS(customer) LIKE ?", ['%"' . $r_list . '"%'])->where('id', $room->id)->get();
                foreach($rs as $room) $count_paxes += $room->customer[$r_list];
            }
            if($count_paxes >= $room->room->max_occupancy) {
                $roomReserved[] = $room->id;
            }

        }
        return view('system.reservation.edit.rooms',  ['activeSb' => 'Reservation', 'r_list' => $reservation, 'rooms' => $rooms, 'rates' => $rate, 'rateid' => $rateID, 'reserved' => $roomReserved]);
    }
    public function updateRooms(Request $request, $id){
        $roomCustomer = [];

        if($request->has('room_rate')) $request['room_rate'] = decrypt($request['room_rate']);
        $validated = $request->validate([
            'room_rate' => ['required', Rule::when($request->has('room_rate'), ['numeric'])],
            'passcode' =>  ['required', 'numeric', 'digits:4'],
        ]);
        
        $system_user = auth('system')->user();
        $reservation = Reservation::findOrFail(decrypt($id));
        if(!($reservation->status >= 2 && $reservation->status <= 3)) abort(404);
        if(!Hash::check($validated['passcode'], $system_user->passcode)) return back()->with('error', 'Invalid Passcode');
        if(empty($request['room_pax'])) return back()->with('error', 'Required to choose rooms')->withInput($request['room_pax']);
        else $validated['room_pax'] = $request['room_pax'];

        $rp=0;
        foreach($validated['room_pax'] as $newPax){
            $rp += (int)$newPax;
            if($rp > $reservation->pax && $rp < $reservation->pax) return back()->with('error', 'Guest you choose ('.$rp.' pax) does not match on Customer Guest ('.$reservation->pax.' pax)')->withInput($validated);
        }

        if(isset($request['force'])) $roomCustomer = $this->roomAssign($validated['room_pax'], $reservation, $validated, true, true);
        else $roomCustomer = $this->roomAssign($validated['room_pax'], $reservation, $validated, false, true);

        if(!is_array($roomCustomer)){
            return $roomCustomer;
        }
        foreach($roomCustomer as $key => $pax){
            $room = Room::find($key);
            if($room) $room->addCustomer($reservation->id, $pax);
        }
        $updated = $reservation->update([
            'roomid' => array_keys($roomCustomer),
        ]);
        if($updated){
            $details = [
                'name' => $reservation->userReservation->name(),
                'title' => 'Reservation Reschedule',
                'body' => 'Your Request are now approved. '
            ];
            // Mail::to(env('SAMPLE_EMAIL') ?? $reservation->userReservation->email)->queue(new ReservationMail($details, 'reservation.mail', $details['title']));
            return redirect()->route('system.reservation.show', encrypt($reservation->id))->with('success', 'Change Room Assign of '.$reservation->userReservation->name().' was updated');
        }
        dd($roomCustomer);
    }
}
