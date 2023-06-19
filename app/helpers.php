<?php

use App\Models\Room;
use Illuminate\Support\Facades\Storage;

function deleteFile($filename){
    if (Storage::disk('public')->exists($filename)) {
        Storage::disk('public')->delete($filename);
    }
}
// Reboot the number of room
function refreshRoomNumber(){
    // Update to blank
    $rooms = Room::all(); // Retrieve all the rooms
    if($rooms){
        $newRoomNo = 1;
        foreach ($rooms as $room) {
            // Perform your logic to update the room_no field here
        
            $room->room_no = $newRoomNo;
            $room->save(); // Save the updated room_no value
            $newRoomNo++;
        }
    }
}

